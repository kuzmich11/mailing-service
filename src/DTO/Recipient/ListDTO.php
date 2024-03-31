<?php

namespace App\DTO\Recipient;

use App\DTO\BaselListDTO;

/**
 * DTO структура для выборки Email шаблонов
 */
class ListDTO extends BaselListDTO
{
    /** @var FilterDTO|null Параметры фильтрации */
    public ?FilterDTO $filter;
}