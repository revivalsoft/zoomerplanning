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
namespace App\Controller\Admin;

use App\Entity\Plage;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ColorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_SUPER_ADMIN')]

class PlageCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Plage::class;
    }

    public function configureFields(string $pageName): iterable
    {

        $choicesheures = [];
        for ($h = 0; $h <= 24; $h++) {
            $choicesheures[$h] = $h;
        }

        $choicesminutes = [];
        for ($m = 0; $m <= 59; $m++) {
            $choicesminutes[$m] = $m;
        }

        return [

            IdField::new('id')
                ->hideOnForm()
                ->hideOnIndex(),

            TextField::new('sigle')
                ->setColumns(2)
                ->setHelp('4 caractères maximum (lettres ou chiffres)')
                ->setFormTypeOption('attr', [
                    'maxlength' => 4,
                    'pattern' => '[a-zA-Z0-9]*',
                    'title' => 'Seuls les chiffres et les lettres sont autorisés',
                ]),

            TextField::new('legende', 'Légende')
                ->setColumns(4)
                ->setHelp('30 caractères maximum')
                ->setFormTypeOption('attr', ['maxlength' => 30]),

            BooleanField::new('absence')
                ->setSortable(false)
                ->setColumns(2)
                ->renderAsSwitch(false), // bouton coulissant désactivé
            AssociationField::new('categorie', 'Catégorie(s)')
                ->setColumns(4)
                ->autocomplete()
                ->setHelp('Une plage peut faire partie de plusieurs catégories')
                ->onlyOnForms(),
            CollectionField::new('categorie', 'Categorie(s)')
                ->setColumns(4)
                ->onlyOnIndex(),
            ColorField::new('couleurfond', 'Couleur de fond')
                ->setSortable(false)
                ->setTextAlign('center')
                ->setColumns(2),
            ColorField::new('couleurtexte', 'Couleur du texte')
                ->setSortable(false)
                ->setTextAlign('center')
                ->setColumns(2),
            ChoiceField::new('heure')
                ->setSortable(false)
                ->setTextAlign('center')
                ->setColumns(2)
                ->setChoices($choicesheures)
                ->setHelp('Durée horaire d\'une plage en heures ...')
                ->setLabel('Heures'),
            ChoiceField::new('minute')
                ->setSortable(false)
                ->setTextAlign('center')
                ->setColumns(2)
                ->setChoices($choicesminutes)
                ->setHelp('...et en minutes')
                ->setLabel('Minutes'),
        ];
    }


    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {

        if ($entityInstance instanceof Plage) {

            $heures = $entityInstance->getHeure();
            if ($heures == null) {
                $entityInstance->setHeure(0); // pour afficher "0" au lieu de "aucune"
            }
            if ($heures == 24) {
                $entityInstance->setMinute(0); // on ne dépasse pas 24 h !
            }

            $minutes = $entityInstance->getMinute();
            if ($minutes == null) {
                $entityInstance->setMinute(0); // pour afficher "0" au lieu de "aucune"
            }
        }
        parent::updateEntity($entityManager, $entityInstance);
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->showEntityActionsInlined()
            ->setEntityLabelInPlural('Plages')
            ->setEntityLabelInSingular('une plage')
            ->setPaginatorPageSize(10);
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        // Vérifiez si votre champ est vide ou a la valeur null
        if ($entityInstance->getHeure() === null) {
            $entityInstance->setHeure(0); // Mettez à 0 si aucune sélection
        }
        if ($entityInstance->getMinute() === null) {
            $entityInstance->setMinute(0); // Mettez à 0 si aucune sélection
        }

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function configureActions(Actions $actions): Actions
    {
        // Personnaliser l'action Modifier pour utiliser une icône Font Awesome
        $editAction = Action::new('edit', ' ', 'fa fa-edit') // "fa fa-edit" est l'icône Font Awesome
            ->linkToCrudAction(Crud::PAGE_EDIT); // Lien vers la page d'édition

        return $actions

            ->disable(Action::DELETE, Action::DELETE)
            ->disable(Action::SAVE_AND_CONTINUE)
            ->update(Crud::PAGE_INDEX, Action::EDIT, fn(Action $action) => $editAction); // Remplacer l'action "edit"
    }
}
