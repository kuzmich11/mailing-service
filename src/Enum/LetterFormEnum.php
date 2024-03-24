<?php

namespace App\Enum;

/**
 * Перечисление типов писем
 */
enum LetterFormEnum: string
{
    /** Системные (разовые) письма */
    case SYSTEM = 'SYSTEM';

    /** Рекламные (массовые) письма */
    case PROMO = 'PROMO';
}
