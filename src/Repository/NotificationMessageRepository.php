<?php

namespace App\Repository;

use App\Entity\NotificationMessage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Objective>
 */
class NotificationMessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NotificationMessage::class);
    }

    // Ajoute des méthodes personnalisées si besoin
}
