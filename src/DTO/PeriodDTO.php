<?php

namespace App\DTO;

use App\DTO\Caster\DateTimeCaster;
use DateTimeImmutable;
use Spatie\DataTransferObject\Attributes\CastWith;
use Spatie\DataTransferObject\DataTransferObject;

/**
 * DTO структура для получаемых интервалов дат
 */
class PeriodDTO extends DataTransferObject
{
    /** @var DateTimeImmutable|null Начало интервала */
    #[CastWith(DateTimeCaster::class, DateTimeImmutable::class)]
    public ?DateTimeImmutable $from = null;

    /** @var DateTimeImmutable|null Окончание интервала */
    #[CastWith(DateTimeCaster::class, DateTimeImmutable::class)]
    public ?DateTimeImmutable $to = null;
}