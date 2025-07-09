<?php
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
