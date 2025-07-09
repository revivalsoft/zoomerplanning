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
