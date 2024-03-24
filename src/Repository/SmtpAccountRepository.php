<?php

namespace App\Repository;

use App\DTO\Smtp\ListDTO;
use App\Entity\SmtpAccount;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Репозиторий SMTP аккаунтов
 *
 * @extends ServiceEntityRepository<SmtpAccount>
 *
 * @method SmtpAccount|null find($id, $lockMode = null, $lockVersion = null)
 * @method SmtpAccount|null findOneBy(array $criteria, array $orderBy = null)
 * @method SmtpAccount[]    findAll()
 * @method SmtpAccount[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SmtpAccountRepository extends ServiceEntityRepository
{
    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SmtpAccount::class);
    }

    /**
     * Получить список SMTP-аккаунтов
     *
     * @param ListDTO $params DTO параметры для выборки SMTP-аккаунтов
     *
     * @return array
     */
    public function findByParams(ListDTO $params): array
    {
        $filters = $params->filter;
        $list = $this->createQueryBuilder('s');
        if ($params->limit > 0) {
            $list->setMaxResults($params->limit);
            if ($params->page > 1) {
                $list->setFirstResult(($params->page - 1) * $params->limit);
            }
        }
        if ($params->sort) {
            $list->orderBy("s.{$params->sort->field}", $params->sort->reverse ? 'DESC' : 'ASC');
        }
        if (null === $filters) {
            $list->andWhere('s.isDeleted = false');
            return $list->getQuery()->getResult();
        }
        if ($filters->id) {
            $list->andWhere('s.id = :id')->setParameter('id', $filters->id);
        }
        if ($filters->host) {
            $list->andWhere('s.host = :host')->setParameter('host', $filters->host);
        }
        if ($filters->title) {
            $list->andWhere('s.title = :title')->setParameter('title', $filters->title);
        }
        if ($filters->login) {
            $list->andWhere('s.login = :login')->setParameter('login', $filters->login);
        }
        if (null !== $filters->isSystem) {
            $list->andWhere('s.isSystem = :isSystem')->setParameter('isSystem', $filters->isSystem);
        }
        if (null !== $filters->isActive) {
            $list->andWhere('s.isActive = :isActive')->setParameter('isActive', $filters->isActive);
        }

        if (!$filters->withDeleted) {
            $list->andWhere('s.isDeleted = false');
        }
        return $list->getQuery()->getResult();
    }

    /**
     * Получить доступные фильтры для выборки SMTP-аккаунтов
     *
     * @return array
     */
    public function findFilters(): array
    {
        $filters = [
            'id' => 'integer',
            'title' => [],
            'host' => [],
            'login' => [],
            'isSystem' => 'bool',
            'isActive' => 'bool'
        ];
        $accounts = $this->findBy(['isDeleted' => false]);
        foreach ($accounts as $smtp) {
            if ($smtp->getTitle() && !in_array($smtp->getTitle(), $filters['title'])) {
                $filters['title'][] = $smtp->getTitle();
            }
            if ($smtp->getHost() && !in_array($smtp->getHost(), $filters['host'])) {
                $filters['host'][] = $smtp->getHost();
            }
            if ($smtp->getLogin() && !in_array($smtp->getLogin(), $filters['login'])) {
                $filters['login'][] = $smtp->getLogin();
            }
        }
        return $filters;
    }
}
