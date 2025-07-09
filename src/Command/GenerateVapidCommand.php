<?php

namespace App\Command;

use Minishlink\WebPush\VAPID;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:webpush:generate-vapid',
    description: 'Génère des clés VAPID et les stocke dans .env.local',
)]
class GenerateVapidCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $keys = VAPID::createVapidKeys();

        $envFile = dirname(__DIR__, 2) . '/.env.local';
        $envContent = file_exists($envFile) ? file_get_contents($envFile) : '';

        $envContent .= "\nVAPID_PUBLIC_KEY={$keys['publicKey']}\n";
        $envContent .= "VAPID_PRIVATE_KEY={$keys['privateKey']}\n";
        $envContent .= "VAPID_SUBJECT=mailto:revivalsoft.planningtools@gmail.com\n";

        file_put_contents($envFile, $envContent);

        $output->writeln("<info>✅ Clés VAPID générées et ajoutées à .env.local</info>");
        return Command::SUCCESS;
    }
}
