<?php

namespace App\DTO\Domain;

use Spatie\DataTransferObject\DataTransferObject;

/**
 * DTO параметров для создания/изменения домена
 */
class ParamsDTO extends DataTransferObject
{
    /** @var int|null ID домена */
    public ?int $id;

    /** @var string Наименование домена */
    public string $name;

    /** @var bool|null Флаг true - домен рабочий */
    public ?bool $isWorks = false;
}