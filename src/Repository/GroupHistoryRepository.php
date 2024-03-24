<?php

namespace App\Repository;

use App\DTO\Group\HistoryDTO;
use App\Entity\GroupHistory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Репозиторий истории изменения групп
 *
 * @extends ServiceEntityRepository<GroupHistory>
 *
 * @method GroupHistory|null find($id, $lockMode = null, $lockVersion = null)
 * @method GroupHistory|null findOneBy(array $criteria, array $orderBy = null)
 * @method GroupHistory[]    findAll()
 * @method GroupHistory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GroupHistoryRepository extends ServiceEntityRepository
{
    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GroupHistory::class);
    }

    /**
     * Получить историю изменения групп
     *
     * @param HistoryDTO $params DTO для получения истории изменений групп
     *
     * @return array
     */
    public function findByParams(HistoryDTO $params): array
    {
        $qb = $this->createQueryBuilder('hg');
        if ($params->limit > 0) {
            $qb->setMaxResults($params->limit);
            if ($params->page > 1) {
                $qb->setFirstResult(($params->page - 1) * $params->limit);
            }
        }
        $qb->orderBy("hg.id", $params->reverse ? 'DESC' : 'ASC');
        if ($params->id) {
            $qb->andWhere('hg.id = :id')->setParameter('id', $params->id);
        }
        if ($params->group) {
            $qb->andWhere('hg.group = :group')->setParameter('group', $params->group);
        }
        if ($params->editor) {
            $qb->andWhere('hg.editor = :editor')->setParameter('editor', $params->editor);
        }
        if ($params->period?->from) {
            $qb->andWhere("hg.editedAt >= '{$params->period->from->format('Y-m-d 00:00:00')}'");
        }
        if ($params->period?->to) {
            $qb->andWhere("hg.editedAt <= '{$params->period->to->format('Y-m-d 23:59:59')}'");
        }
        return $qb->getQuery()->getResult();
    }

    /**
     * Получить доступные фильтры истории
     *
     * @return array
     */
    public function findFilter(): array
    {
        $filters = [
            'id' => 'integer',
            'group' => 'integer',
            'editor' => [],
            'period' => [
                'from' => 'datetime',
                'to' => 'datetime',
            ]
        ];
        $history = $this->findAll();
        foreach ($history as $item) {
            if ($item->getEditor() && !in_array($item->getEditor(), $filters['editor'])) {
                $filters['editor'][] = $item->getEditor();
            }
        }
        return $filters;
    }
}
