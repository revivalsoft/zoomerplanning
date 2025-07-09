<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use App\Classes\JoursFeries;
use App\Classes\Plannings;

use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(('ROLE_USER'))]
class CalendrierListeController extends AbstractController
{
    #[Route('/calendrier/liste/', name: 'app_calendrier_liste', methods: ['GET'])]
    public function index(): Response
    {
        $idcal = $_GET['id'];
        $numanencours = Plannings::anencours();
        $nomzone = Plannings::TabZones($idcal); // 0 à 12 dans Paramètres
        $TabJours = JoursFeries::forYear($numanencours, $nomzone);

        $TabJoursFeries = array();
        foreach ($TabJours as $key => $value) {

            $date = $value;
            $mydate = $date->format('d-m-Y');
            $TabJoursFeries[] = $key . ' : ' . $mydate;
        }

        return $this->render('calendrier_liste/index.html.twig', [
            'tabjoursferies' => $TabJoursFeries,
            'nomzone' => $nomzone,
            'idcal' => $idcal

        ]);
    }
}
