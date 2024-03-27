<?php

namespace App\Service;

use App\DTO\Smtp\HistoryDTO;
use App\DTO\Smtp\ListDTO;
use App\DTO\Smtp\EntityDTO;
use App\Entity\SmtpAccount;
use App\Entity\SmtpHistory;
use App\Exception\SmtpException;
use App\Repository\SmtpAccountRepository;
use App\Repository\SmtpHistoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Uuid;

/**
 * Сервис для работы с данными SMTP-аккаунтов
 */
class SmtpService
{
    /**
     * Конструктор
     *
     * @param LoggerInterface        $logger            Логгер
     * @param EntityManagerInterface $entityManager     Интерфейс работы с БД
     * @param EntityValidatorService $entityValidator   Сервис валидации сущностей БД
     * @param SmtpAccountRepository  $smtpRepository    Репозиторий SMTP-аккаунтов
     * @param SmtpHistoryRepository  $historyRepository Репозиторий истории изменений SMTP-аккаунтов
     */
    public function __construct(
        private readonly LoggerInterface        $logger,
        private readonly EntityManagerInterface $entityManager,
        private readonly EntityValidatorService $entityValidator,
        private readonly SmtpAccountRepository  $smtpRepository,
        private readonly SmtpHistoryRepository  $historyRepository
    )
    {
    }

    /**
     * Сохранить данные SMTP-аккаунта
     *
     * @param EntityDTO $params DTO параметров SMTP-аккаунта
     * @param Uuid $userUuid UUID сотрудника сохраняющего данные
     *
     * @return int|null
     * @throws SmtpException
     */
    public function save(EntityDTO $params, Uuid $userUuid): ?int
    {
        $smtp = $params->id ? $this->smtpRepository->find($params->id) : new SmtpAccount();
        if (null === $smtp) {
            throw new SmtpException(
                "SMTP-аккаунта с ID: $params->id не существует",
                SmtpException::NOT_EXISTS
            );
        }

        $changes = [];

        $value = $params->host;
        if ($smtp->getHost() !== $value) {
            $changes['host'] = [
                'old' => $smtp->getHost(),
                'new' => $value
            ];
            $smtp->setHost($value);
        }
        $value = $params->title;
        if ($smtp->getTitle() !== $value) {
            $changes['title'] = [
                'old' => $smtp->getTitle(),
                'new' => $value
            ];
            $smtp->setTitle($value);
        }
        $value = $params->login;
        if ($smtp->getLogin() !== $value) {
            $changes['login'] = [
                'old' => $smtp->getLogin(),
                'new' => $value
            ];
            $smtp->setLogin($value);
        }
        $value = $params->password; // Нужно подумать о шифровании
        if ($smtp->getPassword() !== $value) {
            $changes['password'] = [
                'old' => $smtp->getPassword(),
                'new' => $value
            ];
            $smtp->setPassword($value);
        }
        $value = $params->port;
        if ($smtp->getPort() !== $value) {
            $changes['port'] = [
                'old' => $smtp->getPort(),
                'new' => $value
            ];
            $smtp->setPort($value);
        }

        $value = $params->isSystem;
        if ($smtp->isSystem() !== $value) {
            $changes['isSystem'] = [
                'old' => $smtp->isSystem(),
                'new' => $value
            ];
            $smtp->setSystem($value);
        }
        $value = $params->isActive;
        if ($smtp->isActive() !== $value) {
            $changes['isActive'] = [
                'old' => $smtp->isActive(),
                'new' => $value
            ];
            $smtp->setActive($value);
        }

        $validateResult = $this->entityValidator->validate($smtp);
        if (is_array($validateResult)) {
            throw new SmtpException(
                implode("\n", $validateResult),
                SmtpException::BAD_VALUES
            );
        }

        try {
            $this->entityManager->persist($smtp);
            if ($params->id && !empty($changes)) {
                $history = new SmtpHistory();
                $history->setSmtp($smtp);
                $history->setChanges($changes);
                $history->setEditor($userUuid);
                $history->setEditedAt(new \DateTimeImmutable('now'));
                $this->entityManager->persist($history);
            }
            $this->entityManager->flush();
        } catch (\Throwable $err) {
            $this->logger->error($err->getMessage(), ['Exception' => $err]);
            throw new SmtpException(
                'Ошибка сохранения данных SMTP-аккаунта в БД',
                SmtpException::DB_PROBLEM);
        }

        return $smtp->getId();
    }

    /**
     * Получить список SMTP-аккаунтов
     *
     * @param ListDTO $params DTO параметров выборки SMTP-аккаунтов
     *
     * @return array
     */
    public function list(ListDTO $params): array
    {
        return $this->smtpRepository->findByParams($params);
    }

    /**
     * Получить данные SMTP-аккаунта
     *
     * @param int $id ID SMTP-аккаунта
     *
     * @return array
     */
    public function entity(int $id): array
    {
        return ($result = $this->smtpRepository->find($id))
            ? $result->toArray()
            : [];
    }

    /**
     * Пометить SMTP-аккаунт как удаленный
     *
     * @param int $id ID аккаунта
     * @param Uuid $userUuid UUID сотрудника удалившего аккаунт
     *
     * @return int|null
     * @throws SmtpException
     */
    public function delete(int $id, Uuid $userUuid): ?int
    {
        $smtp = $this->smtpRepository->find($id);
        if (!$smtp) {
            throw new SmtpException(
                "Попытка удалить несуществующий SMTP-аккаунт с ID: $id",
                SmtpException::NOT_EXISTS
            );
        }
        $smtp->setDeleted(true);
        $history = new SmtpHistory();
        $history->setSmtp($smtp);
        $history->setChanges(["deleted" => true]);
        $history->setEditor($userUuid);
        $history->setEditedAt(new \DateTimeImmutable('now'));
        try {
            $this->entityManager->persist($smtp);
            $this->entityManager->persist($history);
            $this->entityManager->flush();
        } catch (\Throwable $err) {
            $this->logger->error($err->getMessage(), ['Exception' => $err]);
            throw new SmtpException(
                'Ошибка сохранения данных SMTP-аккаунта в БД',
                SmtpException::DB_PROBLEM);
        }
        return $smtp->getId();
    }

    /**
     * Получить доступные фильтры для выборки SMTP-аккаунтов
     *
     * @return array
     */
    public function filters(): array
    {
        return $this->smtpRepository->findFilters();
    }

    /**
     * Получить историю изменений SMTP-аккаунтов
     *
     * @param HistoryDTO $params DTO параметров выборки истории изменений
     *
     * @return array|SmtpHistory[]
     */
    public function history(HistoryDTO $params): array
    {
        return $this->historyRepository->findByParams($params);
    }

    /**
     * Получить доступные фильтры для выборки истории изменений
     *
     * @return array
     */
    public function historyFilters(): array
    {
        return $this->historyRepository->findFilters();
    }
}