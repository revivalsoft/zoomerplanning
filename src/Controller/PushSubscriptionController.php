<?php
// src/Controller/PushSubscriptionController.php
//webpush
namespace App\Controller;

use App\Entity\PushSubscription;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(('ROLE_USER'))]
class PushSubscriptionController extends AbstractController
{
    #[Route('/subscribe', name: 'app_subscribe', methods: ['POST'])]
    public function subscribe(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['endpoint'], $data['keys']['p256dh'], $data['keys']['auth'])) {
            return $this->json(['error' => 'Données invalides'], 400);
        }

        $repo = $em->getRepository(PushSubscription::class);
        $existing = $repo->findOneBy(['endpoint' => $data['endpoint']]);

        if (!$existing) {
            $subscription = new PushSubscription();
            $subscription->setEndpoint($data['endpoint']);
            $subscription->setP256dh($data['keys']['p256dh']);
            $subscription->setAuth($data['keys']['auth']);
            $subscription->setCreatedAt(new \DateTime());

            $em->persist($subscription);
            $em->flush();
        }

        return $this->json(['success' => true]);
    }

    #[Route('/unsubscribe', name: 'app_unsubscribe', methods: ['POST'])]
    public function unsubscribe(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['endpoint'])) {
            return $this->json(['error' => 'Données invalides'], 400);
        }

        $repo = $em->getRepository(PushSubscription::class);
        $existing = $repo->findOneBy(['endpoint' => $data['endpoint']]);

        if ($existing) {
            $em->remove($existing);
            $em->flush();
        }

        return $this->json(['success' => true]);
    }
}
