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
// src/Service/PushNotificationService.php
namespace App\Service;

use App\Entity\Ressource;
use App\Entity\NotificationDestinataire;
use App\Entity\NotificationMessage;
use App\Entity\PushSubscription;
use Doctrine\ORM\EntityManagerInterface;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PushNotificationService
{
    private EntityManagerInterface $em;
    private WebPush $webPush;
    private LoggerInterface $logger;
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(
        EntityManagerInterface $em,
        LoggerInterface $logger,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->em = $em;
        $this->logger = $logger;
        $this->urlGenerator = $urlGenerator;

        $this->webPush = new WebPush([
            'VAPID' => [
                'subject' => $_ENV['VAPID_SUBJECT'],
                'publicKey' => $_ENV['VAPID_PUBLIC_KEY'],
                'privateKey' => $_ENV['VAPID_PRIVATE_KEY'],
            ]
        ]);
    }

    /**
     * Envoie une notification commune à plusieurs ressources, et trace qui l’a reçue.
     */
    public function sendSharedNotification(array $ressources, string $title, Ressource $auteur, ?string $body = null): void
    {
        if (empty($ressources)) {
            return;
        }

        // Créer une seule NotificationMessage partagée
        $notification = new NotificationMessage();
        $notification->setMessage($body ?? '(message vide)');
        $notification->setCreatedAt(new \DateTimeImmutable());
        $notification->setAuteur($auteur);

        foreach ($ressources as $ressource) {
            $dest = new NotificationDestinataire();
            $dest->setNotification($notification);
            $dest->setRessource($ressource);
            $dest->setVue(false);
            $notification->getDestinataires()->add($dest);
        }

        $this->em->persist($notification);
        $this->em->flush();

        // Envoi à chaque ressource (via ses abonnements push)
        foreach ($ressources as $ressource) {
            $notifDest = $notification->getDestinataires()->filter(function (NotificationDestinataire $dest) use ($ressource) {
                return $dest->getRessource()->getId() === $ressource->getId();
            })->first();

            if (!$notifDest) {
                continue;
            }

            $url = $this->urlGenerator->generate('public_notification', [
                'id' => $notification->getId(),
                'notifDestId' => $notifDest->getId(),
                'token' => $notification->getAccessToken(),
            ], UrlGeneratorInterface::ABSOLUTE_URL);

            $payload = json_encode([
                'title' => $title,
                //'body' => $body ?? '',
                'body' => '',
                'url' => $url,
            ]);

            $subscriptions = $this->em->getRepository(PushSubscription::class)->findBy([
                'ressource' => $ressource,
            ]);

            foreach ($subscriptions as $subscriptionEntity) {
                $subscription = Subscription::create([
                    'endpoint' => $subscriptionEntity->getEndpoint(),
                    'publicKey' => $subscriptionEntity->getP256dh(),
                    'authToken' => $subscriptionEntity->getAuth(),
                    'contentEncoding' => $subscriptionEntity->getContentEncoding(),
                ]);

                $this->webPush->queueNotification($subscription, $payload);
            }
        }

        foreach ($this->webPush->flush() as $report) {
            $endpoint = $report->getRequest()->getUri()->__toString();
            if ($report->isSuccess()) {
                $this->logger->info("Notification envoyée avec succès à {$endpoint}");
            } else {
                $this->logger->warning("Erreur d'envoi à {$endpoint}: {$report->getReason()}");
                $this->removeInvalidSubscription($endpoint);
            }
        }
    }

    private function removeInvalidSubscription(string $endpoint): void
    {
        $subscription = $this->em->getRepository(PushSubscription::class)->findOneBy(['endpoint' => $endpoint]);
        if ($subscription) {
            $this->em->remove($subscription);
            $this->em->flush();
            $this->logger->info("Subscription supprimée pour endpoint invalide : {$endpoint}");
        }
    }
    // cette fonction ne sert que pour les tests
    public function sendOne(PushSubscription $subscriptionEntity, array $payload): bool
    {
        $subscription = Subscription::create([
            'endpoint' => $subscriptionEntity->getEndpoint(),
            'publicKey' => $subscriptionEntity->getP256dh(),
            'authToken' => $subscriptionEntity->getAuth(),
            'contentEncoding' => $subscriptionEntity->getContentEncoding(),
        ]);

        try {
            $report = $this->webPush->sendOneNotification($subscription, json_encode($payload));

            $endpoint = $report->getRequest()->getUri()->__toString();
            if ($report->isSuccess()) {
                $this->logger->info("✅ Notification envoyée à $endpoint");
                return true;
            } else {
                $this->logger->warning("⚠️ Échec d'envoi à $endpoint : {$report->getReason()}");
                $this->removeInvalidSubscription($endpoint);
                return false;
            }
        } catch (\Exception $e) {
            $this->logger->error("❌ Exception lors de l'envoi de la notification : " . $e->getMessage());
            return false;
        }
    }
}
