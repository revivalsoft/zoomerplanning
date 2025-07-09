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

use App\Entity\Ressource;
use App\Repository\RessourceRepository;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Doctrine\Persistence\ManagerRegistry;

use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;


#[IsGranted('ROLE_SUPER_ADMIN')]
class AffectationCrudController extends AbstractCrudController
{
    private RessourceRepository $ressourceRepository;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->ressourceRepository = $doctrine->getRepository(Ressource::class);
    }

    public static function getEntityFqcn(): string
    {
        return Ressource::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('adminInfo', 'Administrateur')
                ->hideOnForm(),

            AssociationField::new('groupesAdministres', 'Attribution des groupes')
                ->hideOnIndex()
                ->setFormTypeOption('by_reference', false)
                ->setFormTypeOption('multiple', true)
                ->setFormTypeOption('expanded', false),

            ArrayField::new('groupesAdministres', 'Attribution des groupes')
                ->setSortable(false)
                ->onlyOnIndex(),
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        $editAction = Action::new('edit', ' ', 'fa fa-edit')
            ->linkToCrudAction(Crud::PAGE_EDIT);

        return $actions
            ->disable(Action::NEW, Action::DELETE, Action::SAVE_AND_CONTINUE)
            ->update(Crud::PAGE_INDEX, Action::EDIT, fn(Action $action) => $editAction);
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->showEntityActionsInlined()
            ->setPageTitle(Crud::PAGE_EDIT, fn(Ressource $user) => sprintf('Administrateur : %s', $user->getUsername()));
    }

    public function createIndexQueryBuilder(
        SearchDto $searchDto,
        EntityDto $entityDto,
        FieldCollection $fields,
        FilterCollection $filters
    ): QueryBuilder {
        // Appel au repository pour récupérer uniquement les admins
        return $this->ressourceRepository->createAdminQueryBuilder();
    }
}
