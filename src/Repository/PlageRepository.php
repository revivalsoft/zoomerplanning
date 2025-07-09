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
