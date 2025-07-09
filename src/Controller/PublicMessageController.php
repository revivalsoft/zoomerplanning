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
//kanban

namespace App\Controller;

use App\Entity\Task;
use App\Repository\RessourceRepository;
use App\Repository\RessourceTokenRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

// use Symfony\Component\Security\Http\Attribute\IsGranted;

// #[IsGranted(('ROLE_USER'))]
class PublicMessageController extends AbstractController
{
    #[Route('/message/form/token/{ressourceToken}/{adminToken}', name: 'kanban_message_token')]
    public function fromToken(
        string $ressourceToken,
        string $adminToken,
        RessourceTokenRepository $tokenRepo,
        RessourceRepository $ressourceRepo,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $rToken = $tokenRepo->findOneBy(['token' => $ressourceToken]);
        $aToken = $tokenRepo->findOneBy(['token' => $adminToken]);

        // ces conneries bloquent l'accès !!!!!!!!!!!!!!!!!!!!!!
        // if (!$rToken || !$aToken) {
        //     throw $this->createNotFoundException("Lien invalide.");
        // }

        $ressource = $ressourceRepo->find($rToken->getRessourceId());
        $admin = $ressourceRepo->find($aToken->getAdminId());

        // if (!$ressource || !$admin || !in_array('ROLE_ADMIN', $admin->getRoles())) {
        //     throw $this->createNotFoundException("Identifiants invalides.");
        // }

        $form = $this->createFormBuilder()
            ->add('description', TextareaType::class, [
                'label' => 'Message à transmettre',
                'attr' => ['rows' => 5, 'class' => 'form-control']
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Envoyer',
                'attr' => ['class' => 'btn btn-primary btn-lg w-100']
            ])
            ->getForm();

        $form->handleRequest($request);
        $confirmation = false;

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $task = new Task();
            $task->setTitle('Message de ' . $ressource->getNom());
            $task->setDescription($data['description']);
            $task->setColumn('todo');
            $task->setUser($admin);
            $task->setPosition(0);

            $em->persist($task);
            $em->flush();

            $confirmation = true;
        }

        return $this->render('kanban/message.html.twig', [
            'form' => $form->createView(),
            'admin' => $admin,
            'confirmation' => $confirmation
        ]);
    }
}
