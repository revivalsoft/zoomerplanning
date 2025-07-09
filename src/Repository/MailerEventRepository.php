<?php

namespace App\Repository;

use App\Entity\MailerEvent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class MailerEventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MailerEvent::class);
    }

    public function findByDate(\DateTime $date): array
    {
        $startOfDay = $date->setTime(0, 0);
        $endOfDay = (clone $startOfDay)->modify('+1 day');

        return $this->createQueryBuilder('e')
            ->andWhere('e.createdAt >= :start')
            ->andWhere('e.createdAt < :end')
            ->setParameter('start', $startOfDay)
            ->setParameter('end', $endOfDay)
            ->getQuery()
            ->getResult();
    }
}
