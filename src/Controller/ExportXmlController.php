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

class ExportXmlController extends AbstractController
{
    #[Route('/export/xml', name: 'app_export_xml')]
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
        $query = "
        SELECT 
            r.nom AS nom,g.line,
            r.matricule AS matricule,
            g.date AS date,
            p.sigle AS sigle,
            p.heure, p.minute
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

        // Enregistrer le fichier JSON
        $filePath = __DIR__ . '/../../var/exported_data.xml';
        $newFileGenerated = false;

        if ($form->isSubmitted() && $form->isValid()) {
            // Obtenez les données filtrées (basé sur vos critères)
            //$data = $this->generateData($connection, $form->getData());

            // Convertissez les données en XML et sauvegardez-les dans un fichier
            $xmlData = $this->generateXmlData($groupedData);
            file_put_contents($filePath, $xmlData);

            $newFileGenerated = true;
        }

        // Afficher le formulaire et le chemin du fichier
        return $this->render('exportxml/index.html.twig', [
            'form' => $form->createView(),
            'filePath' => $filePath,
            'newFileGenerated' => $newFileGenerated, // Passer l'indicateur au template
        ]);
    }

    /**
     * @Route("/download-xml", name="download_xml")
     */
    public function downloadXml(): Response
    {
        $filePath = __DIR__ . '/../../var/exported_data.xml';

        if (!file_exists($filePath)) {
            throw $this->createNotFoundException("Le fichier n'existe pas.");
        }

        return new Response(file_get_contents($filePath), 200, [
            'Content-Type' => 'application/xml',
            'Content-Disposition' => 'attachment; filename="exported_data.xml"',
        ]);
    }

    private function generateXmlData(array $data): string
    {
        $xml = new \SimpleXMLElement('<root/>');

        foreach ($data as $entry) {
            // Créer un élément pour chaque enregistrement
            $item = $xml->addChild('entry');
            $item->addChild('nom', htmlspecialchars($entry['nom']));
            $item->addChild('matricule', htmlspecialchars($entry['matricule']));
            $item->addChild('ligne', $entry['ligne']);
            $item->addChild('duree', $entry['duree']);
            $item->addChild('date', $entry['date']);
            $item->addChild('sigle', $entry['sigle']); // Inclure le champ "sigle" de la table `plage`
        }

        // Convertir SimpleXMLElement en DOMDocument pour le formatage
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($xml->asXML());

        // Retourne le XML formaté en tant que chaîne
        return $dom->saveXML();
    }
}
