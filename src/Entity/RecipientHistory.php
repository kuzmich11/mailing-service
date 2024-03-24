<?php

namespace App\Entity;

use App\Repository\RecipientHistoryRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Класс сущности "История изменения получателей"
 */
#[ORM\Entity(repositoryClass: RecipientHistoryRepository::class)]
#[ORM\Table(name: 'recipient_history', schema: 'mail', options: ['comment' => 'История изменения данных получателя'])]
#[ORM\Index(name: 'idx__recipient_history_recipient_id', columns: ['recipient_id'])]
#[ORM\Index(name: 'idx__recipient_history_editor',       columns: ['editor'])]
#[ORM\Index(name: 'idx__recipient_history_edited_at',    columns: ['edited_at'])]
class RecipientHistory
{
    /** @var int|null ID записи */
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    /** @var Recipient|null ID получателя */
    #[ORM\ManyToOne(targetEntity: Recipient::class)]
    #[ORM\JoinColumn(name: 'recipient_id', referencedColumnName: 'id', nullable: false, onDelete: 'cascade', options: ['comment' => 'ID получателя'])]
    private ?Recipient $recipient = null;

    /** @var array Измененные данные */
    #[ORM\Column(options: ['comment' => 'Массив с изменениями'])]
    private array $changes = [];

    /** @var Uuid|null UUID сотрудника изменившего данные */
    #[ORM\Column(type: UuidType::NAME, options: ['comment' => 'UUID сотрудника, редактировавшего группу'])]
    #[Assert\Uuid(message: 'Некорректное значение UUID сотрудника')]
    private ?Uuid $editor = null;

    /** @var DateTimeImmutable|null Дата внесения изменений */
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
            'id'        => $this->id,
            'recipient' => $this->recipient->getId(),
            'changes'   => $this->changes,
            'editor'    => $this->editor,
            'edited_at' => $this->editedAt
        ];
    }
}
