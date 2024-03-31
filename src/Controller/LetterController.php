<?php

namespace App\Controller;

use App\DTO\Letter\HistoryDTO;
use App\DTO\Letter\ListDTO;
use App\DTO\Letter\ParamsDTO;
use App\Exception\LetterException;
use App\Service\NormalizerService;
use App\Service\LetterService;
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
     * @param LoggerInterface   $logger        Логер
     * @param LetterService     $letterService Сервис данных писем
     * @param NormalizerService $normalizer    Нормализатор данных сущностей
     */
    public function __construct(
        private readonly LoggerInterface   $logger,
        private readonly LetterService     $letterService,
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
            $result = $this->letterService->list($params);
            return $this->normalizer->normalize($result);
        } catch (Throwable $err) {
            $this->logger->error($err->getMessage(), ['Exception' => $err]);
            throw $err;
        }
    }

    /**
     * Сохранить данные письма
     *
     * @param ParamsDTO $params DTO параметров письма
     *
     * @return int|null
     * @throws LetterException
     * @throws Throwable
     */
    public function save(ParamsDTO $params): ?int
    {
        try {
            $userUuid = Uuid::fromString($this->getRequestHeaders('x-api-key'));
            return $this->letterService->save($params, $userUuid);
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
            return $this->letterService->delete($id, $userUuid);
        } catch (Throwable $err) {
            $this->logger->error($err->getMessage(), ['Exception' => $err]);
            throw $err;
        }
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
            return $this->letterService->filters();
        } catch (Throwable $err) {
            $this->logger->error($err->getMessage(), ['Exception' => $err]);
            throw $err;
        }
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
            return $this->letterService->entity($id)?->toArray() ?: [];
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
            return $this->letterService->send($id, $userUuid);
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
            $result = $this->letterService->history($params);
            return $this->normalizer->normalize($result);
        } catch (Throwable $err) {
            $this->logger->error($err->getMessage(), ['Exception' => $err]);
            throw $err;
        }
    }

    /**
     * Получить возможные фильтры для истории
     *
     * @return array
     * @throws Throwable
     */
    public function historyFilters(): array
    {
        try {
            return $this->letterService->historyFilters();
        } catch (Throwable $err) {
            $this->logger->error($err->getMessage(), ['Exception' => $err]);
            throw $err;
        }
    }
}