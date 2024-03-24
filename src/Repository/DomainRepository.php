<?php

namespace App\Repository;

use App\DTO\Domain\ListDTO;
use App\Entity\Domain;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Репозиторий доменов
 *
 * @extends ServiceEntityRepository<Domain>
 *
 * @method Domain|null find($id, $lockMode = null, $lockVersion = null)
 * @method Domain|null findOneBy(array $criteria, array $orderBy = null)
 * @method Domain[]    findAll()
 * @method Domain[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DomainRepository extends ServiceEntityRepository
{
    /**
     * Конструктор
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Domain::class);
    }

    /**
     * Получить список доменов
     *
     * @param ListDTO $params DTO параметров выборки доменов
     *
     * @return array
     */
    public function findByParams(ListDTO $params): array
    {
        $filters = $params->filter;
        $qb = $this->createQueryBuilder('d');
        if ($params->limit > 0) {
            $qb->setMaxResults($params->limit);
            if ($params->page > 1) {
                $qb->setFirstResult(($params->page - 1) * $params->limit);
            }
        }
        if ($params->sort) {
            $qb->orderBy("d.{$params->sort->field}", $params->sort->reverse ? 'DESC' : 'ASC');
        }
        if (null === $filters) {
            return $qb->getQuery()->getResult();
        }
        if ($filters->id) {
            $qb->andWhere('d.id = :id')->setParameter('id', $filters->id);
        }
        if ($filters->name) {
            $qb->andWhere($qb->expr()->like('d.name', ':name'))->setParameter('name', "%{$filters->name}%");
        }
        if ($filters->isWorks !== null) {
            $qb->andWhere('d.isWorks = :isWorks')->setParameter('isWorks', $filters->isWorks);
        }
        if ($filters->creator) {
            $qb->andWhere('d.creator = :creator')->setParameter('creator', $filters->creator);
        }
        if ($filters->createdAt?->from) {
            $qb->andWhere("d.createdAt >= '{$filters->createdAt->from->format('Y-m-d 00:00:00')}'");
        }
        if ($filters->createdAt?->to) {
            $qb->andWhere("d.createdAt <= '{$filters->createdAt->to->format('Y-m-d 23:59:59')}'");
        }
        if ($filters->editedAt?->from) {
            $qb->andWhere("d.editedAt >= '{$filters->createdAt->from->format('Y-m-d 00:00:00')}'");
        }
        if ($filters->editedAt?->to) {
            $qb->andWhere("d.editedAt <= '{$filters->createdAt->to->format('Y-m-d 23:59:59')}'");
        }
        return $qb->getQuery()->getResult();
    }

    /**
     * Получить доступные фильтры для выборки доменов
     *
     * @return array
     */
    public function findFilters(): array
    {
        $filters = [
            'id' => 'integer',
            'name' => 'string',
            'isWorks' => 'bool',
            'creator' => [],
            'createdAt' => [
                'from' => 'datetime',
                'to' => 'datetime'
            ],
            'editor' => [],
            'editedAt' => [
                'from' => 'datetime',
                'to' => 'datetime'
            ]
        ];

        $qb = $this->createQueryBuilder('d');
        $domains = $qb->select('d.creator', 'd.editor')
            ->distinct(true)
            ->getQuery()
            ->getResult();

        foreach ($domains as $domain) {
            if ($domain['creator'] && !in_array($domain['creator'], $filters['creator'])) {
                $filters['creator'][] = $domain['creator'];
            }
            if ($domain['editor'] && !in_array($domain['editor'], $filters['editor'])) {
                $filters['editor'][] = $domain['editor'];
            }
        }
        return $filters;
    }
}
