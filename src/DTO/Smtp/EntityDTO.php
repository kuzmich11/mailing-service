<?php

namespace App\DTO\Smtp;

use Spatie\DataTransferObject\DataTransferObject;

/**
 * DTO параметров для создания/изменения данных SMTP-аккаунтов
 */
class EntityDTO extends DataTransferObject
{
    /** @var int|null ID аккаунта */
    public ?int $id;

    /** @var string Хост */
    public string $host;

    /** @var string|null Наименование */
    public ?string $title;

    /** @var string Логин */
    public string $login;

    /** @var string Пароль */
    public string $password;

    /** @var int|null Порт */
    public ?int $port = 25;

    /** @var bool|null Тип аккаунта */
    public ?bool $isSystem;

    /** @var bool|null Флаг активности true - используется */
    public ?bool $isActive = false;
}