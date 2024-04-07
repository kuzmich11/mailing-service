<?php

namespace App\Message;

use Exception;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * Слушатель сообщений Result
 */
#[AsMessageHandler]
class ResultMessageHandler
{
    /**
     * Конструктор
     */
    public function __construct(
    )
    {
    }

    /**
     * Слушает сообщения Result
     *
     * @param ResultMessage $message
     * @return bool
     * @throws Exception
     */
    public function __invoke(ResultMessage $message): bool
    {
        //TODO: Добавить метод обработки результатов или удалить слушателя
        return true;
    }
}