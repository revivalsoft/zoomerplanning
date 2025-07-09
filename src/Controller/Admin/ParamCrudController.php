<?php

namespace App\Controller\Admin;

use App\Entity\Param;
use App\Repository\CalendarRepository;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_SUPER_ADMIN')]

class ParamCrudController extends AbstractCrudController
{

    private $calendarRepository;

    // Injection du repository
    public function __construct(CalendarRepository $calendarRepository)
    {
        $this->calendarRepository = $calendarRepository;
    }


    public static function getEntityFqcn(): string
    {
        return Param::class;
    }


    public function configureFields(string $pageName): iterable
    {
        $calendars = $this->calendarRepository->findAll();

        //$choices = [];
        $choices['Métropole'] = 0;
        foreach ($calendars as $calendar) {

            $choices[$calendar->getRegion()] = $calendar->getId();
        }

        //dd($choices);
        $choicespublic = [];
        for ($p = 1; $p <= 10; $p++) {
            $choicespublic[$p] = $p;
        }

        $choicesadmin = [];
        for ($p = 1; $p <= 10; $p++) {
            $choicesadmin[$p] = $p;
        }

        return [
            IdField::new('id')
                ->hideOnForm()
                ->hideOnIndex(),
            ChoiceField::new('admin', 'Lignes visibles en admin')
                ->setSortable(false) //masque les flèches montantes et descendantes
                ->setTextAlign('center')
                ->setChoices($choicesadmin)
                ->renderAsNativeWidget()
                ->setColumns(1),
            ChoiceField::new('public', 'Lignes visibles en public')
                ->setSortable(false)
                ->setTextAlign('center')
                ->setChoices($choicespublic)
                ->renderAsNativeWidget()
                ->setColumns(1),
            /*La fusion ci-dessous porte sur les calculs (d'horaires et de plages)
            Si fusion est à zéro, il sera mis à défaut à 1,
            ce qui signifie que les calculs ne seront pris en compte que pour la ligne 1
            Si fusion est à 2 par exemple, c'est le total des 2 premières lignes qui
            est pris en compte pour les calculs
            */
            // ChoiceField::new('fusion', 'Fusion')
            //     ->setSortable(false)
            //     ->setTextAlign('center')
            //     ->setChoices($choicespublic)
            //     ->renderAsNativeWidget()
            //     ->setColumns(1),
            ChoiceField::new('calendar')
                ->setSortable(false)
                ->setTextAlign('center')
                ->setColumns(5)
                ->setChoices($choices)
                ->renderAsNativeWidget()
                ->setLabel('Calendrier des jours fériés'),
            BooleanField::new('dates', 'Répétition des dates dans les plannings')
                ->setSortable(false)
                ->renderAsSwitch(true) // bouton coulissant activé au lieu de case à cocher

        ];
    }


    public function configureCrud(Crud $crud): Crud
    {
        return $crud

            ->showEntityActionsInlined() // sans les points de suspension en fin de ligne de chaque enregistrement
            ->setEntityLabelInPlural('Paramètres')
            ->setEntityLabelInSingular('les paramètres')
            ->showEntityActionsInlined();
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {

        if ($entityInstance instanceof Param) {

            $calendrier = $entityInstance->getCalendar();
            if ($calendrier == Null) {
                $entityInstance->setCalendar(0);
            }
        }
        parent::updateEntity($entityManager, $entityInstance);
    }


    public function configureActions(Actions $actions): Actions
    {

        $customLink = Action::new('app_calendrier', 'Détail des calendriers')
            ->linkToUrl($this->generateUrl('app_calendrier'));


        // Personnaliser l'action Modifier pour utiliser une icône Font Awesome
        $editAction = Action::new('edit', ' ', 'fa fa-edit') // "fa fa-edit" est l'icône Font Awesome
            ->linkToCrudAction(Crud::PAGE_EDIT); // Lien vers la page d'édition

        return $actions
            ->disable(Action::DELETE, Action::DELETE)
            ->disable(Action::NEW, Action::NEW)
            ->disable(Action::SAVE_AND_CONTINUE)
            ->add(Crud::PAGE_INDEX, $customLink) // calendrier
            ->update(Crud::PAGE_INDEX, Action::EDIT, fn(Action $action) => $editAction); // Remplacer l'action "edit"
    }
}
