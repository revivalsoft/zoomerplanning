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
// src/Controller/PublicNotificationController.php
namespace App\Controller;

use App\Entity\NotificationDestinataire;
use App\Entity\NotificationMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class PublicNotificationController extends AbstractController
{

    #[Route('/notification/{id<\d+>}/{notifDestId<\d+>}/{token}', name: 'public_notification')]
    public function show(int $id, int $notifDestId, string $token, EntityManagerInterface $em): Response
    {
        $notif = $em->getRepository(NotificationMessage::class)->find($id);

        if (!$notif || $notif->getAccessToken() !== $token) {
            throw $this->createNotFoundException('Notification non trouvée ou accès non autorisé.');
        }

        $notifDest = $em->getRepository(NotificationDestinataire::class)->find($notifDestId);

        if (!$notifDest || $notifDest->getNotification()->getId() !== $notif->getId()) {
            throw $this->createNotFoundException('Destinataire introuvable ou non associé à cette notification.');
        }

        return $this->render('notification/public_show.html.twig', [
            'notification' => $notif,
            'destinataire' => $notifDest->getRessource(),
            'notifDestId' => $notifDest->getId(),
        ]);
    }

    #[Route('/notification/marquer-vue/{notifDestId<\d+>}/{token}', name: 'notification_marquer_vue')]

    public function marquerVue(int $notifDestId, string $token, EntityManagerInterface $em): Response

    {
        $notifDest = $em->getRepository(NotificationDestinataire::class)->find($notifDestId);

        if (!$notifDest) {
            throw $this->createNotFoundException('Notification destinataire introuvable.');
        }

        $notification = $notifDest->getNotification();

        if ($notification->getAccessToken() !== $token) {
            throw $this->createNotFoundException('Accès non autorisé (token invalide).');
        }

        // Marquer comme vue et sauvegarder uniquement si pas déjà lu
        if (!$notifDest->isVue()) {
            $notifDest->setVue(true); // Ici, dans l'entité, la dateVue sera positionnée automatiquement si null
            $em->flush();
        }

        // Afficher la confirmation
        return $this->render('notification/vue_confirmee.html.twig', [
            'notification' => $notification,
            'ressource' => $notifDest->getRessource(),
        ]);
    }
}
