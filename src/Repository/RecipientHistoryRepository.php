<?php

namespace App\Repository;

use App\DTO\Recipient\HistoryDTO;
use App\Entity\RecipientHistory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Репозиторий данных получателей
 *
 * @extends ServiceEntityRepository<RecipientHistory>
 *
 * @method RecipientHistory|null find($id, $lockMode = null, $lockVersion = null)
 * @method RecipientHistory|null findOneBy(array $criteria, array $orderBy = null)
 * @method RecipientHistory[]    findAll()
 * @method RecipientHistory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RecipientHistoryRepository extends ServiceEntityRepository
{
    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RecipientHistory::class);
    }

    /**
     * Получить историю изменения данных получателя
     *
     * @param HistoryDTO $params DTO для получения истории изменений получателей
     *
     * @return array
     */
    public function findByParams(HistoryDTO $params): array
    {
        $qb = $this->createQueryBuilder('hr');
        if ($params->limit > 0) {
            $qb->setMaxResults($params->limit);
            if ($params->page > 1) {
                $qb->setFirstResult(($params->page - 1) * $params->limit);
            }
        }
        $qb->orderBy("hr.id", $params->reverse ? 'DESC' : 'ASC');
        if ($params->id) {
            $qb->andWhere('hr.id = :id')->setParameter('id', $params->id);
        }
        if ($params->recipient) {
            $qb->andWhere('hr.recipient = :recipient')->setParameter('recipient', $params->recipient);
        }
        if ($params->editor) {
            $qb->andWhere('hr.editor = :editor')->setParameter('editor', $params->editor);
        }
        if ($params->period?->from) {
            $qb->andWhere("hr.editedAt >= '{$params->period->from->format('Y-m-d 00:00:00')}'");
        }
        if ($params->period?->to) {
            $qb->andWhere("hr.editedAt <= '{$params->period->to->format('Y-m-d 23:59:59')}'");
        }
        return $qb->getQuery()->getResult();
    }

    /**
     * Получить возможные фильтры для истории изменений
     *
     * @return array
     */
    public function findFilter(): array
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
        $history = $this->findAll();
        foreach ($history as $item) {
            if ($item->getEditor() && !in_array($item->getEditor(), $filters['editor'])) {
                $filters['editor'][] = $item->getEditor();
            }
        }
        return $filters;
    }
}
