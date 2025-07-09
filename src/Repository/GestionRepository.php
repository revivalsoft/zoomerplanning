<?php

namespace App\Repository;

use App\Entity\Gestion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Gestion>
 *
 * @method Gestion|null find($id, $lockMode = null, $lockVersion = null)
 * @method Gestion|null findOneBy(array $criteria, array $orderBy = null)
 * @method Gestion[]    findAll()
 * @method Gestion[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GestionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Gestion::class);
    }

    //    /**
    //     * @return Gestion[] Returns an array of Gestion objects
    //     */
    public function findPlanning($value, $dateFirstDay, $dateLastDay): array
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.ressource = :val')
            ->setParameter('val', $value)
            ->andWhere('g.date >= :valfirst')
            ->setParameter('valfirst', $dateFirstDay)
            ->andWhere('g.date <= :vallast')
            ->setParameter('vallast', $dateLastDay)
            ->getQuery()
            ->getResult()
        ;
    }

    
    public function findOneElementPlageGestion($value): ?Gestion
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.id = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()

        ;
    }
}
