<?php

namespace App\Controller;

use App\Repository\MailerEventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(('ROLE_USER'))]
class WebhookController extends AbstractController
{
    #[Route('/webhook', name: 'webhook_index')]
    public function index(MailerEventRepository $repository, Request $request): Response
    {
        // Récupère la date depuis les paramètres de requête ou utilise la date actuelle
        $dateParam = $request->query->get('date', (new \DateTime())->format('Y-m-d'));
        $date = \DateTime::createFromFormat('Y-m-d', $dateParam);

        // Récupère les événements pour la date spécifiée
        $events = $repository->findByDate($date);

        // Passer les données à Twig
        return $this->render('webhook/index.html.twig', [
            'events' => $events,
            'currentDate' => $date,
        ]);
    }
}
