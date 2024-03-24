<?php

namespace App\Repository;

use App\DTO\Letter\HistoryDTO;
use App\Entity\LetterHistory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Репозиторий истории изменения писем
 *
 * @extends ServiceEntityRepository<LetterHistory>
 *
 * @method LetterHistory|null find($id, $lockMode = null, $lockVersion = null)
 * @method LetterHistory|null findOneBy(array $criteria, array $orderBy = null)
 * @method LetterHistory[]    findAll()
 * @method LetterHistory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LetterHistoryRepository extends ServiceEntityRepository
{
    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LetterHistory::class);
    }

    /**
     * Получить историю изменения писем
     *
     * @param HistoryDTO $params DTO для получения истории изменений писем
     *
     * @return array
     */
    public function findByParams(HistoryDTO $params): array
    {
        $qb = $this->createQueryBuilder('h');
        if ($params->limit > 0) {
            $qb->setMaxResults($params->limit);
            if ($params->page > 1) {
                $qb->setFirstResult(($params->page - 1) * $params->limit);
            }
        }
        $qb->orderBy("h.id", $params->reverse ? 'DESC' : 'ASC');
        if ($params->id) {
            $qb->andWhere('h.id = :id')->setParameter('id', $params->id);
        }
        if ($params->letter) {
            $qb->andWhere('h.letter = :letter')->setParameter('letter', $params->letter);
        }
        if ($params->editor) {
            $qb->andWhere('h.editor = :editor')->setParameter('editor', $params->editor);
        }
        if ($params->period?->from) {
            $qb->andWhere("h.editedAt >= '{$params->period->from->format('Y-m-d 00:00:00')}'");
        }
        if ($params->period?->to) {
            $qb->andWhere("h.editedAt <= '{$params->period->to->format('Y-m-d 23:59:59')}'");
        }
        return $qb->getQuery()->getResult();
    }

    /**
     * Получить возможные фильтры для истории
     *
     * @return array
     */
    public function findFilter(): array
    {
        $filters = [
            'id' => 'integer',
            'letter' => 'integer',
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
