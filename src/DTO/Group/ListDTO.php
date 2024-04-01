<?php

namespace App\DTO\Group;

use App\DTO\BaselListDTO;

/**
 * DTO структура для выборки групп
 */
class ListDTO extends BaselListDTO
{
    /** @var FilterDTO|null Параметры фильтрации */
    public ?FilterDTO $filter;
}