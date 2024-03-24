<?php

namespace App\Entity;

use App\Repository\SmtpAccountRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Сущность SMTP-аккаунтов
 */
#[ORM\Entity(repositoryClass: SmtpAccountRepository::class)]
#[ORM\Table(name: 'smtp_account', schema: 'mail', options: ['comment' => 'Параметры SMTP-аккаунтов'])]
#[ORM\Index(name: 'idx__smtp_account_system',  columns: ['is_system'])]
#[ORM\Index(name: 'idx__smtp_account_encrypt', columns: ['is_encrypt'])]
#[ORM\Index(name: 'idx__smtp_account_deleted', columns: ['is_deleted'])]
#[ORM\Index(name: 'idx__smtp_account_active',  columns: ['is_active'])]
class SmtpAccount
{
    /** @var int|null ID аккаунта */
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    /** @var string|null Наименование */
    #[ORM\Column(length: 255, nullable: true,options: ['comment' => 'Название аккаунта'])]
    private ?string $title = null;

    /** @var string|null Хост */
    #[ORM\Column(length: 45,options: ['comment' => 'Хост'])]
    private ?string $host = null;

    /** @var string|null Логин */
    #[ORM\Column(length: 100,options: ['comment' => 'Логин'])]
    private ?string $login = null;

    /** @var string|null Пароль */
    #[ORM\Column(length: 45,options: ['comment' => 'Пароль'])]
    private ?string $password = null;

    /** @var int|null Порт */
    #[ORM\Column(options: ['default' => 25, 'comment' => 'Порт'])]
    private ?int $port = 25;

    /** @var bool|null Тип аккаунта */
    #[ORM\Column(options: ['comment' => 'Аккаунт является системным'])]
    private ?bool $isSystem = false;

    /** @var bool|null Флаг удаления true - удален */
    #[ORM\Column(options: ['default' => false, 'comment' => 'Флаг удаления'])]
    private ?bool $isDeleted = false;

    /** @var bool|null Флаг активности true - используется */
    #[ORM\Column(options: ['default' => false, 'comment' => 'Флаг активного состояния'])]
    private ?bool $isActive = false;

    /**
     * Получить DSN-строку для подключения
     *
     * @param string $schema   Схема подключения - "{ПРЕФИКС}://..."
     * @param bool   $forDebug Флаг отладки - (verify_peer=0)
     *
     * @return string
     */
    public function getDSN(string $schema = 'smtp', bool $forDebug = false): string
    {
        return sprintf('%s://%s:%s@%s:%d%s',
            ($schema ?: 'smtp'),
            $this->getLogin(),
            $this->getPassword(),
            $this->getHost(),
            $this->getPort(),
            ($forDebug ? '?verify_peer=0' : '')
        );
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
    public function getHost(): ?string
    {
        return $this->host;
    }

    /**
     * @param string|null $host
     * @return $this
     */
    public function setHost(?string $host): static
    {
        $this->host = $host;

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
     * @param string|null $title
     * @return $this
     */
    public function setTitle(?string $title): static
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getLogin(): ?string
    {
        return $this->login;
    }

    /**
     * @param string|null $login
     * @return $this
     */
    public function setLogin(?string $login): static
    {
        $this->login = $login;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * @param string|null $password
     * @return $this
     */
    public function setPassword(?string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getPort(): ?int
    {
        return $this->port;
    }

    /**
     * @param int|null $port
     * @return $this
     */
    public function setPort(?int $port): static
    {
        $this->port = $port;

        return $this;
    }

    /**
     * @return bool|null
     */
    public function isSystem(): ?bool
    {
        return $this->isSystem;
    }

    /**
     * @param bool|null $isSystem
     * @return $this
     */
    public function setSystem(?bool $isSystem): static
    {
        $this->isSystem = $isSystem;

        return $this;
    }

    /**
     * @return bool|null
     */
    public function isDeleted(): ?bool
    {
        return $this->isDeleted;
    }

    /**
     * @param bool|null $isDeleted
     * @return $this
     */
    public function setDeleted(?bool $isDeleted): static
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }

    /**
     * @return bool|null
     */
    public function isActive(): ?bool
    {
        return $this->isActive;
    }

    /**
     * @param bool|null $isActive
     * @return $this
     */
    public function setActive(?bool $isActive): static
    {
        $this->isActive = $isActive;

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
            'id'                       => $this->id,
            'host'                     => $this->host,
            'title'                    => $this->title,
            'login'                    => $this->login,
            'password'                 => $this->password,
            'port'                     => $this->port,
            'is_system'                => $this->isSystem,
            'is_deleted'               => $this->isDeleted,
            'is_active'                => $this->isActive,
        ];
    }
}