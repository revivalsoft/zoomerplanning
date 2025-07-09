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

use App\Entity\Groupe;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;

use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_SUPER_ADMIN')]
class GroupeCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Groupe::class;
    }

    public function configureCrud(Crud $crud): Crud
    {

        return $crud
            //->setSearchFields([]) // champs inclus dans la recherche
            ->setEntityLabelInPlural('Groupes de ressources')
            ->setEntityLabelInSingular('Groupe')
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
            TextField::new('nom')
                //->setColumns(4)
                ->setFormTypeOption('attr', ['maxlength' => 30])
                ->setHelp('30 caractères maximum'),
            ArrayField::new('ressource', 'Ressources :')
                //->setColumns(8)
                ->hideOnForm()
                ->setDisabled(Action::SAVE_AND_ADD_ANOTHER),
            BooleanField::new('visible', 'Visible en public')
                ->setSortable(false)
                ->renderAsSwitch(true), // bouton coulissant activé au lieu de case à cocher

        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::SAVE_AND_CONTINUE)
            ->remove(Crud::PAGE_DETAIL, Action::EDIT);
        //->add(Crud::PAGE_INDEX, Action::DETAIL); // inutile puisque tout figure sur l'index

        //->setPermission(Action::NEW, 'ROLE_ADMIN');
    }
}
