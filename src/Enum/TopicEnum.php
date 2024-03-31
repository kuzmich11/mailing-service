<?php

namespace App\Enum;


/**
 * Перечисление тем сообщений
 */
enum TopicEnum: string
{
    /** Приоритетная */
    case PRIORITY  = 'PRIORITY';

    /** Обычная */
    case REGULAR   = 'REGULAR';

    /** Подготовки рассылки */
    case BROADCAST = 'BROADCAST';

    /** Результаты отправки */
    case RESULTS   = 'RESULTS';
}
