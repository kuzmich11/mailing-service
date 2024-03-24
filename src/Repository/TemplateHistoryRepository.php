<?php

namespace App\Repository;

use App\Entity\TemplateHistory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

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
}
