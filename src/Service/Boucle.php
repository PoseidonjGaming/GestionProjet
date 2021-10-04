<?php
namespace App\Service;

use App\Entity\User;
use App\Entity\Projet;
use App\Entity\FamilleTache;
use App\Entity\Taches;
use App\Entity\Planning;
use DateTime;
use App\Service\ConversionH;

class Boucle{

    public function bouclecouleurT(Taches $tache,ConversionH $conv){

        $tot= $conv->duree_to_secondes($tache->getDureeEst()); 
        $duree=$tache->getDureeRel();
        $somme=  $conv->duree_to_secondes($duree);
        if ($somme <= $tot){
            $color = '#61DA3A';
        }
        elseif ($somme > $tot){
            $color = '#DA2424';
        }
  
        return $color;
  
    }
    public function boucleetatT(Taches $tache,ConversionH $conv){
       
            $tot= $conv->duree_to_secondes($tache->getDureeEst()); 
            $duree=$tache->getDureeRel();
            $somme=  $conv->duree_to_secondes($duree);
           

         if ($somme <= $tot){
             $etat ="Tache en cours";
            }
         elseif ($somme > $tot){
             $etat ="Tache en retard";
         }
         return $etat;
     }
    
public function boucleetatF(FamilleTache $famille,ConversionH $conv){
      
        $somme=0;
        $tot=0;
        $verif= true;
        $duree=0;
        
        foreach($famille->getTaches() as $unetache) {
       
        $ajout= $conv->duree_to_secondes($unetache->getDureeEst());
        $tot = $tot + $ajout;

        
       if($unetache->getFini()==false){
        $duree=$conv->duree_to_secondes($unetache->getDureeRel());
         $somme= $somme+  $duree;
       }
            if ($duree < $ajout && $verif){
            $etat="Dans les temps";}
            elseif ($duree > $ajout) {
            $etat ="Contient des taches en retard";
            $verif=false ;}}
        
    if ($somme > $tot ){
            $etat ="Hors délais";}
            return $etat;
}


        



  public function bouclecouleurF(FamilleTache $famille,ConversionH $conv){
   $somme=0;
   $tot=0;
   $verif= true;
   $duree=0;
   foreach($famille->getTaches() as $unetache) {

    $ajout= $conv->duree_to_secondes($unetache->getDureeEst());
    $tot = $tot + $ajout;


    if($unetache->getFini()==false){
        $duree=$conv->duree_to_secondes($unetache->getDureeRel());
        $somme= $somme+  $duree;
    }

    if ($duree < $ajout && $verif){
    $etat="#61DA3A";}
    elseif ($duree > $ajout) {
    $etat ="orange";
    $verif=false ;}
    }

    if ($somme > $tot ){
        $etat ="#DA2424";}

   return $etat;   
}


public function boucleetatPl(Planning $p, User $user ,ConversionH $conv){
      
    $tot=0;
    $duree= $conv->duree_to_secondes($p->getDureeEst()->format("H:i:s"));
    
    foreach($p->getTache()->getIntervention() as $inter) {
   if ($inter->getLeUser()==$user){
    $ajout= $conv->duree_to_secondes($inter->getDuree()->format("H:i:s"));
    $tot = $tot + $ajout;

   }
        if ($duree >= $tot ){
        $etat="Dans les temps";}
        else
        $etat ="Hors délais";}
        return $etat;
}


public function bouclecouleurPl(Planning $p,User $user,ConversionH $conv){
    $tot=0;
    $duree= $conv->duree_to_secondes($p->getDureeEst()->format("H:i:s"));
    
    foreach($p->getTache()->getIntervention() as $inter) {
   if ($inter->getLeUser()==$user){
    $ajout= $conv->duree_to_secondes($inter->getDuree()->format("H:i:s"));
    $tot = $tot + $ajout;

   }
        if ($duree >= $tot ){
        $etat="#61DA3A";}
        else
        $etat ="#DA2424";}
        return $etat;
}



    public function verifVide(FamilleTache $unefamille, ConversionH $conv){
    $bool1=false;

    foreach($unefamille->getTaches() as $unetache){
        if($unetache->getFini()==false){
            $bool1=true;}}
    

        return $bool1;
    }

    public function verifVideP(Projet $projet, ConversionH $conv){
        $bool1=false;
    
        foreach($projet->getFamilles() as $unefamille){
            if($unefamille){
                foreach( $unefamille->getTaches() as $unetache){
                    if($unetache && $unetache->getFini()==false){
                        $bool1=true;
                    }
                }}
            }   
    
            return $bool1;
        }

    public function verifFiniF(FamilleTache $famille){
        $bool1=true;
        foreach( $famille->getTaches() as $unetache){
            if($unetache->getFini()==false){
                $bool1=false;
            }
        }        
        return $bool1;
    }

    public function verifFini(Taches $unetache, ConversionH $conv){
        $bool1=false;
        if($unetache->getFini()==false){
                $bool1=true;
        }  
            return $bool1;
    }

    public function dureeEstP($projets, ConversionH $conv){
        $arry_dureeEst=array();
        
        foreach($projets as $unProjet){
            $dureeEst=0;
            $familles=$unProjet->getFamilles();
            foreach($familles as $uneFamille){
                if($uneFamille->getNom()!="Autres_".$unProjet->getNom()){
                    $taches=$uneFamille->getTaches();
                    foreach($taches as $uneTache){
                        $dureeEst+=$conv->duree_to_secondes($uneTache->getDureeEst());
                        array_push($arry_dureeEst,$conv->secondes_to_duree($dureeEst));
                    }
                }
                else{
                    array_push($arry_dureeEst,"00:00:00");
                }
            }
        }
        return($arry_dureeEst);
    }
    public function dureeRelP($projets, ConversionH $conv){
        $arry_dureeRel=array();
        foreach($projets as $unProjet){
            $dureeRel=0;
            $familles=$unProjet->getFamilles();
            
            foreach($familles as $uneFamille){
                if($uneFamille->getNom()!="Autres_".$unProjet->getNom()){
                    $taches=$uneFamille->getTaches();
                    foreach($taches as $uneTache){
                        $dureeRel+=$conv->duree_to_secondes($uneTache->getDureeRel());
                        array_push($arry_dureeRel,$conv->secondes_to_duree($dureeRel));
                    }
                }
                else{
                    array_push($arry_dureeRel,"00:00:00");
                }
            }
            
        }
        
        return($arry_dureeRel);
    }

}