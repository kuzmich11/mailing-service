<?php

namespace App\Entity;

use App\Repository\FileRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Класс сущности "Файлы(вложения)"
 */
#[ORM\Entity(repositoryClass: FileRepository::class)]
#[ORM\Table(name: 'file', schema: 'mail', options: ['comment' => 'Файлы(вложения)'])]
#[ORM\UniqueConstraint(name: 'idx__file_hash', columns: ['hash'])]
class File
{
    /** @var int|null Идентификатор */
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    /** @var string|null Наименование */
    #[ORM\Column(options: ['comment' => 'Название'])]
    #[Assert\NotBlank(message: 'Название файла не может быть пустым')]
    private ?string $filename = null;

    /** @var int|null Размер */
    #[ORM\Column(options: ['comment' => 'Размер'])]
    #[Assert\Positive(message: 'Размер файла не может быть меньше или равным нулю')]
    private ?int $fileSize = null;

    /** @var string|null Тип */
    #[ORM\Column(options: ['comment' => 'Тип Mime'])]
    #[Assert\NotBlank(message: 'Тип файла не может быть пустым')]
    private ?string $mimeType = null;

    /** @var string|null Хэш */
    #[ORM\Column(length: 32, unique: true, options: ['comment' => 'Хэш'])]
    private ?string $hash = null;

    /** @var DateTimeImmutable|null Дата загрузки */
    #[ORM\Column(options: ['comment' => 'Дата загрузки'])]
    private ?DateTimeImmutable $uploadAt = null;

    /** @var DateTimeImmutable|null Дата удаления */
    #[ORM\Column(nullable: true, options: ['comment' => 'Дата удаления'])]
    private ?DateTimeImmutable $deletedAt = null;

    /** @var Collection|ArrayCollection Связанные письма */
    #[ORM\ManyToMany(targetEntity: 'Letter', mappedBy: 'attachments')]
    private Collection|ArrayCollection $letters;

    /**
     * Конструктор
     */
    public function __construct()
    {
        $this->letters = new ArrayCollection();
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
    public function getFilename(): ?string
    {
        return $this->filename;
    }

    /**
     * @param string $filename
     * @return $this
     */
    public function setFilename(string $filename): static
    {
        $this->filename = $filename;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getFileSize(): ?int
    {
        return $this->fileSize;
    }

    /**
     * @param int $fileSize
     * @return $this
     */
    public function setFileSize(int $fileSize): static
    {
        $this->fileSize = $fileSize;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    /**
     * @param string $mimeType
     * @return $this
     */
    public function setMimeType(string $mimeType): static
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getHash(): ?string
    {
        return $this->hash;
    }

    /**
     * @param string $hash
     * @return $this
     */
    public function setHash(string $hash): static
    {
        $this->hash = $hash;

        return $this;
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getUploadAt(): ?DateTimeImmutable
    {
        return $this->uploadAt;
    }

    /**
     * @param DateTimeImmutable $uploadAt
     * @return $this
     */
    public function setUploadAt(DateTimeImmutable $uploadAt): static
    {
        $this->uploadAt = $uploadAt;

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
     * @return Collection
     */
    public function getLetters(): Collection
    {
        return $this->letters;
    }

    /**
     * @param Letter $letter
     */
    public function addLetter(Letter $letter): void
    {
        if (!$this->letters->contains($letter)) {
            $this->letters->add($letter);
            $letter->addAttachment($this);
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
            'id' => $this->id,
            'name' => $this->filename,
            'size' => $this->fileSize,
            'type' => $this->mimeType,
            'hash' => $this->hash,
            'uploadAt' => $this->uploadAt,
            'deleted' => $this->deletedAt
        ];
    }
}
