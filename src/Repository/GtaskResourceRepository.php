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
// src/Repository/GtaskResourceRepository.php

namespace App\Repository;

use App\Entity\GtaskResource;
use App\Entity\Project;               // <-- bien l'entité, pas le repository
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class GtaskResourceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GtaskResource::class);
    }

    /**
     * Retourne toutes les Ressource r associées à au moins une Gtask de $project
     *
     * @param Project $project
     * @return array Ressource[]
     */
    // public function findResourcesByProject(Project $project): array
    // {
    //     return $this->createQueryBuilder('gr')
    //         ->select('r')
    //         ->distinct()
    //         ->innerJoin('gr.ressource', 'r')
    //         ->innerJoin('gr.gtask',     't')
    //         ->andWhere('t.project = :project')
    //         ->setParameter('project', $project)
    //         ->orderBy('r.nom', 'ASC')
    //         ->getQuery()
    //         ->getResult();
    // }
}
