<?php

namespace App\Service;

use App\DTO\Group\AddDTO;
use App\DTO\Group\HistoryDTO;
use App\DTO\Group\ListDTO;
use App\DTO\Group\ParamsDTO;
use App\Entity\GroupHistory;
use App\Entity\Group;
use App\Entity\Recipient;
use App\Exception\GroupException;
use App\Repository\GroupHistoryRepository;
use App\Repository\GroupRepository;
use App\Repository\RecipientRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Uuid;

/**
 * Сервис работы с данными групп
 */
class GroupService
{
    /**
     * Конструктор
     *
     * @param GroupRepository        $groupRepository     Репозиторий групп
     * @param RecipientRepository    $recipientRepository Репозиторий получателей
     * @param GroupHistoryRepository $history             Репозиторий истории изменения групп
     * @param EntityManagerInterface $entityManager       Интерфейс работы с БД
     * @param LoggerInterface        $logger              Логгер
     * @param EntityValidatorService $entityValidator     Сервис валидации данных
     */
    public function __construct(
        private readonly GroupRepository        $groupRepository,
        private readonly RecipientRepository    $recipientRepository,
        private readonly GroupHistoryRepository $history,
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface        $logger,
        private readonly EntityValidatorService $entityValidator
    )
    {
    }

    /**
     * Сохранить в БД информацию записи группы получателей
     *
     * @param ParamsDTO $params DTO параметров для сохранения|изменения группы
     * @param Uuid $userUuid UUID сотрудника
     *
     * @return int|null
     * @throws GroupException
     */
    public function save(ParamsDTO $params, Uuid $userUuid): ?int
    {
        $group = $params->id ? $this->groupRepository->find($params->id) : new Group();
        if (null === $group) {
            throw new GroupException(
                "Для изменения отсутствует группа получателей с ID: $params->id",
                GroupException::NOT_EXISTS
            );
        }

        $changes = [];
        $value = $params->name;
        if ($group->getName() !== $value) {
            $changes['name'] = [
                'old' => $group->getName(),
                'new' => $params->name
            ];
            $group->setName($params->name);
        }
        if ($params->recipientIds) {
            $recipients = $this->recipientRepository->findByIds($params->recipientIds);
            if (!$recipients) {
                throw new GroupException(
                    "Заданные получатели не найдены",
                    GroupException::NOT_EXISTS
                );
            }
            array_walk($recipients, fn($recipient) => $group->addRecipient($recipient));
            $changes['addRecipients'] = [
                'recipientIds' => $params->recipientIds
            ];
        }
        if ($params->id) {
            $group->setUpdater($userUuid);
            $group->setUpdatedAt(new DateTimeImmutable('now'));
        } else {
            $group->setCreator($userUuid);
            $group->setCreatedAt(new DateTimeImmutable('now'));
        }

        $validateResult = $this->entityValidator->validate($group);
        if (is_array($validateResult)) {
            throw new GroupException(
                implode("\n", $validateResult),
                GroupException::BAD_VALUES
            );
        }

        try {
            $this->entityManager->persist($group);
            if ($params->id && !empty($changes)) {
                $history = new GroupHistory();
                $history->setGroup($group);
                $history->setChanges($changes);
                $history->setEditor($userUuid);
                $history->setEditedAt(new \DateTimeImmutable('now'));
                $this->entityManager->persist($history);
            }
            $this->entityManager->flush();
        } catch (\Throwable $err) {
            $this->logger->error($err->getMessage(), ['Exception' => $err]);
            throw new GroupException(
                'Ошибка сохранения данных группы в БД',
                GroupException::DB_PROBLEM);
        }

        return $group->getId();
    }

    /**
     * Пометить группу как удаленную
     *
     * @param int $id ID группы
     * @param Uuid $userUuid UUID сотрудника
     *
     * @return int|null
     * @throws GroupException
     */
    public function delete(int $id, Uuid $userUuid): ?int
    {
        $group = $this->groupRepository->find($id);
        if (null === $group) {
            throw new GroupException(
                "Попытка удалить несуществующую группу получателей с ID: $id",
                GroupException::NOT_EXISTS
            );
        }
        $group->setDeleter($userUuid);
        $group->setDeletedAt(new DateTimeImmutable('now'));
        try {
            $this->entityManager->persist($group);
            $this->entityManager->flush();
        } catch (\Throwable $err) {
            $this->logger->error($err->getMessage(), ['Exception' => $err]);
            throw new GroupException(
                'Ошибка сохранения данных группы в БД',
                GroupException::DB_PROBLEM
            );
        }
        return $group->getId();
    }

    /**
     * Получить список групп
     *
     * @param ListDTO $params DTO параметров для фильтрации списка групп
     *
     * @return array
     */
    public function list(ListDTO $params): array
    {
        return $this->groupRepository->findByParams($params);
    }

    /**
     * Получить возможные параметры фильтрации
     *
     * @return array
     */
    public function filters(): array
    {
        return $this->groupRepository->findFilters();
    }

    /**
     * Получить данные группы
     *
     * @param int $id ID группы
     *
     * @return array
     */
    public function group(int $id): array
    {

        return ($group = $this->groupRepository->find($id))
            ? $group->toArray()
            : [];
    }

    /**
     * Добавить получателя в группу
     *
     * @param AddDTO $addDTO DTO параметров добавления
     * @param Uuid $userUuid UUID сотрудника
     *
     * @return bool
     * @throws GroupException
     */
    public function addRecipient(AddDTO $addDTO, Uuid $userUuid): bool
    {
        $group = $this->groupRepository->find($addDTO->groupId);
        if (null === $group) {
            throw new GroupException(
                "Попытка добавить получателя в несуществующую группу с ID: $addDTO->groupId",
                GroupException::NOT_EXISTS
            );
        }
        $recipients = $this->recipientRepository->findByIds($addDTO->recipientIds);
        if (empty($recipients)) {
            throw new GroupException(
                "Нет получателей для добавления в группу с ID: $addDTO->groupId",
                GroupException::NOT_EXISTS
            );
        }
        array_walk($recipients, fn($recipient) => $group->addRecipient($recipient));
        $changes['addRecipients'] = [
            'recipientIds' => $addDTO->recipientIds
        ];
        try {
            $this->entityManager->persist($group);
            $history = new GroupHistory();
            $history->setGroup($group);
            $history->setChanges($changes);
            $history->setEditor($userUuid);
            $history->setEditedAt(new \DateTimeImmutable('now'));
            $this->entityManager->persist($history);
            $this->entityManager->flush();
        } catch (\Throwable $err) {
            $this->logger->error($err->getMessage(), ['Exception' => $err]);
            throw new GroupException(
                'Ошибка сохранения данных группы в БД',
                GroupException::DB_PROBLEM
            );
        }
        return true;
    }

    /**
     * Получить данные получателей связанных с группой
     *
     * @param int $id ID группы
     *
     * @return array
     * @throws GroupException
     */
    public function recipients(int $id): array
    {
        $group = $this->groupRepository->find($id);
        if (null === $group) {
            throw new GroupException(
                "Группы с ID: $id не существует",
                GroupException::NOT_EXISTS
            );
        }
        return $group->getRecipients()->toArray();
    }

    /**
     * Получить историю изменения групп получателей
     *
     * @param HistoryDTO $params DTO для получения истории изменений групп
     *
     * @return array
     */
    public function history(HistoryDTO $params): array
    {
        return $this->history->findByParams($params);
    }

    /**
     * Получить возможные фильтры для истории
     *
     * @return array
     */
    public function historyFilters(): array
    {
        return $this->history->findFilter();
    }

    /**
     * @throws GroupException
     * @throws Exception
     */
    public function removeRecipient(AddDTO $addDTO, Uuid $userUuid): bool
    {
        $group = $this->groupRepository->find($addDTO->groupId);
        if (null === $group) {
            throw new GroupException(
                "Попытка удалить получателя из несуществующей группы с ID: $addDTO->groupId",
                GroupException::NOT_EXISTS
            );
        }
        $recipients = $group->getRecipients()->toArray();
        $recipients = array_map(fn(Recipient $recipient) => $recipient->getId(), $recipients);
        $recipients = array_intersect($addDTO->recipientIds, $recipients);
        if (empty($recipients)) {
            throw new GroupException(
                "Нет получателей для удаления из группы с ID: $addDTO->groupId",
                GroupException::NOT_EXISTS
            );
        }
        $params = array_merge([$addDTO->groupId], $recipients);
        $countRemove = count($params);
        $strPH = str_repeat('?,', $countRemove - 1);
        $queryString = 'DELETE FROM mail.recipient_group WHERE group_id = ? AND recipient_id IN (' . substr($strPH,0, strlen($strPH)-1) . ')';
        $this->entityManager->getConnection()->executeQuery($queryString, $params);
        $changes['removeRecipients'] = [
            'recipientIds' => $recipients
        ];
        try {
            $history = new GroupHistory();
            $history->setGroup($group);
            $history->setChanges($changes);
            $history->setEditor($userUuid);
            $history->setEditedAt(new \DateTimeImmutable('now'));
            $this->entityManager->persist($history);
            $this->entityManager->flush();
        } catch (\Throwable $err) {
            $this->logger->error($err->getMessage(), ['Exception' => $err]);
            throw new GroupException(
                'Ошибка сохранения данных группы в БД',
                GroupException::DB_PROBLEM
            );
        }
        return true;
    }
}