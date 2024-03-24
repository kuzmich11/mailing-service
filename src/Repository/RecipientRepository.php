<?php

namespace App\Repository;

use App\DTO\Recipient\ListDTO;
use App\Entity\Recipient;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Репозиторий получателей
 *
 * @extends ServiceEntityRepository<Recipient>
 *
 * @method Recipient|null find($id, $lockMode = null, $lockVersion = null)
 * @method Recipient|null findOneBy(array $criteria, array $orderBy = null)
 * @method Recipient[]    findAll()
 * @method Recipient[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RecipientRepository extends ServiceEntityRepository
{
    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Recipient::class);
    }

    /**
     * Получить список получателей по заданным фильтрам
     *
     * @param ListDTO $params DTO параметров фильтрации
     *
     * @return array|null
     */
    public function findByParams(ListDTO  $params): ?array
    {
        $filters = $params->filter;
        $list = $this->createQueryBuilder('r');
        if ($params->sort) {
            $list->orderBy("r.{$params->sort->field}", $params->sort->reverse ? 'DESC' : 'ASC');
        }
        if ($params->limit > 0) {
            $list->setMaxResults($params->limit);
            if ($params->page > 1) {
                $list->setFirstResult(($params->page - 1) * $params->limit);
            }
        }
        if (null === $filters) {
            $list->andWhere('r.deletedAt IS NULL');
            return $list->getQuery()->getResult();
        }
        if (!$filters->withDeleted) {
            $list->andWhere('r.deletedAt IS NULL');
        }
        if ($filters->id) {
            $list->andWhere('r.id = :id')->setParameter('id', $filters->id);
        }
        if ($filters->email) {
            $list->andWhere('r.email = :email')->setParameter('email', $filters->email);
        }
        if ($filters->emailState) {
            $list->andWhere('r.mailStatus = :mailStatus')->setParameter('mailStatus', $filters->emailState);
        }
        if ($filters->isConsent !== null) {
            $list->andWhere('r.isConsent = :isConsent')->setParameter('isConsent', $filters->isConsent);
        }
        if ($filters->creator) {
            $list->andWhere('r.creator = :creator')->setParameter('creator', $filters->creator);
        }
        if ($filters->createdAt?->from) {
            $list->andWhere("r.createdAt >= '{$filters->createdAt->from->format('Y-m-d 00:00:00')}'");
        }
        if ($filters->createdAt?->to) {
            $list->andWhere("r.createdAt <= '{$filters->createdAt->to->format('Y-m-d 23:59:59')}'");
        }
        return $list->getQuery()->getResult();
    }

    /**
     * Получить доступные фильтры
     *
     * @return array
     */
    public function findFilter(): array
    {
        $filters = [
            'id' => 'integer',
            'email' => 'string',
            'emailState' => [
                'WORKING',
                'BAD_DOMAIN',
                'BLACK_LIST',
                'NOT_FOUND',
                'CROWDED',
                'UNCONFIRMED',
                'PROBLEM'
            ],
            'isConsent' => 'boolean',
            'creator' => [],
            'createdAt' => [
                'from' => 'datetime',
                'to' => 'datetime',
            ]
        ];
        $recipients = $this->findBy(['deletedAt' => null]);
        foreach ($recipients as $recipient) {
            if ($recipient->getCreator() && !in_array($recipient->getCreator(), $filters['creator'])) {
                $filters['creator'][] = $recipient->getCreator();
            }
        }
        return $filters;
    }

    /**
     * Получить список получателей по заданным ID
     *
     * @param array $ids Массив ID получателей
     *
     * @return array
     */
    public function findByIds(array $ids): array
    {
        $qb = $this->createQueryBuilder('r');
        $result = $qb
            ->where($qb->expr()->in('r.id', ':ids'))
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult();
        return $result ?: [];
    }
}
