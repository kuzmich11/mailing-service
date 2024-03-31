<?php

namespace App\DTO\Recipient;

use App\DTO\Caster\UuidCaster;
use App\DTO\PeriodDTO;
use Spatie\DataTransferObject\Attributes\CastWith;
use Spatie\DataTransferObject\DataTransferObject;
use Symfony\Component\Uid\Uuid;

/**
 * Структура DTO для получения истории изменений групп
 */
class HistoryDTO extends DataTransferObject
{
    /** @var int|null ID записи */
    public ?int $id = null;

    /** @var int|null ID изменённой группы */
    public ?int $recipient = null;

    /** @var PeriodDTO|null Период редактирования */
    public ?PeriodDTO $period = null;

    /** @var Uuid|null UUID сотрудника, редактировавшего группу */
    #[CastWith(UuidCaster::class)]
    public ?Uuid $editor = null;

    /** @var int|null Лимит выборки записей */
    public ?int $limit = 10;

    /** @var int|null Смещение выборки */
    public ?int $page = 1;

    /** @var bool|null Флаг обратной сортировки */
    public ?bool $reverse = false;
}