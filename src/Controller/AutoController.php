<?php

namespace App\Controller;

use App\Classes\JoursFeries;
use App\Entity\Categorie;
use App\Entity\Groupe;
use App\Entity\Gestion;
use App\Entity\Param;
use App\Entity\Plage;
use App\Repository\GroupeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class AutoController extends AbstractController
{
    #[Route('/auto/enregistrer', name: 'app_auto_enregistrer')]
    public function index(GroupeRepository $groupeRepository, Request $request, EntityManagerInterface $em): Response
    {
        $categories = $em->getRepository(Categorie::class)->findAll();
        $allgroupes = $em->getRepository(Groupe::class)->findAll();
        $groupes = array_filter($allgroupes, fn(Groupe $groupe) => $this->isGranted('VIEW', $groupe));

        $form = $this->createFormBuilder(null, ['csrf_protection' => false])
            ->add('groupe', ChoiceType::class, [
                'choices' => $groupes,
                'choice_label' => fn(Groupe $groupe) => $groupe->getNom(),
                'choice_value' => fn(?Groupe $groupe) => $groupe?->getId() ?? '',
                'placeholder' => 'Sélectionnez un groupe',
            ])
            ->add('categorie', ChoiceType::class, [
                'choices' => $categories,
                'choice_label' => fn(Categorie $categorie) => $categorie->getNom(),
                'choice_value' => fn(?Categorie $categorie) => $categorie?->getId() ?? '',
                'placeholder' => 'Sélectionnez une catégorie',
            ])
            ->add('plage', ChoiceType::class, [
                'choices' => [], // ici tu peux charger dynamiquement dans le twig si besoin
                'choice_label' => fn($plage) => $plage->getSigle(),
                'choice_value' => fn($plage) => $plage?->getId() ?? null,
                'placeholder' => 'Sélectionnez une plage',
            ])
            ->add('line', ChoiceType::class, [
                'label' => 'Ligne',
                'choices' => array_combine(range(1, 10), range(1, 10)),
            ])
            ->add('date_start', DateType::class, [
                'label' => 'Date de début',
                'widget' => 'single_text',
            ])
            ->add('date_end', DateType::class, [
                'label' => 'Date de fin',
                'widget' => 'single_text',
            ])
            ->add('jours', ChoiceType::class, [
                'choices' => [
                    'Lundi' => 'Monday',
                    'Mardi' => 'Tuesday',
                    'Mercredi' => 'Wednesday',
                    'Jeudi' => 'Thursday',
                    'Vendredi' => 'Friday',
                    'Samedi' => 'Saturday',
                    'Dimanche' => 'Sunday',
                    'Jours fériés' => 'JF',
                ],
                'expanded' => true,
                'multiple' => true,
                'label' => 'Jours à inclure',
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Enregistrer',
                'attr' => ['class' => 'btn btn-primary'],
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $data = $form->getData();

            // Récupérer l'id de la plage à partir du POST (évite "undefined key" si pas dans $data)
            $post = $request->request->all();
            $plageId = $post['form']['plage'] ?? null;
            if (!$plageId) {
                $form->get('plage')->addError(new FormError('Veuillez sélectionner une plage.'));
                return $this->render('auto/index.html.twig', ['form' => $form->createView()]);
            }
            $plage = $em->getRepository(Plage::class)->find($plageId);
            if (!$plage) {
                $form->get('plage')->addError(new FormError('Plage invalide.'));
                return $this->render('auto/index.html.twig', ['form' => $form->createView()]);
            }

            if ($data['date_start'] > $data['date_end']) {
                $form->get('date_start')->addError(new FormError('La date de début doit précéder la date de fin.'));
                return $this->render('auto/index.html.twig', ['form' => $form->createView()]);
            }

            /** @var Param|null $param */
            $param = $em->getRepository(Param::class)->findOneBy([]);
            $zoneIndex = $param?->getCalendar() ?? 0;
            $zone = JoursFeries::ZONES[$zoneIndex] ?? 'Métropole';

            $joursFeries = array_merge(
                JoursFeries::forYear((int)$data['date_start']->format('Y'), $zone),
                JoursFeries::forYear((int)$data['date_end']->format('Y'), $zone)
            );
            $datesFeries = array_map(fn(\DateTime $d) => $d->format('Y-m-d'), $joursFeries);

            $period = new \DatePeriod(
                $data['date_start'],
                new \DateInterval('P1D'),
                (clone $data['date_end'])->modify('+1 day')
            );

            $jours = $data['jours'] ?? [];
            if (!is_array($jours)) {
                $jours = [$jours];
            }

            $inclureJF = in_array('JF', $jours, true);
            $jours = array_filter($jours, fn($j) => $j !== 'JF');

            $groupe = $groupeRepository->findGroupeData($data['groupe']->getId());
            $ressources = $groupe->getRessource();

            $connection = $em->getConnection();

            foreach ($period as $date) {
                $dateStr = $date->format('Y-m-d');
                $jourSemaine = $date->format('l');
                $estFerie = in_array($dateStr, $datesFeries);
                $estJourDemande = in_array($jourSemaine, $jours);

                if (($estFerie && $inclureJF) || (!$estFerie && $estJourDemande)) {
                    foreach ($ressources as $ressource) {
                        $sql = 'DELETE FROM gestion WHERE ressource_id = :ressource AND line = :line AND date = :date';
                        $connection->prepare($sql)->executeQuery([
                            'ressource' => $ressource->getId(),
                            'line' => $data['line'],
                            'date' => $dateStr,
                        ]);

                        $gestion = new Gestion();
                        $gestion->setRessource($ressource);
                        $gestion->setPlage($plage);
                        $gestion->setLine($data['line']);
                        $gestion->setDate(clone $date);

                        $em->persist($gestion);
                    }
                }
            }

            $em->flush();

            $this->addFlash('success', 'Le planning a été généré avec succès.');
            return $this->redirectToRoute('app_auto_enregistrer');
        }

        return $this->render('auto/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
