<?php

namespace App\Service;

use App\Exception\NormalizeException;
use Psr\Log\LoggerInterface;

/**
 * Сервис нормализации сущностей
 */
class NormalizerService
{
    /**
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly LoggerInterface $logger,
    )
    {
    }

    /**
     * Нормализовать объект сущности
     *
     * @param array $data Массив объектов сущностей
     *
     * @return array
     * @throws NormalizeException
     */
    public function normalize(array $data): array
    {
        try {
            return array_map(fn($entity) => $entity->toArray(), $data);
        } catch (\Throwable $err) {
            $this->logger->error($err->getMessage(), ['Exception' => $err]);
            throw new NormalizeException('Не удалось нормализовать объект');
        }
    }
}