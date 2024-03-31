<?php

namespace App\Controller;

use App\DTO\File\EntityDTO;
use App\Exception\FileException;
use App\Service\FileService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

/**
 * Контроллер запросов для файлов(вложений)
 */
#[AsController]
class FileController extends JsonRpcController
{
    /**
     * Конструктор
     *
     * @param LoggerInterface $logger  Логер
     * @param FileService     $service Сервис обработки файлов
     */
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly FileService     $service
    )
    {
    }

    /**
     * Точка входа для получения и сохранения файлов(вложений)
     *
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('/file', name: 'email_file', methods: ['POST'])]
    public function index(Request $request): JsonResponse
    {
        return parent::index($request);
    }

    /**
     * Загрузить файл
     *
     * @param EntityDTO $params DTO параметров файлов
     *
     * @return int
     * @throws FileException
     */
    public function upload(EntityDTO $params): int
    {
        return $this->service->upload($params);
    }

    /**
     * Получить список файлов
     *
     * @return array
     * @throws Throwable
     */
    public function list(): array
    {
        try {
            return $this->service->list();
        } catch (Throwable $err) {
            $this->logger->error($err->getMessage(), ['Exception' => $err]);
            throw $err;
        }
    }

    /**
     * Получить файл
     *
     * @param int $id Идентификатор файла
     *
     * @return array
     * @throws Throwable
     */
    public function entity(int $id): array
    {
        try {
            return $this->service->entity($id) ?: [];
        } catch (Throwable $err) {
            $this->logger->error($err->getMessage(), ['Exception' => $err]);
            throw $err;
        }
    }

    /**
     * Удалить файл
     *
     * @param int $id Идентификатор файла
     *
     * @return int
     * @throws FileException
     */
    public function delete(int $id): int
    {
        return $this->service->delete($id);
    }
}