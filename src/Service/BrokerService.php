<?php

namespace App\Service;

//use App\Entity\Letter;
//use App\Enum\EmailStateEnum;
//use App\Enum\LetterFormEnum;
//use App\Enum\LetterStatusEnum;
//use App\Enum\TopicEnum;
//use App\Message\BroadcastMessage;
//use App\Message\Message;
//use App\Message\MessageInterface;
//use App\Message\ResultMessage;
//use App\Repository\GroupRepository;
//use App\Repository\RecipientRepository;
//use App\Repository\TemplateRepository;
//use Doctrine\ORM\EntityManagerInterface;
//use Generator;
//use League\Flysystem\FilesystemException;
//use League\Flysystem\FilesystemOperator;
//use Psr\Log\LoggerInterface;
//use Symfony\Component\Messenger\MessageBusInterface;
//use Symfony\Component\Messenger\Stamp\TransportNamesStamp;

/**
 * Брокер очередей
 */
class BrokerService
{
//    /** @var int Размер пакета сообщений публикуемых в брокере очередей (для массовой рассылки) */
//    private const MAX_BROADCAST_PACKET = 10000;
//
//    /**
//     * Конструктор
//     *
//     * @param LoggerInterface        $logger          Логер
//     * @param EntityManagerInterface $doctrine        Интерфейс работы с БД
//     * @param MessageBusInterface    $bus             Интерфейс транспорта
//     * @param TemplateService        $templateService Контроллер шаблонов
//     * @param GroupRepository        $group           Репозиторий групп получателей
//     * @param RecipientRepository    $recipients      Репозиторий получателей
//     * @param TemplateRepository     $templates       Репозиторий шаблонов
//     * @param FilesystemOperator     $defaultStorage  Интерфейс работы с хранилищем файлов
//     */
//    public function __construct(
//        private readonly LoggerInterface        $logger,
//        private readonly EntityManagerInterface $doctrine,
//        private readonly MessageBusInterface    $bus,
//        private readonly TemplateService        $templateService,
//        private readonly GroupRepository        $group,
//        private readonly RecipientRepository    $recipients,
//        private readonly TemplateRepository     $templates,
//        private readonly FilesystemOperator     $defaultStorage
//    )
//    {
//    }
//
//    /**
//     * Подготовить к публикации системное сообщение
//     *
//     * @param Letter $letter Письмо
//     *
//     * @return ?Message
//     * @throws \Exception|FilesystemException
//     */
//    public function prepareSystem(Letter $letter): ?Message
//    {
//        $mainBody = [ 'id' => $letter->getId() ];
//        // определение SMTP аккаунта для отправки
//        $smtp = $letter->getSmtp();
//        if (empty($smtp)) {
//            $mainBody['error'] = 'Указан некорректный SMTP для отправки';
//            $letter->setStatus(LetterStatusEnum::NOT_SENT);
//            $this->saveDbEntity($letter);
//
//            $this->publishResult($mainBody);
//            return null;
//        }
//        $smtpAccount = $smtp[array_rand($smtp->toArray())];
//        // определение корректного получателя для отправки
//        $recipient = current($this->getFilteredRecipients($letter));
//        if(empty($recipient)) {
//            return null;
//        }
//        // формирование сообщения для очереди отправки
//        $template = $this->templates->find($letter->getTemplate());
//        $renderTemplate = $this->templateService->render($template, $letter->getValues() ?: []);
//        $attachments = [];
//        if (!empty($letter->getAttachments())) {
//            foreach ($letter->getAttachments() as $file) {
//                $attachment = $file->toArray();
//                $attachment['content'] = base64_encode($this->defaultStorage->read($file->getHash()));
//                $attachments[] = array_merge($attachments, $attachment);
//            }
//        }
//        return new Message(
//            body: json_encode(array_merge($mainBody, [
//                'subject' => $renderTemplate->getSubject(),
//                'content' => $renderTemplate->getContent(),
//                'template' => $letter->getTemplate(),
//                'smtp' => [ $smtpAccount->getId() ],
//                'recipient' => $recipient->getId(),
//                'form' => $letter->getForm()->value,
//                'attachments' => $attachments
//            ]), JSON_UNESCAPED_UNICODE),
//            headers: [ MessageInterface::TOPIC_NAME_KEY => TopicEnum::PRIORITY->value ]);
//    }
//
//    /**
//     * Подготовить к публикации рекламное сообщение
//     *
//     * @param Letter $letter Письмо
//     *
//     * @return Message|Generator
//     * @throws FilesystemException
//     */
//    public function preparePromo(Letter $letter): array|Generator
//    {
//        $mainBody = [ 'id' => $letter->getId() ];
//        // получить коллекцию SMTP-аккаунтов, указанных в письме
//        $smtp = $letter->getSmtp();
//        if (empty($smtp)) {
//            $letter->setStatus(LetterStatusEnum::NOT_SENT);
//            $this->saveDbEntity($letter);
//
//            $mainBody['error'] = 'Указан некорректный SMTP аккаунт для отправки';
//            $this->publishResult($mainBody);
//            return [];
//        }
//        // получить коллекцию получателей из указанной в письме группы
//        $recipients = $this->getFilteredRecipients($letter);
//        $mainHeaders = [
//            MessageInterface::TOPIC_NAME_KEY => TopicEnum::REGULAR->value,
//            MessageInterface::BROADCAST_COUNT => count($recipients)
//        ];
//        // рендеринг шаблона для сохранения темы и тела в объект письма
//        $renderTemplate = ($template = $this->templates->find($letter->getTemplate()))
//            ? $this->templateService->render($template, $letter->getValues() ?: [])
//            : null;
//        $loopPayload = array_merge($mainBody, [
//            'subject' => $renderTemplate?->getSubject(),
//            'content' => $renderTemplate?->getContent()
//        ]);
//        $attachments = [];
//        if (!empty($letter->getAttachments())) {
//            foreach ($letter->getAttachments() as $file) {
//                $attachment = $file->toArray();
//                $attachment['content'] = base64_encode($this->defaultStorage->read($file->getHash()));
//                $attachments[] = $attachment;
//            }
//        }
//        $loopPayload['attachments'] = $attachments;
//        // формирование, по каждому получателю, сообщений рассылки для публикации
//        $loopCounter = 0;
//        $messages = [];
//        foreach ($recipients as $index => $recipient) {
//            $mainHeaders[MessageInterface::BROADCAST_INDEX] = ++$index;
//            $loopPayload['smtp'] = [ $smtp[array_rand($smtp->toArray())]->getId() ];
//            $loopPayload['recipient'] = $recipient->getId();
//            $messages[] = new Message(
//                body: json_encode($loopPayload, JSON_UNESCAPED_UNICODE),
//                headers: $mainHeaders
//            );
//            // ВЫДАЧА РЕЗУЛЬТИРУЮЩИХ СООБЩЕНИЙ ПО "ПАЧКАМ" ОПРЕДЕЛЁННОГО РАЗМЕРА
//            if (count($messages) >= self::MAX_BROADCAST_PACKET) {
//                yield $messages;
//                ++$loopCounter;
//                unset($messages);
//                $messages = [];
//            }
//        }
//        // сохранить статус письма при ошибке распределения рассылки по получателям
//        if (empty($messages) && !$loopCounter) {
//            $letter->setStatus(LetterStatusEnum::NOT_SENT);
//            $this->saveDbEntity($letter);
//
//            $mainBody['error'] = 'Письмо не добавлено в очередь для рассылки';
//            $this->publishResult($mainBody);
//        }
//
//        yield $messages;
//    }
//
//    /**
//     * Опубликовать сообщение в очереди `broadcast`
//     *
//     * @param array $body    Тело сообщения
//     * @param array $headers Заголовки сообщения
//     *
//     * @return bool
//     */
//    public function publishPromo(array $body, array $headers = []): bool
//    {
//        $message = new BroadcastMessage(json_encode($body, JSON_UNESCAPED_UNICODE), [], $headers);
//        if (empty($headers[MessageInterface::TOPIC_NAME_KEY])) {
//            $message->setHeader(MessageInterface::TOPIC_NAME_KEY, TopicEnum::BROADCAST->value);
//        }
//        $message->setTimestamp(time());
//        return !!$this->publishMessages([ $message ]);
//    }
//
//    /**
//     * Опубликовать сообщение в очереди `result`
//     *
//     * @param array $body    Тело сообщения
//     * @param array $headers Заголовки сообщения
//     *
//     * @return bool
//     */
//    public function publishResult(array $body, array $headers = []): bool
//    {
//        $message = new ResultMessage(json_encode($body, JSON_UNESCAPED_UNICODE), [], $headers);
//        if (empty($headers[MessageInterface::TOPIC_NAME_KEY])) {
//            $message->setHeader(MessageInterface::TOPIC_NAME_KEY, TopicEnum::RESULTS->value);
//        }
//        $message->setTimestamp(time());
//        return !!$this->publishMessages([ $message ]);
//    }
//
//    /**
//     * Опубликовать коллекцию сообщений в соответствующих очередях
//     *
//     * @param \Interop\Queue\Message[] $messages Коллекция объектов сообщений
//     *
//     * @return int
//     */
//    public function publishMessages(array $messages): int
//    {
//        $success = 0;
//        $isResultMessage = false;
//        foreach ($messages as $message) {
//            $topicName = array_key_exists(MessageInterface::TOPIC_NAME_KEY, $message->getHeaders())
//                ? TopicEnum::tryFrom($message->getHeaders()[MessageInterface::TOPIC_NAME_KEY])
//                : null;
//            if (!$topicName) {
//                $this->logger->error('Не определён топик для публикации сообщения');
//                continue;
//            }
//            if (TopicEnum::RESULTS === $topicName) {
//                $isResultMessage = true;
//            }
//            $message->setTimestamp(time());
//            try {
//                $this->bus->dispatch($message, [new TransportNamesStamp(strtolower($topicName->value))]);
//                ++$success;
//            }
//            catch (\Throwable $err) {
//                $this->logger->error($err->getMessage(), ['Exception' => $err]);
//            }
//        }
//        // сохранение результатов публикации сообщений
//        if (!$isResultMessage) {
//            if ($success) {
//                $mainBody['success'] = "Добавлено в очередь $success отправлений";
//            }
//            else {
//                $mainBody['error'] = 'Письмо не добавлено в очередь для рассылки';
//            }
//            $this->publishResult($mainBody);
//        }
//        return $success;
//    }
//
//    /**
//     * Получить получателей, указанных в письме и подходящих для рассылки
//     *
//     * @param Letter $letter Объект письма
//     *
//     * @return array
//     */
//    protected function getFilteredRecipients(Letter $letter): array
//    {
//        $errorSuffix = '';
//        if ($letter->getForm() === LetterFormEnum::PROMO) {
//            // коллекция получателей из группы, установленной в письме
//            $recipients = $this->group->find($letter->getRecipient())?->getRecipients();
//            $errorSuffix = 'в выбранной группе для рассылки';
//        }
//        else {
//            // коллекция из единственного получателя, установленного в письме
//            $recipients = [ $this->recipients->find($letter->getRecipient()) ];
//        }
//        if (!$recipients) {
//            $letter->setStatus(LetterStatusEnum::NOT_SENT);
//            $this->saveDbEntity($letter);
//            $mainBody['error'] = trim("Отсутствуют получатели {$errorSuffix}");
//            $this->publishResult($mainBody);
//            return [];
//        }
//        // фильтрация получателей, подходящих для рассылки
//        $mailingList = $unsentList = [];
//        // !!! Важен порядок следования значений - используется для маппинга в SQL-запрос !!!
//        $loopPayload = [ 'letter' => $letter->getId(), 'recipient' => null, 'isSent' => 'false', 'comment' => null ];
//        foreach ($recipients as $recipient) {
//            if (!$recipient->isConsent() && LetterFormEnum::PROMO === $letter->getForm()) {
//                $unsentList[] = array_merge($loopPayload, [
//                    'recipient' => $recipient->getId(),
//                    'comment' => sprintf('Нет согласия на рассылку [%s]', $recipient->getEmail())
//                ]);
//                continue;
//            }
//            if ($recipient->getState() !== EmailStateEnum::WORKING) {
//                $unsentList[] = array_merge($loopPayload, [
//                    'recipient' => $recipient->getId(),
//                    'comment' => sprintf('Не подходящий статус электронной почты [%s: %s]', $recipient->getEmail(), $recipient->getState()->value)
//                ]);
//                continue;
//            }
//            $mailingList[] = $recipient;
//        }
//        // сохранение данных обнаруженных получателей, НЕ ПОДХОДЯЩИХ для рассылки
//        if ($countItems = count($unsentList)) {
//            $queryString = 'INSERT INTO mail.mailing_list (letter_id, recipient_id, is_sent, comment) VALUES
//                %s
//                ON CONFLICT (letter_id, recipient_id) DO UPDATE SET is_sent = EXCLUDED.is_sent, comment = EXCLUDED.comment
//                RETURNING *';
//            $params = array_merge(...array_map('array_values', $unsentList));
//            $query = sprintf($queryString, implode(',', array_fill(0, $countItems, '(?,?,?,?)')));
//            try {
//                $this->doctrine->getConnection()->executeStatement($query, $params);
//            }
//            catch (\Doctrine\DBAL\Exception $e) {
//                $this->logger->error($e->getMessage(), ['Exception' => $e]);
//            }
//        }
//        return $mailingList;
//    }
//
//    /**
//     * Сохранить в БД данные объекта сущности
//     *
//     * @param mixed $entity Объект сущности БД, для сохранения данных
//     *
//     * @return void
//     */
//    public function saveDbEntity(mixed $entity): void
//    {
//        if (is_object($entity)) {
//            $this->doctrine->persist($entity);
//            $this->doctrine->flush();
//        }
//    }
}