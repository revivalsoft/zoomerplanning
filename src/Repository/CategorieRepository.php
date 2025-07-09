<?php

namespace App\Repository;

use App\Entity\Categorie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Categorie>
 *
 * @method Categorie|null find($id, $lockMode = null, $lockVersion = null)
 * @method Categorie|null findOneBy(array $criteria, array $orderBy = null)
 * @method Categorie[]    findAll()
 * @method Categorie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CategorieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Categorie::class);
    }



    public function findCategorieData($idcategorie): ?Categorie
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.id = :val')
            ->setParameter('val', $idcategorie)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findVisibleCategories(): array
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.visible = :visible')
            ->setParameter('visible', true)
            //->orderBy('g.id', 'ASC') // Optionnel : pour trier les résultats
            ->getQuery()
            ->getResult();
    }
}
