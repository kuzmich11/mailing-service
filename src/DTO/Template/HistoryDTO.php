<?php

namespace App\DTO\Template;

use App\DTO\BaseHistoryDTO;

/**
 * Структура DTO для получения истории изменений шаблонов
 */
class HistoryDTO extends BaseHistoryDTO
{
    /** @var int|null ID изменённого шаблона */
    public ?int $template = null;
}