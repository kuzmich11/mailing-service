<?php

namespace App\Service;

use App\DTO\Recipient\HistoryDTO;
use App\DTO\Recipient\ListDTO;
use App\DTO\Recipient\ParamsDTO;
use App\Entity\Recipient;
use App\Exception\DomainException;
use App\Entity\RecipientHistory;
use App\Exception\RecipientException;
use App\Repository\DomainRepository;
use App\Repository\GroupRepository;
use App\Repository\RecipientHistoryRepository;
use App\Repository\RecipientRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;
use Symfony\Component\Uid\Uuid;

/**
 * Сервис получения и сохранения данных получателей
 */
class RecipientService
{
    /**
     * Конструктор
     *
     * @param LoggerInterface            $logger              Репозиторий получателей
     * @param EntityManagerInterface     $doctrine            Интерфейс для работы с БД
     * @param RecipientRepository        $recipientRepository Репозиторий получателей
     * @param RecipientHistoryRepository $historyRepository   Репозиторий истории изменений получателей
     * @param GroupRepository            $groupsRepository    Репозиторий групп
     * @param DomainRepository           $domainRepository    Репозиторий доменов
     * @param DomainService              $domainService       Сервис работы с доменами
     * @param EntityValidatorService     $entityValidator     Сервис валидации сущностей БД
     */
    public function __construct(
        private readonly LoggerInterface            $logger,
        private readonly EntityManagerInterface     $doctrine,
        private readonly RecipientRepository        $recipientRepository,
        private readonly RecipientHistoryRepository $historyRepository,
        private readonly GroupRepository            $groupsRepository,
        private readonly DomainRepository           $domainRepository,
        private readonly DomainService              $domainService,
        private readonly EntityValidatorService     $entityValidator
    )
    {
    }

    /**
     * Изменить данные о получателе
     *
     * @param ParamsDTO $params DTO параметров получателей
     * @param Uuid $userUuid UUID сотрудника изменяющего запись
     *
     * @return int|null
     * @throws RecipientException
     * @throws UnknownProperties|DomainException
     */
    public function save(ParamsDTO $params, Uuid $userUuid): ?int
    {
        $recipient = $params->id ? $this->recipientRepository->find($params->id) : new Recipient();
        if (!$recipient) {
            throw new RecipientException(
                "Попытка изменить несуществующего получателя c ID: $params->id",
                RecipientException::NOT_EXISTS);
        }

        $changes = [];
        $value = $params->email;
        if ($recipient->getEmail() !== $value) {
            $changes['email'] = [
                'old' => $recipient->getEmail(),
                'new' => $params->email
            ];
            $domainName = substr($value, strpos($value, '@') + 1);
            $domain = $this->domainRepository->findOneBy(['name' => $domainName]);
            if (null === $domain) {
                $paramsDomain = new \App\DTO\Domain\ParamsDTO(name: $domainName, isWorks: true);
                $domain = $this->domainService->save($paramsDomain, $userUuid);
            }
            $recipient->setDomain($domain);
            $recipient->setEmail($value);
        }
        $value = $params->emailState;
        if ($recipient->getState() !== $value) {
            $changes['emailState'] = [
                'old' => $recipient->getState(),
                'new' => $params->emailState
            ];
            $recipient->setState($value);
        }
        $value = $params->isConsent;
        if ($recipient->isConsent() !== $value) {
            $changes['isConsent'] = [
                'old' => $recipient->isConsent(),
                'new' => $params->isConsent
            ];
            $recipient->setConsent($value);
        }
        if ($params->groupIds) {
            $groups = $this->groupsRepository->findByIds($params->groupIds);
            if (!$groups) {
                throw new RecipientException(
                    "Заданные группы не найдены",
                    RecipientException::NOT_EXISTS
                );
            }
            array_walk($groups, fn($group) => $recipient->addGroup($group));
            if ($params->id) {
                $changes['addGroups'] = [
                    'groupIds' => $params->groupIds
                ];
            }
        }
        if (!$params->id) {
            $recipient->setCreator($userUuid);
            $recipient->setCreatedAt(new \DateTimeImmutable('now'));
        } else {
            $recipient->setUpdater($userUuid);
            $recipient->setUpdatedAt(new \DateTimeImmutable('now'));
        }

        $validateResult = $this->entityValidator->validate($recipient);
        if (is_array($validateResult)) {
            throw new RecipientException(
                implode("\n", $validateResult),
                RecipientException::BAD_VALUES
            );
        }

        try {
            $this->doctrine->persist($recipient);
            if ($params->id && !empty($changes)) {
                $history = new RecipientHistory();
                $history->setRecipient($recipient);
                $history->setChanges($changes);
                $history->setEditor($userUuid);
                $history->setEditedAt(new \DateTimeImmutable('now'));
                $this->doctrine->persist($history);
            }
            $this->doctrine->flush();
        } catch (\Throwable $err) {
            $this->logger->error($err->getMessage(), ['Exception' => $err]);
            throw new RecipientException(
                'Ошибка сохранения данных получателя в БД',
                RecipientException::DB_PROBLEM);
        }
        return $recipient->getId();
    }

    /**
     * Пометить получателя как удаленного
     *
     * @param int $id ID получателя
     * @param Uuid $userUuid UUID удалившего
     *
     * @return int|null
     * @throws RecipientException
     */
    public function delete(int $id, Uuid $userUuid): ?int
    {
        $recipient = $this->recipientRepository->find($id);
        if (!$recipient) {
            throw new RecipientException(
                "Попытка удалить несуществующего получателя c ID: $id",
                RecipientException::NOT_EXISTS);
        }
        $recipient->setDeleter($userUuid);
        $recipient->setDeletedAt(new DateTimeImmutable('now'));
        try {
            $this->doctrine->persist($recipient);
            $this->doctrine->flush();
        } catch (\Throwable $err) {
            $this->logger->error($err->getMessage(), ['Exception' => $err]);
            throw new RecipientException(
                'Ошибка сохранения данных получателя в БД',
                RecipientException::DB_PROBLEM);
        }
        return $recipient->getId();
    }

    /**
     * Получить список получателей по заданным параметрам и доступные фильтры
     *
     * @param ListDTO $params DTO параметров фильтрации списка получателей
     *
     * @return array
     */
    public function list(ListDTO $params): array
    {
        return $this->recipientRepository->findByParams($params);
    }

    /**
     * Получить доступные фильтры
     *
     * @return array
     */
    public function filters(): array
    {
        return $this->recipientRepository->findFilter();
    }

    /**
     * Получить данные получателя
     *
     * @param int $id ID получателя
     *
     * @return array
     */
    public function recipient(int $id): array
    {
        return ($result = $this->recipientRepository->find($id))
            ? $result->toArray()
            : [];
    }

    /**
     * Получить историю изменения данных получателя
     *
     * @param HistoryDTO $params DTO для получения истории изменений получателей
     *
     * @return array
     */
    public function history(HistoryDTO $params): array
    {
        return $this->historyRepository->findByParams($params);
    }

    /**
     * Получить возможные фильтры для истории изменений
     *
     * @return array
     */
    public function historyFilters(): array
    {
        return $this->historyRepository->findFilter();
    }
}