<?php

namespace App\Message;

use App\Entity\MailingList;
use App\Enum\LetterFormEnum;
use App\Enum\LetterStatusEnum;
use App\Repository\LetterRepository;
use App\Repository\MailingListRepository;
use App\Repository\RecipientRepository;
use App\Repository\SmtpAccountRepository;
use App\Service\BrokerService;
use App\Service\MailerService;
use DateTimeImmutable;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * Обработчик сообщений Priority и Regular
 */
#[AsMessageHandler]
class MessageHandler
{
    /**
     * Конструктор
     *
     * @param MailerService         $mailer       Сервис отправления писем
     * @param BrokerService         $broker       Брокер очередей
     * @param LetterRepository      $letters      Репозиторий писем
     * @param RecipientRepository   $recipients   Репозиторий получателей
     * @param SmtpAccountRepository $smtpAccounts Репозиторий серверов отправки
     * @param MailingListRepository $mailings     Репозиторий данных рассылки
     */
    public function __construct(
        private readonly MailerService         $mailer,
        private readonly BrokerService         $broker,
        private readonly LetterRepository      $letters,
        private readonly RecipientRepository   $recipients,
        private readonly SmtpAccountRepository $smtpAccounts,
        private readonly MailingListRepository $mailings
    )
    {}

    /**
     * "Слушатель" очередей одиночных сообщений (`priority`, `regular`)
     *
     * @param Message $message Объект сообщения из очереди
     *
     * @return bool
     */
    public function __invoke(Message $message): bool
    {
        $queueData = json_decode($message->getBody(), true);
        // формирование объекта письма для отправки
        $letterId = !empty($queueData['id']) ? (int)$queueData['id'] : null;
        $srcLetter = $letterId ? $this->letters->find($letterId) : null;
        if (!$srcLetter) {
            $this->broker->publishResult([
                'id' => $letterId,
                'error' => sprintf('Не найдено письмо [ID: %d] для отправки', $letterId)
            ]);
            return true;
        }
        // "рабочий" объект письма, который будет непосредственно отправлен
        $workLetter = clone $srcLetter;
        $workLetter->setSubject($queueData['subject'] ?? '');
        $workLetter->setContent($queueData['content'] ?? '');
        $workLetter->setRecipient($queueData['recipient'] ?? null);
        $workSmtps = $this->smtpAccounts->findBy(['id' => $queueData['smtp']]);
        foreach ($workSmtps as $workSmtp) {
            $workLetter->addSmtp($workSmtp);
        }
        //** ВЫПОЛНИТЬ ОТПРАВКУ ПИСЬМА **//
        $result = $this->mailer->sendLetter($workLetter, $queueData['attachments']);
        // сформировать и сохранить данные об отправке
        $mailing = $this->mailings->findOneBy(['letter' => $letterId, 'recipient' => $workLetter->getRecipient()]) ?: new MailingList();
        if (!$mailing->getId()) {
            $recipient = $this->recipients->find($workLetter->getRecipient());
            $mailing->setLetter($srcLetter);
            $mailing->setRecipient($recipient);
        }
        if (!$result) {
            $mailing->setComment('Не удалось отправить письмо. Возможно проблема с SMTP сервером.');
        }
        $smtp = current($workLetter->getSmtp()->toArray());
        $mailing->setSmtp($smtp);
        $mailing->setSent($result);
        $mailing->setSentAt(new DateTimeImmutable('now'));
        $this->broker->saveDbEntity($mailing);
        // обновление статуса для обработанного письма
        $headers = $message->getHeaders();
        if (($srcLetter->getForm() === LetterFormEnum::SYSTEM && $result)
        || ($srcLetter->getForm() === LetterFormEnum::PROMO && $headers[MessageInterface::BROADCAST_COUNT] == $headers[MessageInterface::BROADCAST_INDEX])) {
            $srcLetter->setStatus(LetterStatusEnum::SENT);
            $this->broker->saveDbEntity($srcLetter);
            $this->broker->publishResult([
                'id' => $letterId,
                'success' => 'Письмо отправлено'
            ]);
        }
        return true;
    }
}