<?php

namespace App\DTO\Domain;

use App\DTO\Caster\UuidCaster;
use App\DTO\PeriodDTO;
use Spatie\DataTransferObject\Attributes\CastWith;
use Spatie\DataTransferObject\DataTransferObject;
use Symfony\Component\Uid\Uuid;

/**
 * DTO параметров фильтрации списка доменов
 */
class FilterDTO extends DataTransferObject
{
    /** @var int|null ID домена */
    public ?int $id = null;

    /** @var string|null Наименование домена */
    public ?string $name = null;

    /** @var bool Флаг true - рабочие домены */
    public ?bool $isWorks;

    /** @var Uuid|null UUID создателя домена */
    #[CastWith(UuidCaster::class)]
    public ?Uuid $creator;

    /** @var PeriodDTO|null Период создания домена */
    public ?PeriodDTO $createdAt;

    /** @var Uuid|null UUID редактировавшего домен */
    #[CastWith(UuidCaster::class)]
    public ?Uuid $editor;

    /** @var PeriodDTO|null Период редактирования домена */
    public ?PeriodDTO $editedAt;
}