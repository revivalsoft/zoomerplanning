<?php

namespace App\Controller;

use App\Entity\Objective;
use App\Form\ObjectiveType;
use App\Repository\ObjectiveRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{Request, Response};
use Symfony\Component\Routing\Annotation\Route;


use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(('ROLE_USER'))]

#[Route('/objective/public')]
class ObjectivePublicController extends AbstractController
{


    #[Route('/{id}', name: 'objective_public_show', methods: ['GET'])]
    public function show(Objective $objective): Response
    {
        // if ($objective->getUser() !== $this->getUser()) {
        //     throw $this->createAccessDeniedException();
        // }

        return $this->render('objective_public/show.html.twig', [
            'objective' => $objective,
        ]);
    }
}
