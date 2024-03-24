<?php

namespace App\DTO\Domain;

use App\DTO\GeneralListDTO;

/**
 * DTO структура для выборки доменов
 */
class ListDTO extends GeneralListDTO
{
    /** @var FilterDTO|null Параметры фильтрации */
    public ?FilterDTO $filter;
}