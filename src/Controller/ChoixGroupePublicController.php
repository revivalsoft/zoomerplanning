<?php

namespace App\Controller;

use App\Repository\GroupeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


//use Symfony\Component\Security\Http\Attribute\IsGranted;

//#[IsGranted(('ROLE_USER'))]
class ChoixGroupePublicController extends AbstractController
{
    #[Route('/choix/groupe/public', name: 'app_choix_groupe_public')]

    public function index(GroupeRepository $groupeRepository): Response
    {


        return $this->render('choix_groupe_public/index.html.twig', [
            'groupes' => $groupeRepository->findVisibleGroups()
        ]);
    }
}
