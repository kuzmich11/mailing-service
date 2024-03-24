<?php

namespace App\Repository;

use App\Entity\Template;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

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
}
