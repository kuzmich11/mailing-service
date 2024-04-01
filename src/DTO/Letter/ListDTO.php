<?php

namespace App\DTO\Letter;

use App\DTO\BaselListDTO;

/**
 * DTO структура для выборки писем
 */
class ListDTO extends BaselListDTO
{
    /** @var FilterDTO|null Параметры фильтрации */
    public ?FilterDTO $filter;
}