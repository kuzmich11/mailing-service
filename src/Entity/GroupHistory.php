<?php

namespace App\Entity;

use App\Repository\GroupHistoryRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Класс сущности "История изменения групп получателей"
 */
#[ORM\Entity(repositoryClass: GroupHistoryRepository::class)]
#[ORM\Table(name: 'group_history', schema: 'mail', options: ['comment' => 'История изменения групп'])]
#[ORM\Index(name: 'idx__group_history_group_id',  columns: ['group_id'])]
#[ORM\Index(name: 'idx__group_history_editor',    columns: ['editor'])]
#[ORM\Index(name: 'idx__group_history_edited_at', columns: ['edited_at'])]
class GroupHistory
{
    /** @var int|null ID записи */
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    /** @var Group|null ID группы */
    #[ORM\ManyToOne(targetEntity: Group::class)]
    #[ORM\JoinColumn(name: 'group_id', referencedColumnName: 'id', nullable: false, onDelete: 'cascade', options: ['comment' => 'ID группы'])]
    private ?Group $group = null;

    /** @var array Измененные данные */
    #[ORM\Column(options: ['comment' => 'Массив с изменениями'])]
    private array $changes = [];

    /** @var Uuid|null UUID сотрудника, изменившего данные */
    #[ORM\Column(type: UuidType::NAME, options: ['comment' => 'UUID сотрудника, редактировавшего группу'])]
    #[Assert\Uuid(message: 'Некорректное значение UUID сотрудника')]
    private ?Uuid $editor = null;

    /** @var DateTimeImmutable|null Дата изменения данных */
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
     * @return Group|null
     */
    public function getGroup(): ?Group
    {
        return $this->group;
    }

    /**
     * @param Group $group
     * @return $this
     */
    public function setGroup(Group $group): static
    {
        $this->group = $group;

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
            'group'     => $this->group->getId(),
            'changes'   => $this->changes,
            'editor'    => $this->editor,
            'edited_at' => $this->editedAt
        ];
    }
}
