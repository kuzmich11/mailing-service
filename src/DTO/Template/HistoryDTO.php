<?php

namespace App\DTO\Template;

use App\DTO\Caster\UuidCaster;
use App\DTO\PeriodDTO;
use Spatie\DataTransferObject\Attributes\CastWith;
use Spatie\DataTransferObject\DataTransferObject;
use Symfony\Component\Uid\Uuid;

/**
 * Структура DTO для получения истории изменений шаблонов
 */
class HistoryDTO extends DataTransferObject
{
    /** @var int|null ID изменённого шаблона */
    public ?int $template = null;
}