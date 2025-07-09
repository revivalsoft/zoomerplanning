<?php

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
