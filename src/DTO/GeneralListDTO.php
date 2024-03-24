<?php

namespace App\DTO;

use Spatie\DataTransferObject\DataTransferObject;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;

/**
 * DTO структура для выборки писем
 */
class GeneralListDTO extends DataTransferObject
{
    /** @var int Дефолтный лимит выборки */
    private const DEFAULT_LIMIT = 10;

    /** @var int Дефолтное смещение выборки (страница) */
    private const DEFAULT_PAGE = 1;


    /** @var int|null Лимит выборки */
    public ?int $limit = self::DEFAULT_LIMIT;

    /** @var int|null № страницы (смещение выборки) */
    public ?int $page = self::DEFAULT_PAGE;

    /** @var SortingDTO|null Параметры сортировки */
    public ?SortingDTO $sort;


    /**
     * Конструктор. Дополнительная инициализация сортировки и других параметров
     *
     * @param mixed ...$args Аргументы для DTO
     *
     * @throws UnknownProperties
     */
    public function __construct(...$args)
    {
        parent::__construct(...$args);
        // валидация свойств объекта и установка дефолтных значений
        $this->limit = filter_var($this->limit, FILTER_VALIDATE_INT, [
            'options' => [ 'min_range' => 1, 'default' => self::DEFAULT_LIMIT ]
        ]);
        $this->page = filter_var($this->page, FILTER_VALIDATE_INT, [
            'options' => [ 'min_range' => 1, 'default' => self::DEFAULT_PAGE ]
        ]);
        if (null === $this->sort) {
            $this->sort = new SortingDTO();
        }
    }
}