<?php

namespace App\Controller;

use App\DTO\Template\EntityDTO;
use App\DTO\Template\HistoryDTO;
use App\DTO\Template\ListDTO;
use App\Entity\Template;
use App\Enum\FilterTypeEnum;
use App\Exception\TemplateException;
use App\Service\TemplateService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;

/**
 * Контроллер запросов для Email шаблонов
 */
#[AsController]
class TemplateController extends JsonRpcController
{
    /** @var string Название заголовка авторизованного пользователя */
    private const AUTH_USER_HEADER = 'x-api-key';

    /**
     * Конструктор с DI
     *
     * @param TemplateService $service Сервис данных Email шаблонов
     * @param LoggerInterface $logger  Логгер
     */
    public function __construct(
        private readonly TemplateService $service,
        private readonly LoggerInterface $logger
    )
    {
    }

    /**
     * Основная точка входа для контроллера
     *
     * @param Request $request Объект запроса
     *
     * @return JsonResponse
     */
    #[Route('/template', name: 'email_template', methods: ['POST'])]
    public function index(Request $request): JsonResponse
    {
        return parent::index($request);
    }

    /**
     * Получить доступные фильтры для выборки
     *
     * @param ?string $type Тип фильтра
     *
     * @return array
     * @throws TemplateException
     */
    public function filters(string $type = null): array
    {
        return match ((null === $type) ? FilterTypeEnum::ENTITY : FilterTypeEnum::tryFrom($type)) {
            FilterTypeEnum::HISTORY => $this->service->getHistoryFilters(),
            FilterTypeEnum::ENTITY => $this->service->getEntityFilters(),
            default => throw new TemplateException('Запрошен некорректный тип фильтров', TemplateException::BAD_VALUES)
        };
    }

    /**
     * Получить Email шаблон по его ID
     *
     * @param int $id Идентификатор шаблона
     *
     * @return Template|null
     */
    public function entity(int $id): ?Template
    {
        return $this->service->getTemplate($id);
    }

    /**
     * Получить коллекцию Email шаблонов по параметрам выборки
     *
     * @param ListDTO $params Параметры выборки
     *
     * @return Template[]
     */
    public function list(ListDTO $params): array
    {
        $result = ['count' => 0, 'list' => []];
        $templates = $this->service->getTemplates($params, $result['count']);

        $parents = [];
        foreach ($templates as $template) {
            $this->collectParents($parents, $template);
            $data =$template->toArray();
            $result['list'][$template->getId()] = $data;
        }

        if (!empty($parents)) {
            foreach ($parents as &$item) {
                if (array_key_exists($item->getId(), $result['list'])) {
                    $item = null;
                } else {
                    $item = $item->toArray();
                }
            }
            if ($parents = array_filter($parents)) {
                $result['parents'] = $parents;
            }
        }

        return $result;
    }

    /**
     * Собрать все родительские шаблоны
     *
     * @param array $parents Массив для родительских шаблонов
     * @param Template $template Шаблон для получения родителей
     *
     * @return void
     */
    private function collectParents(array &$parents, Template $template): void
    {
        if ($template->getParent() && !isset($parents[$template->getParent()->getId()])) {
            $parents[$template->getParent()->getId()] = $template->getParent();
            $this->collectParents($parents, $template->getParent());
        }
    }

    /**
     * Сохранить данные шаблона
     *
     * @param EntityDTO $tplData DTO с сохраняемыми данными шаблона
     *
     * @return array
     * @throws TemplateException
     */
    public function save(EntityDTO $tplData): array
    {
        try {
            $userUuid = Uuid::fromString($this->getRequestHeaders(self::AUTH_USER_HEADER));
            // TODO: Добавить проверку доступа
        } catch (\Throwable $error) {
            $this->logger->error($error->getMessage(), ['Exception' => $error]);
            throw new TemplateException(
                'Отсутствует корректный UUID пользователя',
                TemplateException::BAD_VALUES
            );
        }

        try {
            $savingResult = [
                'id' => $this->service->save($tplData, $userUuid),
                'issue' => true
            ];
        } catch (TemplateException $error) {
            $savingResult = [
                'errors' => explode("\n", $error->getMessage()),
                'issue' => false,
            ];
        }
        return $savingResult;
    }

    /**
     * Рендеринг шаблона (тема и содержимое)
     *
     * @param int $id Идентификатор шаблона
     * @param array $values Данные для подстановки
     *
     * @return string[]
     */
    public function rendering(int $id, array $values = []): array
    {
        $template = $this->service->getTemplate($id);
        $renderResult = ['subject' => '', 'content' => ''];
        if ($template) {
            $template = $this->service->render($template, $values);
            $renderResult['subject'] = str_replace(['\n', '\r'], ["\n", ''], $template->getSubject());
            $renderResult['content'] = str_replace(['\n', '\r'], ["\n", ''], $template->getContent());
        }
        return $renderResult;
    }

    /**
     * Установить шаблону отметку об удалении
     *
     * @param int $id
     *
     * @return array
     * @throws TemplateException
     */
    public function delete(int $id): array
    {
        try {
            $userUuid = Uuid::fromString($this->getRequestHeaders(self::AUTH_USER_HEADER));
            // TODO: Добавить проверку доступа
        } catch (\Throwable $error) {
            $this->logger->error($error->getMessage(), ['Exception' => $error]);
            throw new TemplateException(
                'Отсутствует корректный UUID пользователя',
                TemplateException::BAD_VALUES
            );
        }

        try {
            $result = [
                'id' => $this->service->delete($id, $userUuid),
                'issue' => true
            ];
        } catch (TemplateException $error) {
            $result = [
                'errors' => explode("\n", $error->getMessage()),
                'issue' => false,
            ];
        }
        return $result;
    }

    /**
     * Получить данные по истории изменений шаблонов
     *
     * @param HistoryDTO $params Параметры выборки изменений
     *
     * @return array
     */
    public function history(HistoryDTO $params): array
    {
        $count = 0;
        return [
            'count' => &$count,
            'list' => $this->service->getHistory($params, $count)
        ];
    }
}