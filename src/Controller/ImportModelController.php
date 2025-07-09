<?php
// src/Controller/ImportModelController.php
//importation d'un modèle de projet Gantt

namespace App\Controller;


use App\Service\ProjectModelImporter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(('ROLE_ADMIN'))]
class ImportModelController extends AbstractController
{
    #[Route('/import/model', name: 'import_model_form', methods: ['GET'])]
    public function importModelForm(): Response
    {
        // Afficher le formulaire pour choisir le modèle à importer
        $dir = $this->getParameter('kernel.project_dir') . '/var/';

        $files = glob($dir . '*.json');

        return $this->render('import_model/form.html.twig', [
            'files' => $files,
        ]);
    }

    #[Route('/import/model', name: 'import_model', methods: ['POST'])]
    public function importModel(
        Request $request,
        ProjectModelImporter $importer
    ): Response {
        $filePath = $request->request->get('model_file');
        $newName = $request->request->get('new_project_name');
        $startDateStr = $request->request->get('start_date');

        $startDate = $startDateStr ? new \DateTime($startDateStr) : null;

        try {
            $importer->import($filePath, $newName, $startDate);
            $this->addFlash('success', 'Le modèle a été importé avec succès !');
        } catch (\Throwable $e) {
            $this->addFlash('danger', 'Erreur lors de l’import : ' . $e->getMessage());
        }

        return $this->redirectToRoute('project_index');
    }
}
