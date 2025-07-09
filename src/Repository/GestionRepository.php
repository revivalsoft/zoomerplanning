<?php
/*
 * Zoomerplanning - Logiciel de gestion des ressources humaines
 * Copyright (C) 2025 RevivalSoft
 *
 * Ce programme est un logiciel libre ; vous pouvez le redistribuer et/ou
 * le modifier selon les termes de la Licence Publique Générale GNU publiée
 * par la Free Software Foundation Version 3.
 *
 * Ce programme est distribué dans l'espoir qu'il sera utile,
 * mais SANS AUCUNE GARANTIE ; sans même la garantie implicite de
 * COMMERCIALISATION ou D’ADÉQUATION À UN BUT PARTICULIER. Voir la
 * Licence Publique Générale GNU pour plus de détails.
 *
 * Vous devriez avoir reçu une copie de la Licence Publique Générale GNU
 * avec ce programme ; si ce n'est pas le cas, voir
 * <https://www.gnu.org/licenses/>.
 */
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
