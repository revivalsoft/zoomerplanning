<?php
// src/Controller/PlageController.php
namespace App\Controller;

use App\Repository\PlageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(('ROLE_USER'))]
class PlageController extends AbstractController
{
    #[Route('/plages', name: 'plage_index')]
    // #[IsGranted('ROLE_SUPER_ADMIN')]
    public function index(PlageRepository $plageRepository): Response
    {
        $plages = $plageRepository->findWithAtLeastOneVisibleCategory();

        return $this->render('plage/index.html.twig', [
            'plages' => $plages,
        ]);
    }
}
