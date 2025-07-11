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

use App\Entity\Categorie;
use App\Repository\GestionRepository;
use App\Entity\Groupe;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\ParamRepository;
use App\Repository\HierarchicRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Classes\JoursFeries;
use App\Classes\Plannings;
use App\Repository\PlageRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(('ROLE_USER'))]
class PlanningsPublicController extends AbstractController
{


    #[Route('/plannings/public/{groupe}', name: 'app_plannings_public', methods: ['GET'])]


    public function index(
        Request $request,
        HierarchicRepository $hierarchicRepository,
        PlageRepository $plageRepository,
        EntityManagerInterface $entityManager,
        GestionRepository $gestionRepository,
        Groupe $groupe,
        ParamRepository $paramRepository
    ): Response {
        $param = $paramRepository->find(1); // l'id est toujours à 1
        $choixCalendrier = $param->getCalendar();
        $nombreLignesPublic = $param->getPublic();
        $DatesEntreRessources = $param->isDates();

        $ressources = $groupe->getRessource();
        $nomgroupe = $groupe->getNom();

        $orderedRessources = $hierarchicRepository->getOrderedResources($groupe);

        $tabidres = array();
        foreach ($ressources as $value) {
            $tabidres[] = $value->getId();
        }

        $moisParDefaut = Plannings::moisencours();
        $anParDefaut  = Plannings::anencours();

        $nummoiscourant = $request->query->getInt('mois', $moisParDefaut);
        $numanencours   = $request->query->getInt('an',  $anParDefaut);

        $nombrejours = Plannings::nombrejoursmois($nummoiscourant, $numanencours);
        $tabjoursemaine = Plannings::tabJourSemaine($nummoiscourant, $numanencours);
        $nommoisencours = Plannings::NomDuMois($nummoiscourant);

        $dateFirstDay = new DateTime($numanencours . '-' . $nummoiscourant . '-' . '1');
        $dateLastDay = new DateTime($numanencours . '-' . $nummoiscourant . '-' . $nombrejours);
        $prep_planning = array();
        foreach ($tabidres as $value) {
            $prep_planning[] = $gestionRepository->findPlanning($value, $dateFirstDay, $dateLastDay);
        }

        // création tableau vierge pour les ressources du groupe
        $planningGroupe = array();
        $planningCf = array();
        $planningCt = array();
        $planningNote = array();
        $planningIdplan = array();
        for ($r = 0; $r < count($tabidres); $r++) {
            for ($l = 1; $l <= $nombreLignesPublic; $l++) { // nombre de lignes admin
                for ($j = 1; $j <= $nombrejours; $j++) {
                    $planningGroupe[$tabidres[$r]][$l][$j]  = 'OOO';
                    $planningCf[$tabidres[$r]][$l][$j]  = '#FFFFFF';
                    $planningCt[$tabidres[$r]][$l][$j]  = '#FFFFFF';
                    $planningNote[$tabidres[$r]][$l][$j]  = '';
                    $planningIdplan[$tabidres[$r]][$l][$j]  = 0;
                }
            }
        }

        foreach ($prep_planning as $value) {
            foreach ($value as $sousvalue) {
                $ressource = $sousvalue->getRessource();
                $idressource = $ressource->getId();
                $ligne = $sousvalue->getLine();
                $date = $sousvalue->getDate();
                $jour = $date->format('j');
                $note = $sousvalue->getNote();
                $plage = $sousvalue->getPlage();
                $idplage = $plage->getId();
                $sigle = $plageRepository->find($idplage);
                $nomsigle = $sigle->getSigle();
                $cf = $sigle->getCouleurfond();
                $ct = $sigle->getCouleurtexte();
                $idplan = $sousvalue->getId();

                $planningGroupe[$idressource][$ligne][$jour] = $nomsigle;
                $planningCf[$idressource][$ligne][$jour] = $cf;
                $planningCt[$idressource][$ligne][$jour] = $ct;
                $planningNote[$idressource][$ligne][$jour] = $note;
                $planningIdplan[$idressource][$ligne][$jour] = $idplan;
            }
        }

        $today = Plannings::findCurrentDay($numanencours, $nummoiscourant);

        $nomzone = Plannings::TabZones($choixCalendrier); // 0 à 12 dans Paramètres
        $Jours = JoursFeries::forYear($numanencours, $nomzone);
        $tabdatejf = array();

        foreach ($Jours as $value) {
            $date = $value;
            $result = $date->format('n-j');
            $tabdatejf[] = explode('-', $result);
        }

        $tabjourjfmoiscourant = array();
        for ($i = 0; $i < count($tabdatejf); $i++) {

            $valuemoisjf = $tabdatejf[$i][0];
            if ($valuemoisjf == $nummoiscourant) {
                $tabjourjfmoiscourant[] = $tabdatejf[$i][1];
            }
        }
        //Fin classe jours fériés

        //listing catégories de plages
        $categories = $entityManager->getRepository(Categorie::class)->findAll();
        //------------------------

        return $this->render('plannings_public/index.html.twig', [
            'moisencours' => ucfirst($nommoisencours),
            'nombrejoursmois' =>  $nombrejours,
            'an' => $numanencours,
            'tabjour' => $tabjoursemaine,
            'mois' => $nummoiscourant,
            'tabjf' => $tabjourjfmoiscourant,
            'nombrelignespublic' => $nombreLignesPublic, // de 1 à 10 dans les param
            'nombreressources' => count($ressources),  // à prendre dans le groupe sélectionné
            'numjourentreressources' => $DatesEntreRessources, // 0 ou 1 dans les param
            'idgroupe' => $groupe->getId(),
            'nomgroupe' => $nomgroupe,
            'groupe' => $groupe,
            'ressources' =>  $orderedRessources, //$ressources,
            'plannings' => $planningGroupe,
            'today' => $today,
            'categories' => $categories,
            'planningcf' => $planningCf,
            'planningct' => $planningCt,
            'planningnote' => $planningNote,
            'planningidplan' => $planningIdplan
        ]);
    }
}
