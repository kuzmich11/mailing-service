<?php

namespace App\Repository;

use App\Entity\MailingList;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Репозиторий списка рассылок
 *
 * @extends ServiceEntityRepository<MailingList>
 *
 * @method MailingList|null find($id, $lockMode = null, $lockVersion = null)
 * @method MailingList|null findOneBy(array $criteria, array $orderBy = null)
 * @method MailingList[]    findAll()
 * @method MailingList[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MailingListRepository extends ServiceEntityRepository
{
    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MailingList::class);
    }
}
