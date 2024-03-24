<?php

namespace App\DTO\Caster;

use Spatie\DataTransferObject\Caster;
use Symfony\Component\Uid\Uuid;

/**
 * "Кастер" (преобразователь) для UUID значений
 */
class UuidCaster implements Caster
{
    /**
     * @param array $types
     */
    public function __construct(
        private array $types
    )
    {
    }

    /**
     * Метод преобразования
     *
     * @param mixed $value Полученное значение
     * @return Uuid
     */
    public function cast(mixed $value): Uuid
    {
        if ($value instanceof Uuid) {
            return $value;
        }
        if (!Uuid::isValid($value)) {
            throw new \TypeError('Некорректное значение UUID');
        }
        return new Uuid($value);
    }
}