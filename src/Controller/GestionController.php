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

use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Gestion;
use App\Entity\Plage;
use App\Entity\Ressource;
use App\Entity\Journal;
use App\Repository\CategorieRepository;
use App\Repository\GestionRepository;
use App\Repository\ParamRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use DateTime;
use function Symfony\Component\Clock\now;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/gestion')]
#[IsGranted('ROLE_ADMIN')]
class GestionController extends AbstractController
{
    private function getUsername(Security $security): ?string
    {
        $user = $security->getUser();
        return $user instanceof UserInterface ? $user->getUserIdentifier() : null;
    }

    #[Route('/new', name: 'app_gestion_new', methods: ['POST'])]
    public function new(ParamRepository $paramRepository, Security $security, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $idplage = $data['idplage'] ?? null;
        $jour = $data['jour'] ?? null;
        $mois = $data['mois'] ?? null;
        $an = $data['an'] ?? null;
        $ligne = $data['ligne'] ?? null;
        $idressource = $data['idressource'] ?? null;

        $dateencours = $an . '-' . $mois . '-' . $jour;
        $dateSql = DateTime::createFromFormat('Y-m-d', $dateencours);

        if (!$dateSql) {
            return new JsonResponse(['success' => false, 'message' => 'Date invalide'], 400);
        }

        $id_ressource = $entityManager->getRepository(Ressource::class)->find($idressource);
        $id_plage = $entityManager->getRepository(Plage::class)->find($idplage);

        if (!$id_ressource || !$id_plage) {
            return new JsonResponse(['success' => false, 'message' => 'Ressource ou plage introuvable'], 404);
        }

        $gestion = new Gestion();
        $gestion->setRessource($id_ressource);
        $gestion->setPlage($id_plage);
        $gestion->setDate($dateSql);
        $gestion->setLine($ligne);

        $username = $this->getUsername($security);
        $param = $paramRepository->find(1);
        $lignePublic = $param->getPublic();


        // Journal
        $journalLog = new Journal();
        $journalLog->setActionType(1); // new
        $journalLog->setActionDate(now());
        $journalLog->setIdRes($idressource);
        $journalLog->setIdSigle($idplage);
        $journalLog->setLigne($ligne);
        $journalLog->setDateSigle($dateSql);
        $journalLog->setAdministrateur($security->getUser()); //($username);
        //$journalLog->setMail(false);

        $entityManager->persist($gestion);
        if ($ligne <= $lignePublic) {
            $entityManager->persist($journalLog);
        }
        $entityManager->flush();

        return new JsonResponse(['success' => true, 'id' => $gestion->getId()]);
    }

    #[Route('/{id}', name: 'app_gestion_delete', methods: ['POST'])]
    public function delete(ParamRepository $paramRepository, Security $security, GestionRepository $gestionRepository, Request $request, Gestion $gestion, EntityManagerInterface $entityManager): JsonResponse
    {
        if (!$request->isXmlHttpRequest()) {
            return new JsonResponse(['status' => 'error', 'message' => 'Requête non autorisée'], 403);
        }

        $result = $gestionRepository->findOneElementPlageGestion($gestion->getId());

        $tabRes = $result->getRessource();
        $tabPlage = $result->getPlage();

        $idRessource = $tabRes->getId();
        $ligne = $result->getLine();
        $note = $result->getNote();
        $idplage = $tabPlage->getId();
        $dateInsertionPlage = $result->getDate();

        $username = $this->getUsername($security);
        $param = $paramRepository->find(1);
        $lignePublic = $param->getPublic();



        $journalLog = new Journal();
        $journalLog->setActionType(3); // delete
        $journalLog->setActionDate(now());
        $journalLog->setIdRes($idRessource);
        $journalLog->setIdSigle($idplage);
        $journalLog->setNote($note);
        $journalLog->setLigne($ligne);
        $journalLog->setDateSigle($dateInsertionPlage);
        $journalLog->setAdministrateur($security->getUser()); //($username);
        //$journalLog->setMail(false);

        if ($ligne <= $lignePublic) {
            $entityManager->persist($journalLog);
        }

        $entityManager->remove($gestion);
        $entityManager->flush();

        return new JsonResponse(['status' => 'Entity deleted']);
    }

    #[Route('/data-by-selection', name: 'app_gestion_data_by_selection', methods: ['GET'])]
    public function getDataBySelection(Request $request, CategorieRepository $categorieRepository): JsonResponse
    {
        $selectionId = $request->query->get('id');
        $categorie = $categorieRepository->findCategorieData($selectionId);
        $plages = $categorie ? $categorie->getPlage() : [];

        $tabplages = [];
        foreach ($plages as $value) {
            $tabplages[] = [
                'id' => $value->getId(),
                'sigle' => $value->getSigle(),
                'legende' => $value->getLegende(),
                'ct' => $value->getCouleurtexte(),
                'cf' => $value->getCouleurfond(),
            ];
        }

        return new JsonResponse($tabplages);
    }

    #[Route('/update/{id}', name: 'app_gestion_update', methods: ['POST'])]
    public function updateGestion(ParamRepository $paramRepository, Security $security, GestionRepository $gestionRepository, EntityManagerInterface $em, Request $request, int $id): JsonResponse
    {
        $tabgestion = $gestionRepository->find($id);

        if (!$tabgestion) {
            return new JsonResponse(['status' => 'error', 'message' => 'Entité non trouvée'], 404);
        }

        $data = json_decode($request->getContent(), true);
        $newValue = isset($data['note']) ? trim($data['note']) : null;

        if ($newValue === null) {
            return new JsonResponse(['status' => 'error', 'message' => 'Données invalides'], 400);
        }

        $tabgestion->setNote($newValue);

        $result = $gestionRepository->findOneElementPlageGestion($id);

        $dateInsertionPlage = $result->getDate();
        $tabRes = $result->getRessource();
        $tabPlage = $result->getPlage();

        $idRessource = $tabRes->getId();
        $idplage = $tabPlage->getId();

        $note = $newValue;
        $ligne = $tabgestion->getLine();

        $param = $paramRepository->find(1);
        $lignePublic = $param->getPublic();


        $journalLog = new Journal();
        $journalLog->setActionType(2); // update
        $journalLog->setActionDate(now());
        $journalLog->setIdRes($idRessource);
        $journalLog->setIdSigle($idplage);
        $journalLog->setNote($note);
        $journalLog->setLigne($ligne);
        $journalLog->setDateSigle($dateInsertionPlage);
        $journalLog->setAdministrateur($security->getUser()); //($username);
        //$journalLog->setMail(false);

        $em->persist($tabgestion);

        if ($ligne <= $lignePublic) {
            $em->persist($journalLog);
        }

        $em->flush();

        return new JsonResponse(['status' => 'success', 'message' => 'Mise à jour réussie']);
    }
}
