<?php

namespace App\DTO\Group;

use App\DTO\Caster\UuidCaster;
use App\DTO\PeriodDTO;
use Spatie\DataTransferObject\Attributes\CastWith;
use Spatie\DataTransferObject\DataTransferObject;
use Symfony\Component\Uid\Uuid;

/**
 * DTO параметров фильтрации групп
 */
class FilterDTO extends DataTransferObject
{
    /** @var int|null ID группы */
    public ?int $id;

    /** @var string|null Название группы */
    public ?string $name;

    /** @var Uuid|null UID создателя группы */
    #[CastWith(UuidCaster::class)]
    public ?Uuid $creator;

    /** @var PeriodDTO|null Период создания группы */
    public ?PeriodDTO $createdAt;

    /** @var bool Флаг отображения удаленных групп (true - все группы вместе с удаленными) */
    public bool $withDeleted = false;
}