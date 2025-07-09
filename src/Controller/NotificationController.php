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
// src/Controller/NotificationController.php
//webpush
namespace App\Controller;

use App\Form\PushNotificationType;
use App\Service\PushNotificationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(('ROLE_ADMIN'))]
class NotificationController extends AbstractController
{
    #[Route('/admin/push-notification', name: 'admin_push_notification')]
    public function sendPush(
        Request $request,
        PushNotificationService $pushService,
        #EntityManagerInterface $em
    ): Response {
        $form = $this->createForm(PushNotificationType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $ressources = $data['ressources'];
            $message = $data['message'];

            if ($ressources instanceof \Doctrine\Common\Collections\Collection) {
                $ressources = $ressources->toArray();
            }

            $pushService->sendSharedNotification($ressources, 'Notification personnalisée', $this->getUser(), $message);


            $this->addFlash('success', 'Notifications envoyées avec succès !');
            return $this->redirectToRoute('admin_push_notification');
        }

        return $this->render('admin/push_notification.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
