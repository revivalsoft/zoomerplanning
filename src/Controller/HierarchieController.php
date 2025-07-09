<?php

namespace App\Controller;

use App\Entity\Hierarchic;
use App\Repository\GroupeRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\HierarchicRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class HierarchieController extends AbstractController
{
    #[Route('/hierarchie', name: 'app_hierarchie', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_SUPER_ADMIN')]

    public function index(HierarchicRepository $hierarchicRepository, GroupeRepository $groupeRepository): Response
    {
        $idgroupe = $_GET['idgroupe'];
        $groupe = $groupeRepository->findGroupeData($idgroupe);

        $nomgroupe = $groupe->getNom();

        $orderedRessources = $hierarchicRepository->getOrderedResources($groupe);

        // Passer les données triées au template Twig
        return $this->render('hierarchic/index.html.twig', [
            'ressources' => $orderedRessources,
            'nomgroupe' => $nomgroupe,
            'idgroupe' => $idgroupe
        ]);
    }

    #[Route('/hierarchie/saveorder', name: 'app_hierarchie_saveorder', methods: ['POST'])]
    #[IsGranted('ROLE_SUPER_ADMIN')]
    public function saveOrder(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {

        // Récupérer les données JSON de la requête
        $data = json_decode($request->getContent(), true);

        if (!isset($data['groupe_id']) || !isset($data['order']) || !is_array($data['order'])) {
            return new JsonResponse(['error' => 'Données invalides'], 400);
        }

        $idGroupe = $data['groupe_id'];
        $order = $data['order'];

        // Vérifier si une entrée existe déjà pour ce groupe
        $hierarchic = $entityManager->getRepository(Hierarchic::class)->findOneBy(['groupe_id' => $idGroupe]);

        if (!$hierarchic) {
            // Si aucune entrée n'existe, créer une nouvelle
            $hierarchic = new Hierarchic();
            $hierarchic->setGroupeId($idGroupe);
        }

        // Mettre à jour le champ 'order'
        $hierarchic->setPosition($order);

        // Persister et enregistrer
        $entityManager->persist($hierarchic);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Ordre enregistré avec succès']);
    }
}
