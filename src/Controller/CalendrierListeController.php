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
