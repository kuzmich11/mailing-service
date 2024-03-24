<?php

namespace App\DTO\Letter;

use App\DTO\GeneralListDTO;

/**
 * DTO структура для выборки писем
 */
class ListDTO extends GeneralListDTO
{
    /** @var FilterDTO|null Параметры фильтрации */
    public ?FilterDTO $filter;
}