<?php

namespace App\Service;

//use App\DTO\File\ParamsDTO;
//use App\Entity\File;
//use App\Exception\FileException;
//use App\Repository\FileRepository;
//use Doctrine\ORM\EntityManagerInterface;
//use League\Flysystem\FilesystemException;
//use League\Flysystem\FilesystemOperator;
//use Psr\Log\LoggerInterface;

/**
 * Сервис обработки данных файлов
 */
class FileService
{
//    /**
//     * Конструктор
//     *
//     * @param LoggerInterface        $logger         Логгер
//     * @param EntityManagerInterface $doctrine       Интерфейс работы с БД
//     * @param FilesystemOperator     $defaultStorage Интерфейс работы с хранилищем файлов
//     * @param FileRepository         $files          Репозиторий файлов
//     * @param EntityValidatorService $validator      Сервис валидации сущностей БД
//     */
//    public function __construct(
//        private readonly LoggerInterface        $logger,
//        private readonly EntityManagerInterface $doctrine,
//        private readonly FilesystemOperator     $defaultStorage,
//        private readonly FileRepository         $files,
//        private readonly EntityValidatorService $validator,
//    )
//    {
//    }
//
//    /**
//     * Загрузить файл
//     *
//     * @param ParamsDTO $params DTO параметров файла
//     *
//     * @return int
//     * @throws FileException
//     */
//    public function upload(ParamsDTO $params): int
//    {
//        $hash = md5($params->filename . $params->size . $params->type);
//        $existFile = $this->files->findOneBy(['hash' => $hash]);
//        if (null !== $existFile) {
//            if (null === $existFile->getDeletedAt()) {
//                return $existFile->getId();
//            } else {
//                $existFile->setDeletedAt(null);
//                try {
//                    $this->doctrine->persist($existFile);
//                    $this->doctrine->flush();
//                } catch (\Throwable $err) {
//                    $this->logger->error($err->getMessage(), ['Exception' => $err]);
//                    throw new FileException(
//                        'Ошибка сохранения данных загружаемого файла',
//                        FileException::DB_PROBLEM
//                    );
//                }
//                return $existFile->getId();
//            }
//        }
//
//        try {
//            $this->defaultStorage->write($hash, base64_decode($params->content));
//        } catch (\Throwable $err) {
//            throw new FileException(
//                'Ошибка сохранения файла в хранилище',
//                FileException::UPLOAD_PROBLEM
//            );
//        }
//
//        $file = new File();
//        $file->setFilename($params->filename);
//        $file->setFileSize($params->size);
//        $file->setMimeType($params->type);
//        $file->setHash($hash);
//        $file->setUploadAt(new \DateTimeImmutable('now'));
//
//        $validateResult = $this->validator->validate($file);
//        if (is_array($validateResult)) {
//            throw new FileException(
//                implode("\n", $validateResult),
//                FileException::BAD_VALUES
//            );
//        }
//
//        try {
//            $this->doctrine->persist($file);
//            $this->doctrine->flush();
//        } catch (\Throwable $err) {
//            $this->logger->error($err->getMessage(), ['Exception' => $err]);
//            throw new FileException(
//                'Ошибка сохранения данных загружаемого файла',
//                FileException::DB_PROBLEM
//            );
//        }
//
//        return $file->getId();
//    }
//
//
//    /**
//     * Получить содержимое файла в base64
//     *
//     * @param int $fileId Идентификатор файла
//     *
//     * @return string
//     * @throws FileException
//     * @throws FilesystemException
//     */
//    public function getContent(int $fileId): string
//    {
//        $file = $this->files->find($fileId);
//        if (null === $file || !$this->defaultStorage->fileExists($file->getHash())) {
//            throw new FileException(
//                'Запрашиваемый файл не существует',
//                FileException::NOT_EXISTS
//            );
//        }
//        try {
//            return base64_encode($this->defaultStorage->read($file->getHash()));
//        } catch (\Throwable $err) {
//            $this->logger->error($err->getMessage(), ['Exception' => $err]);
//            throw new FileException(
//                'Не удалось прочитать файл. Возможно файл поврежден',
//                FileException::BAD_FILE
//            );
//        }
//    }
//
//    /**
//     * Получить список файлов
//     *
//     * @return array
//     */
//    public function list(): array
//    {
//        $files = $this->files->findBy(['deletedAt' => null]);
//        if (empty($files)) {
//            return [];
//        }
//        $result = [];
//        foreach ($files as $file) {
//            $workFile = $file->toArray();
//            try {
//                $workFile['content'] = $this->getContent($file->getId());
//            } catch (\Throwable $err) {
//                $this->logger->error($err->getMessage(), ['Exception' => $err]);
//                continue;
//            }
//            $result[] = $workFile;
//        }
//        return $result;
//    }
//
//    /**
//     * Получить данные файла
//     *
//     * @param int $id Идентификатор файла
//     *
//     * @return array
//     * @throws FileException
//     * @throws FilesystemException
//     */
//    public function entity(int $id): array
//    {
//        $file = $this->files->find($id);
//        if (null === $file || null !== $file->getDeletedAt()) {
//            return [];
//        }
//        $result = $file->toArray();
//        $result['content'] = $this->getContent($file->getId());
//        return $result;
//    }
//
//    /**
//     * Удалить файл
//     *
//     * @param int $id Идентификатор файла
//     *
//     * @return int
//     * @throws FileException
//     */
//    public function delete(int $id): int
//    {
//        $file = $this->files->find($id);
//        if (!$file) {
//            throw new FileException(
//                "Попытка удалить несуществующий файл с ID: $id",
//                FileException::NOT_EXISTS
//            );
//        }
//        $file->setDeletedAt(new \DateTimeImmutable('now'));
//        try {
//            $this->doctrine->persist($file);
//            $this->doctrine->flush();
//        } catch (\Throwable $err) {
//            $this->logger->error($err->getMessage(), ['Exception' => $err]);
//            throw new FileException(
//                'Ошибка сохранения данных письма в БД',
//                FileException::DB_PROBLEM
//            );
//        }
//        return $file->getId();
//    }
}