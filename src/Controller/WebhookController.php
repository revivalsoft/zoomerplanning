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
