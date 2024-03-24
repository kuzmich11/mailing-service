<?php

namespace App\DTO\Template;

use App\DTO\Caster\UuidCaster;
use App\DTO\PeriodDTO;
use Spatie\DataTransferObject\Attributes\CastWith;
use Spatie\DataTransferObject\DataTransferObject;
use Symfony\Component\Uid\Uuid;

/**
 * DTO структура фильтров для выборки Email шаблонов
 */
class FilterDTO extends DataTransferObject
{
    /** @var string|null Название (часть названия) */
    public ?string $title = null;

    /** @var int|null ID родительской записи */
    public ?int $parent = null;

    /** @var Uuid|null UUID кто создал запись */
    #[CastWith(UuidCaster::class)]
    public ?Uuid $creator = null;

    /** @var PeriodDTO|null Интервал дат создания записи */
    public ?PeriodDTO $created = null;

    /** @var int|null № задачи для шаблона */
    public ?int $taskNumber = null;

    /** @var bool|null Флаг выборки удалённых шаблонов */
    public ?bool $withDeleted = false;
}