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

use App\Entity\Journal;
use App\Entity\Ressource;
use App\Repository\JournalRepository;
use App\Repository\PlageRepository;
use App\Repository\RessourceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/journal')]
final class JournalController extends AbstractController
{
    private const TYPES_ACTION = [
        1 => "Création",
        2 => "Note",
        3 => "Suppression",
    ];

    #[Route(name: 'app_journal_index', methods: ['GET'])]
    public function index(
        PlageRepository $plageRepository,
        RessourceRepository $ressourceRepository,
        JournalRepository $journalRepository
    ): Response {
        $ressources = $ressourceRepository->findAll();
        $tabidres = [];
        foreach ($ressources as $value) {
            $tabidres[$value->getId()] = $value->getNom();
        }

        $plages = $plageRepository->findAll();
        $tabidplages = [];
        $tabcouleurfond = [];
        $tabcouleurtexte = [];
        foreach ($plages as $value) {
            $tabidplages[$value->getId()] = $value->getSigle();
            $tabcouleurfond[$value->getId()] = $value->getCouleurfond();
            $tabcouleurtexte[$value->getId()] = $value->getCouleurtexte();
        }


        $admin = $this->getUser();
        if (!$admin instanceof Ressource) {
            throw $this->createAccessDeniedException('Accès refusé');
        }

        return $this->render('journal/index.html.twig', [
            'journals' => $journalRepository->findAllByMatchingRessourcePlageDate($admin->getId()),
            'tabtypeaction' => self::TYPES_ACTION,
            'tabressources' => $tabidres,
            'tabplages' => $tabidplages,
            'tabcf' => $tabcouleurfond,
            'tabct' => $tabcouleurtexte,
        ]);
    }

    #[Route('/{id}', name: 'app_journal_show', methods: ['GET'])]
    public function show(PlageRepository $plageRepository, RessourceRepository $ressourceRepository, Journal $journal): Response
    {
        $ressource = $ressourceRepository->find($journal->getIdRes());
        if (!$ressource) {
            throw $this->createNotFoundException('Ressource non trouvée.');
        }

        $plage = $plageRepository->find($journal->getIdSigle());
        if (!$plage) {
            throw $this->createNotFoundException('Plage non trouvée.');
        }

        $administrateur = $journal->getAdministrateur(); // la relation ManyToOne

        return $this->render('journal/show.html.twig', [
            'journal' => $journal,
            'tabtypeaction' => self::TYPES_ACTION,
            'nomressource' => $ressource->getNom(),
            'sigle' => $plage->getSigle(),
            'legende' => $plage->getLegende(),
            'cf' => $plage->getCouleurfond(),
            'ct' => $plage->getCouleurtexte(),
            'admin' => $administrateur ? $administrateur->getNom() : 'Inconnu',
        ]);
    }
}
