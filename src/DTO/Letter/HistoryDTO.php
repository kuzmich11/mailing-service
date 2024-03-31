<?php

namespace App\DTO\Letter;

use App\DTO\BaseHistoryDTO;

/**
 * Структура DTO для получения истории изменений писем
 */
class HistoryDTO extends BaseHistoryDTO
{
    /** @var int|null ID изменённого письма */
    public ?int $letter = null;
}