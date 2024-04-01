<?php

namespace App\Exception;

/**
 * Исключения для Email шаблонов
 */
class TemplateException extends \Exception
{
    /** @var int Отсутствует запрошенный шаблон */
    public const NOT_EXISTS = 1;
    /** @var int Некорректное значение для обработки */
    public const BAD_VALUES = 2;
    /** @var int Проблема взаимодействия с БД */
    public const DB_PROBLEM = 3;
}