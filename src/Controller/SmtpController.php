<?php

namespace App\Controller;

use App\DTO\Smtp\HistoryDTO;
use App\DTO\Smtp\ListDTO;
use App\DTO\Smtp\EntityDTO;
use App\Enum\FilterTypeEnum;
use App\Exception\GroupException;
use App\Exception\SmtpException;
use App\Service\NormalizerService;
use App\Service\SmtpService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;
use Throwable;


/**
 * Контроллер для обработки запросов на получение и сохранение данных SMTP-аккаунтов
 */
#[AsController]
class SmtpController extends JsonRpcController
{
    /**
     * Конструктор
     *
     * @param LoggerInterface   $logger     Логер
     * @param NormalizerService $normalizer Нормализатор данных сущностей
     * @param SmtpService       $service    Сервис данных SMTP-аккаунтов
     */
    public function __construct(
        private readonly LoggerInterface   $logger,
        private readonly NormalizerService $normalizer,
        private readonly SmtpService       $service
    )
    {
    }

    /**
     * Точка входа для обработки данных SMTP-аккаунтов
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    #[Route('/smtp', name: 'email_smtp', methods: ['POST'])]
    public function index(Request $request): JsonResponse
    {
        return parent::index($request);
    }

    /**
     * Сохранить данные SMTP-аккаунтов
     *
     * @param EntityDTO $params DTO параметров SMTP-аккаунтов
     *
     * @return int|null
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
     * Получить список SMTP-аккаунтов с заданными параметрами
     *
     * @param ListDTO $params DTO параметров фильтрации SMTP-аккаунтов
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
     * Получить данные SMTP-аккаунта
     *
     * @param int $id Id SMTP-аккаунта
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
        return $result ?: [];
    }

    /**
     * Пометить SMTP-аккаунт как удаленный
     *
     * @param int $id ID SMTP-аккаунта
     *
     * @return int|null
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

    /**
     * Получить доступные фильтры для выборки SMTP-аккаунтов
     *
     * @param string|null $type Тип фильтров
     * @return array
     * @throws SmtpException
     */
    public function filters(?string $type = null): array
    {
        return match ((null === $type) ? FilterTypeEnum::ENTITY : FilterTypeEnum::tryFrom($type)) {
            FilterTypeEnum::HISTORY => $this->service->getHistoryFilters(),
            FilterTypeEnum::ENTITY => $this->service->getEntityFilters(),
            default => throw new SmtpException('Запрошен некорректный тип фильтров', SmtpException::BAD_VALUES)
        };
    }

    /** Получить историю изменения SMTP-аккаунтов
     *
     * @param HistoryDTO $params DTO для получения истории изменений SMTP-аккаунтов
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