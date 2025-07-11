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

use App\Entity\KeyResult;
use App\Entity\Objective;
use App\Form\KeyResultType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/keyresult')]
#[IsGranted(('ROLE_ADMIN'))]
class KeyResultController extends AbstractController
{
    #[Route('/new/{objectiveId}', name: 'keyresult_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em, int $objectiveId): Response
    {
        $user = $this->getUser();
        $objective = $em->getRepository(Objective::class)->find($objectiveId);
        if (!$objective) {
            throw $this->createNotFoundException('Objective not found');
        }

        $keyResult = new KeyResult();
        $keyResult->setObjective($objective);
        $keyResult->setUser($this->getUser());

        $form = $this->createForm(KeyResultType::class, $keyResult);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $keyResult->setLastUpdate(new \DateTime());
            $em->persist($keyResult);
            $em->flush();

            return $this->redirectToRoute('objective_show', ['id' => $objectiveId]);
        }

        return $this->render('keyresult/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/edit/{id}', name: 'keyresult_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, KeyResult $keyResult, EntityManagerInterface $em): Response
    {
        if ($keyResult->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(KeyResultType::class, $keyResult);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $keyResult->setLastUpdate(new \DateTime());
            $em->flush();

            return $this->redirectToRoute('objective_show', ['id' => $keyResult->getObjective()->getId()]);
        }

        return $this->render('keyresult/edit.html.twig', [
            'form' => $form->createView(),
            'key_result' => $keyResult,
        ]);
    }

    #[Route('/delete/{id}', name: 'keyresult_delete', methods: ['POST'])]
    public function delete(Request $request, KeyResult $keyResult, EntityManagerInterface $em): Response
    {

        if ($keyResult->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        if ($this->isCsrfTokenValid('delete' . $keyResult->getId(), $request->request->get('_token'))) {
            $em->remove($keyResult);
            $em->flush();
        }

        return $this->redirectToRoute('objective_show', ['id' => $keyResult->getObjective()->getId()]);
    }
}
