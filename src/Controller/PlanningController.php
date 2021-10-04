<?php

namespace App\Controller;

use DateTime;
use App\Entity\User;
use App\Entity\Projet;
use App\Entity\FamilleTache;
use App\Entity\Taches;
use App\Entity\Intervention;
use App\Form\InterventionFormType;
use App\Form\InterSearchFormType;
use App\Form\TacheFormType;
use App\Service\ConversionH;
use App\Service\Boucle;
use App\Entity\Planning;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\Raccourci;

//Controlleur pour administrer les plannings des ouvriers
class PlanningController extends AbstractController
{  
    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/planning/{idPl}/selection_utilisateur/{modif}", name="listUtilisateurs")
     */
    public function listuser($modif, $idPl): Response
    {        
        $Users=$this->getDoctrine()->getRepository(User::class)->findTech();
        return $this->render('planning/SelectUser.html.twig', [
           'users'=> $Users,
           'modif'=>$modif,
           'idPl'=>$idPl
        ]);      
    }
    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/Planning/{idPl}/utilisateur/{idU}/selection_projet/{modif}", name="choixProjet")
     */
    public function Choixprojet($idU,Boucle $bcl, ConversionH $conv, $modif, $idPl): Response
    {
        $projets=$this->getDoctrine()->getRepository(Projet::class)->findWhere();
        $user=$this->getDoctrine()->getRepository(User::class)->findOne($idU);

        $listprojets=[];
        foreach($projets as $unProjet){
            if( $bcl->verifVideP($unProjet, $conv)){
                array_push($listprojets,$unProjet);
            }
        }

        return $this->render('planning/choix_projet.html.twig', [
            'projets'=> $listprojets,
            'idU'=>$idU,
            'user'=>$user,
            'modif'=>$modif,
            'idPl'=>$idPl
        ]);        
    }

    /**
     * @IsGranted("ROLE_CHANTIER")
     * @Route("/utilisateur/{idU}/selection_projets/", name="choixProjets")
     */
    public function Choixpro($idU,Boucle $bcl, ConversionH $conv): Response
    {
        $date= new DateTime;
        $user=$this->getDoctrine()->getRepository(User::class)->findOne($idU);
        $user1=$this->getUser();
        if(($user1->getRoles()[0]=="ROLE_CHANTIER" && $user==$user1->getUser())||$user1->getRoles()[0]!="ROLE_CHANTIER"){ 
        $PTaches=$this->getDoctrine()->getRepository(Planning::class)->findSemaineUser(intval($date->format("W"))+1,$idU);
        $message="";
        $listprojets=[];
        $dureeRel=[];
        $dureeEst=[];

        if(empty($PTaches)){
            $message='Aucun projet n\'a été affecté à ce technicien';
        }
        else{
            foreach($PTaches as $PT){
            $unProjet=$PT->getTache()->getFamille()->getIdProjet();
                if( $bcl->verifVideP($unProjet, $conv) && in_array($unProjet,$listprojets)==false){
                    array_push($listprojets,$unProjet);
                }
        
            }
            $dureeRel=$bcl->dureeRelP($listprojets, $conv);
            $dureeEst=$bcl->dureeEstP($listprojets, $conv);
        }
        

        return $this->render('intervention/choix_projet.html.twig', [
            'projets'=> $listprojets,
            'idU'=>$idU,
            'user'=>$user,
            'message'=>$message,
            'dureeRel'=>$dureeRel,
            'dureeEst'=>$dureeEst

        ]); 
    }
        else{
            return $this->RedirectToRoute('choixProjets',array( 'idU'=>$user1->getUser()->getId()));
        }    
    }
    
    /**
     * @IsGranted("ROLE_CHANTIER")
     * @Route("/utilisateur/{idU}/selection_projet/{idP}/selection_taches", name="choixTaches")
     */
    public function ChoixT($idU,Boucle $bcl, ConversionH $conv, Raccourci $rac, $idP): Response
    {   
        $date=new DateTime();
        $user=$this->getDoctrine()->getRepository(User::class)->findOne($idU);
        $user1=$this->getUser();
        if(($user1->getRoles()[0]=="ROLE_CHANTIER" && $user==$user1->getUser())||$user1->getRoles()[0]!="ROLE_CHANTIER"){ 

        $PTaches=$this->getDoctrine()->getRepository(Planning::class)->findSemaineUser(intval($date->format("W"))+1,$idU);
        $projet=$this->getDoctrine()->getRepository(Projet::class)->findOne($idP);

        $taches=$rac->getTaches($projet);

        $idPl="n";
        $listtaches=[];

        foreach($PTaches as $PT){
            foreach($taches as $uneTache ){
                if( $PT->getTache()==$uneTache  && in_array($uneTache,$listtaches)==false && $uneTache->getFini()==false){
                    array_push($listtaches,$uneTache);
                }
            }
        }
        

        return $this->render('intervention/choix_tache.html.twig', [
            'taches'=> $listtaches,
            'idU'=>$idU,
            'idP'=>$idP,
            'user'=>$user,
            'projet'=>$projet,

        ]);  }
        else{
            return $this->RedirectToRoute('choixTaches',array( 'idU'=>$user1->getUser()->getId(), 'idP'=>$idP));
        }          
    }

    


    /**
      * @IsGranted("ROLE_ADMIN")
     *  @Route("/planning/ajout/{idU}/{idT}", name="ajoutPlanning")
     */
    public function ajout($idU, $idT, ConversionH $conv, Boucle $bcl, PaginatorInterface $paginator, Request $request){

        $manager=$this->getDoctrine()->getManager();
        $dt=new \DateTime();
        $planning=new Planning();

        $repU=$this->getDoctrine()->getRepository(User::class);
        $repT=$this->getDoctrine()->getRepository(Taches::class);

        $user=$repU->findOne($idU);
        $tache=$repT->findOne($idT);
        $planning->setUser($user);
        $planning->setTache($tache);
        $sem=intval($dt->format("W"))+1;
        $planning->setSemaine($sem);
        $manager->persist($planning);
        $manager->flush();
        $plannings=$this->getDoctrine()->getRepository(Planning::class)->findUser($idU);
        
        $dureeRel=array();
        $couleurT=[];
        $etatT=[];
        $somme=0;

        foreach($plannings as $unPlanning){
            $Interventions=$unPlanning->getTache()->getIntervention();
            foreach($Interventions as $uneIntervention){
                $somme=  $somme + $conv->duree_to_secondes($uneIntervention->getDuree()->format('H:i:s'));
            }
            array_push($dureeRel, $conv->secondes_to_duree($somme));
       
            if(count($Interventions)==0){
             array_push($couleurT, "#DDDDDD");
             array_push($etatT, "Pas d'interventions");}
             elseif($unPlanning->getTache()->getFini()){
               array_push($couleurT, "white");
               array_push($etatT, "Tâche finie");
             }
             else{
              $etat=$bcl->boucleetatPl($unPlanning, $user, $conv);
              array_push($etatT, $etat);
              $color=$bcl->bouclecouleurPl($unPlanning,$user,  $conv);
              array_push($couleurT, $color);}
           
       
            }

            $plan = $paginator->paginate(
                $plannings, // Requête contenant les données à paginer 
                $request->query->getInt('page', 1), // Numéro de la page en cours, passé dans l'URL, 1 si aucune page
                25// Nombre de résultats par page
            );

        return $this->render('planning/preparation.html.twig', [
            'plannings' => $plannings,
            'etats'=>$etatT,
            'couleurs'=>$couleurT,
            'plannningPage'=>$plan
        ]);
    }
     /**
      * @IsGranted("ROLE_ADMIN")
     *  @Route("planning/voir/", name="voirPlanning")
     */
    public function voir(ConversionH $conv, Boucle $bcl, PaginatorInterface $paginator, Request $request){
        
        $manager=$this->getDoctrine()->getManager();


        $plannings=$this->getDoctrine()->getRepository(Planning::class)->findAll();
        $dureeRel=[];
        $couleurT=[];
        $etatT=[];
       
        $tot=[];
      
        foreach($plannings as $planning){ 
        $somme=0;
         $unetache=$planning->getTache();
            $Interventions=$unetache->getIntervention();
            foreach($Interventions as $uneIntervention){
                
                if($uneIntervention->getLeUser()==$planning->getUser()){
                $somme=  $somme + $conv->duree_to_secondes($uneIntervention->getDuree()->format('H:i:s'));
                }
            }
            array_push($tot, $conv->secondes_to_duree($somme));
     
       
            if(count($unetache->getIntervention())==0){
             array_push($couleurT, "#DDDDDD");
             array_push($etatT, "Pas d'interventions");}
             elseif($unetache->getFini()){
               array_push($couleurT, "white");
               array_push($etatT, "Tâche finie");
             }
             else{
              $etat=$bcl->boucleetatPl($planning, $planning->getUser(), $conv);
              array_push($etatT, $etat);
              $color=$bcl->bouclecouleurPl($planning, $planning->getUser(), $conv);
              array_push($couleurT, $color);}
           
       
            }
            $plan = $paginator->paginate(
                $plannings, // Requête contenant les données à paginer 
                $request->query->getInt('page', 1), // Numéro de la page en cours, passé dans l'URL, 1 si aucune page
                25// Nombre de résultats par page
            );

        return $this->render('planning/preparation.html.twig', [
            'plannings' => $plannings,
            'etats'=>$etatT,
            'couleurs'=>$couleurT,
            'plannningPage'=>$plan,
            'total'=>$tot
        ]);
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     *  @Route("planning/voirUser/{idU}", name="observerPlanning")
     */
    public function observer(ConversionH $conv, Boucle $bcl, $idU, PaginatorInterface $paginator, Request $request){
        
        $manager=$this->getDoctrine()->getManager();
        $user=$this->getDoctrine()->getRepository(User::class)->findOne($idU);
        $plannings=$this->getDoctrine()->getRepository(Planning::class)->findUser($idU);
        $dureeRel=[];
        $couleurT=[];
        $etatT=[];
       
        $tot=[]; 
        $tab=array_keys($_GET);
        $dt=new \DateTime();
        $sem=intval($dt->format("W"))+1;

        foreach($tab as $int){

            
            $tache=$this->getDoctrine()->getRepository(Taches::class)->findOne($int);   
            $testPlanning=$this->getDoctrine()->getRepository(Planning::class)->findWhere($idU, $int, $sem);
            if($testPlanning==null && $int != "checkall" && $tache!=null){
                
                        
               
                if($_GET[$int]=="on"){
                    $planning= new Planning();
                    $planning->setSemaine($sem);
                    $planning->setUser($user);
                    $dt= new \DateTime("0".$_GET[$tache->getId()."T"].":00:00");
                    $planning->setDureeEst($dt);
                    $planning->setTache($tache);          
                    $manager->persist($planning);
                    $manager->flush();
                    array_push($plannings, $planning);
                }
                
                
            }           
        }
      
      
        foreach($plannings as $planning){
             $somme=0;
         $unetache=$planning->getTache();
            $Interventions=$unetache->getIntervention();
            foreach($Interventions as $uneIntervention){
                if($uneIntervention->getLeUser()==$planning->getUser()){
                    $somme=  $somme + $conv->duree_to_secondes($uneIntervention->getDuree()->format('H:i:s'));
                    }
            }
            array_push($dureeRel, $conv->secondes_to_duree($somme));
       
            if(count($unetache->getIntervention())==0){
             array_push($couleurT, "#DDDDDD");
             array_push($etatT, "Pas d'interventions");}
             elseif($unetache->getFini()){
               array_push($couleurT, "white");
               array_push($etatT, "Tâche finie");
             }
             else{
              $etat=$bcl->boucleetatPl($planning,$user, $conv);
              array_push($etatT, $etat);
              $color=$bcl->bouclecouleurPl($planning,$user, $conv);
              array_push($couleurT, $color);}
           
       
            }

            $plan = $paginator->paginate(
                $plannings, // Requête contenant les données à paginer 
                $request->query->getInt('page', 1), // Numéro de la page en cours, passé dans l'URL, 1 si aucune page
                25// Nombre de résultats par page
            );

        return $this->render('planning/preparation.html.twig', [
            
            'etats'=>$etatT,
            'couleurs'=>$couleurT,
            'user'=>$user,
            'plannningPage'=>$plan,
            'total'=>$dureeRel,
        
        ]);
    }
    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/planning/supprimer/{id}", name="supprimerPlanning")
     */
    public function deletePl($id): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        
        $planning = $entityManager->getRepository(Planning::class)->find($id);
        $idU=$planning->getUser()->getId();
        $entityManager->remove($planning);
        $entityManager->flush();

        return $this->redirectToRoute("observerPlanning", array('idU'=>$idU));
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/planning/modifier/{idPl}/utilisateur/{idU}/tache/{idT}", name="modifierPlanning")
     */
    public function modifPl($idPl, $idU, $idT): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $planning = $entityManager->getRepository(Planning::class)->findOne($idPl);
        if($idU!= $planning->getUser()->getId()){
            $user=$entityManager->getRepository(User::class)->findOne($idU);
            $planning->setUser($user);
        }
        if($idT!=$planning->getTache()->getId()){
            $tache=$entityManager->getRepository(Taches
            ::class)->findOne($idT);
            $planning->setTache($tache);
        }
        $entityManager->flush();

        return $this->redirectToRoute("observerPlanning", array('idU'=>$idU));
    }


    

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/planning/{idPl}/utilisateur/{idU}/selection_tache/{modif}", name="choixTacheV2")
     */
    public function selectTacheV2($idU, $modif, $idPl,PaginatorInterface $paginator, Request $request): Response
    {
        $manager=$this->getDoctrine()->getManager();

        $user=$manager->getRepository(User::class)->findOne($idU);
        $projet=$manager->getRepository(Projet::class)->findAll();
        
        $array_tache=[];
        foreach($projet as $unProjet){
            $familles=$unProjet->getFamilles();
            foreach($familles as $uneFamille){
                $taches=$uneFamille->getTaches();
                foreach($taches as $uneTache){
                    array_push($array_tache, $uneTache);
                }
            }
        }
       

        $plan = $paginator->paginate(
            $array_tache, // Requête contenant les données à paginer 
            $request->query->getInt('page', 1), // Numéro de la page en cours, passé dans l'URL, 1 si aucune page
            25// Nombre de résultats par page
        );

        return $this->render("planning/choix_tacheV2.html.twig",[   
            'idU'=>$idU,
            'taches'=>$array_tache,
            'user'=>$user,
            'modif'=>$modif,
            'idPl'=>$idPl
            
        ]);
    }

    
}
