<?php

namespace App\Entity;

use App\Repository\DomainRepository;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Класс сущности "Домены"
 */
#[ORM\Entity(repositoryClass: DomainRepository::class)]
#[ORM\Table(name: 'domain', schema: 'mail', options: ['comment' => 'Домены получателей'])]
#[ORM\UniqueConstraint(name: 'idx__domain_name',  columns: ['name'])]
#[ORM\Index(name: 'idx__domain_is_works',         columns: ['is_works'])]
#[ORM\Index(name: 'idx__domain_check_date',       columns: ['check_date'])]
#[ORM\Index(name: 'idx__domain_fail_check_count', columns: ['fail_check_count'])]
#[ORM\Index(name: 'idx__domain_creator',          columns: ['creator'])]
#[ORM\Index(name: 'idx__domain_created_at',       columns: ['created_at'])]
#[ORM\Index(name: 'idx__domain_editor',           columns: ['editor'])]
#[ORM\Index(name: 'idx__domain_edited_at',        columns: ['edited_at'])]
class Domain
{
    /** @var int|null ID записи */
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(options: ['comment' => 'ID домена'])]
    private ?int $id = null;

    /** @var string|null Наименование домена */
    #[ORM\Column(length: 255, options: ['comment' => 'Наименование'])]
    private ?string $name = null;

    /** @var bool|null Флаг true - домен рабочий */
    #[ORM\Column(options: ['comment' => 'Флаг, что домен рабочий'])]
    private ?bool $isWorks = null;

    /** @var DateTimeImmutable|null Дата последней проверки */
    #[ORM\Column(nullable: true, options: ['comment' => 'Дата проверки домена'])]
    private ?DateTimeImmutable $checkDate = null;

    /** @var int|null Кол-во ошибок проверки */
    #[ORM\Column(nullable: true, options: ['comment' => 'Количество ошибок проверки'])]
    private ?int $failCheckCount = null;

    /** @var Uuid|null Создатель записи домена */
    #[ORM\Column(type: UuidType::NAME, options: ['comment' => 'UUID сотрудника, создавшего домен'])]
    #[Assert\Uuid(message: 'Некорректное значение UUID сотрудника')]
    private ?Uuid $creator = null;

    /** @var DateTimeImmutable|null Дата создания записи */
    #[ORM\Column(options: ['comment' => 'Дата создания домена'])]
    private ?DateTimeImmutable $createdAt = null;

    /** @var Uuid|null UUID редактировавшего запись */
    #[ORM\Column(type: UuidType::NAME, nullable: true, options: ['comment' => 'UUID Сотрудника изменившего домен'])]
    private ?Uuid $editor = null;

    /** @var DateTimeImmutable|null Дата редактирования домена */
    #[ORM\Column(nullable: true, options: ['comment' => 'Дата последнего изменения'])]
    private ?DateTimeImmutable $editedAt = null;

    /** @var Collection Получатели состоящие в группе */
    #[ORM\OneToMany(targetEntity: Recipient::class, mappedBy: 'domain')]
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
     * @return bool|null
     */
    public function isWorks(): ?bool
    {
        return $this->isWorks;
    }

    /**
     * @param bool $isWorks
     * @return $this
     */
    public function setWorks(bool $isWorks): static
    {
        $this->isWorks = $isWorks;

        return $this;
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getCheckDate(): ?DateTimeImmutable
    {
        return $this->checkDate;
    }

    /**
     * @param DateTimeImmutable|null $checkDate
     * @return $this
     */
    public function setCheckDate(?DateTimeImmutable $checkDate): static
    {
        $this->checkDate = $checkDate;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getFailCheckCount(): ?int
    {
        return $this->failCheckCount;
    }

    /**
     * @param int $failCheckCount
     * @return $this
     */
    public function setFailCheckCount(int $failCheckCount): static
    {
        $this->failCheckCount = $failCheckCount;

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
     * @return DateTimeImmutable
     */
    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * @param DateTimeImmutable $createdAt
     * @return $this
     */
    public function setCreatedAt(DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

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
     * @return DateTimeInterface
     */
    public function getEditedAt(): DateTimeInterface
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
     * @return Collection
     */
    public function getRecipients(): Collection
    {
        return $this->recipients;
    }

    /**
     * @param Recipient $recipients
     */
    public function addRecipients(Recipient $recipients): void
    {
        if (!$this->recipients->contains($recipients)) {
            $this->recipients->add($recipients);
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
            'id'             => $this->id,
            'name'           => $this->name,
            'isWork'         => $this->isWorks,
            'checkDate'      => $this->checkDate,
            'failCheckCount' => $this->failCheckCount,
            'creator'        => $this->creator,
            'createdAt'      => $this->createdAt,
            'editor'         => $this->editor,
            'editedAt'       => $this->editedAt,
        ];
    }
}
