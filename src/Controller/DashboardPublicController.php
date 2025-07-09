<?php
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
