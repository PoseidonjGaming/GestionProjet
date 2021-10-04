<?php
namespace App\Service;

use App\Entity\User;
use App\Entity\Projet;
use App\Entity\FamilleTache;
use App\Entity\Taches;
use DateTime;

class ConversionH{

    private $annee;
    private $mois;
    private $jours;
    private $heure;
    private $minutes;
    private $secondes;


    public function secondes_to_duree($secondes){
            $s=$secondes % 60; //reste de la division en minutes => secondes
            if($s<10){
                $s="0".$s;
            }
            $m1=($secondes-$s) / 60; //minutes totales
            $m=$m1 % 60;//reste de la division en heures => minutes
            if($m<10){
                $m="0".$m;
            }
            $h=($m1-$m) / 60; //heures
            if($h<10 && $h>0){
                $h="0".$h;
            }
            elseif($h<=0){
                $h="00";
            }
            $resultat=$h.":".$m.":".$s;
            return $resultat;
    }

    public function duree_to_secondes($duree){
            $array_duree=explode(":",$duree);
            $secondes=3600*intval($array_duree[0])+60*intval($array_duree[1])+intval($array_duree[2]);
            return $secondes;  
    }    
    
}