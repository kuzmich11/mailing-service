<?php

namespace App\Controller;

use App\Exception\JsonRpcException;
use Exception;
use ReflectionMethod;
use ReflectionUnionType;
use RuntimeException;
use Spatie\DataTransferObject\DataTransferObject;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Encoder\JsonDecode;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\UidNormalizer;
use Symfony\Component\Serializer\Serializer;
use Throwable;
use TypeError;

/**
 * Абстрактный класс JSON-RPC контроллера
 */
// TODO Внедрение зависимостей в вызываемых действиях
abstract class JsonRpcController extends AbstractController
{
    /** @var array Параметры запроса (значение params) */
    private array $requestParams;

    /** @var string Вызываемый метод (значение method) */
    private string $requestMethod;

    /** @var int Идентификатор запроса (значение id) */
    private int $requestId;

    /** @var array Заголовки запроса */
    private array $requestHeaders = [];

    /** @var Headers $headers Класс заголовков */
    protected readonly Headers $headers;

    /**
     * Основная точка входа конечной точки. Отсюда происходит вызов методов
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $this->headers = new Headers(['Access-Control-Allow-Origin' => '*', 'Access-Control-Allow-Headers' => 'Content-Type']);
        $decode = new JsonDecode();
        $response = ['jsonrpc' => '2.0'];
        $results = [];
        $this->resolveHeaders($request);
        $content = $request->getContent();
        if (empty($content)) {
            if ($request->isMethod('OPTIONS')) {
                return new JsonResponse('', 200, $this->headers->get());
            }
            return new JsonResponse(
                $this->responseError(new Exception('Invalid request JSON', Exception::PARSE_ERROR)),
                400,
                $this->headers->get()
            );
        }
        $contents = $decode->decode($content, JsonEncoder::FORMAT, ['json_decode_associative' => true]);
        $isBatch = !isset($contents['jsonrpc']);
        foreach ($isBatch ? $contents : [$contents] as $data) {
            try {
                $this->resolveRequest($data);
                if (isset($this->requestId)) {
                    $response['id'] = $this->requestId;
                }
                $result = $this->{$this->requestMethod}(...$this->resolveArguments());
                try {
                    $result = (new Serializer($this->getNormalizers(), []))->normalize($result);
                } catch (ExceptionInterface $e) {
                    throw new RuntimeException('Error normalize result to array', 0, $e);
                }
                $results[] = array_merge($response, ['result' => $result]);
            } catch (Throwable $e) {
                $results[] = array_merge($response, $this->responseError(
                    $e instanceof Exception ?
                        $e : new Exception('Internal error', Exception::INTERNAL_ERROR, $e)
                ));
            }
        }
        return new JsonResponse($isBatch ?  $results : $results[0], 200, $this->headers->get());
    }

    /**
     * Получение нормализаторов которые используются для обработки результата на выводе
     *
     * @return array Список нормализаторов для применения к результатам
     */
    protected function getNormalizers(): array
    {
        return [new DateTimeNormalizer([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d H:i:s']), new UidNormalizer(), new ObjectNormalizer()];
    }

    /**
     * Разбор заголовков запроса
     *
     * @param Request $request Объект запроса
     *
     * @return void
     */
    private function resolveHeaders(Request $request): void
    {
        foreach ($request->headers->getIterator() as $name => $values) {
            $this->requestHeaders[strtolower($name)] = (is_array($values) && count($values) == 1)
                ? current($values)
                : $values;
        }
    }

    /**
     * Получить заголовки запроса
     *
     * @param string $name Название заголовка (при отсутствии - вернуть все заголовки)
     *
     * @return array|string|null
     */
    protected function getRequestHeaders(string $name = ''): array|string|null
    {
        if (empty($name)) {
            return $this->requestHeaders;
        }
        $name = strtolower($name);
        return array_key_exists($name, $this->requestHeaders) ? $this->requestHeaders[$name] : null;
    }

    /**
     * Обработка запроса
     *
     * @throws Exception
     */
    private function resolveRequest(bool|string|null|array $data): void
    {
        try {
            $this->checkRequest($data);
            $this->requestParams = $data['params'] ?? [];
            $this->requestMethod = $data['method'];
            $this->requestId = $data['id'];
        } catch (NotEncodableValueException $e) {
            throw new JsonRpcException('Invalid request JSON', JsonRpcException::PARSE_ERROR, $e);
        }
    }

    /**
     * Обработка аргументов и получение списка для вызова запрошенного метода
     * Если аргументы метода заданы как DTO формирует эти объекты
     *
     * @return array Список аргументов в порядке следования в сигнатуре метода.
     *
     * @throws \ReflectionException
     * @throws Exception
     */
    protected function resolveArguments(): array
    {
        $reflection = new ReflectionMethod($this, $this->requestMethod);
        $args = [];
        $params = [];
        $methodParams = $reflection->getParameters();
        $requestParams = $this->requestParams;
        $onlyOneParam = $reflection->getNumberOfParameters() === 1;

        // обрабатываем список параметров
        foreach ($methodParams as $param) {
            $methodType = $param->getType();
            if ($methodType instanceof ReflectionUnionType) {
                $types = $methodType->getTypes();
                foreach ($types as $type) {
                    if ($type != DataTransferObject::class) {
                        $paramClass = $type->getName();
                        $params = [$param->getName() => $requestParams[$param->getName()] ?? $requestParams];
                        if (is_a($paramClass, DataTransferObject::class, true)) {
                            $params = [$param->getName() => !$onlyOneParam ? $requestParams[$param->getName()] : $requestParams];
                        }
                        break;
                    }
                }
            } elseif (str_ends_with($methodType, 'array') && $onlyOneParam) {
                $paramClass = $methodType->getName();
                $params = [$param->getName() => $requestParams];
                // Если один параметр и задан как DTO (или массив) трактуем все данные params запроса как этот DTO (или массив)
            } elseif (is_a($methodType?->getName(), DataTransferObject::class, true)) {
                $paramClass = $methodType?->getName();
                $params = [$param->getName() => !$onlyOneParam ? $requestParams[$param->getName()] : $requestParams];
            }else {
                if (array_key_exists($param->name, $requestParams)) {
                    $paramClass = $methodType?->getName();
                    $params = [$param->getName() => $requestParams[$param->getName()]];
                }
            }

            if (array_key_exists($param->name, $params)) {
                if (!empty($paramClass) && is_a($paramClass, DataTransferObject::class, true)) {
                    try {
                        $args[] = new $paramClass((array)$params[$param->name]);
                    } catch (TypeError $e) {
                        throw new Exception('Invalid request params', Exception::INVALID_PARAMS, $e);
                    }
                } else {
                    $args[] = $params[$param->getName()];
                }
            } elseif ($param->isDefaultValueAvailable()) {
                $args[] = $param->getDefaultValue();
            } else {
                throw new JsonRpcException('Invalid request params', JsonRpcException::INVALID_PARAMS);
            }
        }

        return $args;
    }

    /**
     * Проверяет запроса на корректность
     *
     * @param mixed $data Данные запроса
     *
     * @return void
     *
     * @throws Exception
     */
    private function checkRequest(mixed $data): void
    {
        // проверяем общую структуру
        if (empty($data) || !is_array($data)
            || count(array_diff(array_keys($data), ['jsonrpc', 'id', 'method', 'params']))
            || $data['jsonrpc'] !== '2.0'
        ) {
            throw new JsonRpcException('Invalid request', JsonRpcException::INVALID_REQUEST);
        }
        // проверяем метод
        if (!method_exists($this, $data['method'])) {
            throw new JsonRpcException('Method not found', JsonRpcException::METHOD_NOT_FOUND);
        }
    }

    /**
     * Получение отладочной информации
     *
     * @param Exception $e Исключение вызвавшее ошибку
     *
     * @return array
     */
    private function getDebugData(\Exception $e): array
    {
        if ($this->getParameter('kernel.environment') !== 'dev') {
            return [];
        }

        // В окружении разработки (dev) выводим данные по предыдущему Exception
        $data = [];
        $previousException = $e->getPrevious();
        if ($previousException) {
            $data['previousException'] = [
                'class' => get_class($previousException),
                'code' => $previousException->getCode(),
                'message' => $previousException->getMessage(),
                'file' => $previousException->getFile(),
                'line' => $previousException->getLine(),
            ];
        }

        // Если задана режим отладки, то выводим данные трассировки
        if ($this->getParameter('kernel.debug')) {
            $data['trace'] = ($previousException ?? $e)->getTrace();
        }

        return $data;
    }

    /**
     * Формирование ошибочного ответа
     *
     * @param \Throwable|\Exception $e Исключение с данными по ошибке
     *
     * @return array
     */
    private function responseError(Throwable|\Exception $e): array
    {
        $error = [
            'code' => $e->getCode(),
            'message' => $e->getMessage(),
        ];

        $debugData = $this->getDebugData($e);
        if ($debugData) {
            $error['data'] = $debugData;
        }

        return ['error' => $error];
    }

}