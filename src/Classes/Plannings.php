<?php

namespace App\Classes;

use DateTime;
use DateTimeZone;
use DateTimeInterface;

class Plannings
{

    public static function NomDuMois(int $mois = null)
    {
        $tab_mois = array(
            1 => "janvier",
            2 => "février",
            3 => "mars",
            4 => "avril",
            5 => "mai",
            6 => "juin",
            7 => "juillet",
            8 => "août",
            9 => "septembre",
            10 => "octobre",
            11 => "novembre",
            12 => "décembre"
        );
        return $tab_mois[$mois];
    }

    /*
    Pour afficher en jaune le jour courant dans les plannings
    */
    public static function findCurrentDay($numanencours, $nummoiscourant)
    {
        $CurrentDateYear = date('Y');
        $CurrentDateMonth = date('m');
        $CurrentDate = new DateTime($CurrentDateYear . '-' . $CurrentDateMonth . '-' . '1');
        $SelectedDate = new DateTime($numanencours . '-' . $nummoiscourant . '-' . '1');

        $currentDay = 0;
        if ($SelectedDate == $CurrentDate) {
            $currentDay = date('j');
        }
        return $currentDay;
    }

    public static function nombrejoursmois(int $mois = null, int $annee = null)
    {
        $nombrejours = cal_days_in_month(CAL_GREGORIAN, $mois, $annee);
        return $nombrejours;
    }

    public static function tabJourSemaine(int $Mois = null, int $Annee = null)
    {
        $Tab = array("LU", "MA", "ME", "JE", "VE", "SA", "DI");

        $longueurmois = self::nombrejoursmois($Mois, $Annee);
        for ($i = 1; $i <= $longueurmois; $i++) {
            $JourSemaine = date("N", mktime(0, 0, 0, $Mois, $i, $Annee)); // numéro du jour de la semaine
            $tabJoursSemaine[$i] = $Tab[$JourSemaine - 1];
        }
        return $tabJoursSemaine;
    }

    public static function anencours()
    {
        $an_en_cours = date("Y");
        return $an_en_cours;
    }

    public static function moisencours()
    {
        $mois_en_cours = date("m");
        return $mois_en_cours;
    }

    public static function TabZones(int $numzone = null)
    {
        $tabzone[0] = 'Métropole';
        $tabzone[1] = 'Alsace-Moselle';
        $tabzone[2] = 'Guadeloupe';
        $tabzone[3] = 'Guyane';
        $tabzone[4] = 'Martinique';
        $tabzone[5] = 'Mayotte';
        $tabzone[6] = 'Nouvelle-Calédonie';
        $tabzone[7] = 'La Réunion';
        $tabzone[8] = 'Polynésie Française';
        $tabzone[9] = 'Saint-Barthélémy';
        $tabzone[10] = 'Saint-Martin';
        $tabzone[11] = 'Wallis-et-Futuna';
        $tabzone[12] = 'Saint-Pierre-et-Miquelon';

        return $tabzone[$numzone];
    }

    public static function Calendrier()
    {
        $tabzone = array();

        $tabzone[0] = 'Métropole';
        $tabzone[1] = 'Alsace-Moselle';
        $tabzone[2] = 'Guadeloupe';
        $tabzone[3] = 'Guyane';
        $tabzone[4] = 'Martinique';
        $tabzone[5] = 'Mayotte';
        $tabzone[6] = 'Nouvelle-Calédonie';
        $tabzone[7] = 'La Réunion';
        $tabzone[8] = 'Polynésie Française';
        $tabzone[9] = 'Saint-Barthélémy';
        $tabzone[10] = 'Saint-Martin';
        $tabzone[11] = 'Wallis-et-Futuna';
        $tabzone[12] = 'Saint-Pierre-et-Miquelon';

        return $tabzone;
    }

    // pour l'instant pas utilisée mais on la garde provisoirement
    // cause un disfonctionnement dans les plannings en liaison avec le journal
    public static function convertUtcToParisDateTime($utcTime): DateTimeInterface
    {
        // Créer un objet DateTime à partir de l'heure UTC
        $utcDateTime = new DateTime($utcTime, new DateTimeZone('UTC'));

        // Définir le fuseau horaire de Paris
        $parisTimeZone = new DateTimeZone('Europe/Paris');

        // Convertir l'heure UTC en heure de Paris
        $utcDateTime->setTimezone($parisTimeZone);

        // Retourner l'objet DateTime
        return $utcDateTime;
    }

    function convertUtcToParisTime($utcTime)
    {
        // Créer un objet DateTime à partir de l'heure UTC
        $utcDateTime = new DateTime($utcTime, new DateTimeZone('UTC'));

        // Définir le fuseau horaire de Paris
        $parisTimeZone = new DateTimeZone('Europe/Paris');

        // Convertir l'heure UTC en heure de Paris
        $utcDateTime->setTimezone($parisTimeZone);

        // Retourner l'heure formatée en tant que chaîne
        //return $utcDateTime->format('Y-m-d H:i:s');
        return $utcDateTime; // on formatera dans le controleur
    }
}
