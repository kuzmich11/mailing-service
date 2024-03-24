<?php

namespace App\DTO\Letter;

use App\DTO\Caster\EnumAllCaster;
use App\DTO\Caster\UuidCaster;
use App\DTO\PeriodDTO;
use App\Enum\LetterFormEnum;
use App\Enum\LetterStatusEnum;
use Spatie\DataTransferObject\Attributes\CastWith;
use Spatie\DataTransferObject\DataTransferObject;
use Symfony\Component\Uid\Uuid;

/**
 * DTO параметров фильтрации списка писем
 */
class FilterDTO extends DataTransferObject
{
    /** @var int|null ID письма */
    public ?int $id = null;

    /** @var string|null Тема письма */
    public ?string $subject = null;

    /** @var LetterFormEnum|null Тип письма */
    #[CastWith(EnumAllCaster::class, LetterFormEnum::class)]
    public ?LetterFormEnum $form = null;

    /** @var int|null ID шаблона */
    public ?int $template;

    /** @var int|null */
    public ?int $smtp;

    /** @var int|null ID получателя письма */
    public ?int $recipient;

    /** @var Uuid|null UUID создателя письма */
    #[CastWith(UuidCaster::class)]
    public ?Uuid $creator;

    /** @var PeriodDTO|null Период создания письма */
    public ?PeriodDTO $createdAt;

    /** @var Uuid|null ID отправителя */
    #[CastWith(UuidCaster::class)]
    public ?Uuid $sender;

    /** @var PeriodDTO|null Период создания письма */
    public ?PeriodDTO $sentAt;

    /** @var LetterStatusEnum|null Статус отправления письма */
    #[CastWith(EnumAllCaster::class, LetterStatusEnum::class)]
    public ?LetterStatusEnum $status;

    /** @var bool Флаг отображения удаленных писем (true - вместе с удаленными) */
    public bool $withDeleted = false;
}