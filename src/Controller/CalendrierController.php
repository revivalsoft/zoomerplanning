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

use App\Entity\Param;
use App\Repository\ParamRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use App\Classes\Plannings;

use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(('ROLE_USER'))]
class CalendrierController extends AbstractController
{
    #[Route('/calendrier', name: 'app_calendrier', methods: ['GET'])]
    public function index(ParamRepository $paramRepository, EntityManagerInterface $entityManager): Response
    {
        // il n'y a qu'une ligne d'enregistrement
        // l'idunique "1" correspond à l'unique enreg de la table param
        // donc à vérifier lors d'une installation en production
        $idunique = 1;
        $paramidcal = $paramRepository->find($idunique); // renvoie un tableau
        $idcal = $paramidcal->getCalendar();

        if ($idcal == null) $idcal = 0;

        if (isset($_GET['idcal'])) {
            $idcal = $_GET['idcal'];
            // trouve l'enregistrement pour le mettre à jour
            // l'indice 1 de find correspond à l'id de l'unique
            // ligne d'enregistrement de la table param
            // $idunique est égal à UN correspondant à l'unique ligne 
            // d'enregistrement de la table param
            $parametres = $entityManager->getRepository(Param::class)->find($idunique);
            $parametres->setCalendar($idcal);
            //$entityManager->persist($parametres); // pas utile pour un update
            $entityManager->flush();
        }
        $calendrier = Plannings::Calendrier();

        return $this->render('calendrier/index.html.twig', [
            'calendrier' => $calendrier,
            'idcal' => $idcal
        ]);
    }
}
