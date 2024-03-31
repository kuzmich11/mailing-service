<?php

namespace App\Service;

use App\Entity\Letter;
use App\Entity\SmtpAccount;
use App\Repository\RecipientRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;

/**
 * Класс отправления письма по электронной почте
 */
class MailerService
{
    /**
     * Конструктор
     * @param LoggerInterface $logger Логер
     * @param RecipientRepository $recipients Репозиторий получателей
     */
    public function __construct(
        private readonly LoggerInterface       $logger,
        private readonly RecipientRepository   $recipients,
    )
    {
    }

    /**
     * Отправить письмо
     *
     * @param Letter $letter Письмо для отправки
     * @param array $attachments Вложения письма
     *
     * @return bool
     */
    public function sendLetter(Letter $letter, array $attachments): bool
    {
        /** @var SmtpAccount[] $smtpPool_ Пул используемых SMTP-аккаунтов */
        static $smtpPool_ = [];

        $smtp = current($letter->getSmtp()->toArray());
        $smtpId = $smtp->getId();
        if (!array_key_exists($smtpId, $smtpPool_)) {
            $smtpPool_[$smtpId] = $smtp;
        }
        if (empty($smtpPool_[$smtpId])) {
            $this->logger->error('Некорректный SMTP-аккаунт для отправки письма');
            unset($smtpPool_[$smtpId]);
            return false;
        }
        $smtpAccount = $smtpPool_[$smtpId];
        try {
            $recipient = $this->recipients->find($letter->getRecipient());
            $email = (new Email())
                ->from($smtpAccount->getLogin() . '@gmail.com') //использую для отработки . '@gmail.com')
                ->to($recipient->getEmail())
                ->subject($letter->getSubject())
                ->html($letter->getContent());
            if ((!empty($letter->getAttachments()))) {
                foreach ($attachments as $attachment) {
                    $email->attach(base64_decode($attachment['content']), $attachment['name'], $attachment['type']);
                }
            }
            $transport = Transport::fromDsn($smtpAccount->getDSN(forDebug: true));
            $mailer = new Mailer($transport);
            $mailer->send($email);
            return true;
        } catch (\Throwable $err) {
            $this->logger->error($err->getMessage(), ['Exception' => $err]);
            return false;
        }
    }
}