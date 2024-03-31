<?php

namespace App\DTO\Domain;

use App\DTO\BaselListDTO;

/**
 * DTO структура для выборки доменов
 */
class ListDTO extends BaselListDTO
{
    /** @var FilterDTO|null Параметры фильтрации */
    public ?FilterDTO $filter;
}