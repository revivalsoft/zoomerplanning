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

#[Route('/journal_archives')]
#[IsGranted(('ROLE_ADMIN'))]
final class JournalArchivesController extends AbstractController
{
    #[Route(name: 'app_journal_archives_index', methods: ['GET'])]
    public function index(PlageRepository $plageRepository, RessourceRepository $ressourceRepository, JournalRepository $journalRepository): Response
    {

        $tabTypeAction[1] = "Création";
        $tabTypeAction[2] = "Note";
        $tabTypeAction[3] = "Suppression";

        $Ressources  = $ressourceRepository->findAll();
        $tabidres = array();
        foreach ($Ressources as $value) {
            $tabidres[$value->getId()] = $value->getNom();
        }

        $Plages = $plageRepository->findAll();
        $tabidplages = array();
        $tabcouleurfond = array();
        $tabcouleurtexte = array();
        foreach ($Plages as $value) {
            $tabidplages[$value->getId()] = $value->getSigle();
            $tabcouleurfond[$value->getId()] = $value->getCouleurfond();
            $tabcouleurtexte[$value->getId()] = $value->getCouleurtexte();
        }

        $admin = $this->getUser();
        if (!$admin instanceof Ressource) {
            throw $this->createAccessDeniedException('Accès refusé');
        }


        return $this->render('journal_archives/index.html.twig', [
            'journals' => $journalRepository->findAllByMatchingRessourcePlage($admin->getId()),
            'tabtypeaction' => $tabTypeAction,
            'tabressources' => $tabidres,
            'tabplages' => $tabidplages,
            'tabcf' => $tabcouleurfond,
            'tabct' => $tabcouleurtexte,
        ]);
    }

    #[Route('/{id}', name: 'app_journal_archives_show', methods: ['GET'])]
    public function show(Security $security, PlageRepository $plageRepository, RessourceRepository $ressourceRepository, Journal $journal): Response
    {
        $tabTypeAction[1] = "Création";
        $tabTypeAction[2] = "Note";
        $tabTypeAction[3] = "Suppression";

        $idRessource = $journal->getIdRes();
        $Ressource = $ressourceRepository->find($idRessource);
        $nomRessource = $Ressource->getNom();

        $idPlage = $journal->getIdSigle();
        $Plage = $plageRepository->find($idPlage);
        $sigle = $Plage->getSigle();
        $legende = $Plage->getLegende();
        $cf = $Plage->getCouleurfond();
        $ct = $Plage->getCouleurtexte();

        $user = $security->getUser();
        $admin = ($user instanceof Ressource) ? $user->getNom() : null;

        return $this->render('journal_archives/show.html.twig', [
            'journal' => $journal,
            'tabtypeaction' => $tabTypeAction,
            'nomressource' => $nomRessource,
            'sigle' => $sigle,
            'legende' => $legende,
            'cf' => $cf,
            'ct' => $ct,
            'admin' => $admin
        ]);
    }
}
