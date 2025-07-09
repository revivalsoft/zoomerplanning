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
namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\ResponseEvent;

class MinifyHtmlListener
{
    public function onKernelResponse(ResponseEvent $event): void
    {

        $response = $event->getResponse();

        // Vérifiez si le type MIME est vide ou correspond à 'text/html'
        $contentType = $response->headers->get('Content-Type');
        if (null === $contentType || strpos($contentType, 'text/html') !== false) {
            // Si le type MIME est 'text/html' ou s'il est vide, on définit 'text/html; charset=UTF-8'
            $response->headers->set('Content-Type', 'text/html; charset=UTF-8');

            // Minification du contenu HTML
            $content = $response->getContent();
            $minifiedContent = preg_replace(
                ['/>\s+</', '/\s+/', '/<!--.*?-->/s'],
                ['><', ' ', ''],
                $content
            );

            // Applique le contenu minifié à la réponse
            $response->setContent($minifiedContent);
        }
    }
}
