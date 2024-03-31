<?php

namespace App\DTO\Group;

use Spatie\DataTransferObject\DataTransferObject;

/**
 * DTO параметров групп
 */
class ParamsDTO extends DataTransferObject
{
    /** @var int|null ID группы */
    public ?int $id;

    /** @var string Название группы */
    public string $name;

    /** @var array|null ID получателей связанных с группой */
    public ?array $recipientIds;
}