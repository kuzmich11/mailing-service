<?php

namespace App\Entity;

use App\Repository\SmtpHistoryRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Класс сущности Истории изменения SMTP-аккаунтов
 */
#[ORM\Entity(repositoryClass: SmtpHistoryRepository::class)]
#[ORM\Table(name: 'smtp_history', schema: 'mail', options: ['comment' => 'История изменения аккаунтов'])]
#[ORM\Index(name: 'idx__smtp_history_smtp_id',   columns: ['smtp_id'])]
#[ORM\Index(name: 'idx__smtp_history_editor',    columns: ['editor'])]
#[ORM\Index(name: 'idx__smtp_history_edited_at', columns: ['edited_at'])]
class SmtpHistory
{
    /** @var int|null ID записи */
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    /** @var SmtpAccount|null ID SMTP-аккаунта */
    #[ORM\ManyToOne(targetEntity: SmtpAccount::class)]
    #[ORM\JoinColumn(name: 'smtp_id', referencedColumnName: 'id', nullable: false, onDelete: 'cascade', options: ['comment' => 'ID группы'])]
    private ?SmtpAccount $smtp = null;

    /** @var array Массив с изменениями */
    #[ORM\Column(options: ['comment' => 'Массив с изменениями'])]
    private array $changes = [];

    /** @var Uuid|null UUID UUID сотрудника, редактировавшего SMTP-аккаунт */
    #[ORM\Column(type: UuidType::NAME, options: ['comment' => 'UUID сотрудника, редактировавшего аккаунт'])]
    #[Assert\Uuid(message: 'Некорректное значение UUID сотрудника')]
    private ?Uuid $editor = null;

    /** @var DateTimeImmutable|null Когда были внесены изменения */
    #[ORM\Column(options: ['comment' => 'Когда сохранены изменения'])]
    private ?DateTimeImmutable $editedAt = null;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
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
     * @return array
     */
    public function getChanges(): array
    {
        return $this->changes;
    }

    /**
     * @param array $changes
     * @return $this
     */
    public function setChanges(array $changes): static
    {
        $this->changes = $changes;

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
     * @param Uuid $editor
     * @return $this
     */
    public function setEditor(Uuid $editor): static
    {
        $this->editor = $editor;

        return $this;
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getEditedAt(): ?DateTimeImmutable
    {
        return $this->editedAt;
    }

    /**
     * @param DateTimeImmutable $editedAt
     * @return $this
     */
    public function setEditedAt(DateTimeImmutable $editedAt): static
    {
        $this->editedAt = $editedAt;

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
            'smtp' => $this->smtp->getId(),
            'changes' => $this->changes,
            'editor' => $this->editor,
            'edited_at' => $this->editedAt
        ];
    }
}
