<?php

namespace App\Controller;

use App\Repository\GroupeRepository;
use App\Classes\Plannings;
use App\Repository\ParamRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(('ROLE_ADMIN'))]
class CalculsHorairesController extends AbstractController
{

    #[Route('/calculs/horaires', name: 'app_calculs_horaires', methods: ['GET'])]
    public function calculerHeuresParGroupe(ParamRepository $paramRepository, GroupeRepository $groupeRepository, EntityManagerInterface $em): Response
    {
        $groupeId = $_GET['idgroupe'];; // ID du groupe
        $groupe = $groupeRepository->findGroupeData($groupeId);
        $nomGroupe = $groupe->getNom();

        $param = $paramRepository->find(1);
        $lignePublic = $param->getPublic();

        $nummoiscourant = Plannings::moisencours();
        $numanencours = Plannings::anencours();



        if (isset($_GET['moiscourantmoins'])) {
            $nummoiscourant = $_GET['moiscourantmoins'] - 1;
            $numanencours = $_GET['ancourant'];
            if ($nummoiscourant == 0) {
                $numanencours -= 1;
                $nummoiscourant = 12;
            }
        }
        if (isset($_GET['moiscourantplus'])) {
            $nummoiscourant = $_GET['moiscourantplus'] + 1;
            $numanencours = $_GET['ancourant'];
            if ($nummoiscourant == 13) {
                $numanencours += 1;
                $nummoiscourant = 1;
            }
        }

        if (isset($_GET['ancourantmoins'])) {
            $numanencours = $_GET['ancourantmoins'] - 1;
            $nummoiscourant = 1;
        }

        if (isset($_GET['ancourantplus'])) {
            $numanencours = $_GET['ancourantplus'] + 1;
            $nummoiscourant = 1;
        }

        // Obtenez la date du mois depuis la requête (format AAAA-MM)
        $mois =  (new DateTime($numanencours . '-' . $nummoiscourant))->format('Y-m');

        $nommoisencours = Plannings::NomDuMois($nummoiscourant);

        // Convertissez en DateTime pour le premier et dernier jour du mois
        $dateDebut = new \DateTime("first day of $mois");
        $dateFin = new \DateTime("last day of $mois");

        // Récupérez les ressources du groupe sélectionné
        $ressources = $em->getConnection()->executeQuery("
            SELECT r.id, r.nom 
            FROM ressource r
            JOIN ressource_groupe rg ON rg.ressource_id = r.id
            WHERE rg.groupe_id = :groupe_id
        ", ['groupe_id' => $groupeId])->fetchAllAssociative();

        // Initialisez le tableau des résultats
        $resultats = [];

        // Pour chaque jour du mois, pour chaque ressource
        foreach ($ressources as $ressource) {
            $heuresTotal = 0;
            $minutesTotal = 0;
            $journeeData = [];

            $dateCourante = clone $dateDebut;
            while ($dateCourante <= $dateFin) {
                // Obtenez les plages valides pour la ressource et la date courante
                $resultatsJour = $em->getConnection()->executeQuery("
                    SELECT p.heure, p.minute,g.line
                    FROM gestion g
                    JOIN plage p ON p.id = g.plage_id
                    WHERE g.ressource_id = :ressource_id
                      AND g.date = :date
                      AND g.line <= :lignepublic 
                      AND p.absence = false
                ", [
                    'ressource_id' => $ressource['id'],
                    'date' => $dateCourante->format('Y-m-d'),
                    //'fusion' => $fusion,
                    'lignepublic' => $lignePublic,
                ])->fetchAllAssociative();

                // Calculez le total des heures et des minutes pour cette journée
                $heuresJour = 0;
                $minutesJour = 0;
                foreach ($resultatsJour as $plage) {
                    $heuresJour += (int)$plage['heure'];
                    $minutesJour += (int)$plage['minute'];
                }

                // Ajoutez les minutes supplémentaires aux heures
                $heuresJour += intdiv($minutesJour, 60);
                $minutesJour %= 60;
                $heuresJour = sprintf('%02d', $heuresJour ?? 0);
                $minutesJour = sprintf('%02d', $minutesJour ?? 0);

                $heuresTotal += $heuresJour;
                $minutesTotal += $minutesJour;


                //$journeeData[$dateCourante->format('Y-m-d')] = [
                $journeeData[$dateCourante->format('d')] = [
                    'heures' => $heuresJour,
                    'minutes' => $minutesJour,
                ];

                $dateCourante->modify('+1 day');
            }

            // Ajustement final des minutes totales
            $heuresTotal += intdiv($minutesTotal, 60);
            $minutesTotal %= 60;

            $heuresTotal = sprintf('%02d', $heuresTotal ?? 0);
            $minutesTotal = sprintf('%02d', $minutesTotal ?? 0);

            // Ajouter les données calculées pour la ressource au résultat final
            $resultats[] = [
                'ressource' => $ressource['nom'],
                'jours' => $journeeData,
                'total_heures' => $heuresTotal,
                'total_minutes' => $minutesTotal,

            ];
        }

        // Rendu de la vue Twig
        return $this->render('calculs_horaires/index.html.twig', [
            //'mois' => $mois,
            'resultats' => $resultats,
            'nommois' => ucfirst($nommoisencours),
            'annee' => $numanencours,
            'nomgroupe' => $nomGroupe,
            'an' => $numanencours,
            'mois' => $nummoiscourant,
            'idgroupe' => $groupeId,

        ]);
    }
}
