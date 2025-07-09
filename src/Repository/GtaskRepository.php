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

use App\Entity\Gtask;
use App\Entity\Ressource;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class GtaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Gtask::class);
    }

    public function findWithAllDependencies(int $projectId): array
    {
        return $this->createQueryBuilder('t')
            ->leftJoin('t.dependencies', 'd')
            ->addSelect('d')
            ->where('t.project = :project')
            ->setParameter('project', $projectId)
            ->getQuery()
            ->getResult();
    }

    public function findByUserProjects(Ressource $user): array
    {
        return $this->createQueryBuilder('g')
            ->join('g.project', 'p')
            ->where('p.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }

    public function findByProjectWithResources($project): array
    {
        return $this->createQueryBuilder('t')
            ->leftJoin('t.gtaskResources', 'gr')
            ->addSelect('gr')
            ->leftJoin('gr.ressource', 'r')
            ->addSelect('r')
            ->where('t.project = :project')
            ->setParameter('project', $project)
            ->getQuery()
            ->getResult();
    }
}
