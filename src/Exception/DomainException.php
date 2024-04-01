<?php

namespace App\Exception;

/**
 * Класс исключений доменов
 */
class DomainException extends \Exception
{
    /** @var int Элемент не существует */
    public const NOT_EXISTS = 1;

    /** @var int Некорректное значение */
    public const BAD_VALUES = 2;

    /** @var int Ошибка БД */
    public const DB_PROBLEM = 3;

    /** @var int Дубликат записи */
    public const DUPLICATE_ENTRY = 4;
}