<?php

namespace App\Enum;

/**
 * Перечисление статусов писем
 */
enum LetterStatusEnum: string
{
    /** Отправлено */
    case SENT = 'SENT';

    /** Не отправлено */
    case NOT_SENT = 'NOT_SENT';

    /** В процессе отправки */
    case PROCESSING = 'PROCESSING';

    /** Отправка приостановлена */
    case PAUSED = 'PAUSED';

    /** Ошибка отправки */
    case ERROR = 'ERROR';

    /** Плохой получатель */
    case BAD_RECIPIENT = "BAD_RECIPIENT";
}
