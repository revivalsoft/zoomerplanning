<?php

namespace App\Repository;

use App\Entity\Ressource;
use App\Entity\Project;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

/**
 * @extends ServiceEntityRepository<Ressource>
 *
 * @method Ressource|null find($id, $lockMode = null, $lockVersion = null)
 * @method Ressource|null findOneBy(array $criteria, array $orderBy = null)
 * @method Ressource[]    findAll()
 * @method Ressource[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RessourceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ressource::class);
    }

    public function findByGroupe(int $groupeId): array
    {
        return $this->createQueryBuilder('r')
            ->join('r.groupe', 'g')  // Assurez-vous que la relation est définie dans l'entité Ressource
            ->where('g.id = :groupeId')
            ->setParameter('groupeId', $groupeId)
            ->getQuery()
            ->getResult();
    }

    public function findByUserGroups(array $groupIds): array
    {
        return $this->createQueryBuilder('r')
            ->join('r.groupe', 'g')
            ->andWhere('g.id IN (:groupIds)')
            ->setParameter('groupIds', $groupIds)
            ->getQuery()
            ->getResult();
    }

    public function createAdminQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.roles LIKE :admin OR r.roles LIKE :superadmin')
            ->setParameter('admin', '%ROLE_ADMIN%')
            ->setParameter('superadmin', '%ROLE_SUPER_ADMIN%');
    }

    public function findByProject(Project $project): array
    {
        return $this->createQueryBuilder('r')
            ->innerJoin('r.gtaskResources', 'gr')
            ->innerJoin('gr.gtask', 'g')
            ->andWhere('g.project = :project')
            ->setParameter('project', $project)
            ->groupBy('r.id') // pour éviter les doublons
            ->getQuery()
            ->getResult();
    }
}
