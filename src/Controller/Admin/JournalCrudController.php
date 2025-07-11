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
// src/Controller/Admin/JournalCrudController.php
namespace App\Controller\Admin;

use App\Entity\Journal;
use App\Service\MailService;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(('ROLE_ADMIN'))]

class JournalCrudController extends AbstractCrudController
{
    private MailService $mailService;
    private AdminUrlGenerator $adminUrlGenerator;
    public Security $security;


    public function __construct(

        Security $security,
        MailService $mailService,
        AdminUrlGenerator $adminUrlGenerator
    ) {

        $this->security = $security;
        $this->mailService = $mailService;
        $this->adminUrlGenerator = $adminUrlGenerator;
    }

    public static function getEntityFqcn(): string
    {
        return Journal::class;
    }

    #[Route('/admin/send-emails', name: 'admin_send_emails')]
    public function sendEmails(): Response
    {
        $nb = $this->mailService->sendPlanningMailsPourAdministrateur($this->security->getUser());

        if ($nb === 0) {
            $this->addFlash('success', 'Pas de mails à envoyer.');
        } elseif ($nb === 1) {
            $this->addFlash('success', 'Un mail envoyé. Un mail récapitulatif vous a été adressé.');
        } else {
            $this->addFlash('success', "$nb mails envoyés. Un mail récapitulatif vous a été adressé.");
        }

        return $this->redirect($this->adminUrlGenerator->setController(DashboardController::class)->generateUrl());
    }

    public function configureActions(Actions $actions): Actions
    {
        $sendEmailsAction = Action::new('sendEmails', 'Send Emails', 'fa fa-envelope')
            ->linkToRoute('admin_send_emails');

        return $actions->add(Action::INDEX, $sendEmailsAction);
    }
}
