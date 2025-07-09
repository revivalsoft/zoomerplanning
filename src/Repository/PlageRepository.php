<?php

namespace App\Repository;

use App\Entity\Plage;
use App\Entity\Categorie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Plage>
 *
 * @method Plage|null find($id, $lockMode = null, $lockVersion = null)
 * @method Plage|null findOneBy(array $criteria, array $orderBy = null)
 * @method Plage[]    findAll()
 * @method Plage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PlageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Plage::class);
    }

   

    public function findWithAtLeastOneVisibleCategory(): array
{
    return $this->createQueryBuilder('p')
        ->join('p.categorie', 'c')
        ->where('c.visible = true')
        ->orderBy('p.sigle', 'ASC') // Tri alphabétique croissant
        ->distinct()
        ->getQuery()
        ->getResult();
}
}
