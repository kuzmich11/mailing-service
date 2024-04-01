<?php

namespace App\DTO\Group;

use Spatie\DataTransferObject\DataTransferObject;

/**
 * DTO параметров групп
 */
class EntityDTO extends DataTransferObject
{
    /** @var int|null ID группы */
    public ?int $id;

    /** @var string Название группы */
    public string $name;

    /** @var array|null ID получателей связанных с группой */
    public ?array $recipients;
}