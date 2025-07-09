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
//c'est le dashboard public OKR !
namespace App\Controller;

use App\Repository\ObjectiveRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(('ROLE_USER'))]
class DashboardPublicController extends AbstractController
{
    #[Route('/dashboard/public', name: 'app_dashboard_public')]

    public function index(ObjectiveRepository $objectiveRepository): Response
    {

        $objectives = $objectiveRepository->findBy(['isClosed' => false, 'isPublic' => true]);

        // Calculer progression moyenne de chaque objectif
        $data = [];

        foreach ($objectives as $objective) {
            $keyResults = $objective->getKeyResults();
            $total = count($keyResults);
            $sum = 0;

            foreach ($keyResults as $kr) {
                $sum += $kr->getProgress();
            }

            $average = $total > 0 ? round($sum / $total) : 0;

            $data[] = [
                'objective' => $objective,
                'average_progress' => $average,
            ];
        }

        return $this->render('dashboard_public/index.html.twig', [
            'dashboard_data' => $data,
        ]);
    }
}
