<?php

namespace App\DTO\Template;

use App\DTO\GeneralListDTO;

/**
 * DTO структура для выборки Email шаблонов
 */
class ListDTO extends GeneralListDTO
{
    /** @var FilterDTO|null Параметры фильтрации */
    public ?FilterDTO $filter;
}