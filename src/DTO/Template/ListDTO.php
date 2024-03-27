<?php

namespace App\DTO\Template;

use App\DTO\BaselListDTO;

/**
 * DTO структура для выборки Email шаблонов
 */
class ListDTO extends BaselListDTO
{
    /** @var FilterDTO|null Параметры фильтрации */
    public ?FilterDTO $filter;
}