<?php

namespace App\Entity;

use App\Repository\MailingListRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

/**
 * Класс сущности "Почтовая рассылка"
 */
#[ORM\Entity(repositoryClass: MailingListRepository::class)]
#[ORM\Table(name: 'mailing_list', schema: 'mail', options: ['comment' => 'Данные рассылки'])]
#[ORM\UniqueConstraint(name: 'idx__mailing_list_letter_recipient', columns: ['letter_id', 'recipient_id'])]
#[ORM\Index(name: 'idx__mailing_list_letter_id',    columns: ['letter_id'])]
#[ORM\Index(name: 'idx__mailing_list_recipient_id', columns: ['recipient_id'])]
#[ORM\Index(name: 'idx__mailing_list_smtp_id',      columns: ['smtp_id'])]
#[ORM\Index(name: 'idx__mailing_list_is_sent',      columns: ['is_sent'])]
#[ORM\Index(name: 'idx__mailing_list_is_delivered', columns: ['is_delivered'])]
#[ORM\Index(name: 'idx__mailing_list_sent_at',      columns: ['sent_at'])]
class MailingList
{
    /** @var int|null ID записи */
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    /** @var Letter|null ID письма */
    #[ORM\ManyToOne(targetEntity: Letter::class, inversedBy: 'mailingList')]
    #[ORM\JoinColumn(name: 'letter_id', referencedColumnName: 'id', nullable: false, options: ['comment' => 'Письмо'])]
    private ?Letter $letter = null;

    /** @var Recipient|null ID получателя */
    #[ORM\ManyToOne(targetEntity: Recipient::class)]
    #[ORM\JoinColumn(name: 'recipient_id', referencedColumnName: 'id', nullable: false, options: ['comment' => 'Получатель'])]
    private ?Recipient $recipient = null;

    /** @var bool|null Флаг отправки */
    #[ORM\Column(options: ['default' => false, 'comment' => 'Флаг отправления'])]
    private ?bool $isSent = false;

    /** @var bool|null Флаг доставки */
    #[ORM\Column(options: ['default' => false, 'comment' => 'Флаг доставки'])]
    private ?bool $isDelivered = false;

    /** @var DateTimeImmutable|null Дата отправки */
    #[ORM\Column(nullable: true, options: ['comment' => 'Дата отправления'])]
    private ?DateTimeImmutable $sentAt = null;

    /** @var string|null Комментарии */
    #[ORM\Column(nullable: true, options: ['comment' => 'Примечания'])]
    private ?string $comment = null;

    /** @var SmtpAccount|null ID SMTP-аккаунта */
    #[ORM\ManyToOne(targetEntity: SmtpAccount::class)]
    #[ORM\JoinColumn(name: 'smtp_id', referencedColumnName: 'id', nullable: true, options: ['comment' => 'Сервер отправления'])]
    private ?SmtpAccount $smtp = null;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Letter|null
     */
    public function getLetter(): ?Letter
    {
        return $this->letter;
    }

    /**
     * @param Letter $letter
     * @return $this
     */
    public function setLetter(Letter $letter): static
    {
        $this->letter = $letter;

        return $this;
    }

    /**
     * @return Recipient|null
     */
    public function getRecipient(): ?Recipient
    {
        return $this->recipient;
    }

    /**
     * @param Recipient $recipient
     * @return $this
     */
    public function setRecipient(Recipient $recipient): static
    {
        $this->recipient = $recipient;

        return $this;
    }

    /**
     * @return bool|null
     */
    public function isSent(): ?bool
    {
        return $this->isSent;
    }

    /**
     * @param bool $isSent
     * @return $this
     */
    public function setSent(bool $isSent): static
    {
        $this->isSent = $isSent;

        return $this;
    }

    /**
     * @return bool|null
     */
    public function isDelivered(): ?bool
    {
        return $this->isDelivered;
    }

    /**
     * @param bool|null $isDelivered
     * @return $this
     */
    public function setDelivered(?bool $isDelivered): static
    {
        $this->isDelivered = $isDelivered;

        return $this;
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getSentAt(): ?DateTimeImmutable
    {
        return $this->sentAt;
    }

    /**
     * @param DateTimeImmutable|null $sentAt
     * @return $this
     */
    public function setSentAt(?DateTimeImmutable $sentAt): static
    {
        $this->sentAt = $sentAt;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getComment(): ?string
    {
        return $this->comment;
    }

    /**
     * @param string|null $comment
     * @return $this
     */
    public function setComment(?string $comment): static
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * @return SmtpAccount|null
     */
    public function getSmtp(): ?SmtpAccount
    {
        return $this->smtp;
    }

    /**
     * @param SmtpAccount $smtp
     * @return $this
     */
    public function setSmtp(SmtpAccount $smtp): static
    {
        $this->smtp = $smtp;

        return $this;
    }

    /**
     * Преобразовать объект сущности в массив
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'letter' => $this->letter->getId(),
            'recipient' => $this->recipient->getId(),
            'smtp' => $this->smtp->getId(),
            'isSent' => $this->isSent,
            'isDelivered' => $this->isDelivered,
            'sentAt' => $this->sentAt,
            'comment' => $this->comment,
        ];
    }
}
