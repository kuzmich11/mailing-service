<?php

namespace App\Service;

use App\DTO\Group\HistoryDTO;
use App\DTO\Group\ListDTO;
use App\DTO\Group\EntityDTO;
use App\Entity\GroupHistory;
use App\Entity\Group;
use App\Entity\Recipient;
use App\Exception\GroupException;
use App\Exception\RecipientException;
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
     * @param GroupRepository        $groups     Репозиторий групп
     * @param RecipientRepository    $recipients Репозиторий получателей
     * @param GroupHistoryRepository $histories  Репозиторий истории изменения групп
     * @param EntityManagerInterface $doctrine   Интерфейс работы с БД
     * @param LoggerInterface        $logger     Логгер
     * @param EntityValidatorService $validator  Сервис валидации данных
     */
    public function __construct(
        private readonly GroupRepository        $groups,
        private readonly RecipientRepository    $recipients,
        private readonly GroupHistoryRepository $histories,
        private readonly EntityManagerInterface $doctrine,
        private readonly LoggerInterface        $logger,
        private readonly EntityValidatorService $validator
    )
    {
    }

    /**
     * Сохранить в БД информацию записи группы получателей
     *
     * @param EntityDTO $params DTO параметров для сохранения|изменения группы
     * @param Uuid $userUuid UUID сотрудника
     *
     * @return int|null
     * @throws GroupException
     * @throws RecipientException
     */
    public function save(EntityDTO $params, Uuid $userUuid): ?int
    {
        $group = $params->id ? $this->groups->find($params->id) : new Group();
        if (null === $group) {
            throw new GroupException("Отсутствует группа с ID: " . $params->id, GroupException::NOT_EXISTS);
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

        $value = $params->recipients;
        $recipientsId = array_map(fn(Recipient $recipient) => $recipient->getId(), $group->getRecipients()->toArray());
        if ($recipientsId != $value) {
            $recipients = $this->recipients->findBy(['id' => $value]);
            if ($value && !$recipients) {
                throw new GroupException("Заданные получатели не найдены", GroupException::NOT_EXISTS);
            }
            $changes['recipients'] = ['old' => $recipientsId, 'new' => []];
            $group->clearRecipients();
            if ($recipients) {
                foreach ($recipients as $recipient) {
                    $group->addRecipient($recipient);
                    $changes['recipients']['new'][] = [$recipient->getId()];
                }
            }
        }

        if ($params->id) {
            $group->setEditor($userUuid);
            $group->setEditedAt(new DateTimeImmutable('now'));
        } else {
            $group->setCreator($userUuid);
            $group->setCreatedAt(new DateTimeImmutable('now'));
        }

        $validateResult = $this->validator->validate($group);
        if (is_array($validateResult)) {
            throw new GroupException(
                implode("\n", $validateResult),
                GroupException::BAD_VALUES
            );
        }

        try {
            $this->doctrine->persist($group);
            if ($params->id && !empty($changes)) {
                $history = new GroupHistory();
                $history->setGroup($group);
                $history->setChanges($changes);
                $history->setEditor($userUuid);
                $history->setEditedAt(new \DateTimeImmutable('now'));
                $this->doctrine->persist($history);
            }
            $this->doctrine->flush();
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
        $group = $this->groups->find($id);
        if (null === $group) {
            throw new GroupException(
                "Попытка удалить несуществующую группу получателей с ID: $id",
                GroupException::NOT_EXISTS
            );
        }
        $group->setDeleter($userUuid);
        $group->setDeletedAt(new DateTimeImmutable('now'));
        try {
            $this->doctrine->persist($group);
            $this->doctrine->flush();
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
        return $this->groups->findByParams($params);
    }

    /**
     * Получить возможные параметры фильтрации
     *
     * @return array
     */
    public function getEntityFilters(): array
    {
        return $this->groups->findFilters();
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

        return ($group = $this->groups->find($id))
            ? $group->toArray()
            : [];
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
        $group = $this->groups->find($id);
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
        return $this->histories->findByParams($params);
    }

    /**
     * Получить возможные фильтры для истории
     *
     * @return array
     */
    public function getHistoryFilters(): array
    {
        return $this->histories->findFilter();
    }
}