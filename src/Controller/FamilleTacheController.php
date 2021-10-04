<?php

namespace App\Controller;

use DateTime;
use App\Entity\Taches;
use App\Entity\FamilleTache;
use App\Entity\Projet;
use App\Entity\User;
use App\Form\Projet2FormType;
use App\Form\FamilleTacheFormType;
use App\Form\FamilleTache2FormType;
use App\Form\ImportationFormType;
use App\Form\TacheFormType;
use App\Form\ProjetFormType;
use App\Service\ConversionH;
use App\Service\Boucle;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
//Controlleur pour administrer les familles de tâche
class FamilleTacheController extends AbstractController
{
   
    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/projet/{idP}/famille", name="ajoutFT")
     */
    public function assocFP(Request $request,$idP, ConversionH $conv, Boucle $bcl): Response
    {   
        $manager=$this->getDoctrine()->getManager();
        $repP=$this->getDoctrine()->getRepository(Projet::class);
        $rep=$this->getDoctrine()->getRepository(FamilleTache::class);

        $projet=$repP->findOne($idP);
        $familles=$rep->OrderASCNull($idP);
        $familleTache=new FamilleTache();

        $formFamille=$this->createForm(FamilleTacheFormType::Class, $familleTache);
        $formFamille->handleRequest($request);
        $formP=$this->createForm(Projet2FormType::class, $projet);
        $formP->handleRequest($request);

        $couleurF=[];
        $etatF=[];

      foreach($familles as $unefamille){ 
        if(!$this->getDoctrine()->getRepository(Taches::class)->OrderASCNull($unefamille))
        {
        array_push($couleurF, "#DDDDDD");
        array_push($etatF, 'Pas de tâches');
        }
        elseif($bcl->verifVide($unefamille, $conv)==false){
            array_push($couleurF, "white");
            array_push($etatF, "Famille finie");
        }
        elseif($unefamille->getNom()=="Autres_".$projet->getNom()){
            array_push($couleurF, "#61DA3A");
            array_push($etatF, "Dans les temps");
        }
        else{
        $color=$bcl->bouclecouleurF($unefamille, $conv);
        array_push($couleurF, $color);
        $etat=$bcl->boucleetatF($unefamille, $conv);
        array_push($etatF, $etat);

        }}

        
        $listProjet=array();

        if($formP->isSubmitted() && ($formP->isValid()) ){
            $manager->flush();
            return $this->redirectToRoute("ajoutFT",array('idP' => $idP));
        }
    
        if($formFamille->isSubmitted() && ($formFamille->isValid())){

      
            $familleTache->setIdProjet($projet);
            $manager->persist($familleTache);
            $manager->flush();
            return  $this->redirectToRoute('ajouterTacheAssos',array('idP' => $idP,'idF'=>$familleTache->getId()));           
        }     

        return $this->render('famille_tache/projetFamille.html.twig', [
            'title_projet'=>'Modifier le projet',
            "form_projet"=>$formP->createView(),
            'title_famille'=>'Ajouter une famille de tâches',
            "form_famille"=>$formFamille->createView(),
            'idP'=>$idP,
            'projet'=>$projet,
            'famille_tache'=>$familleTache, 
            'familles'=>$familles,
            'couleurF'=>$couleurF,
            'etatF'=>$etatF,
            
        ]);
    }
    

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/familleTache/modifier/{idP}/{idF}", name="updateFM")
     */
    public function update(Request $request, $idP, int $idF, ConversionH $conv, Boucle $bcl): Response
    { 
        $manager=$this->getDoctrine()->getManager();
        $repP=$this->getDoctrine()->getRepository(Projet::class);
        $repF=$this->getDoctrine()->getRepository(FamilleTache::class);
        $repT=$this->getDoctrine()->getRepository(Taches::class);
        $repU=$this->getDoctrine()->getRepository(User::class);
       
        $projet=$repP->findOne($idP);
        $familleTache=$repF->findOne($idF);
        $Tache=new Taches();

        $users=$repU->findAll();
        $familles=$repF->OrderASCNull($idP);
        $taches=$repT->findAOF($familleTache);
       
        $formP=$this->createForm(Projet2FormType::class, $projet);
        $formP->handleRequest($request);
        $formF=$this->createForm(FamilleTache2FormType::class, $familleTache);
        $formF->handleRequest($request);
        $formT=$this->createForm(TacheFormType::Class, $Tache);
        $formT->handleRequest($request);
             
        $couleurF=[];
        $etatF=[];
        $couleurT=[];
        $etatT=[];
        $dureeRel=[];
        $somme=0;
    

        foreach($familles as $unefamille){
            if(!$repT->OrderASCNull($unefamille))
            {
             array_push($couleurF, "#DDDDDD");
             array_push($etatF, 'Pas de tâches');
            }
            elseif($bcl->verifVide($unefamille, $conv)==false){
                array_push($couleurF, "white");
                array_push($etatF, "Famille finie");
            }
            elseif($unefamille->getNom()=="Autres_".$projet->getNom()){
                array_push($couleurF, "#61DA3A");
                array_push($etatF, "Dans les temps");
            }          
            else{
             $couleur=$bcl->bouclecouleurF($unefamille, $conv);
             array_push($couleurF, $couleur);
             $state=$bcl->boucleetatF($unefamille, $conv);
             array_push($etatF, $state);}
            }
    
        foreach($taches as $unetache){
         foreach($unetache->getIntervention() as $uneIntervention){
             $somme=  $somme + $conv->duree_to_secondes($uneIntervention->getDuree()->format('H:i:s'));
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
           $etat=$bcl->boucleetatT($unetache, $conv);
           array_push($etatT, $etat);
           $color=$bcl->bouclecouleurT($unetache, $conv);
           array_push($couleurT, $color);}
        }

        if($formP->isSubmitted() && ($formP->isValid()) ){
         $manager->flush();
         return $this->redirectToRoute("ajoutFT",array('idP' => $idP));
        }
        
        if($formF->isSubmitted() && $formF->isValid()){
         $manager->flush();
         return $this->redirectToRoute('ajouterTacheAssos',array('idP' => $idP,'idF'=>$idF));
        }
              
        if($formT->isSubmitted() && $formT->isValid()){
            
            if($_POST['mn']==0){
                $mn="00";
            }
            else{
                $mn="30";
            }
            $Tache->setDureeEst($_POST['h'].":".$mn.":00");
            
            $Tache->setDureeRel("00:00:00");
            $Tache->setFamille($familleTache);
            $Tache->setFini(false);
            $Tache->setNom($formT->get('nom')->getData());
            $manager->persist($Tache);
            $manager->flush();
            return $this->redirectToRoute('ajouterTacheAssos',array('idP' => $idP,'idF'=>$idF));
        }


    return $this->render('tache/association_TacheFamille.html.twig', [    
        'idP'=>$idP,
        'idF'=>$idF,
        'projet'=>$projet,
        'famille'=>$familleTache,
        'familles'=>$familles,
        'taches'=>$taches,
        'users'=>$users,       
        'dureeRel'=>$dureeRel,       
        'title_projet'=>'Modifier le projet',
        'form_projet'=>$formP->createView(),
        'title_famille'=>'Modifier une famille de tâches',
        'form_famille'=>$formF->createView(),
        'title_tache'=>'Ajouter une tâche',
        'form_tache'=>$formT->createView(),
        'couleurF'=>$couleurF,
        'etatF'=>$etatF,
        'couleurT'=>$couleurT,
        'etatT'=>$etatT,
    ]);
}

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/familleTache/supprimer/{idP}/{idF}", name="supprimerFM")
     */
    public function supprimer($idP,$idF): Response
    {
        $manager=$this->getDoctrine()->getManager();
        $familleTache=$manager->getRepository(FamilleTache::class)->findOne($idF);

        $manager->remove($familleTache);
        $manager->flush();

        return $this->redirectToRoute("ajoutFT",array('idP' => $idP));
    }

     /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/familleTache/importer/{idP}", name="importer")
     */
    public function import(Request $request, $idP, ConversionH $conv, Boucle $bcl): Response
    {
        $manager=$this->getDoctrine()->getManager();
        $repP=$this->getDoctrine()->getRepository(Projet::class);
        $repF=$this->getDoctrine()->getRepository(FamilleTache::class);
        $repT=$this->getDoctrine()->getRepository(Taches::class);
        $repU=$this->getDoctrine()->getRepository(User::class);
        
        $projet=$repP->findOne($idP);
        $familleTache=new FamilleTache();

        $familles=$repF->OrderASCNull($idP);
        $users=$repU->findAll();
        
        $formI=$this->createForm(ImportationFormType::class);
        $formI->handleRequest($request);
        $file=$formI->get('excel')->getData();
        $formF=$this->createForm(FamilleTacheFormType::Class, $familleTache);
        $formF->handleRequest($request);
        $formP=$this->createForm(Projet2FormType::class, $projet);
        $formP->handleRequest($request);;

        $couleurF=[];
        $etatF=[];
        

       
        foreach($familles as $unefamille){
         if(!$repT->OrderASCNull($unefamille))
         {
          array_push($couleurF, "#DDDDDD");
          array_push($etatF, 'Pas de tâches');
         }
         elseif($bcl->verifVide($unefamille, $conv)==false){
            array_push($couleurF, "white");
            array_push($etatF, "Famille finie");
        }
        elseif($unefamille->getNom()=="Autres_".$projet->getNom()){
            array_push($couleurF, "#61DA3A");
            array_push($etatF, "Dans les temps");
        }   
         else{
          $couleur=$bcl->bouclecouleurF($unefamille, $conv);
          array_push($couleurF, $couleur);
          $state=$bcl->boucleetatF($unefamille, $conv);
          array_push($etatF, $state);}
         }

         
        if($formP->isSubmitted() && ($formP->isValid()) ){
            $manager->flush();
            return $this->redirectToRoute("ajoutFT",array('idP' => $idP));
           }
           
        if($formF->isSubmitted() && $formF->isValid()){
            $manager->flush();
            return $this->redirectToRoute("ajoutFT",array('idP' => $idP));
           }

        if($formI->isSubmitted() && $formI->isValid()){
            $manager->flush();
            $fileName =$file->getClientOriginalName();
            $file->move(
                $this->getParameter('photo_directory').'import/',
            $fileName);

            return $this->redirectToRoute('importTache',array('idP'=>$idP, 'nom'=>$fileName));
        }

        return $this->render('famille_tache/importation.html.twig', [
            'idP'=>$idP,
            'projet'=>$projet,
            'famille'=>$familleTache,
            'familles'=>$familles,
            'users'=>$users,
            'import_title'=>'Sélectionner le fichier à importer',
            "form_importation"=>$formI->createView(),
            'title_projet'=>'Modifier le projet',
            "form_projet"=>$formP->createView(),
            'title_famille'=>'Ajouter une famille de tâches',
            "form_famille"=>$formF->createView(),
            'couleurF'=>$couleurF,
            'etatF'=>$etatF,
            
       ]);
    }   
}
