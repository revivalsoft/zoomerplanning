<?php

// src/Controller/Admin/ChangePasswordController.php
namespace App\Controller\Admin;

use App\Form\ChangePasswordType;
//use App\Entity\Ressource;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ChangePasswordController extends AbstractController
{

    public $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }


    #[Route('/admin/change-password', name: 'admin_change_password')]
    #[IsGranted('ROLE_ADMIN')]


    public function changePassword(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager
    ): Response {
        // Utilisation du service Security injecté dans le constructeur

        //$user = $this->security->getUser();

        $user = $this->getUser();
        if (!$user instanceof \App\Entity\Ressource) {
            throw new \LogicException('L’utilisateur connecté est invalide ou non authentifié.');
        }


        $form = $this->createForm(ChangePasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newPassword = $form->get('plainPassword')->getData();
            $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
            $user->setPassword($hashedPassword);

            $entityManager->flush();

            $this->addFlash('success', 'Votre mot de passe a été mis à jour.');

            return $this->redirectToRoute('admin');
        }

        return $this->render('admin/change_password.html.twig', [
            'changePasswordForm' => $form->createView(),
        ]);
    }
}
