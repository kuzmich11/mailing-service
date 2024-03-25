<?php

namespace App\DTO\Domain;

use App\DTO\SortingDTO;
use Spatie\DataTransferObject\DataTransferObject;

/**
 * DTO структура для выборки доменов
 */
class ListDTO extends DataTransferObject
{
    /** @var int|null Лимит выборки */
    public ?int $limit = 10;

    /** @var int|null № страницы (смещение выборки) */
    public ?int $page = 1;

    /** @var FilterDTO|null Параметры фильтрации */
    public ?FilterDTO $filter;

    /** @var SortingDTO|null Параметры сортировки */
    public ?SortingDTO $sort;
}