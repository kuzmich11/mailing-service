<?php

namespace App\DTO\Group;

use Spatie\DataTransferObject\DataTransferObject;

/**
 * DTO параметров добавления получателей в группу
 */
class AddDTO extends DataTransferObject
{
    /** @var int ID группы */
    public int $groupId;

    /** @var array ID получателей */
    public array $recipientIds;
}