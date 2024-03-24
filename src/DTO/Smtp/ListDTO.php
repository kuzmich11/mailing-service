<?php

namespace App\DTO\Smtp;

use App\DTO\GeneralListDTO;

/**
 * DTO структура для выборки SMTP-аккаунтов
 */
class ListDTO extends GeneralListDTO
{
    /** @var FilterDTO|null Параметры фильтрации */
    public ?FilterDTO $filter;
}