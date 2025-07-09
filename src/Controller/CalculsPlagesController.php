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
use App\Entity\Ressource;
use App\Entity\Categorie;
use App\Repository\GroupeRepository;
use App\Repository\ParamRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(('ROLE_ADMIN'))]
class CalculsPlagesController extends AbstractController
{
    #[Route('/calculs/plages', name: 'app_calculs_plages')]
    public function index(GroupeRepository $groupeRepository, ParamRepository $paramRepository, Request $request, EntityManagerInterface $em): Response
    {

        $param = $paramRepository->find(1);
        //  $fusion = $param->getFusion();
        //  if ($fusion == null) $fusion = 1;

        $param = $paramRepository->find(1);
        $lignePublic = $param->getPublic();

        // Récupérer tous les groupes
        $allGroupes = $groupeRepository->findAll();

        // Filtrer les groupes selon les permissions
        $accessibleGroupes = array_filter($allGroupes, function (Groupe $groupe) {
            return $this->isGranted('VIEW', $groupe);
        });

        // Formulaire pour sélectionner le groupe, la catégorie et la fourchette de dates
        $form = $this->createFormBuilder()
            ->add('groupe', EntityType::class, [
                'class' => Groupe::class,
                'choices' => $accessibleGroupes,  // Limiter les choix avec les groupes accessibles
                'choice_label' => 'nom',
                'label' => 'Choisissez un groupe de ressources',
            ])
            ->add('categorie', EntityType::class, [
                'class' => Categorie::class,
                'choice_label' => 'nom',
                'label' => 'Choisissez une catégorie de plages',
            ])
            ->add('dateDebut', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date de début',
            ])
            ->add('dateFin', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date de fin',
            ])
            // ->add('submit', SubmitType::class, ['label' => 'Filtrer'])
            ->getForm();

        $form->handleRequest($request);

        $sigles = [];
        $totauxParRessource = [];
        $formSubmitted = false;

        if ($form->isSubmitted() && $form->isValid()) {

            $formSubmitted = true;

            $groupeId = $form->get('groupe')->getData()->getId();

            $categorieId = $form->get('categorie')->getData()->getId();
            $startDate = $form->get('dateDebut')->getData();
            $endDate = $form->get('dateFin')->getData();

            // Récupérer les ressources du groupe sélectionné
            $ressources = $em->getRepository(Ressource::class)
                ->findByGroupe($groupeId);

            // Récupérer les sigles uniques de la catégorie sélectionnée
            $sigles = $em->getConnection()->fetchAllAssociative("
            SELECT p.sigle, p.couleurtexte, p.couleurfond
            FROM plage p
            JOIN plage_categorie pc ON pc.plage_id = p.id
            WHERE pc.categorie_id = :categorieId
            ", ['categorieId' => $categorieId]);

            // Préparer les données pour compter les sigles par ressource
            $totauxParRessource = [];
            foreach ($ressources as $ressource) {
                $totauxParRessource[$ressource->getId()]['nom'] = $ressource->getNom();

                foreach ($sigles as $sigle) {
                    $totauxParRessource[$ressource->getId()]['sigles'][$sigle['sigle']] = 0;

                    $compte = $em->getConnection()->fetchOne("
                SELECT COUNT(g.plage_id)
                FROM gestion g
                JOIN plage p ON p.id = g.plage_id
                WHERE g.ressource_id = :ressourceId
                AND g.date BETWEEN :dateDebut AND :dateFin
                AND g.line <= :lignepublic
                AND p.sigle = :sigle
                ", [
                        'ressourceId' => $ressource->getId(),
                        'dateDebut' => $startDate->format('Y-m-d'),
                        'dateFin' => $endDate->format('Y-m-d'),
                        'sigle' => $sigle['sigle'],
                        //'fusion' => $fusion,
                        'lignepublic' => $lignePublic,
                    ]);

                    // Stocker le nombre d'occurrences du sigle pour cette ressource
                    $totauxParRessource[$ressource->getId()]['sigles'][$sigle['sigle']] = $compte;
                }
            }
        }
        // Passer les données au template
        return $this->render('calculs_plages/index.html.twig', [
            'form' => $form->createView(),
            'totauxParRessource' => $totauxParRessource,
            'sigles' => $sigles,
            'formSubmitted' => $formSubmitted,
        ]);
    }
}
