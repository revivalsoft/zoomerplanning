<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Categorie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ApiPlagesController extends AbstractController
{
    #[Route('/api/plages', name: 'api_plages', methods: ['GET'])]
    public function getPlages(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $categorieId = $request->query->get('categorieId');
        if (!$categorieId) {
            return new JsonResponse(['error' => 'ID de catégorie manquant'], Response::HTTP_BAD_REQUEST);
        }

        $categorie = $em->getRepository(Categorie::class)->find($categorieId);

        if (!$categorie) {
            return new JsonResponse(['error' => 'Catégorie non trouvée'], Response::HTTP_NOT_FOUND);
        }

        $plages = $categorie->getPlage(); // Relation Many-to-Many

        $data = [];
        foreach ($plages as $plage) {
            $data[] = [
                'id' => $plage->getId(),
                'sigle' => $plage->getSigle(),
            ];
        }

        return new JsonResponse($data);
    }
}
