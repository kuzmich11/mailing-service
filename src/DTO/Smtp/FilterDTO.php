<?php

namespace App\DTO\Smtp;

use Spatie\DataTransferObject\DataTransferObject;

/**
 * DTO параметров фильтрации списка SMTP-аккаунтов
 */
class FilterDTO extends DataTransferObject
{
    /** @var int|null ID аккаунта */
    public ?int $id;

    /** @var string|null Хост */
    public ?string $host;

    /** @var string|null Наименование */
    public ?string $title;

    /** @var string|null Логин */
    public ?string $login;

    /** @var bool|null Тип аккаунта (true - системный, false - рекламный) */
    public ?bool $isSystem;

    /** @var bool|null Флаг отображения удаленных аккаунтов (true - вместе с удаленными) */
    public ?bool $withDeleted = false;

    /** @var bool|null Флаг активности true - используется */
    public ?bool $isActive;
}