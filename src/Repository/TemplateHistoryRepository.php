<?php

namespace App\Repository;

use App\DTO\Template\HistoryDTO;
use App\Entity\TemplateHistory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Types\UuidType;

/**
 * Репозиторий сущностей БД для истории изменений Email шаблонов
 *
 * @extends ServiceEntityRepository<TemplateHistory>
 *
 * @method TemplateHistory|null find($id, $lockMode = null, $lockVersion = null)
 * @method TemplateHistory|null findOneBy(array $criteria, array $orderBy = null)
 * @method TemplateHistory[]    findAll()
 * @method TemplateHistory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TemplateHistoryRepository extends ServiceEntityRepository
{
    /** @var array Объявление фильтров для Email шаблонов */
    private const HISTORY_FILTERS = [
        'id' => 'integer',
        'template' => [],
        'editor' => [],
        'period' => [
            'from' => 'datetime',
            'to' => 'datetime'
        ],
        'limit' => 'integer',
        'page' => 'integer',
        'reverse' => 'boolean',
    ];

    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TemplateHistory::class);
    }

    /**
     * Получить информацию о доступных фильтрах
     *
     * @return array
     */
    public function getFilters(): array
    {
        $qb = $this->createQueryBuilder('h')
            ->select(['t.id', 't.title', 'h.editor'])->distinct()
            ->from(\App\Entity\Template::class, 't')
            ->where('t.id = h.template');
        $dbResult = $qb->getQuery()->getResult();
        $filters = self::HISTORY_FILTERS;
        if (is_array($dbResult)) {
            foreach ($dbResult as $dbItem) {
                if (empty($dbItem['id']) || empty($dbItem['editor'])) {
                    continue;
                }
                if (!array_key_exists($dbItem['id'], $filters['template'])) {
                    $filters['template'][$dbItem['id']] = $dbItem['title'];
                }
                if (!in_array($dbItem['editor'], $filters['editor'])) {
                    $filters['editor'][] = $dbItem['editor'];
                }
            }
            natcasesort($filters['template']);
        }
        return $filters;
    }

    public function getQueryBuilder(HistoryDTO $params): QueryBuilder
    {
        $qb = $this->createQueryBuilder('h');
        $expr = $qb->expr();
        if (null !== $params->template) {
            $qb->andWhere($expr->eq('h.template', ':template'))
                ->setParameter('template', $params->template);
        }
        if (null !== $params->editor) {
            $qb->andWhere($expr->eq('h.editor', ':editor'))
                ->setParameter('editor', $params->editor, UuidType::NAME);
        }
        if (null !== $params->period?->from) {
            $qb->andWhere($expr->gte('h.editedAt', ':from'))
                ->setParameter(':from', $params->period->from->format('Y-m-d 00:00:00'));
        }
        if (null !== $params->period?->to) {
            $qb->andWhere($expr->lte('h.editedAt', ':to'))
                ->setParameter(':to', $params->period->to->format('Y-m-d 23:59:59'));
        }

        $qb->orderBy('h.editedAt', $params->reverse ? 'DESC' : 'ASC');

        if ($params->limit > 0) {
            $qb->setMaxResults($params->limit);
            if ($params->page > 1) {
                $qb->setFirstResult(($params->page - 1) * $params->limit);
            }
        }
        return $qb;
    }

    public function getCountRequested(QueryBuilder $qb): int
    {
        return (int)$qb->select('COUNT(h.id)')
            ->resetDQLPart('orderBy')
            ->setMaxResults(1)
            ->setFirstResult(null)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
