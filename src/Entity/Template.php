<?php

namespace App\Entity;

use App\Repository\TemplateRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Сущность записи БД для Email шаблона
 */
#[ORM\Entity(repositoryClass: TemplateRepository::class)]
#[ORM\Table(name: 'template', schema: 'mail', options: ['comment' => 'Шаблоны для писем'])]
#[ORM\UniqueConstraint(name: 'idx__template_title_exists', columns: ['title', 'deleter', 'deleted_at'])]
#[ORM\Index(name: 'idx__template_title',       columns: ['title'])]
#[ORM\Index(name: 'idx__template_parent_id',   columns: ['parent_id'])]
#[ORM\Index(name: 'idx__template_creator',     columns: ['creator'])]
#[ORM\Index(name: 'idx__template_created_at',  columns: ['created_at'])]
#[ORM\Index(name: 'idx__template_editor',      columns: ['editor'])]
#[ORM\Index(name: 'idx__template_edited_at',   columns: ['edited_at'])]
#[ORM\Index(name: 'idx__template_deleter',     columns: ['deleter'])]
#[ORM\Index(name: 'idx__template_deleted_at',  columns: ['deleted_at'])]
class Template
{
    /** @var int|null ID записи */
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    /** @var Template|null Родительский шаблон */
    #[ORM\ManyToOne(targetEntity: self::class)]
    #[ORM\JoinColumn(name: 'parent_id', referencedColumnName: 'id', nullable: true, options: ['comment' => 'ID родительского шаблона'])]
    private ?Template $parent = null;

    /** @var string|null Название */
    #[ORM\Column(length: 255, options: ['comment' => 'Название'])]
    #[Assert\NotBlank(message: 'Название шаблона не может быть пустым')]
    private ?string $title = null;

    /** @var string|null Содержимое */
    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => 'Содержимое'])]
    private ?string $content = null;

    /** @var string|null Тема для письма */
    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => 'Тема для письма'])]
    private ?string $subject = null;

    /** @var array Массив с плейсхолдерами (переменными) */
    #[ORM\Column(type: Types::JSON, options: ['default' => '{}', 'comment' => 'Массив с плейсхолдерами (переменными)'])]
    private array $variables = [];

    /** @var Uuid|null UUID сотрудника, создавшего шаблон */
    #[ORM\Column(type: UuidType::NAME, options: ['comment' => 'UUID сотрудника, создавшего шаблон'])]
    #[Assert\Uuid(message: 'Некорректное значение UUID сотрудника')]
    private ?Uuid $creator = null;

    /** @var DateTimeImmutable|null Когда создан шаблон */
    #[ORM\Column(precision: 0, options: ['default' => 'CURRENT_TIMESTAMP', 'comment' => 'Когда создан шаблон'])]
    private ?DateTimeImmutable $created_at = null;

    /** @var Uuid|null UUID сотрудника, редактировавшего шаблон */
    #[ORM\Column(type: UuidType::NAME, nullable: true, options: ['comment' => 'UUID сотрудника, редактировавшего шаблон'])]
    #[Assert\Uuid(message: 'Некорректное значение UUID сотрудника')]
    private ?Uuid $editor = null;

    /** @var DateTimeImmutable|null Когда было предыдущее редактирование */
    #[ORM\Column(precision: 0, nullable: true, options: ['comment' => 'Когда было предыдущее редактирование'])]
    private ?DateTimeImmutable $edited_at = null;

    /** @var Uuid|null UUID сотрудника, кто удалил шаблон */
    #[ORM\Column(type: UuidType::NAME, nullable: true, options: ['comment' => 'UUID сотрудника, кто удалил шаблон'])]
    #[Assert\Uuid(message: 'Некорректное значение UUID сотрудника')]
    private ?Uuid $deleter = null;

    /** @var DateTimeImmutable|null Когда шаблон был удален */
    #[ORM\Column(precision: 0, nullable: true, options: ['comment' => 'Когда шаблон был удален'])]
    private ?DateTimeImmutable $deleted_at = null;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return $this
     */
    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return $this|null
     */
    public function getParent(): ?self
    {
        return $this->parent;
    }

    /**
     * @param Template|null $parent Объект "родительского" шаблона
     * @return $this
     */
    public function setParent(?self $parent): static
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return $this
     */
    public function setTitle(string $title): static
    {
        $this->title = $title;

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
     * @param string $content
     * @return $this
     */
    public function setContent(string $content): static
    {
        $this->content = $content;

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
     * @param string $subject
     * @return $this
     */
    public function setSubject(string $subject): static
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * @return array
     */
    public function getVariables(): array
    {
        return $this->variables;
    }

    /**
     * @param array $variables
     * @return $this
     */
    public function setVariables(array $variables): static
    {
        $this->variables = $variables;

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
     * @return DateTimeImmutable|null
     */
    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->created_at;
    }

    /**
     * @param DateTimeImmutable $createdAt
     * @return $this
     */
    public function setCreatedAt(DateTimeImmutable $createdAt): static
    {
        $this->created_at = $createdAt;

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
     * @return DateTimeImmutable|null
     */
    public function getEditedAt(): ?DateTimeImmutable
    {
        return $this->edited_at;
    }

    /**
     * @param DateTimeImmutable|null $editedAt
     * @return $this
     */
    public function setEditedAt(?DateTimeImmutable $editedAt): static
    {
        $this->edited_at = $editedAt;

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
    public function getDeletedAt(): ?DateTimeImmutable
    {
        return $this->deleted_at;
    }

    /**
     * @param DateTimeImmutable|null $deletedAt
     * @return $this
     */
    public function setDeletedAt(?DateTimeImmutable $deletedAt): static
    {
        $this->deleted_at = $deletedAt;

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
            'parent' => $this->parent?->getId(),
            'title' => $this->title,
            'content' => $this->content,
            'subject' => $this->subject,
            'variables' => $this->variables,
            'creator' => $this->creator,
            'editor' => $this->editor,
            'deleter' => $this->deleter,
            'created_at' => $this->created_at,
            'edited_at' => $this->edited_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
