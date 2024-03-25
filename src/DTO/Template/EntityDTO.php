<?php

namespace App\DTO\Template;

use Spatie\DataTransferObject\DataTransferObject;

/**
 * DTO структура для данных Email шаблонов
 */
class EntityDTO extends DataTransferObject
{
    /** @var int|null ID шаблона - для обновления данных */
    public ?int $id = null;

    /** @var int|null ID "родительского" шаблона */
    public ?int $parentId = null;

    /** @var string Название */
    public string $title;

    /** @var string Содержимое */
    public string $content;

    /** @var string Тема */
    public string $subject;

    /** @var array Массив переменных (плейсхолдеров) */
    public array $variables = [];
}