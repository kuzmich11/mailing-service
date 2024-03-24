<?php

namespace App\Enum;

/**
 * Перечисление типов фильтров
 */
enum FilterTypeEnum: string
{
    /** Для выборки сущностей */
    case ENTITY = 'ENTITY';
    /** Для выборки истории изменений */
    case HISTORY = 'HISTORY';
}
