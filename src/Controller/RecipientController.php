<?php

namespace App\Controller;

use App\DTO\Recipient\HistoryDTO;
use App\DTO\Recipient\ListDTO;
use App\DTO\Recipient\ParamsDTO;
use App\Exception\RecipientException;
use App\Service\NormalizerService;
use App\Service\RecipientService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;
use Throwable;

/**
 * Контроллер для обработки запросов на сохранение и получение данных получателей
 */
#[AsController]
class RecipientController extends JsonRpcController
{
    /**
     * Конструктор
     *
     * @param LoggerInterface   $logger     Логер
     * @param NormalizerService $normalizer Нормализатор данных сущностей
     * @param RecipientService  $service    Сервис данных получателей
     */
    public function __construct(
        private readonly LoggerInterface   $logger,
        private readonly NormalizerService $normalizer,
        private readonly RecipientService  $service
    )
    {
    }

    /**
     * Точка входа для обработки данных получателей
     *
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('/recipient', name: 'email_recipient', methods: ['POST'])]
    public function index(Request $request): JsonResponse
    {
        return parent::index($request);
    }

    /**
     * Получить список получателей по заданным параметрам и доступные фильтры
     *
     * @param ListDTO $params DTO параметров фильтрации списка получателей
     *
     * @return array
     * @throws Throwable
     */
    public function list(ListDTO $params): array
    {
        try {
            $result = $this->service->list($params);
            $result = $this->normalizer->normalize($result);
        } catch (Throwable $err) {
            $this->logger->error($err->getMessage(), ['Exception' => $err]);
            throw $err;
        }
        return $result;
    }

    /**
     * Получить данные получателя
     *
     * @param int $id ID получателя
     *
     * @return array
     * @throws Throwable
     */
    public function entity(int $id): array
    {
        try {
            $result = $this->service->recipient($id);
        } catch (\Throwable $err) {
            $this->logger->error($err->getMessage(), ['Exception' => $err]);
            throw $err;
        }
        return $result ?: [];
    }

    /**
     * Получить доступные фильтры
     *
     * @return array
     * @throws Throwable
     */
    public function filters(): array
    {
        try {
            $result = $this->service->filters();
        } catch (Throwable $err) {
            $this->logger->error($err->getMessage(), ['Exception' => $err]);
            throw $err;
        }
        return $result;
    }

    /**
     * Сохранить данные получателя
     *
     * @param ParamsDTO $params DTO параметров получателей
     *
     * @return int|null
     * @throws RecipientException
     * @throws Throwable
     */
    public function save(ParamsDTO $params): ?int
    {
        try {
            $userUuid = Uuid::fromString($this->getRequestHeaders('x-api-key'));
            $result = $this->service->save($params, $userUuid);
        } catch (Throwable $err) {
            $this->logger->error($err->getMessage(), ['Exception' => $err]);
            throw $err;
        }
        return $result;
    }

    /**
     * Пометить получателя как удаленного
     *
     * @param int $id ID получателя
     *
     * @return int|null
     * @throws RecipientException
     * @throws Throwable
     */
    public function delete(int $id): ?int
    {
        try {
            $userUuid = Uuid::fromString($this->getRequestHeaders('x-api-key'));
            $result = $this->service->delete($id, $userUuid);
        } catch (Throwable $err) {
            $this->logger->error($err->getMessage(), ['Exception' => $err]);
            throw $err;
        }
        return $result;
    }


    /** Получить историю изменения получателей
     *
     * @param HistoryDTO $params DTO для получения истории изменений получателей
     *
     * @return array
     * @throws Throwable
     */
    public function history(HistoryDTO $params): array
    {
        try {
            $result = $this->service->history($params);
            $result = $this->normalizer->normalize($result);
        } catch (Throwable $err) {
            $this->logger->error($err->getMessage(), ['Exception' => $err]);
            throw $err;
        }
        return $result;
    }

    /**
     * Получить возможные фильтры для истории изменений
     *
     * @return array
     * @throws Throwable
     */
    public function historyFilters(): array
    {
        try {
            $result = $this->service->historyFilters();
        } catch (Throwable $err) {
            $this->logger->error($err->getMessage(), ['Exception' => $err]);
            throw $err;
        }
        return $result;
    }
}