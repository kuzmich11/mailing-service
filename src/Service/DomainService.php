<?php

namespace App\Service;

use App\DTO\Domain\HistoryDTO;
use App\DTO\Domain\ListDTO;
use App\DTO\Domain\ParamsDTO;
use App\Entity\Domain;
use App\Entity\DomainHistory;
use App\Exception\DomainException;
use App\Repository\DomainHistoryRepository;
use App\Repository\DomainRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Uuid;

/**
 * Сервис получения и сохранения данных доменов
 */
class DomainService
{
    /**
     * Конструктор
     *
     * @param LoggerInterface         $logger            Логгер
     * @param EntityManagerInterface  $entityManager     Интерфейс работы с БД
     * @param EntityValidatorService  $validator         Валидатор сущностей
     * @param DomainRepository        $domainRepository  Репозиторий доменов
     * @param DomainHistoryRepository $historyRepository Репозиторий истории изменения доменов
     */
    public function __construct(
        private readonly LoggerInterface         $logger,
        private readonly EntityManagerInterface  $entityManager,
        private readonly EntityValidatorService  $validator,
        private readonly DomainRepository        $domainRepository,
        private readonly DomainHistoryRepository $historyRepository
    )
    {
    }

    /**
     * Сохранить данные домена
     *
     * @param ParamsDTO $params DTO параметров доменов
     * @param Uuid $userUuid UUID сотрудника сохраняющего данные
     *
     * @return Domain|null
     * @throws DomainException
     */
    public function save(ParamsDTO $params, Uuid $userUuid): ?Domain
    {
        $domain = $params->id ? $this->domainRepository->find($params->id) : new Domain();
        if (null === $domain) {
            throw new DomainException(
                "Домена с ID: $params->id не существует",
                DomainException::NOT_EXISTS);
        }

        $changes = [];
        $value = $params->name;
        if ($domain->getName() !== $value) {
            $changes['name'] = [
                'old' => $domain->getName(),
                'new' => $value
            ];
            $domain->setName($value);
        }
        $value = $params->isWorks;
        if ($domain->isWorks() !== $value) {
            $changes['isWorks'] = [
                'old' => $domain->isWorks(),
                'new' => $value
            ];
            $domain->setWorks($value);
        }

        $validateResult = $this->validator->validate($domain);
        if (is_array($validateResult)) {
            throw new DomainException(
                implode("\n", $validateResult),
                DomainException::BAD_VALUES
            );
        }

        if (!$params->id) {
            $domain->setCreator($userUuid);
            $domain->setCreatedAt(new \DateTimeImmutable('now'));
        } else {
            $domain->setEditor($userUuid);
            $domain->setEditedAt(new \DateTimeImmutable('now'));
            if (!empty($changes)) {
                $history = new DomainHistory();
                $history->setDomain($domain);
                $history->setChanges($changes);
                $history->setEditor($userUuid);
                $history->setEditedAt(new \DateTimeImmutable('now'));
            }
        }

        try {
            $this->entityManager->persist($domain);
            if (isset($history)) {
                $this->entityManager->persist($history);
            }
            $this->entityManager->flush();
        } catch (UniqueConstraintViolationException $err) {
            throw new DomainException(
                'Домен с таким наименованием уже существует',
                DomainException::DUPLICATE_ENTRY
            );
        } catch (\Throwable $err) {
            $this->logger->error($err->getMessage(), ['Exception' => $err]);
            throw new DomainException(
                'Ошибка сохранения данных домена',
                DomainException::DB_PROBLEM
            );
        }
        return $domain;
    }

    /**
     * Получить список доменов
     *
     * @param ListDTO $params DTO параметров фильтрации выборки доменов
     *
     * @return array
     */
    public function list(ListDTO $params): array
    {
        return $this->domainRepository->findByParams($params);
    }

    /**
     * Получить данные домена
     *
     * @param int $id ID домена
     *
     * @return array
     */
    public function entity(int $id): array
    {
        return ($result = $this->domainRepository->find($id))
            ? $result->toArray()
            : [];
    }

    /**
     * Получить доступные фильтры для списка доменов
     *
     * @return array
     */
    public function filters(): array
    {
        return $this->domainRepository->findFilters();
    }

    /**
     * Получить историю изменения доменов
     *
     * @param HistoryDTO $params DTO параметров фильтрации истории изменений
     *
     * @return array
     */
    public function history(HistoryDTO $params): array
    {
        return $this->historyRepository->findByParams($params);
    }

    /**
     * Получить доступные фильтры для истории изменений
     *
     * @return array
     */
    public function historyFilters(): array
    {
        return $this->historyRepository->findFilters();
    }
}