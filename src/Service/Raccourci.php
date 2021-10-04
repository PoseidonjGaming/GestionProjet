<?php
namespace App\Service;

use App\Entity\User;
use App\Entity\Projet;
use App\Entity\FamilleTache;
use App\Entity\Taches;
use DateTime;

class Raccourci{

    public function getTaches(Projet $projet){
        $array=[];
        foreach($projet->getFamilles() as $uneFamille){
            foreach($uneFamille->getTaches() as $unetache){
                array_push($array, $unetache);
            }
        }
        return $array;
    }
}