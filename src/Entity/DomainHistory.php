<?php

namespace App\Entity;

use App\Repository\DomainHistoryRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Класс сущности "История изменения доменов"
 */
#[ORM\Entity(repositoryClass: DomainHistoryRepository::class)]
#[ORM\Table(name: 'domain_history', schema: 'mail', options: ['comment' => 'История изменения доменов'])]
#[ORM\Index(name: 'idx__domain_history_domain_id', columns: ['domain_id'])]
#[ORM\Index(name: 'idx__domain_history_editor',    columns: ['editor'])]
#[ORM\Index(name: 'idx__domain_history_edited_at', columns: ['edited_at'])]
class DomainHistory
{
    /** @var int|null ID записи */
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    /** @var Domain|null ID домена */
    #[ORM\ManyToOne(targetEntity: Domain::class)]
    #[ORM\JoinColumn(name: 'domain_id', referencedColumnName: 'id', nullable: false, onDelete: 'cascade', options: ['comment' => 'ID домена'])]
    private ?Domain $domain = null;

    /** @var array Изменения */
    #[ORM\Column(options: ['comment' => 'Массив с изменениями'])]
    private array $changes = [];

    /** @var Uuid|null UUID редактировавшего данные домена */
    #[ORM\Column(type: UuidType::NAME, options: ['comment' => 'UUID сотрудника, редактировавшего домен'])]
    #[Assert\Uuid(message: 'Некорректное значение UUID сотрудника')]
    private ?Uuid $editor = null;

    /** @var DateTimeImmutable|null Дата редактирования данных домена */
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
            'domain' => $this->domain->getId(),
            'changes' => $this->changes,
            'editor' => $this->editor,
            'edited_at' => $this->editedAt
        ];
    }
}
