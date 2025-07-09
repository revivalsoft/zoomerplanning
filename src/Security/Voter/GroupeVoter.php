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
namespace App\Security\Voter;

use App\Entity\Ressource;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Groupe;

class GroupeVoter extends Voter
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    protected function supports(string $attribute, $subject): bool
    {
        return $attribute === 'VIEW' && $subject instanceof Groupe;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof Ressource) {
            return false;
        }

        if (!$user || !$user->getId()) {
            return false;
        }


        $groupeId = $subject->getId();
        $userId = $user->getId();

        // Utiliser une requête SQL native pour vérifier l'association
        $connection = $this->entityManager->getConnection();
        //$sql = 'SELECT 1 FROM ressource_groupe WHERE ressource_id = :userId AND groupe_id = :groupeId';

        $sql = 'SELECT 1 FROM admin_groupe WHERE ressource_id = :userId AND groupe_id = :groupeId';


        $stmt = $connection->prepare($sql);
        $result = $stmt->executeQuery(['userId' => $userId, 'groupeId' => $groupeId]);

        // Utiliser fetchAssociative() pour récupérer une ligne si elle existe
        return $result->fetchAssociative() !== false;
    }
}
