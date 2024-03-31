<?php

namespace App\DTO\Recipient;

use App\DTO\Caster\EnumAllCaster;
use App\Enum\EmailStateEnum;
use Spatie\DataTransferObject\Attributes\CastWith;
use Spatie\DataTransferObject\DataTransferObject;

/**
 * DTO параметров получателей
 */
class EntityDTO extends DataTransferObject
{
    /** @var int|null ID получателя */
    public ?int $id;

    /** @var string Email получателя */
    public string $email;

    /** @var EmailStateEnum Статус почты получателя */
    #[CastWith(EnumAllCaster::class, EmailStateEnum::class)]
    public EmailStateEnum $emailState = EmailStateEnum::UNCONFIRMED;

    /** @var bool|null Согласие на рассылку */
    public bool $isConsent = false;

    /** @var array|null Массив ID групп получателей */
    public ?array $groups;
}