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
