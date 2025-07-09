<?php

namespace App\Controller;

use App\Entity\Groupe;
use App\Repository\GroupeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

// #[Route('/choix/groupe', name: 'app_choix_groupe', methods: ['GET'])]
// #[IsGranted('ROLE_ADMIN')]

class ChoixGroupeController extends AbstractController
{


    #[Route('/choix/groupe', name: 'app_choix_groupe', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function index(GroupeRepository $groupeRepository): Response
    {
        $groupes = $groupeRepository->findAll();

        // Filtrer les groupes avec le Voter
        $accessibles = array_filter($groupes, fn(Groupe $groupe) => $this->isGranted('VIEW', $groupe));

        return $this->render('choix_groupe/index.html.twig', [
            'groupes' => $accessibles,
        ]);
    }
}
