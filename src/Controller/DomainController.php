<?php

namespace App\Controller;

use App\DTO\Domain\HistoryDTO;
use App\DTO\Domain\ListDTO;
use App\DTO\Domain\ParamsDTO;
use App\Exception\DomainException;
use App\Service\DomainService;
use App\Service\NormalizerService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;
use Throwable;

/**
 * Контроллер для обработки запросов на сохранение и получение доменов
 */
#[AsController]
class DomainController extends JsonRpcController
{
    /**
     * Конструктор
     *
     * @param LoggerInterface   $logger     Логер
     * @param NormalizerService $normalizer Нормализатор данных сущностей
     * @param DomainService     $service    Сервис данных доменов
     */
    public function __construct(
        private readonly LoggerInterface   $logger,
        private readonly DomainService     $service,
        private readonly NormalizerService $normalizer
    )
    {
    }

    /**
     * Точка входа для обработки данных доменов
     *
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('/domain', name: 'email_domain', methods: ['POST'])]
    public function index(Request $request): JsonResponse
    {
        return parent::index($request);
    }

    /**
     * Сохранить данные домена
     *
     * @param ParamsDTO $params DTO параметров домена
     *
     * @return int|null
     * @throws Throwable
     * @throws DomainException
     */
    public function save(ParamsDTO $params): ?int
    {
        try {
            $userUuid = Uuid::fromString($this->getRequestHeaders('x-api-key'));
            $result = $this->service->save($params, $userUuid)->getId();
        } catch (\Throwable $err) {
            $this->logger->error($err->getMessage(), ['Exception' => $err]);
            throw $err;
        }
        return $result;
    }

    /**
     * Получить список доменов
     *
     * @param ListDTO $params DTO параметров фильтрации доменов
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
     * Получить данные домена
     *
     * @param int $id ID домена
     *
     * @return array
     * @throws Throwable
     */
    public function entity(int $id): array
    {
        try {
            $result = $this->service->entity($id);
        } catch (Throwable $err) {
            $this->logger->error($err->getMessage(), ['Exception' => $err]);
            throw $err;
        }
        return $result;
    }

    /**
     * Получить доступные фильтры для выборки доменов
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
     * Получить историю изменений доменов
     *
     * @param HistoryDTO $params DTO параметров фильтрации истории
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
     * Получить доступные фильтры для истории
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