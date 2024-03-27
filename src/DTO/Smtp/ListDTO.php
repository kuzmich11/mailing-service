<?php

namespace App\DTO\Smtp;

use App\DTO\BaselListDTO;

/**
 * DTO структура для выборки SMTP-аккаунтов
 */
class ListDTO extends BaselListDTO
{
    /** @var FilterDTO|null Параметры фильтрации */
    public ?FilterDTO $filter;
}