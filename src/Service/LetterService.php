<?php

namespace App\Service;

use App\DTO\Letter\HistoryDTO;
use App\DTO\Letter\ListDTO;
use App\DTO\Letter\EntityDTO;
use App\Entity\Letter;
use App\Entity\LetterHistory;
use App\Enum\LetterFormEnum;
use App\Enum\LetterStatusEnum;
use App\Exception\FileException;
use App\Exception\LetterException;
use App\Exception\SmtpException;
use App\Repository\LetterHistoryRepository;
use App\Repository\FileRepository;
use App\Repository\LetterRepository;
use App\Repository\SmtpAccountRepository;
use App\Repository\TemplateRepository;
use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Uuid;

/**
 * Сервис получения и сохранения данных писем
 */
class LetterService
{
    /**
     * Конструктор
     *
     * @param LoggerInterface         $logger             Логгер
     * @param LetterRepository        $letterRepository   Репозиторий писем
     * @param LetterHistoryRepository $history            Репозиторий истории изменений писем
     * @param TemplateRepository      $templateRepository Репозиторий шаблонов
     * @param FileRepository          $fileRepository     Репозиторий файлов
     * @param SmtpAccountRepository   $smtpRepository     Репозиторий серверов отправки
     * @param EntityManagerInterface  $doctrine           Интерфейс работы с БД
     * @param EntityValidatorService  $entityValidator    Сервис валидации сущностей БД
     * @param BrokerService           $broker             Брокер очередей
     */
    public function __construct(
        private readonly LoggerInterface         $logger,
        private readonly LetterRepository        $letterRepository,
        private readonly LetterHistoryRepository $history,
        private readonly TemplateRepository      $templateRepository,
        private readonly FileRepository          $fileRepository,
        private readonly SmtpAccountRepository   $smtpRepository,
        private readonly EntityManagerInterface  $doctrine,
        private readonly EntityValidatorService  $entityValidator,
        private readonly BrokerService           $broker
    )
    {
    }

    /**
     * Получить список писем с заданными параметрами
     *
     * @param ListDTO $params DTO параметров фильтрации
     *
     * @return array
     */
    public function list(ListDTO $params): array
    {
        return $this->letterRepository->findByParams($params);
    }

    /**
     * Сохранить данные письма
     *
     * @param EntityDTO $params   Параметры письма
     * @param Uuid      $userUuid UUID сотрудника, вносящего изменение
     *
     * @return int|null
     * @throws LetterException
     */
    public function save(EntityDTO $params, Uuid $userUuid): ?int
    {
        $letter = $params->id ? $this->letterRepository->find($params->id) : new Letter();
        if (null === $letter) {
            throw new LetterException("Письма с ID: $params->id не существует",
                LetterException::NOT_EXISTS);
        }
        $changes = [];
        $value = trim($params->subject);
        if ($letter->getSubject() !== $value) {
            $changes['subject'] = [
                'old' => $letter->getSubject(),
                'new' => $params->subject
            ];
            $letter->setSubject($params->subject);
        }
        $value = $params->form;
        if ($letter->getForm() !== $value) {
            $changes['form'] = [
                'old' => $letter->getForm(),
                'new' => $params->form
            ];
            $letter->setForm($params->form);
        }
        $value = $this->templateRepository->find($params->template);
        if ($letter->getTemplate() !== $value) {
            $changes['template'] = [
                'old' => $letter->getTemplate(),
                'new' => $params->template
            ];
            $letter->setTemplate($value);
        }
        $value = $params->smtp;
        if (!empty($value)) {
            $smtp = $this->smtpRepository->findBy(['id' => $value]);
            if (!$smtp) {
                throw new SmtpException(
                    "Заданные серверы не найдены",
                    SmtpException::NOT_EXISTS
                );
            }
            array_walk($smtp, fn($account) => $letter->addSmtp($account));
            if ($params->id) {
                $changes['addSmtp'] = [
                    'smtp' => $value
                ];
            }
        }
        $value = $params->attachments;
        if (!empty($value)) {
            $attachments = $this->fileRepository->findBy(['id' => $params->smtp]);
            if (!$attachments) {
                throw new FileException(
                    "Заданные вложения не найдены",
                    FileException::NOT_EXISTS
                );
            }
            array_walk($attachments, fn($file) => $letter->addAttachment($file));
            if ($params->id) {
                $changes['addAttachment'] = [
                    'attachments' => $value
                ];
            }
        }
        $value = $params->recipient;
        if ($letter->getRecipient() !== $value) {
            $changes['recipient'] = [
                'old' => $letter->getRecipient(),
                'new' => $params->recipient
            ];
            $letter->setRecipient($params->recipient);
        }
        $value = $params->values;
        if ($letter->getValues() !== $value) {
            $changes['values'] = [
                'old' => $letter->getValues(),
                'new' => $params->values
            ];
            $letter->setValues($params->values);
        }
        if (!$params->id) {
            $letter->setCreator($userUuid);
            $letter->setCreatedAt(new \DateTimeImmutable('now'));
        } else {
            $letter->setEditor($userUuid);
            $letter->setEditedAt(new \DateTimeImmutable('now'));
        }

        $validateResult = $this->entityValidator->validate($letter);
        if (is_array($validateResult)) {
            throw new LetterException(
                implode("\n", $validateResult),
                LetterException::BAD_VALUES
            );
        }

        try {
            $this->doctrine->persist($letter);
            $this->doctrine->flush();
        } catch (\Throwable $err) {
            $this->logger->error($err->getMessage(), ['Exception' => $err]);
            throw new LetterException(
                'Ошибка сохранения данных письма в БД',
                LetterException::DB_PROBLEM);
        }

        if ($params->id && !empty($changes)) {
            $history = new LetterHistory();
            $history->setLetter($letter);
            $history->setChanges($changes);
            $history->setEditor($userUuid);
            $history->setEditedAt(new \DateTimeImmutable('now'));
            try {
                $this->doctrine->persist($history);
                $this->doctrine->flush();
            } catch (\Throwable $err) {
                $this->logger->error($err->getMessage(), ['Exception' => $err]);
                throw new LetterException(
                    'Ошибка сохранения изменений данных письма в БД',
                    LetterException::DB_PROBLEM);
            }
        }

        return $letter->getId();
    }

    /**
     * Получить доступные фильтры
     *
     * @return array
     */
    public function getEntityFilters(): array
    {
        return $this->letterRepository->findFilter();
    }

    /**
     * Пометить письмо как удаленное
     *
     * @param int $id ID письма
     * @param Uuid $userUuid UUID пользователя
     *
     * @return int|null
     * @throws LetterException
     */
    public function delete(int $id, Uuid $userUuid): ?int
    {
        $letter = $this->letterRepository->find($id);
        if (!$letter) {
            throw new LetterException(
                "Попытка удалить несуществующее письмо с ID: $id",
                LetterException::NOT_EXISTS
            );
        }
        $letter->setDeleter($userUuid);
        $letter->setDeletedAt(new \DateTimeImmutable('now'));
        try {
            $this->doctrine->persist($letter);
            $this->doctrine->flush();
        } catch (\Throwable $err) {
            $this->logger->error($err->getMessage(), ['Exception' => $err]);
            throw new LetterException(
                'Ошибка сохранения данных письма в БД',
                LetterException::DB_PROBLEM
            );
        }
        return $letter->getId();
    }

    /** Получить историю изменения писем
     *
     * @param HistoryDTO $params DTO для получения истории изменений писем
     *
     * @return array
     */
    public function history(HistoryDTO $params): array
    {
        return $this->history->findByParams($params);
    }

    /**
     * Получить возможные фильтры для истории
     *
     * @return array
     */
    public function getHistoryFilters(): array
    {
        return $this->history->findFilter();
    }

    /**
     * Поместить письмо в очередь на отправку
     *
     * @param int $id ID письма
     * @param Uuid $userUuid UUID сотрудника
     *
     * @return array
     * @throws LetterException
     * @throws FilesystemException
     */
    public function send(int $id, Uuid $userUuid): array
    {
        $letter = $this->letterRepository->find($id);
        if (!$letter) {
            throw new LetterException('Попытка отправить несуществующее письмо', LetterException::NOT_EXISTS);
        }
        if ($letter->getDeletedAt()) {
            throw new LetterException('Попытка отправить удаленное письмо', LetterException::BAD_VALUES);
        }
        if (!$letter->getTemplate()) {
            throw new LetterException('Не выбран шаблон для отправки письма', LetterException::BAD_VALUES);
        }
        if ($letter->getStatus() === LetterStatusEnum::PROCESSING) {
            throw new LetterException('Указанное письмо уже в процессе отправки', LetterException::MAILING_ERROR);
        }
        // получить "рабочий" объект ЕЩЁ НЕ ОТПРАВЛЕННОГО письма
        $letter = $this->getUnsentLetter($letter, $userUuid);
        if ($letter->getForm() == LetterFormEnum::SYSTEM) {
            $message = $this->broker->prepareSystem($letter);
            if (null === $message) {
                $letter->setStatus(LetterStatusEnum::BAD_RECIPIENT);
                try {
                    $this->doctrine->persist($letter);
                    $this->doctrine->flush();
                } catch (\Throwable $err) {
                    $this->logger->error($err->getMessage(), ['Exception' => $err]);
                    throw new LetterException('Ошибка сохранения данных письма в БД', LetterException::DB_PROBLEM);
                }
                $result = false;
            } else {
                $result = $this->broker->publishMessages([$message]);
            }
        } else {
            $result = $this->broker->publishPromo(['id' => $letter->getId()]);
        }
        if ($result) {
            $letter->setSender($userUuid);
            $letter->setStatus(LetterStatusEnum::PROCESSING);
            $letter->setSentAt(new \DateTimeImmutable('now'));
        } else {
            $letter->setStatus(LetterStatusEnum::NOT_SENT);
        }
        try {
            $this->doctrine->persist($letter);
            $this->doctrine->flush();
        } catch (\Throwable $err) {
            $this->logger->error($err->getMessage(), ['Exception' => $err]);
            throw new LetterException('Ошибка сохранения данных письма в БД', LetterException::DB_PROBLEM);
        }
        return [
            'id' => $letter->getId(),
            'result' => $result
        ];
    }

    /**
     * Получить объект НЕОТПРАВЛЕННОГО письма - при необходимости создать его в БД
     *
     * @param Letter $srcLetter Объект исходного письма
     * @param Uuid   $userUuid  UUID сотрудника
     *
     * @return Letter
     * @throws LetterException
     */
    protected function getUnsentLetter(Letter $srcLetter, Uuid $userUuid): Letter
    {
        if ($srcLetter->getStatus() !== LetterStatusEnum::SENT) {
            return $srcLetter;
        }

        $letter = new Letter();
        if (!empty($srcLetter->getSubject())) {
            $letter->setSubject($srcLetter->getSubject());
        }
        if (!empty($srcLetter->getContent())) {
            $letter->setContent($srcLetter->getContent());
        }
        if (!empty($srcLetter->getForm())) {
            $letter->setForm($srcLetter->getForm());
        }
        if (!empty($srcLetter->getTemplate())) {
            $letter->setTemplate($srcLetter->getTemplate());
        }
        if (!empty($srcLetter->getSmtp())) {
            $smtps = $srcLetter->getSmtp()->toArray();
            foreach ($smtps as $smtp){
                $letter->addSmtp($smtp);
            }
        }
        if (!empty($srcLetter->getRecipient())) {
            $letter->setRecipient($srcLetter->getRecipient());
        }
        if (!empty($srcLetter->getAttachments())) {
            foreach ($srcLetter->getAttachments() as $attachment) {
                $letter->addAttachment($attachment);
            }
        }
        if (!empty($srcLetter->getValues())) {
            $letter->setValues($srcLetter->getValues());
        }
        $letter->setCreator($userUuid);
        $letter->setCreatedAt(new \DateTimeImmutable('now'));
        try {
            $this->doctrine->persist($letter);
            $this->doctrine->flush();
        } catch (\Throwable $err) {
            $this->logger->error($err->getMessage(), ['Exception' => $err]);
            throw new LetterException(
                'Ошибка сохранения в БД созданной копии письма для повторной отправки',
                LetterException::DB_PROBLEM
            );
        }
        return $letter;
    }

    /**
     * Получить данные письма
     *
     * @param int $id Id письма
     *
     * @return Letter|null
     */
    public function entity(int $id): ?Letter
    {
        return $this->letterRepository->find($id);
    }
}