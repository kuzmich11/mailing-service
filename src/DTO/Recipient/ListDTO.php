<?php

namespace App\DTO\Recipient;

use App\DTO\GeneralListDTO;

/**
 * DTO структура для выборки Получателей
 */
class ListDTO extends GeneralListDTO
{
    /** @var FilterDTO|null Параметры фильтрации */
    public ?FilterDTO $filter;
}