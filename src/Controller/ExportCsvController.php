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

use Doctrine\DBAL\Connection;
use App\Form\DateRangeType;
use App\Repository\ParamRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(('ROLE_ADMIN'))]
class ExportCsvController extends AbstractController
{
    #[Route('/export/csv', name: 'app_export_csv')]
    public function index(ParamRepository $paramRepository, Request $request, Connection $connection): Response
    {
        // Créer le formulaire de sélection de dates
        $form = $this->createForm(DateRangeType::class);
        $form->handleRequest($request);


        $param = $paramRepository->find(1);
        $lignePublic = $param->getPublic();


        // Initialiser les valeurs par défaut pour éviter les erreurs si le formulaire n'est pas encore soumis
        $startDate = new \DateTime('first day of this month');
        $endDate = new \DateTime('last day of this month');

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupérer les valeurs des dates
            $startDate = $form->get('start_date')->getData();
            $endDate = $form->get('end_date')->getData();
        }

        // Requête SQL modifiée avec filtrage par dates
        //SUM(p.heure * 60 + p.minute) AS total_minutes
        $query = "
        SELECT 
            r.nom AS nom,g.line,
            r.matricule AS matricule,
            g.date AS date,
            p.sigle AS sigle,
            p.heure ,p.minute 
        FROM gestion g
        INNER JOIN ressource r ON g.ressource_id = r.id
        INNER JOIN plage p ON g.plage_id = p.id
        INNER JOIN ressource_groupe rg ON r.id = rg.ressource_id
        WHERE g.date BETWEEN :startDate AND :endDate 
        AND g.line <= :ligne
        GROUP BY r.nom, r.matricule, g.date, p.sigle, g.line, p.heure, p.minute
        ORDER BY r.nom, g.date, g.line
    ";

        // Exécution de la requête avec paramètres de date
        $results = $connection->fetchAllAssociative($query, [
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d'),
            'ligne' => $lignePublic
        ]);

        // Traitement des résultats comme précédemment
        $groupedData = [];
        foreach ($results as $row) {
            // $hours = intdiv($row['total_minutes'], 60);
            // $minutes = $row['total_minutes'] % 60;
            $hours = $row['heure'];
            $minutes = $row['minute'];
            $duree = sprintf("%02d:%02d", $hours, $minutes);

            $groupedData[] = [
                'nom' => $row['nom'],
                'matricule' => $row['matricule'],
                'ligne' => $row['line'],
                'sigle' => $row['sigle'],
                'duree' => $duree,
                'date' => $row['date']
            ];
        }


        $filePath = __DIR__ . '/../../var/exported_data.csv';
        $newFileGenerated = false;

        if ($form->isSubmitted() && $form->isValid()) {

            $this->generateCsvData($groupedData, $filePath);

            $newFileGenerated = true;
        }

        // Afficher le formulaire et le chemin du fichier
        return $this->render('exportcsv/index.html.twig', [
            'form' => $form->createView(),
            'filePath' => $filePath,
            'newFileGenerated' => $newFileGenerated, // Passer l'indicateur au template
        ]);
    }

    /**
     * @Route("/download-csv", name="download_csv")
     */
    public function downloadCsv(): Response
    {
        $filePath = __DIR__ . '/../../var/exported_data.csv';

        if (!file_exists($filePath)) {
            throw $this->createNotFoundException("Le fichier n'existe pas.");
        }

        return new Response(file_get_contents($filePath), 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="exported_data.csv"',
        ]);
    }

    private function generateCsvData(array $data, string $filePath): void
    {
        // Ouvre un fichier en écriture
        $file = fopen($filePath, 'w');

        // Ajoute une ligne d'en-tête pour le CSV
        fputcsv($file, ['Nom', 'Matricule', 'Ligne', 'Durée', 'Date', 'Sigle']);

        // Ajoute chaque ligne de données dans le CSV
        foreach ($data as $entry) {
            fputcsv($file, [
                $entry['nom'] ?? '',
                $entry['matricule'] ?? '',
                $entry['ligne'] ?? '',
                $entry['duree'] ?? '',
                $entry['date'] ?? '',
                $entry['sigle'] ?? '',
            ]);
        }

        // Ferme le fichier
        fclose($file);
    }
}
