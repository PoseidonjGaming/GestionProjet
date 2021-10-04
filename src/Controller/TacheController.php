<?php

namespace App\Controller;

use DateTime;
use App\Entity\Taches;
use App\Entity\Projet;
use App\Entity\FamilleTache;
use App\Entity\Intervention;
use App\Form\FamilleTache2FormType;
use App\Form\FamilleTacheFormType;
use App\Form\TacheFormType;
use App\Form\Tache2FormType;
use App\Form\Projet2FormType;
use App\Form\ProjetFormType;
use App\Form\CheckboxFormType;
use App\Service\Boucle;
use App\Service\ConversionH;
use PhpOffice\PhpSpreadsheet\Reader\Ods;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;



class TacheController extends AbstractController
{
    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/projet/{idP}/famille/{idF}/tache" ,name="ajouterTacheAssos")
     */
    public function insertAsso(Request $request, $idP, $idF, ConversionH $conv,Boucle $bcl): Response
    {        
        $manager=$this->getDoctrine()->getManager();
        $repP=$this->getDoctrine()->getRepository(Projet::class);
        $repF=$this->getDoctrine()->getRepository(FamilleTache::class);
        $repT=$this->getDoctrine()->getRepository(Taches::class);
        
        $projet=$repP->findOne($idP);
        $famille=$repF->findOne($idF);
        $familles=$repF->OrderASCNull($idP);
        $Tache=new Taches();
        $taches=$repT->findAOF($famille);

        $formP=$this->createForm(Projet2FormType::class, $projet);
        $formP->handleRequest($request);
        $formFamille2=$this->createForm(FamilleTache2FormType::Class, $famille);
        $formFamille2->handleRequest($request);
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
            elseif($unefamille->getNom()=="Autres_".$projet->getNom()){
                array_push($couleurF, "#61DA3A");
                array_push($etatF, "Dans les temps");
            }
            elseif($bcl->verifVide($unefamille, $conv)==false){
                array_push($couleurF, "white");
                array_push($etatF, "Famille finie");}
            else{
             $couleur=$bcl->bouclecouleurF($unefamille, $conv);
             array_push($couleurF, $couleur);
             $state=$bcl->boucleetatF($unefamille, $conv);
             array_push($etatF, $state);}
            }
    
        foreach($taches as $unetache){
            
         foreach($unetache->getIntervention() as $uneIntervention){
           
             $somme=  $somme + $conv->duree_to_secondes($uneIntervention->getDuree()->format('%H:%I:%S'));
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

        if($formFamille2->isSubmitted() && $formFamille2->isValid()){
            
            return $this->redirectToRoute('ajoutFT',array ('idP'=>$idP));
        }

        if($formT->isSubmitted() && $formT->isValid()){
            $Tache->setFamille($famille);
            if($_POST['mn']==0){
                $mn="00";
            }
            else{
                $mn="30";
            }
  
            $Tache->setDureeEst($_POST['h'].":".$mn.":00");
            $Tache->setDureeRel("00:00:00");
            $Tache->setFini(false);
            $Tache->setNom($formT->get('nom')->getData());
            $manager->persist($Tache);
            $manager->flush();
 
            return $this->redirectToRoute('ajouterTacheAssos', [ 'idP'=>$idP,'idF'=>$idF,]
                
        );
        }
    
        
        return $this->render('tache/association_TacheFamille.html.twig', [
            'idP'=>$idP,
            'idF'=>$idF,
            'projet'=>$projet,
            'famille'=>$famille,
            'familles'=>$familles,
            'tache'=>$Tache,
            'taches'=>$taches,
            'title_projet'=>'Modifier le projet',
            "form_projet"=>$formP->createView(),         
            'title_famille'=>'Modifier une famille de tâches',
            "form_famille"=>$formFamille2->createView(),
            'title_tache'=>'Ajouter une tâche',
            'form_tache'=>$formT->createView(),
            'couleurF'=>$couleurF,
            'etatF'=>$etatF,
            'couleurT'=>$couleurT,
            'etatT'=>$etatT,
            'dureeRel'=>$dureeRel
        ]);
 }
   
    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/projet/{idP}/famille/{idF}/ModifierTache/{id}", name="modifierTache")
     */
    public function modifytache(Request $request, $idP, $idF, $id, ConversionH $conv,Boucle $bcl): Response
    {

      $entityManager = $this->getDoctrine()->getManager();
      $repP=$this->getDoctrine()->getRepository(Projet::class);
      $repF=$this->getDoctrine()->getRepository(FamilleTache::class);
      $repT=$this->getDoctrine()->getRepository(Taches::class);
      $repI=$this->getDoctrine()->getRepository(Intervention::class);
 
      $projet=$repP->findOne($idP);
      $famille=$repF->findOne($idF);
      $familles=$repF->OrderASCNull($idP);
      $tache = $repT->find($id);
      $Tache=new Taches();
      $taches=$repT->findAOF($famille);
      $Interventions=$repI->findAll();
 
      $formP=$this->createForm(Projet2FormType::class, $projet);
      $formP->handleRequest($request);
      $formFamille2=$this->createForm(FamilleTache2FormType::Class, $famille);
      $formFamille2->handleRequest($request);
      $formT = $this->createForm(Tache2FormType::class, $tache);
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
         $somme=  $somme + $conv->duree_to_secondes($uneIntervention->getDuree()->format('%H:%I:%S'));
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
        $entityManager->flush();
        return $this->redirectToRoute("ajoutFT",array('idP' => $idP));
    }

      if($formT->isSubmitted() && $formT->isValid())
      {
          
          if($_POST['mn']==0){
              $mn="00";
          }
          else{
              $mn="30";
          }

        $tache->setDureeEst($_POST['h'].":".$mn.":00");
        dump( $Tache->getDureeEst());
        $tache->setNom($formT->get('nom')->getData());
        $entityManager->persist($tache);
        $entityManager->flush();
        return $this->redirectToRoute("ajouterTacheAssos",array('idP' => $idP,'idF'=>$idF));
        


      }
      else{
            return $this->render("tache/association_TacheFamille.html.twig", [
             'idP'=>$idP,
             'idF'=>$idF,
             'projet'=>$projet,
             'famille'=>$famille,
             'familles'=>$familles,
             'taches'=>$taches,
             'title_projet'=>'Modifier le projet',
             'form_projet'=>$formP->createView(),
             'title_famille'=>'Modifier une famille de tâches',
             'form_famille'=>$formFamille2->createView(),
             "title_tache" => "Modifier une tache",
             "form_tache" => $formT->createView(),
             'couleurF'=>$couleurF,
             'etatF'=>$etatF,
             'couleurT'=>$couleurT,
             'etatT'=>$etatT,
            ]);
        }
      
    }

    /**
     *@IsGranted("ROLE_ADMIN")
     * @Route("/projet/{idP}/famille/{idF}/delete-tache/{id}", name="supprimerTache")
     */
    public function deletetache($idP, $idF, $id): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $tache = $entityManager->getRepository(Taches::class)->find($id);
        $entityManager->remove($tache);
        $entityManager->flush();

        return $this->redirectToRoute("ajouterTacheAssos",array('idP' => $idP,'idF'=>$idF));
    }


    /**
     *@IsGranted("ROLE_ADMIN")
     * @Route("/projet/{idP}/famille/{idF}/finir-tache/{id}/{rep}", name="FinirTache")
     */
    public function finirtache($idP, $idF, $id, $rep, Boucle $bcl): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $tache = $entityManager->getRepository(Taches::class)->find($id);
        $famille=$tache->getFamille();

        if($rep=="oui"){
            $tache->setFini(true);
            
        }
        else{
            $tache->setFini(false);
        }
        
        if($bcl->verifFiniF($famille)){
            $famille->setEtat('Terminée');
                
        }
        else{
            $famille->setEtat('En cours');
        }
        $entityManager->persist($famille);
        $entityManager->persist($tache);
        $entityManager->flush();

        return $this->redirectToRoute("ajouterTacheAssos",array('idP' => $idP,'idF'=>$idF));
    }
    /**
     *@IsGranted("ROLE_CHANTIER")
     * @Route("/finir_tache/{idT}/{rep}", name="FinirTacheUser")
     */
    public function finirtacheUser($idT, $rep, Boucle $bcl): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $tache = $entityManager->getRepository(Taches::class)->find($idT);
        $famille=$tache->getFamille();

        if($rep=="oui"){
            $tache->setFini(true);
        }
        else{
            $tache->setFini(false);
        }
        
        $etat=true;
        
        foreach($famille->getTaches() as $uneTache){
            $etatF=$uneTache->getFini();
            if($etatF==false){
                $etat=false;
            }
        }

        if($etat){
            $famille->setEtat('Terminée');
        }
        else{
            $famille->setEtat('En cours'); 
        }

        $entityManager->persist($tache);
        $entityManager->persist($famille);
        $entityManager->flush();

        return $this->redirectToRoute("listuser");
    }


    /**
     *@IsGranted("ROLE_ADMIN")
     * @Route("/projet/{idP}/import_tache/{nom}", name="importTache")
     */
    public function importtache($idP,$nom): Response
    {

        $array_nom=explode(".", $nom);
        if($array_nom[1]=="ods"){
            $reader = new Ods(); 
        }
        elseif($array_nom[1]=="xlsx"){
            $reader = new Xlsx();
        }
       
        $reader->setReadDataOnly(TRUE);
        $spreadsheet = $reader->load( $this->getParameter('photo_directory').'import/'.$nom);
        $worksheet = $spreadsheet->getActiveSheet();
    
        $highestRow = $worksheet->getHighestRow(); 
        $highestColumn = $worksheet->getHighestColumn(); 
        $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);

        $res = array();
        $titreCol=array();

        $repT=$this->getDoctrine()->getRepository(Taches::class);
        $repP=$this->getDoctrine()->getRepository(Projet::class);
        for($row=1; $row <= $highestRow; $row++){
            $tache=new Taches();
            for($col = 1; $col <= $highestColumnIndex; $col++){
                $value = $worksheet->getCellByColumnAndRow($col,$row)->getValue();
                
                if($value!=null){
                    if($row==1){
                        $titre=$value;
                        $titreCol[$value]=$col;
                    }                
                    else{
                        if($col==$titreCol['Durée estimée']){
                        $date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value);
                        $tache->setDureeEst($date->format("H:i:s"));
                        }
                
                
                        if($col==$titreCol['Famille de taches']){
                            $projet=$repP->findOne($idP);
                            $famille1=$this->getDoctrine()->getRepository(FamilleTache::class)->findNameProjet($value, $projet->getId());
                            $famille2=$this->getDoctrine()->getRepository(FamilleTache::class)->findNameProjet($value."_".$projet->getNom(), $projet->getId());
                            
                            if($famille1==null && $famille2==null){
                                $emFM=$this->getDoctrine()->getManager();
                                $Nfamille=new FamilleTache();
                                $Nfamille->setIdProjet($projet);
                                if($value=="Autres"){
                                    $Nfamille->setNom($value."_".$projet->getNom());
                                }
                                else{
                                    $Nfamille->setNom($value);
                                }
                                
                                $Nfamille->setEtat("en_cours");
                                $emFM->persist($Nfamille);
                                $emFM->flush();
                                $tache->setFamille($Nfamille);
                            }
                            else{
                                
                                if($famille1!=null){
                                   
                                    $tache->setFamille($famille1);
                                }
                                else{
                                    if($famille2!=null){
                                        $tache->setFamille($famille2);
                                    }
                                }
                                
                                
                                
                                
                            }              
                        }

                        if($col==$titreCol['Nom']){
                            $tache->setNom($value);
                        }
                        $tache->setFini(false);
                        $tache->setDureerel("00:00:00");

                     

                        if($col==$highestColumnIndex){
                            $emT=$this->getDoctrine()->getManager();
                            $emT->persist($tache);
                            $emT->flush();
                        }
                    }
                   
                    
                }
            }
        }
        if($nom !="Autres.xlsx"){unlink($this->getParameter('photo_directory')."import/".$nom);}
        
        return $this->redirectToRoute("ajoutFT",array('idP' => $idP));
        
    } 
    
    
}