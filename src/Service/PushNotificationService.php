<?php

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
    private ?WebPush $webPush = null;
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
        // on n'initialise PAS WebPush ici (pour éviter les erreurs de param au build)
    }

    /**
     * Envoie une notification commune à plusieurs ressources, et trace qui l’a reçue.
     */
    public function sendSharedNotification(array $ressources, string $title, Ressource $auteur, ?string $body = null): void
    {
        if (empty($ressources)) {
            return;
        }

        $this->ensureWebPushInitialized();

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
                'body' => $body ?? '',
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
        $this->ensureWebPushInitialized();

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

    /**
     * Initialise WebPush à la demande. Tente :
     * 1) getenv / $_ENV / $_SERVER
     * 2) parse .env.local puis .env (project root)
     */
    private function ensureWebPushInitialized(): void
    {
        if ($this->webPush !== null) {
            return;
        }

        $vapid = $this->loadVapidFromEnvironmentOrFiles();

        if (empty($vapid['publicKey']) || empty($vapid['privateKey'])) {
            $msg = "Clés VAPID introuvables. Exécute : symfony console app:webpush:generate-vapid
Ensuite vérifie que .env.local contient VAPID_PUBLIC_KEY et VAPID_PRIVATE_KEY, ou exporte les variables d'environnement.";
            $this->logger->critical($msg);
            throw new \RuntimeException($msg);
        }

        $this->webPush = new WebPush([
            'VAPID' => [
                'subject' => $vapid['subject'] ?? 'mailto:contact@exemple.com',
                'publicKey' => $vapid['publicKey'],
                'privateKey' => $vapid['privateKey'],
            ],
        ]);
    }

    private function loadVapidFromEnvironmentOrFiles(): array
    {
        // 1) Tentative via getenv / _ENV / _SERVER
        $keys = [
            'subject' => getenv('VAPID_SUBJECT') ?: ($_ENV['VAPID_SUBJECT'] ?? $_SERVER['VAPID_SUBJECT'] ?? null),
            'publicKey' => getenv('VAPID_PUBLIC_KEY') ?: ($_ENV['VAPID_PUBLIC_KEY'] ?? $_SERVER['VAPID_PUBLIC_KEY'] ?? null),
            'privateKey' => getenv('VAPID_PRIVATE_KEY') ?: ($_ENV['VAPID_PRIVATE_KEY'] ?? $_SERVER['VAPID_PRIVATE_KEY'] ?? null),
        ];

        if (!empty($keys['publicKey']) && !empty($keys['privateKey'])) {
            return $keys;
        }

        // 2) Fallback: lire .env.local puis .env depuis la racine du projet
        $projectDir = dirname(__DIR__, 2); // src/Service -> project root
        foreach (['.env.local', '.env'] as $f) {
            $path = $projectDir . DIRECTORY_SEPARATOR . $f;
            if (!is_file($path)) {
                continue;
            }
            $envs = $this->parseEnvFile($path);
            if (empty($keys['subject']) && isset($envs['VAPID_SUBJECT'])) {
                $keys['subject'] = $envs['VAPID_SUBJECT'];
            }
            if (empty($keys['publicKey']) && isset($envs['VAPID_PUBLIC_KEY'])) {
                $keys['publicKey'] = $envs['VAPID_PUBLIC_KEY'];
            }
            if (empty($keys['privateKey']) && isset($envs['VAPID_PRIVATE_KEY'])) {
                $keys['privateKey'] = $envs['VAPID_PRIVATE_KEY'];
            }
            if (!empty($keys['publicKey']) && !empty($keys['privateKey'])) {
                break;
            }
        }

        return $keys;
    }

    /**
     * Parse un fichier .env simple (gère comments et quotes basiques)
     */
    private function parseEnvFile(string $path): array
    {
        $out = [];
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            // support "export FOO=bar"
            if (str_starts_with($line, 'export ')) {
                $line = substr($line, 7);
            }
            if (!str_contains($line, '=')) {
                continue;
            }
            [$k, $v] = explode('=', $line, 2);
            $k = trim($k);
            $v = trim($v);

            // remove inline comments after a space
            if (strpos($v, ' #') !== false) {
                $v = preg_replace('/\s+#.*$/', '', $v);
            }

            // remove surrounding quotes if present
            if (strlen($v) >= 2 && (($v[0] === '"' && substr($v, -1) === '"') || ($v[0] === "'" && substr($v, -1) === "'"))) {
                $v = substr($v, 1, -1);
            }

            $out[$k] = $v;
        }
        return $out;
    }
}
