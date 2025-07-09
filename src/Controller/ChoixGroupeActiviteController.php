<?php

namespace App\Controller;

use App\Entity\Groupe;
use App\Repository\GroupeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ChoixGroupeActiviteController extends AbstractController
{

    #[Route('/choix/groupe/activite', name: 'app_choix_groupe_activite', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function index(GroupeRepository $groupeRepository): Response
    {
        $allGroupes = $groupeRepository->findAll();

        // Filtrer les groupes selon les permissions
        $accessibleGroupes = array_filter($allGroupes, function (Groupe $groupe) {
            return $this->isGranted('VIEW', $groupe);
        });

        return $this->render('choix_groupe_activite/index.html.twig', [
            'groupes' => $accessibleGroupes,
        ]);
    }
}
