<?php

namespace App\DTO\Smtp;

use App\DTO\BaseHistoryDTO;

/**
 * Структура DTO для получения истории изменений SMTP-аккаунтов
 */
class HistoryDTO extends BaseHistoryDTO
{
    /** @var int|null ID изменённого SMTP-аккаунта */
    public ?int $smtp = null;
}
