<?php

namespace App\Repository;

use App\DTO\Domain\HistoryDTO;
use App\Entity\DomainHistory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Репозиторий истории изменений доменов
 *
 * @extends ServiceEntityRepository<DomainHistory>
 *
 * @method DomainHistory|null find($id, $lockMode = null, $lockVersion = null)
 * @method DomainHistory|null findOneBy(array $criteria, array $orderBy = null)
 * @method DomainHistory[]    findAll()
 * @method DomainHistory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DomainHistoryRepository extends ServiceEntityRepository
{
    /**
     * Конструктор
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DomainHistory::class);
    }

    /**
     * Получить список доменов
     *
     * @param HistoryDTO $params DTO параметров фильтрации выборки доменов
     *
     * @return array
     */
    public function findByParams(HistoryDTO $params): array
    {
        $qb = $this->createQueryBuilder('hd');
        if ($params->limit > 0) {
            $qb->setMaxResults($params->limit);
            if ($params->page > 1) {
                $qb->setFirstResult(($params->page - 1) * $params->limit);
            }
        }
        $qb->orderBy("hd.id", $params->reverse ? 'DESC' : 'ASC');
        if ($params->id) {
            $qb->andWhere('hd.id = :id')->setParameter('id', $params->id);
        }
        if ($params->domain) {
            $qb->andWhere('hd.domain = :domain')->setParameter('domain', $params->domain);
        }
        if ($params->editor) {
            $qb->andWhere('hd.editor = :editor')->setParameter('editor', $params->editor);
        }
        if ($params->period?->from) {
            $qb->andWhere("hd.editedAt >= '{$params->period->from->format('Y-m-d 00:00:00')}'");
        }
        if ($params->period?->to) {
            $qb->andWhere("hd.editedAt <= '{$params->period->to->format('Y-m-d 23:59:59')}'");
        }
        return $qb->getQuery()->getResult();
    }

    /**
     * Получить фильтры для истории изменений
     *
     * @return array
     */
    public function findFilters(): array
    {
        $filters = [
            'id' => 'integer',
            'recipient' => 'integer',
            'editor' => [],
            'period' => [
                'from' => 'datetime',
                'to' => 'datetime',
            ]
        ];
        $qb = $this->createQueryBuilder('hd');
        $history = $qb->select('hd.editor')
            ->distinct(true)
            ->getQuery()
            ->getResult();
        array_walk($history, fn(&$item) => $item = $item['editor']);
        $filters['editor'] = $history;
        return $filters;
    }
}
