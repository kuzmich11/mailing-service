<?php

namespace App\Entity;

use App\Repository\TemplateHistoryRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Сущность записи БД для истории изменений Email шаблона
 */
#[ORM\Entity(repositoryClass: TemplateHistoryRepository::class)]
#[ORM\Table(name: 'template_history', schema: 'mail', options: ['comment' => 'История изменения Email шаблонов'])]
#[ORM\Index(name: 'idx__template_history_template_id', columns: ['template_id'])]
#[ORM\Index(name: 'idx__template_history_editor',      columns: ['editor'])]
#[ORM\Index(name: 'idx__template_history_edited_at',   columns: ['edited_at'])]
class TemplateHistory
{
    /** @var int|null ID записи */
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    /** @var Template|null ID изменённого шаблона */
    #[Assert\NotBlank(message: 'Не указан редактируемый шаблон')]
    #[ORM\ManyToOne(targetEntity: Template::class)]
    #[ORM\JoinColumn(name: 'template_id', referencedColumnName: 'id', nullable: false, onDelete: 'cascade', options: ['comment' => 'ID шаблона'])]
    private ?Template $template = null;

    /** @var array Массив с изменениями */
    #[ORM\Column(type: Types::JSON, options: ['comment' => 'Массив с изменениями'])]
    private array $changes = [];

    /** @var Uuid|null UUID сотрудника, редактировавшего шаблон */
    #[ORM\Column(type: UuidType::NAME, options: ['comment' => 'UUID сотрудника, редактировавшего шаблон'])]
    #[Assert\Uuid(message: 'Некорректное значение UUID сотрудника')]
    private ?Uuid $editor = null;

    /** @var DateTimeImmutable|null Когда были внесены изменения */
    #[ORM\Column(precision: 0, options: ['comment' => 'Когда сохранены изменения'])]
    private ?DateTimeImmutable $editedAt = null;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Template|null
     */
    public function getTemplate(): ?Template
    {
        return $this->template;
    }

    /**
     * @param Template $templateId
     * @return $this
     */
    public function setTemplate(Template $templateId): static
    {
        $this->template = $templateId;

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
            'template' => $this->template->getId(),
            'changes' => $this->changes,
            'editor' => $this->editor,
            'edited_at' => $this->editedAt
        ];
    }
}
