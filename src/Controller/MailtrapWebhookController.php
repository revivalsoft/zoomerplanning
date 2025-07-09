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
namespace App\Controller;


use App\Entity\MailerEvent;
use App\Entity\Ressource;
use App\Repository\JournalRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class MailtrapWebhookController extends AbstractController
{

    private EntityManagerInterface $entityManager;
    private $journalRepository;

    public function __construct(EntityManagerInterface $entityManager, JournalRepository $journalRepository)
    {
        $this->entityManager = $entityManager;
        $this->journalRepository = $journalRepository;
    }

    #[Route('/webhook/mailtrap', name: 'webhook_mailtrap', methods: ['POST'])]
    public function handle(Request $request): JsonResponse
    {
        $payload = $request->getContent();
        $data = json_decode($payload, true);


        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->json(['error' => 'Invalid JSON'], 400);
        }

        // Vérifier si 'events' existe et est un tableau
        if (!isset($data['events']) || !is_array($data['events'])) {
            return $this->json(['error' => 'Invalid payload structure'], 400);
        }


        foreach ($data['events'] as $eventData) {

            $email = $eventData['email'];

            // pour filtrer les emails par sous-domaine, sinon tous les sous-domaines reçoivent
            //tous les mails !!!
            $ressource = $this->entityManager->getRepository(Ressource::class)->findOneBy(['email' => $email]);
            if ($ressource) {
                $event = new MailerEvent();
                $event->setEvent($eventData['event']);
                $event->setEmail($eventData['email']);
                $event->setAdmin($eventData['category']);
                $event->setCreatedAt(new \DateTimeImmutable());

                //Remise à false du champ booléen mail dans la table journal 
                // si event = reject
                $this->journalRepository->updateMailField($eventData);

                $this->entityManager->persist($event);
            }
        }

        $this->entityManager->flush();

        return new JsonResponse(['status' => 'success']);
    }
}
