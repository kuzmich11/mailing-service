<?php

namespace App\Exception;

/**
 * Класс исключений SMTP-аккаунтов
 */
class SmtpException extends \Exception
{
    /** @var int Элемент не существует */
    public const NOT_EXISTS = 1;

    /** @var int Некорректное значение */
    public const BAD_VALUES = 2;

    /** @var int Ошибка БД */
    public const DB_PROBLEM = 3;
}