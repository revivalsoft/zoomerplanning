<?php

namespace App\Controller\Admin;

use App\Entity\Ressource;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_SUPER_ADMIN')]
class RessourceCrudController extends AbstractCrudController
{
    private UserPasswordHasherInterface $passwordHasher;
    private RoleHierarchyInterface $roleHierarchy;
    private Security $security;
    private TokenStorageInterface $tokenStorage;

    public function __construct(
        Security $security,
        UserPasswordHasherInterface $passwordHasher,
        RoleHierarchyInterface $roleHierarchy,
        TokenStorageInterface $tokenStorage
    ) {
        $this->passwordHasher = $passwordHasher;
        $this->roleHierarchy = $roleHierarchy;
        $this->security = $security;
        $this->tokenStorage = $tokenStorage;
    }

    public static function getEntityFqcn(): string
    {
        return Ressource::class;
    }

    public function configureFields(string $pageName): iterable
    {
        $roles = [
            'Super Administrateur' => 'ROLE_SUPER_ADMIN',
            'Administrateur' => 'ROLE_ADMIN',
            'User' => 'ROLE_USER',
        ];

        return [
            IdField::new('id')
                ->hideOnForm()
                ->hideOnIndex(),

            TextField::new('nom', 'Nom (pseudonymisé)')
                ->setColumns(6)
                ->setFormTypeOption('attr', ['maxlength' => 15])
                ->setHelp('15 caractères maximum'),

            TextField::new('password', 'Mot de passe')
                ->onlyOnForms()
                ->setFormTypeOption('mapped', false)
                ->setRequired($pageName === Crud::PAGE_NEW)
                ->setHelp($pageName === Crud::PAGE_NEW
                    ? 'Insérer un mot de passe. L\'utilisateur pourra le modifier à loisir'
                    : 'Laisser vide pour ne pas modifier le mot de passe'),

            TextField::new('fonction', 'Fonction')
                ->setColumns(6)
                ->setFormTypeOption('attr', ['maxlength' => 30])
                ->setHelp('30 caractères maximum'),

            EmailField::new('email', 'Adresse mail professionnelle')
                ->setColumns(6)
                ->setFormTypeOption('attr', ['maxlength' => 100])
                ->setHelp('100 caractères maximum'),

            ChoiceField::new('roles')
                ->setChoices($roles)
                ->allowMultipleChoices()
                ->renderExpanded(true)
                ->setRequired(true)
                ->setFormTypeOption('by_reference', false)
                ->setFormTypeOption('multiple', true)
                ->setFormTypeOption('expanded', true),

            AssociationField::new('groupe', 'Groupes d\'appartenance')
                ->autocomplete()
                ->setColumns(6)
                ->setHelp('Une ressource peut appartenir à plusieurs groupes')
                ->onlyOnForms(),

            CollectionField::new('groupe', 'groupe(s)')
                ->setColumns(6)
                ->onlyOnIndex(),

            TextField::new('matricule', 'Matricule'),
        ];
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->showEntityActionsInlined()
            ->setPaginatorPageSize(10)
            ->setEntityLabelInPlural('Ressources')
            ->setEntityLabelInSingular('une ressource');
    }

    public function configureActions(Actions $actions): Actions
    {
        $editAction = Action::new('edit', ' ', 'fa fa-edit')
            ->linkToCrudAction(Crud::PAGE_EDIT);

        return $actions
            ->disable(Action::DELETE)
            ->disable(Action::SAVE_AND_CONTINUE)
            ->update(Crud::PAGE_INDEX, Action::EDIT, fn(Action $action) => $editAction);
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof Ressource) {
            return;
        }

        $request = $this->getContext()->getRequest();
        $plainPassword = $request->request->all('Ressource')['password'] ?? null;

        if ($plainPassword) {
            $hashedPassword = $this->passwordHasher->hashPassword($entityInstance, $plainPassword);
            $entityInstance->setPassword($hashedPassword);
        }

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof Ressource) {
            return;
        }

        $request = $this->getContext()->getRequest();
        $plainPassword = $request->request->all('Ressource')['password'] ?? null;

        if ($plainPassword) {
            $hashedPassword = $this->passwordHasher->hashPassword($entityInstance, $plainPassword);
            $entityInstance->setPassword($hashedPassword);
        }

        $currentUser = $this->getUser();
        if ($currentUser instanceof Ressource && $currentUser->getId() === $entityInstance->getId()) {
            $token = new UsernamePasswordToken(
                $entityInstance,
                'main',
                $entityInstance->getRoles()
            );

            $this->tokenStorage->setToken($token);

            $session = $request->getSession();
            $session->set('_security_main', serialize($token));
        }

        parent::updateEntity($entityManager, $entityInstance);
    }
}
