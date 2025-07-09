<?php

namespace App\Repository;

use App\Entity\Journal;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Journal>
 */
class JournalRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Journal::class);
    }

    public function findAllByMatchingRessourcePlageDate(int $adminId): array
    {
        $start = new \DateTimeImmutable('today midnight');
        $end = $start->modify('+1 day');

        return $this->createQueryBuilder('a')
            ->innerJoin('App\Entity\Ressource', 'b', 'WITH', 'a.idRes = b.id')
            ->innerJoin('App\Entity\Plage', 'c', 'WITH', 'a.idSigle = c.id')
            ->where('a.actionDate >= :start')
            ->andWhere('a.actionDate < :end')
            ->andWhere('IDENTITY(a.administrateur) = :adminId')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->setParameter('adminId', $adminId)
            ->select('a')
            ->getQuery()
            ->getResult();
    }
    // archives : toutes dates
    public function findAllByMatchingRessourcePlage(int $adminId): array
    {
        return $this->createQueryBuilder('a')
            ->innerJoin('App\Entity\Ressource', 'b', 'WITH', 'a.idRes = b.id')
            ->innerJoin('App\Entity\Plage', 'c', 'WITH', 'a.idSigle = c.id')
            ->andWhere('IDENTITY(a.administrateur) = :adminId')
            ->setParameter('adminId', $adminId)
            ->select('a')
            ->getQuery()
            ->getResult();
    }

    public function updateMailField(array $eventData): void
    {
        // Vérification de l'événement reçu
        if ($eventData['event'] !== 'reject') {
            return;
        }

        // Récupération de la date du jour (début et fin)
        $startOfDay = new \DateTime(); // remarque : on ne tient pas compte de la zone horaire mondiale : à creuser !!
        //$startOfDay->setTime(0, 0, 0);
        $startOfDay->modify('-3 days')->setTime(0, 0, 0);

        $endOfDay = new \DateTime();
        //$endOfDay->setTime(23, 59, 59);
        $endOfDay->modify('+1 day')->setTime(23, 59, 59); // Fin de la journée suivante

        // Création de la requête DQL
        $this->createQueryBuilder('j')
            ->update()
            ->set('j.mail', ':mail')
            ->where('j.actionDate BETWEEN :startOfDay AND :endOfDay')
            ->andWhere('j.idRes IN (
        SELECT r.id FROM App\Entity\Ressource r WHERE r.email = :email
    )')
            ->setParameter('mail', false)
            ->setParameter('startOfDay', $startOfDay)
            ->setParameter('endOfDay', $endOfDay)
            ->setParameter('email', $eventData['email'])
            ->getQuery()
            ->execute();
    }
}
