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
namespace App\Controller;

use App\Entity\NotificationMessage;
use App\Repository\PushSubscriptionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(('ROLE_ADMIN'))]

final class WebpushListNotificationsController extends AbstractController
{
    #[Route('/admin/notifications-envoyees', name: 'admin_notifications_envoyees')]
    public function notificationsEnvoyees(Request $request, EntityManagerInterface $em): Response
    {
        $ressource = $this->getUser(); // administrateur connecté
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = 10;
        $offset = ($page - 1) * $limit;
        $search = trim($request->query->get('q', ''));

        // Total avec filtre
        $qbTotal = $em->createQueryBuilder()
            ->select('COUNT(DISTINCT n.id)')
            ->from(NotificationMessage::class, 'n')
            ->leftJoin('n.destinataires', 'd')
            ->leftJoin('d.ressource', 'r')
            ->where('n.auteur = :auteur')
            ->setParameter('auteur', $ressource);

        if ($search !== '') {
            $qbTotal
                ->andWhere('n.message LIKE :search OR r.nom LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        $total = $qbTotal->getQuery()->getSingleScalarResult();
        $totalPages = ceil($total / $limit);

        // Résultats paginés
        $qb = $em->createQueryBuilder()
            ->select('n', 'd', 'r')
            ->from(NotificationMessage::class, 'n')
            ->leftJoin('n.destinataires', 'd')
            ->leftJoin('d.ressource', 'r')
            ->where('n.auteur = :auteur')
            ->setParameter('auteur', $ressource)
            ->orderBy('n.createdAt', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        if ($search !== '') {
            $qb
                ->andWhere('n.message LIKE :search OR r.nom LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        $notifications = $qb->getQuery()->getResult();

        return $this->render('admin/notifications_envoyees.html.twig', [
            'notifications' => $notifications,
            'page' => $page,
            'totalPages' => $totalPages,
            'search' => $search,
        ]);
    }

    #[Route('/admin/subscriptions', name: 'push_subscriptions_list')]
    public function list(Request $request, PushSubscriptionRepository $repo): Response
    {
        $page = max(1, (int)$request->query->get('page', 1));
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $qb = $repo->createQueryBuilder('ps')
            ->leftJoin('ps.ressource', 'r')
            ->addSelect('r')
            ->orderBy('ps.createdAt', 'DESC');

        // Total des enregistrements
        $total = (clone $qb)->select('COUNT(ps.id)')->getQuery()->getSingleScalarResult();

        // Résultats paginés
        $subscriptions = $qb
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return $this->render('webpush_list_notifications/index.html.twig', [
            'subscriptions' => $subscriptions,
            'currentPage' => $page,
            'totalPages' => ceil($total / $limit),
        ]);
    }
}
