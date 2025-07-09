<?php
/*
 * Zoomerplanning - Logiciel de gestion des ressources humaines
 * Copyright (C) 2025 RevivalSoft
 *
 * Ce programme est un logiciel libre ; vous pouvez le redistribuer et/ou
 * le modifier selon les termes de la Licence Publique Générale GNU publiée
 * par la Free Software Foundation Version 3.
 *
 * Ce programme est distribué dans l'espoir qu'il sera utile,
 * mais SANS AUCUNE GARANTIE ; sans même la garantie implicite de
 * COMMERCIALISATION ou D’ADÉQUATION À UN BUT PARTICULIER. Voir la
 * Licence Publique Générale GNU pour plus de détails.
 *
 * Vous devriez avoir reçu une copie de la Licence Publique Générale GNU
 * avec ce programme ; si ce n'est pas le cas, voir
 * <https://www.gnu.org/licenses/>.
 */
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
