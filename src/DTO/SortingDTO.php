<?php

namespace App\DTO;

use Spatie\DataTransferObject\DataTransferObject;

/**
 * DTO структура для сортировки выборки
 */
class SortingDTO extends DataTransferObject
{
    /** @var string Название поля */
    public string $field = 'id';

    /** @var bool Необходим обратный порядок (DESC) */
    public ?bool $reverse = false;
}