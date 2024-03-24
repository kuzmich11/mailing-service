<?php

namespace App\DTO\Caster;

use Spatie\DataTransferObject\Caster;

/**
 * "Кастер" (преобразователь) для разных типов перечислений
 */
class EnumAllCaster implements Caster
{
    /**
     * @param array $types
     * @param string $enumType
     */
    public function __construct(
        private array $types,
        private string $enumType
    )
    {}

    /**
     * Метод преобразования
     *
     * @param mixed $value Полученное значение
     * @return mixed
     */
    public function cast(mixed $value): mixed
    {
        if ($value instanceof $this->enumType) {
            return $value;
        }

        try {
            if (is_subclass_of($this->enumType, 'BackedEnum')) {
                $castedValue = $this->enumType::tryFrom($value);
            }
        }
        catch (\Throwable) {}
        finally {
            if (!isset($castedValue)) {
                $definedCases = array_filter(array_unique([
                    "$this->enumType::$value",
                    "$this->enumType::" . mb_strtoupper($value),
                    "$this->enumType::" . mb_strtolower($value)
                ]), 'defined');
                if (!empty($definedCases)) {
                    $castedValue = constant(array_shift($definedCases));
                }
            }
        }
        if (!isset($castedValue)) {
            throw new \LogicException("Couldn't cast enum [$this->enumType] with value [$value]");
        }

        return $castedValue;
    }
}
