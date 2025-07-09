<?php

namespace App\Controller\Admin;

use App\Entity\Categorie;
use App\Entity\Plage;
use App\Entity\Ressource;
use App\Entity\Groupe;
use App\Entity\Param;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;


use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(('ROLE_ADMIN'))]

class DashboardController extends AbstractDashboardController
{
    #[Route('/admin', name: 'admin')]

    public function index(): Response
    {

        return $this->render('admin/dashboard.html.twig');
    }

    public function configureAssets(): Assets
    {
        return Assets::new()->addCssFile('styles/easyadmin.css');
    }


    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('ZoomerPlanning')
            ->renderContentMaximized()
            ->setDefaultColorScheme('dark')
            ->setFaviconPath('favicon.png');
    }

    public function configureCrud(): Crud
    {
        return Crud::new();
    }

    public function configureMenuItems(): iterable
    {


        yield MenuItem::subMenu('Plannings', 'fa-solid fa-calendar-days')
            ->setSubItems([
                MenuItem::linkToRoute('Gestion', 'fa-solid fa-users-rectangle', 'app_choix_groupe')
                    ->setPermission('ROLE_ADMIN'),
                MenuItem::linkToRoute('Planning express', 'fa-solid fa-truck', 'app_auto_enregistrer')
                    ->setPermission('ROLE_ADMIN'),
                MenuItem::linkToRoute('Espace users', 'fa-solid fa-calendar-days', 'app_accueil')
                    ->setPermission('ROLE_ADMIN')

            ]);

        yield MenuItem::subMenu('Mails', 'fa-solid fa-envelope')
            ->setSubItems([
                MenuItem::linkToRoute('Envoi de mails', 'fa-solid fa-paper-plane', 'admin_send_emails')
                    ->setPermission('ROLE_ADMIN'),
                MenuItem::linkToRoute('Webhook', 'fa-solid fa-envelope-open', 'webhook_index')
                    ->setPermission('ROLE_ADMIN'),
            ]);

        yield MenuItem::subMenu('Notifications', 'fa-solid fa-bell')
            ->setSubItems([
                MenuItem::linkToRoute('Envoi', 'fa-solid fa-bell', 'admin_push_notification')
                    ->setPermission('ROLE_ADMIN'),
                MenuItem::linkToRoute('Suivi', 'fa-solid fa-file', 'admin_notifications_envoyees')
                    ->setPermission('ROLE_ADMIN'),
                MenuItem::linkToRoute('Abonnés', 'fa-solid fa-file', 'push_subscriptions_list')
                    ->setPermission('ROLE_ADMIN'),
            ]);


        yield MenuItem::subMenu('Export', 'fa-solid fa-file-code')
            ->setSubItems([
                MenuItem::linkToRoute('Export Json', 'fa-solid fa-file-text', 'app_export_json')
                    ->setPermission('ROLE_SUPER_ADMIN'),
                MenuItem::linkToRoute('Export XML', 'fa-solid fa-file-code', 'app_export_xml')
                    ->setPermission('ROLE_SUPER_ADMIN'),
                MenuItem::linkToRoute('Export Csv', 'fa-solid fa-file-excel', 'app_export_csv')
                    ->setPermission('ROLE_SUPER_ADMIN'),
            ]);

        yield MenuItem::subMenu('OKR', 'fa-solid fa-thumbs-up')
            ->setSubItems([
                MenuItem::linkToRoute('Objectifs', 'fa-solid fa-thumbtack', 'objective_index')
                    ->setPermission('ROLE_ADMIN'),
                MenuItem::linkToRoute('Tableau de bord', 'fa-solid fa-book', 'okr_dashboard')
                    ->setPermission('ROLE_ADMIN'),

            ]);


        //     ]);
        yield MenuItem::subMenu('Gantt', 'fa fa-chart-gantt')
            ->setSubItems([
                MenuItem::linkToRoute('Projets', 'fa-solid fa-diagram-project', 'project_index')
                    ->setPermission('ROLE_ADMIN'),
                MenuItem::linkToRoute('Affectations', 'fa-solid fa-user', 'gantt_affectations')
                    ->setPermission('ROLE_ADMIN'),
                MenuItem::linkToRoute('Calculs', 'fa-solid fa-calculator', 'selection_projet')
                    ->setPermission('ROLE_ADMIN'),
                MenuItem::linkToRoute('Exporter un modèle', 'fa-solid fa-file-export', 'project_export_list')
                    ->setPermission('ROLE_ADMIN'), // les modèles seront partagés avec tous
                MenuItem::linkToRoute('Importer un modèle', 'fa-solid fa-file-import', 'import_model_form')
                    ->setPermission('ROLE_ADMIN'), // les modèles seront partagés avec tous
                MenuItem::linkToRoute('Matrice RACIE', 'fa-solid fa-square-xmark', 'raci_select')
                    ->setPermission('ROLE_ADMIN'), // les modèles seront partagés avec tous

            ]);


        yield MenuItem::linkToRoute('Kanban', 'fa-solid fa-tasks', 'kanban_index')
            ->setPermission('ROLE_ADMIN');


        yield MenuItem::subMenu('Gestion des ressources', 'fa-solid fa-person')
            ->setPermission('ROLE_SUPER_ADMIN')
            ->setSubItems([
                MenuItem::linkToCrud('Groupes de ressources', 'fa-solid fa-users', Groupe::class),
                MenuItem::linkToRoute('Groupe express', 'fa-solid fa-users-rays', 'ressource_groupe_index'),
                MenuItem::linkToCrud('Ressources', 'fa-solid fa-user', Ressource::class),
                MenuItem::linkToRoute('Hiérarchie', 'fa-solid fa-arrows-v', 'app_choix_groupe_hierarchie')
            ]);

        yield MenuItem::subMenu('Gestion des plages', 'fa-solid fa-clock')
            ->setPermission('ROLE_SUPER_ADMIN')
            ->setSubItems([
                MenuItem::linkToCrud('Catégories de plages', 'fa-solid fa-sitemap', Categorie::class),
                MenuItem::linkToCrud('Plages', 'fa-solid fa-tachometer', Plage::class),
            ]);


        yield MenuItem::subMenu('Administrateurs', 'fa-solid fa-house-lock')
            ->setPermission('ROLE_SUPER_ADMIN')
            ->setSubItems([
                // MenuItem::linkToCrud('Gestion', 'fa-solid fa-house-lock', Ressource::class),
                MenuItem::linkToCrud('Attributions', 'fa-solid fa-user-tag', Ressource::class)
                    ->setController(AffectationCrudController::class),
            ]);

        yield MenuItem::linkToCrud('Paramètres', 'fa-solid fa-cog', Param::class)
            ->setPermission('ROLE_SUPER_ADMIN');
    }
}
