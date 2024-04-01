<?php

namespace App\Service;

use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Сервис для валидации объектов сущностей для БД
 */
final class EntityValidatorService
{
    /**
     * @param ValidatorInterface $validator Компонент Symfony валидатора
     */
    public function __construct(
        private readonly ValidatorInterface $validator
    )
    {}

    /**
     * Проверить ошибки в объекте сущности БД
     *
     * @param object $entity Проверяемый объект сущности БД
     *
     * @return bool|array
     */
    public function validate(object $entity): bool|array
    {
        $violations = [];
        $errors = $this->validator->validate($entity);
        if ($errors->count() > 0) {
            foreach ($errors->getIterator() as $error) {
                $violations[] = $error->getMessage();
            }
        }
        return empty($violations) ?: $violations;
    }
}