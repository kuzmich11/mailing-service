<?php

namespace App\Controller;

use App\DTO\Letter\EntityDTO;
use App\DTO\Letter\HistoryDTO;
use App\DTO\Letter\ListDTO;
use App\Enum\FilterTypeEnum;
use App\Exception\LetterException;
use JsonRpcBundle\JsonRpcController;
use App\Service\LetterService;
use App\Service\NormalizerService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;
use Throwable;

/**
 * Контроллер для обработки запросов на сохранение и получение писем
 */
#[AsController]
class LetterController extends JsonRpcController
{
    /**
     * Конструктор
     * @param LoggerInterface   $logger     Логер
     * @param LetterService     $service    Сервис данных писем
     * @param NormalizerService $normalizer Нормализатор данных сущностей
     */
    public function __construct(
        private readonly LoggerInterface   $logger,
        private readonly LetterService     $service,
        private readonly NormalizerService $normalizer
    )
    {
    }

    /**
     * Точка входа для обработки данных писем
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    #[Route('/letter', name: 'email_letter', methods: ['POST'])]
    public function index(Request $request): JsonResponse
    {
        return parent::index($request);
    }

    /**
     * Получить список писем с заданными параметрами
     *
     * @param ListDTO $params DTO параметров фильтрации писем
     *
     * @return array
     * @throws Throwable
     */
    public function list(ListDTO $params): array
    {
        try {
            $result = $this->service->list($params);
            return $this->normalizer->normalize($result);
        } catch (Throwable $err) {
            $this->logger->error($err->getMessage(), ['Exception' => $err]);
            throw $err;
        }
    }

    /**
     * Сохранить данные письма
     *
     * @param EntityDTO $params DTO параметров письма
     *
     * @return int|null
     * @throws LetterException
     * @throws Throwable
     */
    public function save(EntityDTO $params): ?int
    {
        try {
            $userUuid = Uuid::fromString($this->getRequestHeaders('x-api-key'));
            return $this->service->save($params, $userUuid);
        } catch (Throwable $err) {
            $this->logger->error($err->getMessage(), ['Exception' => $err]);
            throw $err;
        }
    }

    /**
     * Пометить письмо как удаленное
     *
     * @param int $id ID письма
     *
     * @return int|null
     * @throws LetterException
     * @throws Throwable
     */
    public function delete(int $id): ?int
    {
        try {
            $userUuid = Uuid::fromString($this->getRequestHeaders('x-api-key'));
            return $this->service->delete($id, $userUuid);
        } catch (Throwable $err) {
            $this->logger->error($err->getMessage(), ['Exception' => $err]);
            throw $err;
        }
    }

    /**
     * Получить доступные фильтры для выборки SMTP-аккаунтов
     *
     * @param string|null $type Тип фильтров
     * @return array
     * @throws LetterException
     */
    public function filters(?string $type = null): array
    {
        return match ((null === $type) ? FilterTypeEnum::ENTITY : FilterTypeEnum::tryFrom($type)) {
            FilterTypeEnum::HISTORY => $this->service->getHistoryFilters(),
            FilterTypeEnum::ENTITY => $this->service->getEntityFilters(),
            default => throw new LetterException('Запрошен некорректный тип фильтров', LetterException::BAD_VALUES)
        };
    }

    /**
     * Получить данные письма
     *
     * @param int $id Id письма
     *
     * @return array
     * @throws Throwable
     */
    public function entity(int $id): array
    {
        try {
            return $this->service->entity($id)?->toArray() ?: [];
        } catch (Throwable $err) {
            $this->logger->error($err->getMessage(), ['Exception' => $err]);
            throw $err;
        }
    }

    /**
     * Поместить письмо в очередь для отправки
     *
     * @param int $id Идентификатор письма для рассылки
     *
     * @return array
     * @throws LetterException
     * @throws Throwable
     */
    public function send(int $id): array
    {
        try {
            $userUuid = Uuid::fromString($this->getRequestHeaders('x-api-key'));
            return $this->service->send($id, $userUuid);
        } catch (\Throwable $err) {
            $this->logger->error($err->getMessage(), ['Exception' => $err]);
            throw $err;
        }
    }

    /** Получить историю изменения писем
     *
     * @param HistoryDTO $params DTO для получения истории изменений писем
     *
     * @return array
     * @throws Throwable
     */
    public function history(HistoryDTO $params): array
    {
        try {
            $result = $this->service->history($params);
            return $this->normalizer->normalize($result);
        } catch (Throwable $err) {
            $this->logger->error($err->getMessage(), ['Exception' => $err]);
            throw $err;
        }
    }
}