<?php

namespace App\Message;

/**
 * Интерфейс сообщений для брокера очередей
 */
interface MessageInterface
{
    /** @var string Ключ данных для названия топика */
    const TOPIC_NAME_KEY  = 'topic';
    /** @var string Ключ для общего количества писем в рассылке */
    const BROADCAST_COUNT = 'count';
    /** @var string Ключ для текущего индекса письма в рассылке */
    const BROADCAST_INDEX = 'current';

    public function getBody(): string;

    public function setBody($body): void;

    public function getHeaders(): array;

    public function setHeaders(array $headers): void;
}