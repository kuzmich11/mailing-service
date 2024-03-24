<?php

namespace App\DTO\Caster;

use Spatie\DataTransferObject\Caster;

/**
 * "Кастер" (преобразователь) для значений \DateTimeInterface
 */
class DateTimeCaster implements Caster
{
    /**
     * @param array $types
     * @param string $datetimeType
     */
    public function __construct(
        private array $types,
        private string $datetimeType
    )
    {
        if (!is_subclass_of($this->datetimeType, 'DateTimeInterface')) {
            throw new \TypeError("Тип [{$this->datetimeType}] должен наследоваться от DateTimeInterface");
        }
    }

    /**
     * Метод преобразования
     *
     * @param mixed $value Полученное значение
     *
     * @return \DateTimeInterface|null
     */
    public function cast(mixed $value): ?\DateTimeInterface
    {
        if ($value instanceof $this->datetimeType) {
            return $value;
        }
        try {
            return new $this->datetimeType($value);
        }
        catch (\Throwable $error) {
            throw new \ValueError("Некорректное значение даты/времени: [{$value}]");
        }
    }
}