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
namespace App\Controller;

use App\Entity\Objective;
use App\Form\ObjectiveType;
use App\Repository\ObjectiveRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{Request, Response};
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/objective')]
#[IsGranted(('ROLE_ADMIN'))]
class ObjectiveController extends AbstractController
{
    #[Route('/', name: 'objective_index', methods: ['GET'])]
    public function index(ObjectiveRepository $objectiveRepository): Response
    {

        $user = $this->getUser();

        return $this->render('objective/index.html.twig', [
            'objectives' => $objectiveRepository->findBy(['user' => $user]),
        ]);
    }

    #[Route('/new', name: 'objective_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $objective = new Objective();
        $objective->setUser($this->getUser());
        $form = $this->createForm(ObjectiveType::class, $objective);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($objective);
            $em->flush();
            return $this->redirectToRoute('objective_index');
        }

        return $this->render('objective/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'objective_show', methods: ['GET'])]
    public function show(Objective $objective): Response
    {
        if ($objective->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }
        return $this->render('objective/show.html.twig', [
            'objective' => $objective,
        ]);
    }

    #[Route('/{id}/edit', name: 'objective_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Objective $objective, EntityManagerInterface $em): Response
    {

        if ($objective->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(ObjectiveType::class, $objective);
        $form->handleRequest($request);



        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            return $this->redirectToRoute('objective_index');
        }

        return $this->render('objective/edit.html.twig', [
            'objective' => $objective,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'objective_delete', methods: ['POST'])]
    public function delete(Request $request, Objective $objective, EntityManagerInterface $em): Response
    {

        if ($objective->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }
        if ($this->isCsrfTokenValid('delete' . $objective->getId(), $request->request->get('_token'))) {
            $em->remove($objective);
            $em->flush();
        }

        return $this->redirectToRoute('objective_index');
    }
}
