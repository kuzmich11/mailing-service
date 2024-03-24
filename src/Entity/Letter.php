<?php

namespace App\Entity;

use App\Enum\LetterFormEnum;
use App\Enum\LetterStatusEnum;
use App\Repository\LetterRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Класс сущности "Письма"
 */
#[ORM\Entity(repositoryClass: LetterRepository::class)]
#[ORM\Table(name: 'letter', schema: 'mail', options: ['comment' => 'Данные писем для отправки'])]
#[ORM\Index(name: 'idx__letter_subject',    columns: ['subject'])]
#[ORM\Index(name: 'idx__letter_form',       columns: ['form'])]
#[ORM\Index(name: 'idx__letter_template',   columns: ['template'])]
#[ORM\Index(name: 'idx__letter_recipient',  columns: ['recipient'])]
#[ORM\Index(name: 'idx__letter_creator',    columns: ['creator'])]
#[ORM\Index(name: 'idx__letter_editor',     columns: ['editor'])]
#[ORM\Index(name: 'idx__letter_deleter',    columns: ['deleter'])]
#[ORM\Index(name: 'idx__letter_created_at', columns: ['created_at'])]
#[ORM\Index(name: 'idx__letter_edited_at',  columns: ['edited_at'])]
#[ORM\Index(name: 'idx__letter_deleted_at', columns: ['deleted_at'])]
#[ORM\Index(name: 'idx__letter_sender',     columns: ['sender'])]
#[ORM\Index(name: 'idx__letter_sent_at',    columns: ['sent_at'])]
#[ORM\Index(name: 'idx__letter_status',     columns: ['status'])]
class Letter
{
    /** @var int|null ID письма */
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(options: ['comment' => 'Идентификатор'])]
    private ?int $id = null;

    /** @var string|null Тема письма */
    #[ORM\Column(length: 255, nullable: true, options: ['comment' => 'Название (тема)'])]
    private ?string $subject = null;

    /** @var string|null Тело письма */
    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => 'Содержимое (тело)'])]
    private ?string $content = null;

    /** @var LetterFormEnum|null Тип письма */
    #[ORM\Column(type: Types::STRING, enumType: LetterFormEnum::class, options: ['comment' => 'Тип'])]
    private ?LetterFormEnum $form = null;

    /** @var Template|null ID шаблона */
    #[ORM\ManyToOne(targetEntity: Template::class)]
    #[ORM\JoinColumn(name: 'template', referencedColumnName: 'id', nullable: false, options: ['comment' => 'Шаблон'])]
    private ?Template $template = null;

    /** @var ArrayCollection|Collection ID сервера отправления */
    #[ORM\ManyToMany(targetEntity: 'SmtpAccount')]
    #[ORM\JoinTable(name: 'letter_smtp', schema: 'mail', options: ['comment' => 'Связующая таблица писем с серверами отправлений'])]
    #[ORM\JoinColumn(name: 'letter_id', referencedColumnName: 'id', options: ['comment' => 'Письмо'])]
    #[ORM\InverseJoinColumn(name: 'smtp_id', referencedColumnName: 'id',options: ['comment' => 'Сервер отправления'])]
    private Collection|ArrayCollection $smtp;

    /** @var int|null ID получателя письма */
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => 'Получатель(и)'])]
    private ?int $recipient = null;

    /** @var ArrayCollection|Collection Вложения письма */
    #[ORM\ManyToMany(targetEntity: 'File', inversedBy: 'letters', orphanRemoval: true)]
    #[ORM\JoinTable(name: 'letter_file', schema: 'mail', options: ['comment' => 'Связующая таблица писем с файлами(вложениями)'])]
    #[ORM\JoinColumn(name: 'letter_id', referencedColumnName: 'id', options: ['comment' => 'Письмо'])]
    #[ORM\InverseJoinColumn(name: 'file_id', referencedColumnName: 'id', options: ['comment' => 'Файл'])]
    private Collection|ArrayCollection $attachments;

    /** @var array|null Значения для шаблонов */
    #[ORM\Column(nullable: true, options: ['comment' => 'Значения для шаблонов'])]
    private ?array $values = null;

    /** @var Uuid|null ID создателя письма */
    #[ORM\Column(type: UuidType::NAME, options: ['comment' => 'Кто создал письмо'])]
    #[Assert\Uuid(message: 'Некорректное значение UUID сотрудника')]
    private ?Uuid $creator = null;

    /** @var Uuid|null UUID изменившего письмо */
    #[ORM\Column(type: UuidType::NAME, nullable: true, options: ['comment' => 'Кто изменил письмо'])]
    #[Assert\Uuid(message: 'Некорректное значение UUID сотрудника')]
    private ?Uuid $editor = null;

    /** @var Uuid|null UUID удалившего письмо */
    #[ORM\Column(type: UuidType::NAME, nullable: true, options: ['comment' => 'Кто удалил письмо'])]
    #[Assert\Uuid(message: 'Некорректное значение UUID сотрудника')]
    private ?Uuid $deleter = null;

    /** @var DateTimeImmutable|null Дата создания */
    #[ORM\Column(options: ['comment' => 'Дата создания'])]
    private ?DateTimeImmutable $createdAt = null;

    /** @var DateTimeImmutable|null Дата изменения */
    #[ORM\Column(nullable: true, options: ['comment' => 'Дата изменения'])]
    private ?DateTimeImmutable $editedAt = null;

    /** @var DateTimeImmutable|null Дата удаления */
    #[ORM\Column(nullable: true, options: ['comment' => 'Дата удаления'])]
    private ?DateTimeImmutable $deletedAt = null;

    /** @var Uuid|null ID отправителя письма */
    #[ORM\Column(type: UuidType::NAME, nullable: true, options: ['comment' => 'Отправитель письма'])]
    #[Assert\Uuid(message: 'Некорректное значение UUID сотрудника')]
    private ?Uuid $sender = null;

    /** @var DateTimeImmutable|null Дата отправки */
    #[ORM\Column(nullable: true, options: ['comment' => 'Дата и время отправления'])]
    private ?DateTimeImmutable $sentAt = null;

    /** @var LetterStatusEnum|null Статус отправления */
    #[ORM\Column(type: Types::STRING, nullable: true, enumType: LetterStatusEnum::class, options: ['default' => 'NOT_SENT', 'comment' => 'Статус отправки'])]
    private ?LetterStatusEnum $status = LetterStatusEnum::NOT_SENT;

    /** @var Collection Список рассылки */
    #[ORM\OneToMany(mappedBy: 'letter', targetEntity: MailingList::class)]
    private Collection $mailingList;

    /**
     * Конструктор
     */
    public function __construct()
    {
        $this->mailingList = new ArrayCollection();
        $this->attachments = new ArrayCollection();
        $this->smtp = new ArrayCollection();
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     * @return $this
     */
    public function setId(?int $id): static
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getSubject(): ?string
    {
        return $this->subject;
    }

    /**
     * @param string|null $subject
     * @return $this
     */
    public function setSubject(?string $subject): static
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getContent(): ?string
    {
        return $this->content;
    }

    /**
     * @param string|null $content
     * @return $this
     */
    public function setContent(?string $content): static
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @return LetterFormEnum|null
     */
    public function getForm(): ?LetterFormEnum
    {
        return $this->form;
    }

    /**
     * @param $form
     * @return $this
     */
    public function setForm($form): static
    {
        $this->form = $form;

        return $this;
    }

    /**
     * @return Template|null
     */
    public function getTemplate(): ?Template
    {
        return $this->template;
    }

    /**
     * @param Template|null $template
     * @return $this
     */
    public function setTemplate(?Template $template): static
    {
        $this->template = $template;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getSmtp(): Collection
    {
        return $this->smtp;
    }

    /**
     * @param SmtpAccount $smtp
     * @return $this
     */
    public function addSmtp(SmtpAccount $smtp): static
    {
        if (!$this->smtp->contains($smtp)) {
            $this->smtp->add($smtp);
        }
        return $this;
    }

    /**
     * @return int|null
     */
    public function getRecipient(): ?int
    {
        return $this->recipient;
    }

    /**
     * @param int|null $recipient
     * @return $this
     */
    public function setRecipient(?int $recipient): static
    {
        $this->recipient = $recipient;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getAttachments(): Collection
    {
        return $this->attachments;
    }

    /**
     * @param File $attachment
     * @return $this
     */
    public function addAttachment(File $attachment): static
    {
        if (!$this->attachments->contains($attachment)) {
            $this->attachments->add($attachment);
        }
        return $this;
    }

    /**
     * @return array|null
     */
    public function getValues(): ?array
    {
        return $this->values;
    }

    /**
     * @param array|null $values
     * @return $this
     */
    public function setValues(?array $values): static
    {
        $this->values = $values;

        return $this;
    }

    /**
     * @return Uuid|null
     */
    public function getCreator(): ?Uuid
    {
        return $this->creator;
    }

    /**
     * @param Uuid $creator
     * @return $this
     */
    public function setCreator(Uuid $creator): static
    {
        $this->creator = $creator;

        return $this;
    }

    /**
     * @return Uuid|null
     */
    public function getEditor(): ?Uuid
    {
        return $this->editor;
    }

    /**
     * @param Uuid|null $editor
     * @return $this
     */
    public function setEditor(?Uuid $editor): static
    {
        $this->editor = $editor;

        return $this;
    }

    /**
     * @return Uuid|null
     */
    public function getDeleter(): ?Uuid
    {
        return $this->deleter;
    }

    /**
     * @param Uuid|null $deleter
     * @return $this
     */
    public function setDeleter(?Uuid $deleter): static
    {
        $this->deleter = $deleter;

        return $this;
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * @param DateTimeImmutable $createdAt
     * @return $this
     */
    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getEditedAt(): ?\DateTimeImmutable
    {
        return $this->editedAt;
    }

    /**
     * @param DateTimeImmutable|null $editedAt
     * @return $this
     */
    public function setEditedAt(?\DateTimeImmutable $editedAt): static
    {
        $this->editedAt = $editedAt;

        return $this;
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getDeletedAt(): ?\DateTimeImmutable
    {
        return $this->deletedAt;
    }

    /**
     * @param DateTimeImmutable|null $deletedAt
     * @return $this
     */
    public function setDeletedAt(?\DateTimeImmutable $deletedAt): static
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    /**
     * @return Uuid|null
     */
    public function getSender(): ?Uuid
    {
        return $this->sender;
    }

    /**
     * @param Uuid|null $sender
     * @return $this
     */
    public function setSender(?Uuid $sender): static
    {
        $this->sender = $sender;

        return $this;
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getSentAt(): ?\DateTimeImmutable
    {
        return $this->sentAt;
    }

    /**
     * @param DateTimeImmutable|null $sentAt
     * @return $this
     */
    public function setSentAt(?\DateTimeImmutable $sentAt): static
    {
        $this->sentAt = $sentAt;

        return $this;
    }

    /**
     * @return LetterStatusEnum|null
     */
    public function getStatus(): ?LetterStatusEnum
    {
        return $this->status;
    }

    /**
     * @param LetterStatusEnum|null $status
     * @return $this
     */
    public function setStatus(?LetterStatusEnum $status): static
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getMailingList(): Collection
    {
        return $this->mailingList;
    }

    /**
     * @param MailingList $mailing
     */
    public function addMailing(MailingList $mailing): void
    {
        if (!$this->mailingList->contains($mailing)) {
            $this->mailingList->add($mailing);
        }
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
            'subject' => $this->subject,
            'form' => $this->form?->value,
            'template' => $this->template->getId(),
            'smtp' => array_map(static fn(SmtpAccount $smtp) => [
                'id' => $smtp->getId(),
                'title' => $smtp->getTitle()
            ], $this->smtp->toArray()),
            'recipient' => $this->recipient,
            'attachments' => array_map(static fn(File $attachment) => [
                'id' => $attachment->getId(),
                'filename' => $attachment->getFilename()
            ], $this->attachments->toArray()),
            'values' => $this->values,
            'creator' => $this->creator,
            'editor' => $this->editor,
            'deleter' => $this->deleter,
            'created_at' => $this->createdAt,
            'edited_at' => $this->editedAt,
            'deleted_at' => $this->deletedAt,
            'sender' => $this->sender,
            'sent_at' => $this->sentAt,
            'status' => $this->status?->value,
            'mailingList' => array_map(static fn(MailingList $mailing) => $mailing->toArray(), $this->mailingList->toArray())
        ];
    }
}
