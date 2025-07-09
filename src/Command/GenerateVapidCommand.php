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
