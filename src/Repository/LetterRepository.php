<?php

namespace App\Repository;

use App\DTO\Letter\ListDTO;
use App\Entity\Letter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Репозиторий писем
 *
 * @extends ServiceEntityRepository<Letter>
 *
 * @method Letter|null find($id, $lockMode = null, $lockVersion = null)
 * @method Letter|null findOneBy(array $criteria, array $orderBy = null)
 * @method Letter[]    findAll()
 * @method Letter[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LetterRepository extends ServiceEntityRepository
{
    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Letter::class);
    }

    /**
     * Получить список писем с заданными параметрами
     *
     * @param ListDTO $params
     * @return array
     */
    public function findByParams(ListDTO $params): array
    {
        $filters = $params->filter;
        $list = $this->createQueryBuilder('l');
        if ($params->limit > 0) {
            $list->setMaxResults($params->limit);
            if ($params->page > 1) {
                $list->setFirstResult(($params->page - 1) * $params->limit);
            }
        }
        if ($params->sort) {
            $list->orderBy("l.{$params->sort->field}", $params->sort->reverse ? 'DESC' : 'ASC');
        }
        if (null === $filters) {
            $list->andWhere('l.deletedAt IS NULL');
            return $list->getQuery()->getResult();
        }
        if ($filters->id) {
            $list->andWhere('l.id = :id')->setParameter('id', $filters->id);
        }
        if ($filters->subject) {
            $list->andWhere($list->expr()->like('l.subject', ':subject'))
                ->setParameter('subject', "%{$filters->subject}%");
        }
        if ($filters->form) {
            $list->andWhere('l.form = :form')->setParameter('form', $filters->form);
        }
        if ($filters->template) {
            $list->andWhere('l.template = :template')->setParameter('template', $filters->template);
        }
        if ($filters->smtp) {
            $list->join('l.smtp', 's')
                ->andWhere('s.id = :smtp')->setParameter('smtp', $filters->smtp);
        }
        if ($filters->sender) {
            $list->andWhere('l.sender = :sender')->setParameter('sender', $filters->sender);
        }
        if ($filters->recipient) {
            $list->andWhere('l.recipient = :recipient')->setParameter('recipient', $filters->recipient);
        }
        if ($filters->creator) {
            $list->andWhere('l.creator = :creator')->setParameter('creator', $filters->creator);
        }
        if (!$filters->withDeleted) {
            $list->andWhere('l.deletedAt IS NULL');
        }
        if ($filters->createdAt?->from) {
            $list->andWhere("l.createdAt >= '{$filters->createdAt->from->format('Y-m-d 00:00:00')}'");
        }
        if ($filters->createdAt?->to) {
            $list->andWhere("l.createdAt <= '{$filters->createdAt->to->format('Y-m-d 23:59:59')}'");
        }
        if ($filters->sentAt?->from) {
            $list->andWhere("l.sentAt >= '{$filters->sentAt->from->format('Y-m-d 00:00:00')}'");
        }
        if ($filters->sentAt?->to) {
            $list->andWhere("l.sentAt <= '{$filters->sentAt->to->format('Y-m-d 23:59:59')}'");
        }
        if ($filters->status) {
            $list->andWhere('l.status = :status')->setParameter('status', $filters->status);
        }
        return $list->getQuery()->getResult();
    }

    /**
     * Получить доступные фильтры
     *
     * @return array
     */
    public function findFilter(): array
    {
        $filters = [
            'id' => 'integer',
            'subject' => 'string',
            'form' => [
                'SYSTEM',
                'PROMO'
            ],
            'template' => [],
            'smtp' => [],
            'recipient' => [],
            'creator' => [],
            'createdAt' => [
                'from' => 'datetime',
                'to' => 'datetime',
            ],
            'sender' => [],
            'sentAt' => [
                'from' => 'datetime',
                'to' => 'datetime',
            ],
            'status' => [],
            'withDeleted' => 'boolean',
        ];
        $letters = $this->findBy(['deletedAt' => null]);
        foreach ($letters as $letter) {
            if ($letter->getTemplate() && !in_array($letter->getTemplate()->getId(), $filters['template'])) {
                $filters['template'][] = $letter->getTemplate()->getId();
            }
            if ($servers = $letter->getSmtp()) {
                foreach ($servers as $smtp) {
                    if (!in_array($smtp->getId(), $filters['smtp'])) {
                        $filters['smtp'][] = $smtp->getId();
                    }
                }
            }
            if ($letter->getRecipient() && !in_array($letter->getRecipient(), $filters['recipient'])) {
                $filters['recipient'][] = $letter->getRecipient();
            }
            if ($letter->getCreator() && !in_array($letter->getCreator(), $filters['creator'])) {
                $filters['creator'][] = $letter->getCreator();
            }
            if ($letter->getSender() && !in_array($letter->getSender(), $filters['sender'])) {
                $filters['sender'][] = $letter->getSender();
            }
            if ($letter->getStatus() && !in_array($letter->getStatus()->value, $filters['status'])) {
                $filters['status'][] = $letter->getStatus()->value;
            }
        }
        return $filters;
    }
}
