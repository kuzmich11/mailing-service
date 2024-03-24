<?php

namespace App\Entity;

use App\Repository\GroupRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Сущность групп получателей
 */
#[ORM\Entity(repositoryClass: GroupRepository::class)]
#[ORM\Table(name: 'group', schema: 'mail',options: ['comment' => 'Группы получателей писем'])]
#[ORM\Index(name: 'idx__group_name',       columns: ['name'])]
#[ORM\Index(name: 'idx__group_creator',    columns: ['creator'])]
#[ORM\Index(name: 'idx__group_editor',    columns: ['editor'])]
#[ORM\Index(name: 'idx__group_deleter',    columns: ['deleter'])]
#[ORM\Index(name: 'idx__group_created_at', columns: ['created_at'])]
#[ORM\Index(name: 'idx__group_edited_at', columns: ['edited_at'])]
#[ORM\Index(name: 'idx__group_deleted_at', columns: ['deleted_at'])]
class Group
{
    /** @var int|null ID группы */
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    /** @var string|null Название группы */
    #[ORM\Column(length: 255, options: ['comment' => 'Название группы'])]
    private ?string $name = null;

    /** @var Uuid|null Создатель группы */
    #[ORM\Column(type: UuidType::NAME, options: ['comment' => 'UUID создавшего группу'])]
    #[Assert\Uuid(message: 'Некорректное значение UUID сотрудника')]
    private ?Uuid $creator = null;

    /** @var Uuid|null Обновивший данные группы */
    #[ORM\Column(type: UuidType::NAME, nullable: true, options: ['comment' => 'UUID обновившего группу'])]
    #[Assert\Uuid(message: 'Некорректное значение UUID сотрудника')]
    private ?Uuid $editor = null;

    /** @var Uuid|null Удаливший группу */
    #[ORM\Column(type: UuidType::NAME, nullable: true, options: ['comment' => 'UUID удалившего группу'])]
    #[Assert\Uuid(message: 'Некорректное значение UUID сотрудника')]
    private ?Uuid $deleter = null;

    /** @var DateTimeImmutable|null Дата создания */
    #[ORM\Column(options: ['comment' => 'Дата создания'])]
    private ?DateTimeImmutable $createdAt = null;

    /** @var DateTimeImmutable|null Дата обновления */
    #[ORM\Column(nullable: true, options: ['comment' => 'Дата обновления'])]
    private ?DateTimeImmutable $editedAt = null;

    /** @var DateTimeImmutable|null Дата удаления */
    #[ORM\Column(nullable: true, options: ['comment' => 'Дата удаления'])]
    private ?DateTimeImmutable $deletedAt = null;

    /** @var Collection Коллекция получателей связанных с группой */
    #[ORM\ManyToMany(targetEntity: Recipient::class, mappedBy: 'groups', orphanRemoval: true)]
    private Collection $recipients;

    /**
     * Конструктор
     */
    public function __construct()
    {
        $this->recipients = new ArrayCollection();
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName(string $name): static
    {
        $this->name = $name;

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
     * @param Uuid|null $creator
     * @return Group
     */
    public function setCreator(?Uuid $creator): static
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
    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * @param DateTimeImmutable|null $createdAt
     * @return $this
     */
    public function setCreatedAt(?DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

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
     * @param DateTimeImmutable|null $editedAt
     * @return $this
     */
    public function setEditedAt(?DateTimeImmutable $editedAt): static
    {
        $this->editedAt = $editedAt;

        return $this;
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getDeletedAt(): ?DateTimeImmutable
    {
        return $this->deletedAt;
    }

    /**
     * @param DateTimeImmutable|null $deletedAt
     * @return $this
     */
    public function setDeletedAt(?DateTimeImmutable $deletedAt): static
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    /**
     * Установить массив с ID Получателей в коллекцию их объектов
     *
     * @param array $recipientIds ID получателей
     *
     * @return $this
     */
    public function setRecipientIds(array $recipientIds): static
    {
        $recipientIds = array_filter(filter_var($recipientIds, FILTER_VALIDATE_INT, FILTER_REQUIRE_ARRAY));
        $this->recipients = new ArrayCollection($recipientIds);

        return $this;
    }

    /**
     * @return Collection<int, Recipient>
     */
    public function getRecipients(): Collection
    {
        return $this->recipients;
    }

    /**
     * @param Recipient $recipient
     * @return $this
     */
    public function addRecipient(Recipient $recipient): static
    {
        if (!$this->recipients->contains($recipient)) {
            $this->recipients->add($recipient);
            $recipient->addGroup($this);
        }

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
            'id'         => $this->id,
            'name'       => $this->name,
            'creator'    => $this->creator,
            'editor'    => $this->editor,
            'deleter'    => $this->deleter,
            'created_at' => $this->createdAt,
            'edited_at' => $this->editedAt,
            'deleted_at' => $this->deletedAt,
        ];
    }
}
