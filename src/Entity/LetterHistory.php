<?php

namespace App\Entity;

use App\Repository\LetterHistoryRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Класс сущности "История писем"
 */
#[ORM\Entity(repositoryClass: LetterHistoryRepository::class)]
#[ORM\Table(name: 'letter_history', schema: 'mail', options: ['comment' => 'История изменения писем'])]
#[ORM\Index(name: 'idx__letter_history_letter_id', columns: ['letter_id'])]
#[ORM\Index(name: 'idx__letter_history_editor',    columns: ['editor'])]
#[ORM\Index(name: 'idx__letter_history_edited_at', columns: ['edited_at'])]
class LetterHistory
{
    /** @var int|null ID записи */
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    /** @var Letter|null ID измененного письма */
    #[ORM\ManyToOne(targetEntity: Letter::class)]
    #[ORM\JoinColumn(name: 'letter_id', referencedColumnName: 'id', nullable: false, onDelete: 'cascade', options: ['comment' => 'ID письма'])]
    private ?Letter $letter = null;

    /** @var array|null Описание изменений */
    #[ORM\Column(options: ['comment' => 'Массив с изменениями'])]
    private ?array $changes = [];

    /** @var Uuid|null UUID изменившего письмо */
    #[ORM\Column(type: UuidType::NAME, options: ['comment' => 'UUID сотрудника, редактировавшего письмо'])]
    #[Assert\Uuid(message: 'Некорректное значение UUID сотрудника')]
    private ?Uuid $editor = null;

    /** @var DateTimeImmutable|null Дата изменения письма */
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
     * @return array|null
     */
    public function getChanges(): ?array
    {
        return $this->changes;
    }

    /**
     * @param array|null $changes
     * @return $this
     */
    public function setChanges(?array $changes): static
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
            'letter' => $this->letter->getId(),
            'changes' => $this->changes,
            'editor' => $this->editor,
            'edited_at' => $this->editedAt
        ];
    }
}
