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
// src/Controller/WebPushController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

use App\Entity\PushSubscription;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(('ROLE_USER'))]
class WebPushController extends AbstractController
{

    #[Route('/webpush', name: 'app_webpush')]
    public function index(): Response
    {
        return $this->render('web_push/index.html.twig', [
            'vapidPublicKey' => $_ENV['VAPID_PUBLIC_KEY'],
        ]);
    }

    #[Route('/webpush/subscribe', name: 'webpush_subscribe', methods: ['POST'])]
    public function subscribe(Request $request, EntityManagerInterface $em): JsonResponse
    {
        try {
            /** @var \App\Entity\Ressource $ressource */
            $ressource = $this->getUser();
            if (!$ressource) {
                return new JsonResponse(['error' => 'Utilisateur non connecté'], 401);
            }

            $data = json_decode($request->getContent(), true);
            if (!$data || !isset($data['endpoint'], $data['keys']['p256dh'], $data['keys']['auth'])) {
                return new JsonResponse(['error' => 'Données d\'abonnement invalides'], 400);
            }



            $existing = $em->getRepository(PushSubscription::class)->findOneBy([
                'endpoint' => $data['endpoint'],
                'ressource' => $ressource,
            ]);

            if (!$existing) {
                $subscription = new PushSubscription();
                $subscription->setEndpoint($data['endpoint']);                      // Obligatoire !
                $subscription->setPublicKey($data['keys']['p256dh']);               // correspond à publicKey
                $subscription->setAuthToken($data['keys']['auth']);                 // correspond à authToken
                $subscription->setP256dh($data['keys']['p256dh']);                  // correspond à p256dh
                $subscription->setAuth($data['keys']['auth']);                      // correspond à auth
                $subscription->setContentEncoding('aes128gcm');                     // constante ou variable selon besoin
                $subscription->setCreatedAt(new \DateTime());
                $subscription->setRessource($ressource);

                $em->persist($subscription);
                $em->flush();
            }

            return new JsonResponse(['success' => true]);
        } catch (\Throwable $e) {
            return new JsonResponse([
                'error' => 'Exception: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }
}
