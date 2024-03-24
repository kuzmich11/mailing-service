<?php

namespace App\Repository;

use App\DTO\Smtp\HistoryDTO;
use App\Entity\SmtpHistory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Репозиторий истории изменений SMTP-аккаунтов
 *
 * @extends ServiceEntityRepository<SmtpHistory>
 *
 * @method SmtpHistory|null find($id, $lockMode = null, $lockVersion = null)
 * @method SmtpHistory|null findOneBy(array $criteria, array $orderBy = null)
 * @method SmtpHistory[]    findAll()
 * @method SmtpHistory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SmtpHistoryRepository extends ServiceEntityRepository
{
    /**
     * Конструктор
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SmtpHistory::class);
    }

    /**
     * Получить историю изменения SMTP-аккаунтов
     *
     * @param HistoryDTO $params DTO параметров для выборки истории изменения
     *
     * @return array
     */
    public function findByParams(HistoryDTO $params): array
    {
        $qb = $this->createQueryBuilder('hs');
        if ($params->limit > 0) {
            $qb->setMaxResults($params->limit);
            if ($params->page > 1) {
                $qb->setFirstResult(($params->page - 1) * $params->limit);
            }
        }
        $qb->orderBy("hs.id", $params->reverse ? 'DESC' : 'ASC');
        if ($params->id) {
            $qb->andWhere('hs.id = :id')->setParameter('id', $params->id);
        }
        if ($params->smtp) {
            $qb->andWhere('hs.smtp = :smtp')->setParameter('smtp', $params->smtp);
        }
        if ($params->editor) {
            $qb->andWhere('hs.editor = :editor')->setParameter('editor', $params->editor);
        }
        if ($params->period?->from) {
            $qb->andWhere("hs.editedAt >= '{$params->period->from->format('Y-m-d 00:00:00')}'");
        }
        if ($params->period?->to) {
            $qb->andWhere("hs.editedAt <= '{$params->period->to->format('Y-m-d 23:59:59')}'");
        }
        return $qb->getQuery()->getResult();
    }

    /**
     * Получить доступные фильтры для выборки истории изменений
     *
     * @return array
     */
    public function findFilters(): array
    {
        $filters = [
            'id' => 'integer',
            'smtp' => 'integer',
            'editor' => [],
            'period' => [
                'from' => 'datetime',
                'to' => 'datetime',
            ]
        ];
        $qb = $this->createQueryBuilder('h');
        $history = $qb->select('h.editor')
            ->distinct()
            ->getQuery()
            ->getResult();
        foreach ($history as $item) {
            if ($item['editor'] && !in_array($item['editor'], $filters['editor'])) {
                $filters['editor'][] = $item['editor'];
            }
        }
        return $filters;
    }
}
