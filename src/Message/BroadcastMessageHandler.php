<?php

namespace App\Message;

use App\Repository\LetterRepository;
use App\Service\BrokerService;
use Doctrine\DBAL\Exception;
use League\Flysystem\FilesystemException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * Обработчик сообщений Broadcast
 */
#[AsMessageHandler]
class BroadcastMessageHandler
{
    /**
     * Конструктор
     *
     * @param BrokerService    $broker  Брокер очередей
     * @param LetterRepository $letters Репозиторий писем
     */
    public function __construct(
        private readonly BrokerService    $broker,
        private readonly LetterRepository $letters
    )
    {
    }

    /**
     * "Слушатель" очереди массовых сообщений (`broadcast`)
     *
     * @param BroadcastMessage $message Объект сообщения из очереди
     *
     * @return bool
     * @throws FilesystemException
     */
    public function __invoke(BroadcastMessage $message): bool
    {
        $body = json_decode($message->getBody(), true);
        if (empty($body['id']) || !is_numeric($body['id'])) {
            $this->broker->publishResult([
                'message' => $body,
                'error' => 'Не определён идентификатор письма для отправки'
            ]);
            return true;
        }
        $letter = $this->letters->find($body['id']);
        if (!$letter) {
            $this->broker->publishResult([
                'id' => $body['id'],
                'error' => 'Отсутствует письмо для рассылки с указанным ID'
            ]);
            return true;
        }

        $promoGenerator = $this->broker->preparePromo($letter);
        foreach ($promoGenerator as $messages) {
            $this->broker->publishMessages($messages);
        }
        return true;
    }
}