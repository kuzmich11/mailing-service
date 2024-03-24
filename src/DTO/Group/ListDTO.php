<?php

namespace App\DTO\Group;

use App\DTO\GeneralListDTO;

/**
 * DTO структура для выборки групп
 */
class ListDTO extends GeneralListDTO
{
    /** @var FilterDTO|null Параметры фильтрации */
    public ?FilterDTO $filter;
}