<?php

namespace App\Command;

use App\Entity\PushSubscription; // adapte selon ton entité
use App\Service\PushNotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

// ce controleur n'est utile que pour faire des tests
// en ligne de commande : php bin/console app:send-push-to-all
class SendPushNotificationsCommand extends Command
{
    protected static $defaultName = 'app:send-push-to-all';

    private $em;
    private $pushNotificationService;

    public function __construct(EntityManagerInterface $em, PushNotificationService $pushNotificationService)
    {
        parent::__construct();
        $this->em = $em;
        $this->pushNotificationService = $pushNotificationService;
    }

    protected function configure()
    {
        $this
            ->setName('app:send-push-to-all')
            ->setDescription('Envoie une notification push à tous les abonnés');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $subscriptions = $this->em->getRepository(PushSubscription::class)->findAll();

        if (!$subscriptions) {
            $output->writeln('❌ Aucun abonnement trouvé.');
            return Command::FAILURE;
        }

        $payload = [
            'title' => 'Notification de test',
            'body' => 'Ceci est un test envoyé à tous les abonnés.',
            'icon' => '/logo.png',
        ];

        $successCount = 0;
        $failureCount = 0;

        foreach ($subscriptions as $subscription) {
            $success = $this->pushNotificationService->sendOne($subscription, $payload);
            if ($success) {
                $successCount++;
            } else {
                $failureCount++;
            }
        }

        $output->writeln("✅ Notifications envoyées : $successCount");
        if ($failureCount > 0) {
            $output->writeln("⚠️ Échecs d'envoi : $failureCount");
        }

        return Command::SUCCESS;
    }
}
