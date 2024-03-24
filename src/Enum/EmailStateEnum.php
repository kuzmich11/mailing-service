<?php

namespace App\Enum;

/**
 * Перечисление статусов почтовых адресов получателей
 */
enum EmailStateEnum: string
{
    /** Работает */
    case WORKING = 'WORKING';

    /** Не рабочий домен */
    case BAD_DOMAIN = 'BAD_DOMAIN';

    /** В черном списке */
    case BLACK_LIST = 'BLACK_LIST';

    /** Не найден */
    case NOT_FOUND = 'NOT_FOUND';

    /** Переполнен */
    case CROWDED = 'CROWDED';

    /** Не подтвержден */
    case UNCONFIRMED = 'UNCONFIRMED';

    /** Проблемный */
    case PROBLEM = 'PROBLEM';
}
