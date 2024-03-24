<?php

namespace App\Repository;

use App\DTO\Group\ListDTO;
use App\Entity\Group;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Репозиторий групп получателей
 *
 * @extends ServiceEntityRepository<Group>
 *
 * @method Group|null find($id, $lockMode = null, $lockVersion = null)
 * @method Group|null findOneBy(array $criteria, array $orderBy = null)
 * @method Group[]    findAll()
 * @method Group[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GroupRepository extends ServiceEntityRepository
{
    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Group::class);
    }

    /**
     * Получить список групп с заданными параметрами фильтрации
     *
     * @param ListDTO $params DTO параметров выборки
     *
     * @return array|null
     */
    public function findByParams(ListDTO $params): ?array
    {
        $filters = $params->filter;
        $list = $this->createQueryBuilder('g');
        if ($params->limit > 0) {
            $list->setMaxResults($params->limit);
            if ($params->page > 1) {
                $list->setFirstResult(($params->page - 1) * $params->limit);
            }
        }
        if ($params->sort) {
            $list->orderBy("g.{$params->sort->field}", $params->sort->reverse ? 'DESC' : 'ASC');
        }
        if (null === $filters) {
            $list->andWhere('g.deletedAt IS NULL');
            return $list->getQuery()->getResult();
        }
        if ($filters->id) {
            $list->andWhere('g.id = :id')->setParameter('id', $filters->id);
        }
        if ($filters->name) {
            $list->andWhere($list->expr()->like('g.name', ':name'))
                ->setParameter('name', "%{$filters->name}%");
        }
        if ($filters->creator) {
            $list->andWhere('g.creator = :creator')->setParameter('creator', $filters->creator);
        }
        if ($filters->createdAt?->from) {
            $list->andWhere("g.createdAt >= '{$filters->createdAt->from->format('Y-m-d 00:00:00')}'");
        }
        if ($filters->createdAt?->to) {
            $list->andWhere("g.createdAt <= '{$filters->createdAt->to->format('Y-m-d 23:59:59')}'");
        }
        if (!$filters->withDeleted) {
            $list->andWhere('g.deletedAt IS NULL');
        }
        return $list->getQuery()->getResult();
    }

    /**
     * Получить доступные фильтры
     *
     * @return array
     */
    public function findFilters(): array
    {
        $filters = [
            'id' => 'integer',
            'name' => 'string',
            'creator' => [],
            'createdAt' => [
                'from' => 'datetime',
                'to' => 'datetime',
            ],
            'withDeleted' => 'boolean',
        ];
        $groups = $this->findBy(['deletedAt' => null]);
        foreach ($groups as $group) {
            if ($group->getCreator() && !in_array($group->getCreator(), $filters['creator'])) {
                $filters['creator'][] = $group->getCreator();
            }
        }
        return $filters;
    }

    /**
     * Получить список групп по ID
     *
     * @param array $ids Массив ID групп
     *
     * @return array
     */
    public function findByIds(array $ids): array
    {
        $qb = $this->createQueryBuilder('g');
        $result = $qb
            ->where($qb->expr()->in('g.id', ':ids'))
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult();
        return $result ?: [];
    }
}
