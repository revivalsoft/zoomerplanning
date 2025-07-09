<?php

namespace App\Service;

use App\Entity\Journal;
use App\Entity\Plage;
use App\Entity\Ressource;
use App\Entity\RessourceToken;
use App\Repository\RessourceRepository;
use App\Repository\RessourceTokenRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Environment;

class MailService
{
    private MailerInterface $mailer;
    private EntityManagerInterface $em;
    private Environment $twig;
    private UrlGeneratorInterface $urlGenerator;
    private RequestStack $requestStack;
    private RessourceRepository $ressourceRepo;
    private RessourceTokenRepository $tokenRepo;

    public function __construct(
        MailerInterface $mailer,
        EntityManagerInterface $em,
        Environment $twig,
        UrlGeneratorInterface $urlGenerator,
        RequestStack $requestStack,
        RessourceRepository $ressourceRepo,
        RessourceTokenRepository $tokenRepo
    ) {
        $this->mailer = $mailer;
        $this->em = $em;
        $this->twig = $twig;
        $this->urlGenerator = $urlGenerator;
        $this->requestStack = $requestStack;
        $this->ressourceRepo = $ressourceRepo;
        $this->tokenRepo = $tokenRepo;
    }

    public function sendEmail(string $to, string $subject, string $bodyHtml, string $category): void
    {
        $request = $this->requestStack->getCurrentRequest();
        $host = $request ? $request->getHost() : 'example.com';

        // Extraire le domaine principal sans sous-domaine
        $domainParts = explode('.', $host);
        $domain = implode('.', array_slice($domainParts, -2)); // exemple : revivalsoft.com

        $fromEmail = 'no-reply@' . $domain;

        $email = (new Email())
            ->from($fromEmail)
            ->to($to)
            ->subject($subject)
            ->html($bodyHtml)
            ->text(strip_tags($bodyHtml));

        if ($category) {
            $email->getHeaders()->addTextHeader('X-MT-Category', $category);
        }

        $this->mailer->send($email);
    }

    public function sendPlanningMailsPourAdministrateur($adminUser): int
    {
        $username = $adminUser->getUserIdentifier();
        $usermail = $adminUser->getEmail();
        $baseUrl = $this->requestStack->getCurrentRequest()->getSchemeAndHttpHost();
        $subject = "Mise à jour de votre planning";
        $tabTypeAction = [1 => "Création", 2 => "Note", 3 => "Suppression"];

        $journaux = $this->em->getRepository(Journal::class)->findAll();
        $listRessources = [];
        $contenus = [];

        foreach ($journaux as $entry) {
            //if ($entry->isMail() || $entry->getAdministrateur() !== $username) continue;
            $adminId = $adminUser->getId();
            if ($entry->isMail()) continue; // si isMail() == true, on ignore ce journal
            $adminIdEntry = $entry->getAdministrateur() ? $entry->getAdministrateur()->getId() : null;
            if ($adminIdEntry !== $adminId) continue;

            $ressource = $this->em->getRepository(Ressource::class)->find($entry->getIdRes());
            if (!$ressource) continue;
            $mail = $ressource->getEmail();

            $sigle = $this->em->getRepository(Plage::class)->find($entry->getIdSigle());
            $label = $sigle ? $sigle->getSigle() . ' (' . $sigle->getLegende() . ')' : '';
            $text = $tabTypeAction[$entry->getActionType()] . ' - ' . $entry->getDateSigle()->format('d/m/Y') . ' : ' . $label;

            $listRessources[$mail][] = ['text' => $text, 'journal' => $entry];
            $contenus[$mail][] = $text;
        }

        $flag = 0;
        foreach ($listRessources as $email => $infos) {
            $ressource = $this->ressourceRepo->findOneBy(['email' => $email]);
            if (!$ressource) continue;

            $bodyText = "";
            foreach ($infos as $info) {
                $bodyText .= $info['text'] . "\n";
                $info['journal']->setMail(true);
            }
            $this->em->flush(); // rajout du 30 6 25

            $urlToken = $this->generateMessageLinkFromIds($ressource->getId(), $adminUser->getId());
            $bodyHtml = $this->twig->render('emails/planning.html.twig', [
                'subject' => $subject,
                'bodyContent' => $bodyText,
                'baseUrl' => $baseUrl,
                'adminMessageUrl' => $urlToken,
            ]);

            $this->sendEmail($email, $subject, $bodyHtml, $username);
            $flag++;
        }

        //$this->em->flush();

        if ($flag > 0 && $usermail) {
            $bodyHtmlAdmin = $this->twig->render('emails/admin_recap.html.twig', [
                'contenus' => $contenus
            ]);
            $this->sendEmail($usermail, 'Récapitulatif des mails envoyés', $bodyHtmlAdmin, $username);
        }

        return $flag;
    }

    private function generateMessageLinkFromIds(int $ressourceId, int $adminId): string
    {
        $ressourceToken = $this->tokenRepo->findOneBy(['ressourceId' => $ressourceId]);
        if (!$ressourceToken) {
            $ressourceToken = new RessourceToken();
            $ressourceToken->setToken(bin2hex(random_bytes(16)));
            $ressourceToken->setRessourceId($ressourceId);
            $this->em->persist($ressourceToken);
        }

        $adminToken = $this->tokenRepo->findOneBy(['adminId' => $adminId]);
        if (!$adminToken) {
            $adminToken = new RessourceToken();
            $adminToken->setToken(bin2hex(random_bytes(16)));
            $adminToken->setAdminId($adminId);
            $this->em->persist($adminToken);
        }

        $this->em->flush();

        return $this->urlGenerator->generate('kanban_message_token', [
            'ressourceToken' => $ressourceToken->getToken(),
            'adminToken' => $adminToken->getToken(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);
    }
}

/*  NOTE POUR LE SERVEUR DE PRODUCTION :

php bin/console messenger:consume async --time-limit=3600 --memory-limit=128M

Vous pouvez configurer ce consommateur pour qu'il s'exécute automatiquement via 
un gestionnaire de processus comme Supervisor ou systemd.

Créez un fichier de configuration /etc/supervisor/conf.d/messenger_consumer.conf :

[program:messenger_consumer]
command=/usr/bin/php /var/www/html/zoomerplanning/ptadmin/bin/console messenger:consume async --no-interaction
autostart=true
autorestart=true
stderr_logfile=/var/log/messenger_consumer.err.log
stdout_logfile=/var/log/messenger_consumer.out.log

Puis démarrez Supervisor :
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start messenger_consumer

*/
