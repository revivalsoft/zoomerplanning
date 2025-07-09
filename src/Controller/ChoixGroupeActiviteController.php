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
