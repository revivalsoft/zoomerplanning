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

use App\Entity\Groupe;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;



/**
 * @extends ServiceEntityRepository<Groupe>
 *
 * @method Groupe|null find($id, $lockMode = null, $lockVersion = null)
 * @method Groupe|null findOneBy(array $criteria, array $orderBy = null)
 * @method Groupe[]    findAll()
 * @method Groupe[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GroupeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Groupe::class);
    }


    /**
     * Retourne les ressources composant le groupe
     * dans le contrôleur Plannings
     *
     * @param [type] $idgroupe
     * @return Groupe|null
     */
    public function findGroupeData($idgroupe): ?Groupe
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.id = :val')
            ->setParameter('val', $idgroupe)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findVisibleGroups(): array
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.visible = :visible')
            ->setParameter('visible', true)
            //->orderBy('g.id', 'ASC') // Optionnel : pour trier les résultats
            ->getQuery()
            ->getResult();
    }
}
