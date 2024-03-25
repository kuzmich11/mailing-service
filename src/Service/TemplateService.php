<?php

namespace App\Service;

use App\DTO\Template\EntityDTO;
use App\DTO\Template\HistoryDTO;
use App\DTO\Template\ListDTO;
use App\Entity\Template;
use App\Entity\TemplateHistory;
use App\Exception\TemplateException;
use App\Repository\TemplateHistoryRepository;
use App\Repository\TemplateRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\UnexpectedResultException;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;
use Twig\Environment as TwigEnvironment;

/**
 * Сервис для работы с Email шаблонами
 */
class TemplateService
{
    /**
     * Конструктор с DI
     *
     * @param TemplateRepository        $templates       Репозиторий БД для Email шаблонов
     * @param TemplateHistoryRepository $histories       Репозиторий БД для изменений Email шаблонов
     * @param EntityValidatorService    $entityValidator Сервис валидатора объектов сущностей БД
     * @param EntityManagerInterface    $doctrine        Менеджер сущностей БД
     * @param TwigEnvironment           $twig            Twig-шаблонизатор
     * @param LoggerInterface           $logger          Логгер
     */
    public function __construct(
        private readonly TemplateRepository        $templates,
        private readonly TemplateHistoryRepository $histories,
        private readonly EntityValidatorService    $entityValidator,
        private readonly EntityManagerInterface    $doctrine,
        private readonly TwigEnvironment           $twig,
        private readonly LoggerInterface           $logger
    )
    {}

    /**
     * Сохранить данные Email шаблона
     *
     * @param EntityDTO $tplData  Данные шаблона
     * @param Uuid|null $userUuid UUID пользователя
     *
     * @return int
     * @throws TemplateException
     */
    public function save(EntityDTO $tplData, ?Uuid $userUuid = null): int
    {
        $template = $tplData->id ? $this->templates->find($tplData->id) : new Template();
        if (null === $template) {
            throw new TemplateException(
                "Отсутствует шаблон для редактирования [ID: {$tplData->id}]",
                TemplateException::NOT_EXISTS
            );
        }
        // заполнить данными объект шаблона
        if (!$template->getId()) {
            $this->fillingByDTO($template, $tplData);
            $template->setCreator($userUuid);
            $template->setCreatedAt(new \DateTimeImmutable());
        }
        else {
            if ($tplChanges = $this->fillingByDTO($template, $tplData)) {
                $template->setEditor($userUuid);
                $template->setEditedAt(new \DateTimeImmutable());
                $tplChanges->setEditor($userUuid);
                $tplChanges->setEditedAt($template->getEditedAt());
            }
        }
        $valid = $this->entityValidator->validate($template);
        if (!$valid) {
            throw new TemplateException(
                implode("\n", $valid),
                TemplateException::BAD_VALUES
            );
        }

        try {
            $this->doctrine->persist($template);
            $this->doctrine->flush();
            if (isset($tplChanges)) {
                $tplChanges->setTemplate($template);
                $valid = $this->entityValidator->validate($tplChanges);
                if (!$valid) {
                    throw new TemplateException(
                        implode("\n", $valid),
                        TemplateException::BAD_VALUES
                    );
                }

                $this->doctrine->persist($tplChanges);
                $this->doctrine->flush();
            }
        }
        catch (\Exception $error) {
            $this->logger->error($error->getMessage(), ['Exception' => $error]);
            throw new TemplateException(
                'Ошибка БД при сохранении изменений шаблона',
                TemplateException::DB_PROBLEM,
                $error
            );
        }

        return $template->getId();
    }

    /**
     * Заполнить объект шаблона данными из DTO
     *
     * @param Template  $template Заполняемый шаблон
     * @param EntityDTO $tplData  DTO с данными
     *
     * @return TemplateHistory|null Объект для сохранения изменений шаблона
     * @throws TemplateException
     */
    private function fillingByDTO(Template $template, EntityDTO $tplData): ?TemplateHistory
    {
        $changes = [];
        $value = null;
        if ($tplData->parentId) {
            $value = $this->templates->find($tplData->parentId);
            if (!$value) {
                throw new TemplateException(
                    "Отсутствует родительский шаблон [ID: {$tplData->id}]",
                    TemplateException::NOT_EXISTS
                );
            }
        }
        if ($template->getParent() !== $value) {
            $changes['parent'] = [
                'old' => $template->getParent()?->getId(),
                'new' => $value?->getId(),
            ];
            $template->setParent($value);
        }

        $value = trim($tplData->title);
        if ($template->getTitle() !== $value) {
            $changes['title'] = [
                'old' => $template->getTitle(),
                'new' => $value,
            ];
            $template->setTitle($value);
        }

        $value = trim($tplData->content);
        if ($template->getContent() !== $value) {
            $changes['content'] = [
                'old' => $template->getContent(),
                'new' => $value,
            ];
            $template->setContent($value);
        }

        $value = trim($tplData->subject);
        if ($template->getSubject() !== $value) {
            $changes['subject'] = [
                'old' => $template->getSubject(),
                'new' => $value,
            ];
            $template->setSubject($value);
        }

        $tplVariables = array_merge(
            $this->getPlaceholders($tplData->subject),
            $this->getPlaceholders($tplData->content),
            $tplData->variables
        );
        if ($template->getVariables() !== $tplVariables) {
            $changes['variables'] = [
                'old' => $template->getVariables(),
                'new' => $tplVariables,
            ];
            $template->setVariables($tplVariables);
        }

        return !empty($changes)
            ? (new TemplateHistory())->setChanges($changes)
            : null;
    }

    /**
     * Получить плейсхолдеры из содержимого шаблона
     *
     * @param string $content Содержимое шаблона
     *
     * @return array
     */
    private function getPlaceholders(string $content): array
    {
        if (empty($content) || !preg_match_all('/{{\s*([\w.-]+?)\s*}}/imu', $content, $m)) {
            return [];
        }
        $plItems = [];
        foreach ($m[1] as $pl) {
            $chunks = explode('.', $pl);
            $loopItem = &$plItems;
            foreach ($chunks as $plKey) {
                if (empty($plKey)) {
                    continue;
                }
                if (!is_array($loopItem)) {
                    $loopItem = [];
                }
                if (!array_key_exists($plKey, $loopItem)) {
                    $loopItem[$plKey] = '';
                }
                $loopItem = &$loopItem[$plKey];
            }
        }

        return $plItems;
    }

    /**
     * Получить доступные фильтры для выборки Email шаблонов
     *
     * @return array
     */
    public function getEntityFilters(): array
    {
        return $this->templates->getFilters();
    }

    /**
     * Получить доступные фильтры для выборки истории изменений Email шаблонов
     *
     * @return array
     */
    public function getHistoryFilters(): array
    {
        return $this->histories->getFilters();
    }

    /**
     * Получить объект шаблона
     *
     * @param int $id Идентификатор шаблона
     *
     * @return Template|null
     */
    public function getTemplate(int $id): ?Template
    {
        return $this->templates->find($id);
    }

    /**
     * Получить коллекцию шаблонов
     *
     * @param ListDTO  $params Параметры выборки шаблонов
     * @param int|null $count  Полное количество записей для выборки (OUT)
     *
     * @return Template[]
     */
    public function getTemplates(ListDTO $params, ?int &$count = null): array
    {
        $qb = $this->templates->createQueryBuilder('t');
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
            if ($filters->taskNumber) {
                $qb->andWhere($qb->expr()->eq('t.task_number', ':task'))
                    ->setParameter('task', $filters->taskNumber);
            }
            if ($filters->created?->from) {
                $qb->andWhere($qb->expr()->gte('t.created_at', ':from'))
                    ->setParameter('from', $filters->created->from->format('Y-m-d 00:00:00'));
            }
            if ($filters->created?->to) {
                $qb->andWhere($qb->expr()->lte('t.created_at', ':to'))
                    ->setParameter('to', $filters->created->to->format('Y-m-d 23:59:59'));
            }
        }
        if (!$filters?->withDeleted) {
            $qb->andWhere($qb->expr()->isNull('t.deleter'));
            $qb->andWhere($qb->expr()->isNull('t.deleted_at'));
        }
        // сортировка и ограничение выборки
        $qb->orderBy("t.{$params->sort->field}", $params->sort->reverse ? 'DESC' : 'ASC');
        $qb->setMaxResults($params->limit);
        if ($params->page > 1) {
            $qb->setFirstResult(($params->page - 1) * $params->limit);
        }
        // получение данных
        $result = (array)$qb->getQuery()->getResult();
        if (null !== $count) {
            try {
                $count = (int)$qb->select('COUNT(t.id)')
                    ->resetDQLPart('orderBy')
                    ->setMaxResults(1)
                    ->setFirstResult(null)
                    ->getQuery()
                    ->getSingleScalarResult();
            }
            catch (UnexpectedResultException $error) {
                $this->logger->error($error->getMessage(), ['exception' => $error]);
                $count = 0;
            }
        }

        return $result;
    }

    /**
     * Рендеринг темы и контента Email шаблона со всеми родителями
     *
     * @param Template $template  Шаблон для рендеринга
     * @param array    $variables Значения подставляемых переменных
     *
     * @return Template Шаблон с обработанными темой и контентом
     */
    public function render(Template $template, array $variables = []): Template
    {
        $content = trim($template->getContent());
        $subject = trim($template->getSubject());
        $workVariables = array_merge($this->getPlaceholders($subject), $this->getPlaceholders($content), $variables);
        $workTemplate = clone $template;
        try {
            foreach ($workTemplate->getVariables() as $name => $tplVariable) {
                $tplVariable = trim($tplVariable);
                if ($tplVariable && !array_key_exists($name, $workVariables)) {
                    $workVariables[$name] = $this->getTwigRenderedData($tplVariable, $workVariables);
                }
            }
            $content = $this->getTwigRenderedData($content, $workVariables);
            $subject = $this->getTwigRenderedData($subject, $workVariables);
        }
        catch (\Throwable $error) {
            $this->logger->warning($error->getMessage(), ['Exception' => $error]);
        }
        finally {
            $workTemplate->setContent($content);
            $workTemplate->setSubject($subject);
        }

        $workVariables = array_merge($workVariables, [ 'content' => $workTemplate->getContent() ]);
        $parent = $workTemplate->getParent();
        if (!empty($parent)) {
            $parent = $this->render($parent, $workVariables);
            $workTemplate->setContent($parent->getContent());
        }

        return $workTemplate;
    }

    /**
     * Рендеринг шаблонного текста по переданным значениям
     *
     * @param string $tplContent Содержимое для twig-шаблона
     * @param array $values      Значения для обработки
     *
     * @return string
     * @throws \Twig\Error\Error
     */
    private function getTwigRenderedData(string $tplContent, array $values): string
    {
        return htmlspecialchars_decode(
            $this->twig->createTemplate($tplContent)->render($values),
            ENT_COMPAT | ENT_QUOTES | ENT_HTML5
        );
    }

    /**
     * Сохранить в БД отметку об удалении шаблона с указанным ID
     *
     * @param int       $id       Идентификатор шаблона
     * @param Uuid|null $userUuid UUID пользователя
     *
     * @return int
     * @throws TemplateException
     */
    public function delete(int $id, Uuid $userUuid = null): int
    {
        $template = $this->getTemplate($id);
        if (null === $template) {
            throw new TemplateException(
                "Отсутствует шаблон с указанным ID [{$id}]",
                TemplateException::NOT_EXISTS
            );
        }

        $template->setDeleter($userUuid);
        $template->setDeletedAt(new \DateTimeImmutable('now'));
        $this->doctrine->persist($template);
        $this->doctrine->flush($template);

        return $template->getId();
    }

    /**
     * Получить данные истории изменений шаблонов
     *
     * @param HistoryDTO $params Параметры выборки
     * @param int|null   $count  Полное количество записей для выборки (OUT)
     *
     * @return array
     */
    public function getHistory(HistoryDTO $params, ?int &$count = null): array
    {
        if (null !== $params->id) {
            $result = $this->histories->find($params->id);
            if (null !== $count && $result) {
                $count = 1;
            }
            return [ $result->toArray() ];
        }

        $qb = $this->histories->createQueryBuilder('h');
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
            $qb->andWhere($expr->gte('h.edited_at', ':from'))
                ->setParameter(':from', $params->period->from->format('Y-m-d 00:00:00'));
        }
        if (null !== $params->period?->to) {
            $qb->andWhere($expr->lte('h.edited_at', ':to'))
                ->setParameter(':to', $params->period->to->format('Y-m-d 23:59:59'));
        }

        $qb->orderBy('h.edited_at', $params->reverse ? 'DESC' : 'ASC');

        if ($params->limit > 0) {
            $qb->setMaxResults($params->limit);
            if ($params->page > 1) {
                $qb->setFirstResult(($params->page - 1) * $params->limit);
            }
        }

        $result = (array)$qb->getQuery()->getResult();
        if (null !== $count) {
            try {
                $count = (int)$qb->select('COUNT(h.id)')
                    ->resetDQLPart('orderBy')
                    ->setMaxResults(1)
                    ->setFirstResult(null)
                    ->getQuery()
                    ->getSingleScalarResult();
            }
            catch (UnexpectedResultException $error) {
                $this->logger->error($error->getMessage(), ['exception' => $error]);
                $count = 0;
            }
        }

        return array_map(static fn(TemplateHistory $history) => $history->toArray(), $result);
    }
}