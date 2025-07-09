<?php
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
