<?php
// src/Controller/DashboardController.php

//c'est le dashboard OKR !
namespace App\Controller;

use App\Repository\ObjectiveRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(('ROLE_ADMIN'))]
class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'okr_dashboard')]
    #[IsGranted(('ROLE_ADMIN'))]
    public function index(ObjectiveRepository $objectiveRepository): Response
    {
      
        $user = $this->getUser();

        $objectives = $objectiveRepository->findBy(['isClosed' => false,'user' => $user]);

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

        return $this->render('dashboard/index.html.twig', [
            'dashboard_data' => $data,
        ]);
    }
}
