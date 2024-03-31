<?php

namespace App\DTO\File;

use Spatie\DataTransferObject\DataTransferObject;

/**
 * DTO параметров загружаемого файла
 */
class ParamsDTO extends DataTransferObject
{
    /** @var string Наименование */
    public string $filename;

    /** @var int Размер */
    public int $size;

    /** @var string Тип */
    public string $type;

    /** @var string Содержимое файла в base64 */
    public string $content;
}