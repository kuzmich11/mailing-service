<?php

namespace App\DTO\Recipient;

use App\DTO\Caster\EnumAllCaster;
use App\DTO\Caster\UuidCaster;
use App\DTO\PeriodDTO;
use App\Enum\EmailStateEnum;
use Spatie\DataTransferObject\Attributes\CastWith;
use Spatie\DataTransferObject\DataTransferObject;
use Symfony\Component\Uid\Uuid;

/**
 * DTO параметров фильтрации списка получателей
 */
class FilterDTO extends DataTransferObject
{
    /** @var int|null ID получателя */
    public ?int $id;

    /** @var string|null Email получателя */
    public ?string $email;

    /** @var EmailStateEnum|null Статус почты получателя */
    #[CastWith(EnumAllCaster::class, EmailStateEnum::class)]
    public ?EmailStateEnum $emailState;

    /** @var bool|null Согласие получателя на рассылку */
    public ?bool $isConsent = false;

    /** @var UUID|null UUID создателя записи получателя */
    #[CastWith(UuidCaster::class)]
    public ?UUID $creator;

    /** @var PeriodDTO|null Период создания записи о получателе */
    public ?PeriodDTO $createdAt;

    /** @var bool Флаг true - все получатели включая удаленных */
    public bool $withDeleted = false;
}