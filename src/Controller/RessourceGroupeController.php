<?php

namespace App\Controller;

use App\Repository\GroupeRepository;
use App\Repository\RessourceRepository;
use App\Entity\Groupe;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/ressource-groupe', name: 'ressource_groupe')]
#[IsGranted(('ROLE_SUPER_ADMIN'))]
class RessourceGroupeController extends AbstractController
{
    #[Route('/', name: '_index', methods: ['GET'])]
    public function index(GroupeRepository $groupeRepository): Response
    {
        $groupes = $groupeRepository->findAll(); // y compris les groupes non affectés au supadmin

        return $this->render('ressource_groupe/index.html.twig', [
            'groupes' => $groupes,
        ]);
    }

    #[Route('/load-ressources/{groupe}', name: '_load_ressources', methods: ['GET'])]
    public function loadRessources(Groupe $groupe): JsonResponse
    {
        $ressources = $groupe->getRessource();

        return $this->json([
            'ressources' => array_map(fn($r) => ['id' => $r->getId(), 'nom' => $r->getNom()], $ressources->toArray()),

        ]);
    }

    #[Route('/assign', name: '_assign', methods: ['POST'])]
    public function assign(
        Request $request,
        GroupeRepository $groupeRepository,
        RessourceRepository $ressourceRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        $groupe = $groupeRepository->find($data['groupe_id']);
        $ressource = $ressourceRepository->find($data['ressource_id']);

        if ($groupe && $ressource && !$groupe->getRessource()->contains($ressource)) {
            $groupe->addRessource($ressource);
            $ressource->addGroupe($groupe);
            $entityManager->flush();
        }

        return $this->json(['success' => true]);
    }
}
