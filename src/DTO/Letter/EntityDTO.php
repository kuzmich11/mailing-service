<?php

namespace App\DTO\Letter;

use App\DTO\Caster\EnumAllCaster;
use App\Enum\LetterFormEnum;
use Spatie\DataTransferObject\Attributes\CastWith;
use Spatie\DataTransferObject\DataTransferObject;

/**
 * DTO параметров для создания/изменения письма
 */
class EntityDTO extends DataTransferObject
{
    /** @var int|null ID письма */
    public ?int $id;

    /** @var string|null Тема письма */
    public ?string $subject;

    /** @var LetterFormEnum Тип письма */
    #[CastWith(EnumAllCaster::class, LetterFormEnum::class)]
    public LetterFormEnum $form;

    /** @var int ID шаблона */
    public int $template;

    /** @var array ID сервера отправления */
    public array $smtp;

    /** @var int ID получателя письма */
    public int $recipient;

    /** @var array|null Массив вложений письма */
    public ?array $attachments;

    /** @var array|null Массив значений для шаблонов */
    public ?array $values;
}