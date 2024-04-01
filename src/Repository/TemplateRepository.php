<?php

namespace App\Repository;

use App\DTO\Template\ListDTO;
use App\Entity\Template;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Types\UuidType;

/**
 * Репозиторий сущностей БД для Email шаблонов
 *
 * @extends ServiceEntityRepository<Template>
 *
 * @method Template|null find($id, $lockMode = null, $lockVersion = null)
 * @method Template|null findOneBy(array $criteria, array $orderBy = null)
 * @method Template[]    findAll()
 * @method Template[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TemplateRepository extends ServiceEntityRepository
{
    /** @var array Объявление фильтров для Email шаблонов */
    private const TEMPLATE_FILTERS = [
        'title' => 'string',
        'parent' => [],
        'creator' => [],
        'created' => [
            'from' => 'datetime',
            'to' => 'datetime',
        ],
        'taskNumber' => 'integer',
        'withDeleted' => 'boolean'
    ];

    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Template::class);
    }

    /**
     * Получить информацию о доступных фильтрах
     *
     * @return array
     */
    public function getFilters(): array
    {
        $filters = self::TEMPLATE_FILTERS;
        foreach ($this->getExistTemplates(true) as $template) {
            $parent = $template->getParent();
            if ($parent && !isset($filters['parent'][$parent->getId()])) {
                $filters['parent'][$parent->getId()] = $parent->getTitle();
            }
            if (!in_array($template->getCreator(), $filters['creator'])) {
                $filters['creator'][] = $template->getCreator();
            }
        }
        natcasesort($filters['parent']);

        return $filters;
    }

    /**
     * Получить существующие (не удалённые) шаблоны
     *
     * @param bool $withDeleted Флаг для выборки "удалённых" шаблонов
     *
     * @return Template[]
     */
    public function getExistTemplates(bool $withDeleted = false): array
    {
        return $this->findBy($withDeleted ? [] : ['deleter' => null, 'deleted_at' => null], ['id' => 'ASC']);
    }

    public function getQueryBuilder(ListDTO $params): QueryBuilder
    {
        $qb = $this->createQueryBuilder('t');
        // подготовка запроса
        if ($filters = $params->filter) {
            if (null !== $filters->parent) {
                $qb->andWhere($filters->parent
                    ? $qb->expr()->eq('t.parent', $filters->parent)
                    : $qb->expr()->isNull('t.parent')
                );
            }
            if ($filters->title) {
                $qb->andWhere($qb->expr()->like('t.title', ':title'))
                    ->setParameter('title', "%{$filters->title}%");
            }
            if ($filters->creator) {
                $qb->andWhere($qb->expr()->eq('t.creator', ':uuid'))
                    ->setParameter('uuid', $filters->creator, UuidType::NAME);
            }
            if ($filters->created?->from) {
                $qb->andWhere($qb->expr()->gte('t.createdAt', ':from'))
                    ->setParameter('from', $filters->created->from->format('Y-m-d 00:00:00'));
            }
            if ($filters->created?->to) {
                $qb->andWhere($qb->expr()->lte('t.createdAt', ':to'))
                    ->setParameter('to', $filters->created->to->format('Y-m-d 23:59:59'));
            }
        }
        if (!$filters?->withDeleted) {
            $qb->andWhere($qb->expr()->isNull('t.deleter'));
            $qb->andWhere($qb->expr()->isNull('t.deletedAt'));
        }
        // сортировка и ограничение выборки
        $qb->orderBy("t.{$params->sort->field}", $params->sort->reverse ? 'DESC' : 'ASC');
        $qb->setMaxResults($params->limit);
        if ($params->page > 1) {
            $qb->setFirstResult(($params->page - 1) * $params->limit);
        }
        return $qb;
    }

    public function getCountRequested(QueryBuilder $qb): int
    {
        return (int)$qb->select('COUNT(t.id)')
            ->resetDQLPart('orderBy')
            ->setMaxResults(1)
            ->setFirstResult(null)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
