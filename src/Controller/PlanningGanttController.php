<?php

namespace App\Controller;

use App\Classes\JoursFeries;
use App\Classes\Plannings;
use App\Entity\Categorie;
use App\Entity\Gtask;
use App\Entity\Project;

use App\Entity\GtaskResource;

use App\Repository\GestionRepository;
use App\Repository\ParamRepository;
use App\Repository\PlageRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;           // ← ajouté
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

// use Symfony\Component\Security\Http\Attribute\IsGranted;


class PlanningGanttController extends AbstractController
{
    #[Route('/planning/gantt/{project}', name: 'app_planning_gantt', methods: ['GET'])]
    // #[IsGranted('ROLE_ADMIN')]
    public function index(
        Request                  $request,
        Project                  $project,
        PlageRepository          $plageRepository,
        EntityManagerInterface   $entityManager,
        GestionRepository        $gestionRepository,
        ParamRepository          $paramRepository,
    ): Response {
        // --- Paramètres généraux ---
        $param                = $paramRepository->find(1);
        $choixCalendrier      = $param->getCalendar();
        $nombreLignesPublic   = $param->getPublic();
        $DatesEntreRessources = $param->isDates();

        // -------------------------------------------------
        // 1) On récupère d’abord toutes les tâches du projet
        // -------------------------------------------------
        $tasks = $entityManager->getRepository(Gtask::class)
            ->findBy(['project' => $project]);

        // 2) On cherche la date de début la plus ancienne
        $earliest = null;
        foreach ($tasks as $t) {
            $sd = $t->getStartDate();
            if (!$earliest || $sd < $earliest) {
                $earliest = $sd;
            }
        }

        // 3) On détermine mois/année par défaut sur cette date
        if ($earliest) {
            $defaultMois = (int)$earliest->format('n');
            $defaultAn   = (int)$earliest->format('Y');
        } else {
            // Pas de tâche : on reste sur le mois courant
            $defaultMois = Plannings::moisencours();
            $defaultAn   = Plannings::anencours();
        }


        $mois = $request->query->getInt('mois', $defaultMois);
        $an   = $request->query->getInt('an',   $defaultAn);

        $nommoisencours = ucfirst(Plannings::NomDuMois($mois));

        // --- Calculs de bornes sur ce mois/année ---
        $nombrejours  = Plannings::nombrejoursmois($mois, $an);
        $dateFirstDay = (new DateTime("$an-$mois-1"))->setTime(0, 0, 0);
        $dateLastDay  = (new DateTime("$an-$mois-$nombrejours"))->setTime(23, 59, 59);

        $ressources = [];
        foreach ($tasks as $task) {
            foreach ($task->getGtaskResources() as $gr) {
                $r = $gr->getRessource();
                $ressources[$r->getId()] = $r;
            }
        }
        $ressources = array_values($ressources);

        // --- IDs des ressources pour le planning public ---
        $tabidres = array_map(fn($r) => $r->getId(), $ressources);

        // --- Préparation du planning public (existants) ---
        $prep_planning = [];
        foreach ($tabidres as $rid) {
            $prep_planning[] = $gestionRepository->findPlanning($rid, $dateFirstDay, $dateLastDay);
        }

        // --- Construction de la ligne Gantt pour chaque ressource/jour ---
        $planningGantt = [];
        foreach ($tasks as $task) {

            // 1) Récupérez les dates de la tâche
            $taskStart = clone $task->getStartDate();
            $taskEnd   = clone $task->getEndDate();

            // 2) Initialisez vos bornes
            $start     = clone $dateFirstDay;
            $end       = clone $dateLastDay;

            // 3) Ajustez-les explicitement
            if ($taskStart > $dateFirstDay) {
                $start = $taskStart;
            }
            if ($taskEnd < $dateLastDay) {
                $end = $taskEnd;
            }
            while ($start <= $end) {
                $day = (int)$start->format('j');
                foreach ($task->getGtaskResources() as $gr) {
                    $rid = $gr->getRessource()->getId();
                    $planningGantt[$rid][$day][] = [
                        'id'    => $task->getId(),
                        'name'  => $task->getName(),
                        'start' => $task->getStartDate()->format('Y-m-d'),
                        'end'   => $task->getEndDate()->format('Y-m-d'),

                    ];
                }
                $start->modify('+1 day');
            }
        }

        //  ––––– Génération d’une couleur aléatoire par task ID –––––
        $taskColors = [];
        foreach ($planningGantt as $rid => $days) {
            foreach ($days as $day => $tasksOnDay) {
                foreach ($tasksOnDay as $task) {
                    $tid = $task['id'];
                    if (!isset($taskColors[$tid])) {
                        // couleur hexadécimale aléatoire
                        $taskColors[$tid] = sprintf('#%06X', mt_rand(0, 0xFFFFFF));
                    }
                }
            }
        }

        // --- Construction des tableaux vides + remplissage du planning public ---
        $planningGroupe = $planningCf = $planningCt = $planningNote = $planningIdplan = [];
        for ($i = 0, $n = count($tabidres); $i < $n; $i++) {
            $rid = $tabidres[$i];
            for ($l = 1; $l <= $nombreLignesPublic; $l++) {
                for ($d = 1; $d <= $nombrejours; $d++) {
                    $planningGroupe[$rid][$l][$d] = 'OOO';
                    $planningCf[$rid][$l][$d] = '#FFFFFF';
                    $planningCt[$rid][$l][$d] = '#FFFFFF';
                    $planningNote[$rid][$l][$d] = '';
                    $planningIdplan[$rid][$l][$d] = 0;
                }
            }
        }
        foreach ($prep_planning as $entries) {
            foreach ($entries as $e) {
                $rid    = $e->getRessource()->getId();
                $ligne  = $e->getLine();
                $jour   = $e->getDate()->format('j');
                $sigleO = $plageRepository->find($e->getPlage()->getId());
                $planningGroupe[$rid][$ligne][$jour]  = $sigleO->getSigle();
                $planningCf[$rid][$ligne][$jour]  = $sigleO->getCouleurfond();
                $planningCt[$rid][$ligne][$jour]  = $sigleO->getCouleurtexte();
                $planningNote[$rid][$ligne][$jour]  = $e->getNote();
                $planningIdplan[$rid][$ligne][$jour]  = $e->getId();
            }
        }

        // --- Jours fériés ---
        $today   = Plannings::findCurrentDay($an, $mois);

        $allHolidays = JoursFeries::forYear($an, Plannings::TabZones($choixCalendrier));
        $tabjf = [];
        foreach ($allHolidays as $date) {
            if ((int)$date->format('n') === $mois) {
                $tabjf[] = (int)$date->format('j');
            }
        }


        // --- Catégories pour la légende AJAX ---
        $categories = $entityManager->getRepository(Categorie::class)->findAll();

        // --- Render ---
        return $this->render('planning_gantt/index.html.twig', [
            'project'               => $project,
            'mois'                  => $mois,
            'an'                    => $an,
            'nombrejoursmois'       => $nombrejours,
            'tabjour'               => Plannings::tabJourSemaine($mois, $an),
            'tabjf'                 => $tabjf,
            'nombrelignespublic'    => $nombreLignesPublic,
            'ressources'            => $ressources,
            'plannings'             => $planningGroupe,
            'today'                 => $today,
            'planningcf'            => $planningCf,
            'planningct'            => $planningCt,
            'planningnote'          => $planningNote,
            'planningidplan'        => $planningIdplan,
            'planningGantt'         => $planningGantt,
            'taskColors'            => $taskColors,
            'categories'            => $categories,
            'nombreressources'      => count($ressources),
            'numjourentreressources' => $DatesEntreRessources,
            'moisencours'           => $nommoisencours
        ]);
    }
}
