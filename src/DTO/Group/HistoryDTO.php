<?php

namespace App\DTO\Group;

use App\DTO\BaseHistoryDTO;

/**
 * Структура DTO для получения истории изменений групп
 */
class HistoryDTO extends BaseHistoryDTO
{
    /** @var int|null ID изменённой группы */
    public ?int $group = null;
}