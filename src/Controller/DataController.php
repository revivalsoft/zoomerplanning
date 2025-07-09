<?php

namespace App\Controller;

use App\Repository\PlageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/*
Ce controller récupère la légende du sigle cliqué pour l'afficher dans les boites modales
*/

class DataController extends AbstractController
{
    #[Route('/get-field-value-by-name/{name}', name: 'get_field_value_by_name', methods: ['GET'])]
    public function getFieldValueByName(string $name, PlageRepository $plageRepository): JsonResponse
    {
        $entity = $plageRepository->findOneBy(['sigle' => $name]);

        return new JsonResponse(['value' => $entity->getLegende()]);
    }
}
