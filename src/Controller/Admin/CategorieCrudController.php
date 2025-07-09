<?php

namespace App\Controller\Admin;

use App\Entity\Categorie;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;

use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_SUPER_ADMIN')]
class CategorieCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Categorie::class;
    }

    public function configureCrud(Crud $crud): Crud
    {

        return $crud
            ->setEntityLabelInPlural('Catégories de plages')
            ->setEntityLabelInSingular('Catégorie de plages')
            ->setPaginatorPageSize(10);
    }

    public function configureFields(string $pageName): iterable
    {
        return [

            IdField::new('id')
                ->hideOnForm()
                ->hideOnDetail()
                ->hideWhenUpdating()
                ->hideWhenCreating()
                ->hideOnIndex(),
            TextField::new('nom', 'Nom')
                ->setFormTypeOption('attr', ['maxlength' => 30])
                ->setHelp('30 caractères maximum'),
            ArrayField::new('plage', 'Plages')
                ->hideOnForm()
                ->setDisabled(Action::SAVE_AND_ADD_ANOTHER),
            BooleanField::new('visible', 'Visible')
                ->setSortable(false)
                ->renderAsSwitch(true), // bouton coulissant activé au lieu de case à cocher
        ];
    }
    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::SAVE_AND_CONTINUE)
            ->remove(Crud::PAGE_DETAIL, Action::EDIT);
        // ->add(Crud::PAGE_INDEX, Action::DETAIL); // inutile puisque tout est sur l'index
    }
}
