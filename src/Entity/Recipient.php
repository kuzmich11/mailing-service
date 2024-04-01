<?php

namespace App\Entity;

use App\Enum\EmailStateEnum;
use App\Repository\RecipientRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Сущность получатели
 */
#[ORM\Entity(repositoryClass: RecipientRepository::class)]
#[ORM\Table(name: 'recipient', schema: 'mail',options: ['comment' => 'Получатели писем'])]
#[ORM\UniqueConstraint(name: 'idx__recipient_email',columns: ['email'])]
#[ORM\Index(name: 'idx__recipient_state',      columns: ['state'])]
#[ORM\Index(name: 'idx__recipient_is_consent', columns: ['is_consent'])]
#[ORM\Index(name: 'idx__recipient_creator',    columns: ['creator'])]
#[ORM\Index(name: 'idx__recipient_editor',     columns: ['editor'])]
#[ORM\Index(name: 'idx__recipient_deleter',    columns: ['deleter'])]
#[ORM\Index(name: 'idx__recipient_created_at', columns: ['created_at'])]
#[ORM\Index(name: 'idx__recipient_edited_at',  columns: ['edited_at'])]
#[ORM\Index(name: 'idx__recipient_deleted_at', columns: ['deleted_at'])]
#[ORM\Index(name: 'idx__recipient_domain_id',  columns: ['domain_id'])]
class Recipient
{
    /** @var int|null ID получателя */
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    /** @var string|null Email получателя */
    #[ORM\Column(length: 255, options: ['comment' => 'Email'])]
    #[Assert\Email(message: 'Некорректный формат Email адреса')]
    private ?string $email = null;

    /** @var string|null Комментарий */
    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => 'Комментарий'])]
    private ?string $comment = null;

    /** @var EmailStateEnum|null Статус почты получателя */
    #[ORM\Column(type: Types::STRING, nullable: true, enumType: EmailStateEnum::class, options: ['default' => 'UNCONFIRMED', 'comment' => 'Статус email'])]
    private ?EmailStateEnum $state = null;

    /** @var bool|null Согласие на рассылку */
    #[ORM\Column(options: ['comment' => 'Согласие на рассылку'])]
    private ?bool $isConsent = null;

    /** @var Uuid|null Создатель записи получателя */
    #[ORM\Column(type: UuidType::NAME, options: ['comment' => 'Кто создал письмо'])]
    #[Assert\Uuid(message: 'Некорректное значение UUID сотрудника')]
    private ?Uuid $creator = null;

    /** @var Uuid|null Обновивший данные получателя */
    #[ORM\Column(type: UuidType::NAME, nullable: true, options: ['comment' => 'Кто изменил письмо'])]
    #[Assert\Uuid(message: 'Некорректное значение UUID сотрудника')]
    private ?Uuid $editor = null;

    /** @var Uuid|null Удаливший получателя */
    #[ORM\Column(type: UuidType::NAME, nullable: true, options: ['comment' => 'Кто удалил письмо'])]
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

    /** @var Collection Группы получателей */
    #[ORM\ManyToMany(targetEntity: Group::class, inversedBy: 'recipients')]
    #[ORM\JoinTable(name: 'recipient_group', schema: 'mail', options: ['comment' => 'Связующая таблица получателей с группами'])]
    #[ORM\JoinColumn(name: 'recipient_id', referencedColumnName: 'id', options: ['comment' => 'Получатель'])]
    #[ORM\InverseJoinColumn(name: 'group_id', referencedColumnName: 'id', options: ['comment' => 'Группа'])]
    private Collection $groups;

    /** @var Domain ID домена */
    #[ORM\ManyToOne(targetEntity: Domain::class, inversedBy: 'recipients')]
    #[ORM\JoinColumn(name: 'domain_id', referencedColumnName: 'id', options: ['comment' => 'ID домена'])]
    private Domain $domain;

    /**
     * Конструктор
     */
    public function __construct()
    {
        $this->groups = new ArrayCollection();
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
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return $this
     */
    public function setEmail(string $email): static
    {
        $this->email = $email;

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
     * @param string $comment
     * @return $this
     */
    public function setComment(string $comment): static
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * @return EmailStateEnum|null
     */
    public function getState(): ?EmailStateEnum
    {
        return $this->state;
    }

    /**
     * @param EmailStateEnum|null $state
     * @return $this
     */
    public function setState(?EmailStateEnum $state): static
    {
        $this->state = $state;

        return $this;
    }

    /**
     * @return bool|null
     */
    public function isConsent(): ?bool
    {
        return $this->isConsent;
    }

    /**
     * @param bool $isConsent
     * @return $this
     */
    public function setConsent(bool $isConsent): static
    {
        $this->isConsent = $isConsent;

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
     * @return $this
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
     * @return Collection<int, Group>
     */
    public function getGroups(): Collection
    {
        return $this->groups;
    }

    /**
     * @param Group $group
     * @return $this
     */
    public function addGroup(Group $group): static
    {
        if (!$this->groups->contains($group)) {
            $this->groups->add($group);
        }

        return $this;
    }

    public function clearGroup(): void
    {
        $this->groups->clear();
    }

    /**
     * @return Domain|null
     */
    public function getDomain(): ?Domain
    {
        return $this->domain;
    }

    /**
     * @param Domain $domain
     * @return $this
     */
    public function setDomain(Domain $domain): static
    {
        $this->domain = $domain;

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
            'email'     => $this->email,
            'state'     => $this->state->value,
            'isConsent' => $this->isConsent,
            'creator'   => $this->creator,
            'editor'   => $this->editor,
            'deleter'   => $this->deleter,
            'createdAt' => $this->createdAt,
            'editedAt' => $this->editedAt,
            'deletedAt' => $this->deletedAt,
            'domain'    => $this->domain->toArray(),
            'groups'    => array_map(fn(Group $group) => $group->getId(), $this->groups->toArray())
        ];
    }
}
