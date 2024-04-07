<?php

namespace FileManagerBundle\Exception;

/**
 * Исключение для файлов(вложений)
 */
class FileException extends \Exception
{
    /** @var int Элемент не существует */
    public const NOT_EXISTS = 1;

    /** @var int Некорректное значение */
    public const BAD_VALUES = 2;

    /** @var int Ошибка БД */
    public const DB_PROBLEM = 3;

    /** @var int Ошибка сохранения файла */
    public const UPLOAD_PROBLEM = 4;

    /** @var int Файл поврежден */
    public const BAD_FILE = 5;
}