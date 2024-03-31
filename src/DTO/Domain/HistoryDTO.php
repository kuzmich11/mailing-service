<?php

namespace App\DTO\Domain;

use App\DTO\BaseHistoryDTO;

/**
 * Структура DTO для получения истории изменений доменов
 */
class HistoryDTO extends BaseHistoryDTO
{
    /** @var int|null ID изменённого домена */
    public ?int $domain = null;
}