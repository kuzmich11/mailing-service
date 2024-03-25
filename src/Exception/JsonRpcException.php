<?php

namespace App\Exception;

use Exception;

/**
 * Исключения для JSON RPC
 */
class JsonRpcException extends Exception
{
    /** @var int Invalid JSON was received by the server. An error occurred on the server while parsing the JSON text */
    public const PARSE_ERROR = -32700;

    /** @var int The JSON sent is not a valid Request object */
    public const INVALID_REQUEST = -32600;

    /** @var  int The method does not exist / is not available */
    public const METHOD_NOT_FOUND = -32601;

    /** @var int Invalid method parameter(s) */
    public const INVALID_PARAMS = -32602;

    /** @var int Internal JSON-RPC error. */
    public const INTERNAL_ERROR = -32603;

    /** @var int INTEGRITY error */
    public const INTEGRITY_ERROR = -32020;

    /** @var int Not found in Data Base */
    public const NOT_FOUND = -31000;

    /** @var int Invalid validation parameter(s) */
    public const INVALID_VALIDATION = -32000;

    /** @var int Data Base error */
    public const DB_ERROR = -32010;

    /** @var array|null Дополнительные данные по исключению */
    private ?array $data = null;

    /**
     * Конструктор
     *
     * @param string $message Текст сообщения исключения
     * @param int $code Код искобчения
     * @param \Throwable|null $previous Предыдущее исключение
     * @param array|null $data Дополнительные данные по исключению
     */
    public function __construct(string $message = "", int $code = 0, ?\Throwable $previous = null, ?array $data = null)
    {
        parent::__construct($message, $code, $previous);
        $this->data = $data;
    }

    /**
     * Получение дополнительных данных по исключению
     *
     * @return array|null
     */
    public function getData(): ?array
    {
        return $this->data;
    }
}