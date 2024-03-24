<?php

namespace App\Enum;

/**
 * Перечисление типов писем
 */
enum LetterTypeEnum: int
{
    /** Системная (разовая) рассылка */
    case SYSTEM = 1;

    /** Рекламная массовая рассылка */
    case PROMO = 2;
}
