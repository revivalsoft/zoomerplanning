<?php

namespace App\Repository;

use App\Entity\Hierarchic;
use App\Entity\Groupe;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class HierarchicRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Hierarchic::class);
    }

    public function getOrderedResources(Groupe $groupe): array
    {
        // Étape 1 : Récupérer toutes les ressources associées au groupe
        $ressources = $groupe->getRessource();

        // Étape 2 : Récupérer les données de position existantes pour ce groupe
        $hierarchic = $this->findOneBy(['groupe_id' => $groupe->getId()]);
        $positions = $hierarchic ? $hierarchic->getPosition() : [];

        // Étape 3 : Organiser les ressources
        $orderedRessources = [];
        $unpositionedRessources = [];

        foreach ($ressources as $ressource) {
            $ressourceId = $ressource->getId();

            if (in_array($ressourceId, $positions)) {
                $orderedRessources[array_search($ressourceId, $positions)] = $ressource;
            } else {
                $unpositionedRessources[] = $ressource;
            }
        }

        // Trier selon les positions et ajouter les non positionnées
        ksort($orderedRessources);
        return array_merge($orderedRessources, $unpositionedRessources);
    }
}
