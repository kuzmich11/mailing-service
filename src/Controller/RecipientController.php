<?php

namespace App\Controller;

use App\DTO\Recipient\HistoryDTO;
use App\DTO\Recipient\ListDTO;
use App\DTO\Recipient\EntityDTO;
use App\Enum\FilterTypeEnum;
use App\Exception\DomainException;
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
            return $this->normalizer->normalize($result);
        } catch (Throwable $err) {
            $this->logger->error($err->getMessage(), ['Exception' => $err]);
            throw $err;
        }
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
     * Получить доступные фильтры для выборки получателей
     *
     * @param string|null $type Тип фильтров
     * @return array
     * @throws RecipientException
     */
    public function filters(?string $type = null): array
    {
        return match ((null === $type) ? FilterTypeEnum::ENTITY : FilterTypeEnum::tryFrom($type)) {
            FilterTypeEnum::HISTORY => $this->service->getHistoryFilters(),
            FilterTypeEnum::ENTITY => $this->service->getEntityFilters(),
            default => throw new RecipientException('Запрошен некорректный тип фильтров', RecipientException::BAD_VALUES)
        };
    }

    /**
     * Сохранить данные получателя
     *
     * @param EntityDTO $params DTO параметров получателей
     *
     * @return int|null
     * @throws RecipientException
     * @throws Throwable
     */
    public function save(EntityDTO $params): ?int
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
}