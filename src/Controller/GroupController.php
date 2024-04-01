<?php

namespace App\Controller;

use App\DTO\Group\HistoryDTO;
use App\DTO\Group\ListDTO;
use App\DTO\Group\EntityDTO;
use App\Enum\FilterTypeEnum;
use App\Exception\GroupException;
use App\Exception\RecipientException;
use App\Service\NormalizerService;
use App\Service\GroupService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;
use Throwable;

/**
 * Контроллер для обработки запросов на сохранение и получение данных групп
 */
#[AsController]
class GroupController extends JsonRpcController
{
    /**
     * Конструктор
     *
     * @param LoggerInterface   $logger     Логер
     * @param NormalizerService $normalizer Нормализатор данных сущностей
     * @param GroupService      $service    Сервис данных групп
     */
    public function __construct(
        private readonly LoggerInterface   $logger,
        private readonly NormalizerService $normalizer,
        private readonly GroupService      $service
    )
    {
    }

    /**
     * Точка входа для обработки данных групп
     *
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('/group', name: 'email_group', methods: ['POST'])]
    public function index(Request $request): JsonResponse
    {
        return parent::index($request);
    }

    /**
     * Сохранить или изменить группу
     *
     * @param EntityDTO $params DTO параметров данных группы
     *
     * @return int|null
     * @throws Throwable
     * @throws GroupException
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
     * Пометить группу как удаленную
     *
     * @param int $id ID группы
     *
     * @return int|null
     * @throws Throwable
     * @throws GroupException
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

    /**
     * Получить данные группы
     *
     * @param int $id ID группы
     *
     * @return array
     * @throws Throwable
     */
    public function entity(int $id): array
    {
        try {
            $result = $this->service->group($id);
        } catch (Throwable $err) {
            $this->logger->error($err->getMessage(), ['Exception' => $err]);
            throw $err;
        }
        return $result ?: [];
    }

    /**
     * Получить список групп
     *
     * @param ListDTO $params DTO параметров фильтрации списка
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
     * Получить доступные фильтры для выборки групп получателей
     *
     * @param string|null $type Тип фильтров
     * @return array
     * @throws GroupException
     */
    public function filters(?string $type = null): array
    {
        return match ((null === $type) ? FilterTypeEnum::ENTITY : FilterTypeEnum::tryFrom($type)) {
            FilterTypeEnum::HISTORY => $this->service->getHistoryFilters(),
            FilterTypeEnum::ENTITY => $this->service->getEntityFilters(),
            default => throw new GroupException('Запрошен некорректный тип фильтров', GroupException::BAD_VALUES)
        };
    }

    /**
     * Получить данные получателей связанных с группой
     *
     * @param int $id ID группы получателей
     *
     * @return array
     * @throws Throwable
     * @throws GroupException
     */
    public function recipients(int $id): array
    {
        try {
            $result = $this->service->recipients($id);
            $result = $this->normalizer->normalize($result);
        } catch (Throwable $err) {
            $this->logger->error($err->getMessage(), ['Exception' => $err]);
            throw $err;
        }
        return $result ?: [];
    }

    /** Получить историю изменения групп получателей
     *
     * @param HistoryDTO $params DTO для получения истории изменений групп
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