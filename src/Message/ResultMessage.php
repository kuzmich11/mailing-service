<?php

namespace App\Message;

/**
 * Сообщения очереди Result
 */
class ResultMessage implements MessageInterface
{
    private string $body;
    private array $headers;

    public function __construct(string $body, array $headers)
    {
        $this->body = $body;
        $this->headers = $headers;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function setBody($body): void
    {
        $this->body = $body;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function setHeaders(array $headers): void
    {
        $this->headers = $headers;
    }
}