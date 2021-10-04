<?php

namespace App\Controller;

use DateTime;
use App\Entity\User;
use App\Entity\Projet;
use App\Entity\FamilleTache;
use App\Entity\Taches;
use App\Entity\Intervention;
use App\Entity\Planning;
use App\Form\InterventionFormType;
use App\Form\InterSearchFormType;
use App\Form\TacheFormType;
use App\Service\ConversionH;
use App\Service\Boucle;
use Knp\Component\Pager\PaginatorInterface;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Routing\Annotation\Route;


class InterventionController extends AbstractController
{
   

    /**
     * @IsGranted("ROLE_CHEF_CHANTIER")
     * @Route("/selection_utilisateur", name="listuser")
     */
    public function listuser(): Response
    { 
       $projets=$this->getDoctrine()->getRepository(Projet::class)->findWhere();
       $Users=$this->getDoctrine()->getRepository(User::class)->findTech();
       return $this->render('intervention/choix_utilisateur.html.twig', [
           'users'=> $Users,
       ]);      
    }


    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/utilisateur/{idU}/projet/{idP}/selection_famille/", name="selectionFamille")
     */
    public function selectFamille($idU, $idP, Boucle $bcl, ConversionH $conv): Response
    {
        $manager=$this->getDoctrine()->getManager();
        
        $user=$manager->getRepository(User::class)->findOne($idU);
        $projet=$manager->getRepository(Projet::class)->findOne($idP);
        $familleTaches=$manager->getRepository(FamilleTache::class)->findWhere($idP);

        $familles=[];

        foreach($familleTaches as $uneFamille){
            if( $bcl->VerifVide($uneFamille, $conv)){
                array_push($familles,$uneFamille);
            }
        }
       
        return $this->render("intervention/choix_famille.html.twig",[   
            'idU'=>$idU,
            'idP'=>$idP,
            'familles'=>$familles,
            'user'=>$user, 
            'projet'=>$projet
        ]);
    }

    



    /**
     * @IsGranted("ROLE_CHANTIER")
     * @Route("/utilisateur/{idU}/projet/{idP}/famille/{idF}/tache/{idT}/ajout_intervention", name="ajouterIntervention")
     */
    public function addIntervention(Request $request, $idU, $idP, $idF, $idT, ConversionH $conv): Response
    {   
        $doctrine=$this->getDoctrine();
        $user=$doctrine->getRepository(User::class)->findOne($idU);
        $tache=$doctrine->getRepository(Taches::class)->findOne($idT);    
        if($tache->getFini()==false) {
        $user1=$this->getUser();
        if(($user1->getRoles()[0]=="ROLE_CHANTIER" && $user==$user1->getUser())||$user1->getRoles()[0]!="ROLE_CHANTIER"){
  
        $entityManager = $this->getDoctrine()->getManager();

        $Intervention = new Intervention();
        $form = $this->createForm(InterventionFormType::class,$Intervention);
        $form->handleRequest($request);
       
          
        $projet=$doctrine->getRepository(Projet::class)->findOne($idP);
        $famille=$doctrine->getRepository(FamilleTache::class)->findOne($idF);
        
        if($form->isSubmitted() && $form->isValid())
        {            
            $dureeRelTMP= ($conv->duree_to_secondes($tache->getDureeRel()) + $conv->duree_to_secondes($form->get('duree')->getData()->format('H:i:s')));
            $dureeRel=$conv->secondes_to_duree($dureeRelTMP);
            $tache->setDureeRel($dureeRel);
            $entityManager->persist($tache);
            $Intervention->setLeUser($user);
            $Intervention->setTache($tache);
            $entityManager->persist($Intervention);

            

            if(array_key_exists('finir', $_POST)){
                if($_POST['finir']=='on'){
                $tache->setFini(true);
                $entityManager->persist($tache);
                }
            }
            
           
            $entityManager->flush();

            
            
            return $this->redirectToRoute("ajouterIntervention", array('idU'=>$idU,'idP'=>$idP,'idF'=>$idF,'idT'=>$idT));
        }
        else{
            return $this->render("intervention/Intervention-form.html.twig", [
                "form_title" => "Ajouter une intervention",
                "form_Intervention" => $form->createView(),
                'Intervention'=>$Intervention,
                'idU'=>$idU,
                'idP'=>$idP,
                'idF'=>$idF,
                'user'=>$user,
                'projet'=>$projet,
                'famille'=>$famille,
                'tache'=>$tache,


            ]);
        } 
    }  else{
        return $this->RedirectToRoute('ajouterIntervention',array( 'idU'=>$user1->getUser()->getId(), 'idP'=>$idP, 'idF'=>$idF, 'idT'=>$idT));
     }
    }
    else{ return $this->RedirectToroute('listuser');}
        
    }

    /**
     * @IsGranted("ROLE_CHEF_CHANTIER")
     * @Route("/Interventions", name="Interventions")
     */
     public function Interventions(Request $request, PaginatorInterface $paginator,ConversionH $conv)
    {   
        
        $Interventions=new Intervention();
        $emI=$this->getDoctrine()->getRepository(Intervention::class);
        $form = $this->createForm(InterSearchFormType::class,$Interventions);
        $form->handleRequest($request); 
        
        if($form->get('Le_User')->getData()!=null){
            $user=$form->get('Le_User')->getData()->getId();
        }
        else{
            $user="null";
        }
        if($form->get('date')->getData()!=null){
            $date=$form->get('date')->getData()->format("Y-m-d");
        }
        else{
            $date="null";
        }
        if($form->get('Tache')->getData()!=null){
            $tache=$form->get('Tache')->getData()->getId();
            
        }
        else{
            $tache="null";
        }
        if($form->get('Famille')->getData()!=null){
            $famille=$form->get('Famille')->getData()->getId();
            
        }
        else{            
            
            $famille="null";
        }
        if($form->get('Projet')->getData()!=null){
            $projet=$form->get('Projet')->getData()->getId();
            
        }
        else{
            $projet="null";
        }
        
         
       
        $Interventions=$emI->search($user, $date, $tache, $famille, $projet);
        $duree=0;
        foreach($Interventions as $uneIntervention){
            $duree+= $conv->duree_to_secondes($uneIntervention->getDuree()->format("H:i:s"));
        }
        $dureeTot=$conv->secondes_to_duree($duree);
 
        $inter = $paginator->paginate(
            $Interventions, // Requête contenant les données à paginer 
            $request->query->getInt('page', 1), // Numéro de la page en cours, passé dans l'URL, 1 si aucune page
            25,
            [
                'defaultSortFieldName'      => 'date',
                'defaultSortDirection' => 'desc'
            ] // Nombre de résultats par page
        );

        
        return $this->render('intervention/Interventions.html.twig', [
            'InterventionPage'=>$inter,
            "Intervention"=>$Interventions,
            'form_interSearch'=>$form->createView(),
            'idU'=>$user,
            'date'=>$date,
            'idT'=>$tache,
            'idF'=>$famille,
            'idP'=>$projet,
            'dureeTot'=>$dureeTot       
    
        ]);
    }


     /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/Interventions/tache/{idT}", name="InterventionsT")
     */
    public function InterventionsTache(Request $request, PaginatorInterface $paginator,$idT, ConversionH $conv)
    {   
        $Interventions=new Intervention();
        $emI=$this->getDoctrine()->getRepository(Intervention::class);
        $form = $this->createForm(InterSearchFormType::class,$Interventions);
        $form->handleRequest($request);     
    
        if($form->get('Le_User')->getData()!=null){
            $user=$form->get('Le_User')->getData()->getId();
        }
        else{
            $user="null";
        }
        if($form->get('date')->getData()!=null){
            $date=$form->get('date')->getData()->format("Y-m-d");
        }
        else{
            $date="null";
        }
        if($form->get('Famille')->getData()!=null){
            $famille=$form->get('Famille')->getData()->getId();
            
        }
        else{
            $famille="null";
        }
        if($form->get('Projet')->getData()!=null){
            $projet=$form->get('Projet')->getData()->getId();
           
        }
        else{
            $projet="null";
        }

        $Interventions=$emI->search($user, $date,$idT, $famille,$projet);
        
        $duree=0;
        foreach($Interventions as $uneIntervention){
            $duree+= $conv->duree_to_secondes($uneIntervention->getDuree()->format("H:i:s"));
        }
        $duree=$conv->secondes_to_duree($duree);

        $inter = $paginator->paginate(
            $Interventions, // Requête contenant les données à paginer 
            $request->query->getInt('page', 1), // Numéro de la page en cours, passé dans l'URL, 1 si aucune page
            25,
            [
                'defaultSortFieldName'      => 'date',
                'defaultSortDirection' => 'desc'
            ] // Nombre de résultats par page
        );
        $listProjets=$this->getDoctrine()->getRepository(Projet::class)->findAll();
        
        return $this->render('intervention/Interventions.html.twig', [
            'InterventionPage'=>$inter,
            "Intervention"=>$Interventions,
            'form_interSearch'=>$form->createView(),
            'idU'=>$user,
            'date'=>$date,
            'idT'=>$idT,
            'idF'=>$famille,
            'idP'=>$projet,
            'dureeTot'=>$duree    

            
        ]);
    }

    


    

    /**
     * @IsGranted("ROLE_CHANTIER")
     * @Route("/Mesinterventions/{idU}", name="ListeInter")
     */
    public function ListeInter(Request $request, PaginatorInterface $paginator, $idU, ConversionH $conv)
    {  

        $user=$this->getUser();
	  if($user->getUser()){
        $Interventions=new Intervention();
        $emI=$this->getDoctrine()->getRepository(Intervention::class);
        $form = $this->createForm(InterSearchFormType::class,$Interventions);
        $form->handleRequest($request); 
       
        if($idU==$user->getUser()->getId()){
              
      

            if($form->get('date')->getData()!=null){
                $date=$form->get('date')->getData()->format("Y-m-d");
            }
            else{
                $date="null";
            }
            if($form->get('Tache')->getData()!=null){
                $tache=$form->get('Tache')->getData()->getId();
            }
            else{
                $tache="null";
            }
            if($form->get('Famille')->getData()!=null){
                $famille=$form->get('Famille')->getData()->getId();
            }
            else{
                $famille="null";
            }
            if($form->get('Projet')->getData()!=null){
                $projet=$form->get('Projet')->getData()->getId();
            }
            else{
                $projet="null";
            }

            $Interventions=$emI->search($idU,$date,$tache, $famille, $projet);

            $duree=0;
            foreach($Interventions as $uneIntervention){
                $duree+= $conv->duree_to_secondes($uneIntervention->getDuree()->format("H:i:s"));
            }
            $dureeTot=$conv->secondes_to_duree($duree);
            $inter = $paginator->paginate(
                $Interventions, // Requête contenant les données à paginer 
                $request->query->getInt('page', 1), // Numéro de la page en cours, passé dans l'URL, 1 si aucune page
                25,
                [
                    'defaultSortFieldName' => 'date',
                    'defaultSortDirection' => 'desc'
                ] // Nombre de résultats par page
            );
            return $this->render('intervention/Interventions.html.twig', [
                'InterventionPage'=>$inter,
                "Intervention" => $Interventions,
                'form_interSearch'=>$form->createView(),
                'idU'=>$idU,
                'date'=>$date,
                'idT'=>$tache,
                'idF'=>$famille,
                'idP'=>$projet,
                'dureeTot'=>$dureeTot
            
            ]);
        }
        else{
            return $this->redirectToRoute('ListeInter',array('idU'=>$user->getUser()->getId()));
        }
         }
        else{
            return $this->redirectToRoute('app_login');
        }
    
    
  
    }

    /**
     * @IsGranted("ROLE_CHANTIER")
    * @Route("/modifierIntervention/{id}", name="modifierIntervention")
    */
     public function modifyIntervention(Request $request, int $id,ConversionH $conv): Response
    {
      $entityManager = $this->getDoctrine()->getManager();
      $Intervention = $entityManager->getRepository(Intervention::class)->find($id);
      $duree=$conv->duree_to_secondes($Intervention->getDuree()->format("H:i:s"));
      $form = $this->createForm(InterventionFormType::class, $Intervention);
      $form->handleRequest($request);    
      $user=$Intervention->getLeUser();
      $tache=$Intervention->getTache();
      $famille=$tache->getFamille();
      $projet=$famille->getIdProjet();
     
      $Nduree=$conv->duree_to_secondes($form->get('duree')->getData()->format("H:i:s"));
      
      
        if($form->isSubmitted() && $form->isValid())
        {
            if($Nduree<$duree){
                $diff=$duree-$Nduree;
                $tache->setDureeRel($conv->secondes_to_duree($conv->duree_to_secondes($tache->getDureeRel())-$diff));
           
        }
        else{
            $diff=$Nduree-$duree;
            
            $tache->setDureeRel($conv->secondes_to_duree($conv->duree_to_secondes($tache->getDureeRel())-$diff));
        }
        $entityManager->persist($tache);
        $entityManager->persist($Intervention);
        $entityManager->flush();
        return $this->redirectToRoute("Interventions");
        }
      else{
          return $this->render("intervention/Intervention-form.html.twig", [
            "form_title" => "Modifier une intervention",
            "form_Intervention" => $form->createView(),
            'Intervention'=>$Intervention,
            'user'=>$user,
            'projet'=>$projet,
            'famille'=>$famille,
            'tache'=>$tache,
            'idP'=>$projet->getId(),
            'idF'=>$famille->getId(),
            'idU'=>$user->getId(),
    ]);
      }
        
    }

    /**
     * @IsGranted("ROLE_CHEF_CHANTIER")
     * @Route("/changer_tache/{idU}/intervention/{idI}", name="changerTache")
     */
    public function changerTache($idU, $idI): Response
    { 
        if(!empty($_GET)){
            $em=$this->getDoctrine()->getManager();

            $tache=$this->getDoctrine()->getRepository(Taches::class)->findOne($_GET['idT']);
            $Intervention=$this->getDoctrine()->getRepository(Intervention::class)->find($idI);
            $Intervention->setTache($tache);

            $em->persist($Intervention);
            $em->flush();

            return $this->redirectToRoute("Interventions");

        }
        $Ptache=$this->getDoctrine()->getRepository(Planning::class)->findUser($idU);
        return $this->render('intervention/modifTache.html.twig', [
           "planning"=>$Ptache,
           'idU'=>$idU,
           'idI'=>$idI
       ]);      
    }

    /**
     * @IsGranted("ROLE_CHANTIER")
     * @Route("/supprimerIntervention/{id}", name="supprimerIntervention")
     */
    public function deleteIntervention($id): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        
        $Intervention = $entityManager->getRepository(Intervention::class)->find($id);
        $user=$Intervention->getLeUser();

        $entityManager->remove($Intervention);
        $entityManager->flush();

        if($this->getUser()->getRoles()[0]=="ROLE_CHANTIER"){
            return $this->redirectToRoute("ListeInter",array('idU'=>$user->getId()));
        }
        else{
            return $this->redirectToRoute("Interventions");
        }
        
    }
    
    /**
     * @IsGranted("ROLE_CHANTIER")
     * @Route("/intervention/export/{idU}/{date}/{idT}/{idF}/{idP}", name="export")
     */
    public function export($idU,$date,$idT,$idF,$idP){
        $entityManager = $this->getDoctrine()->getManager();
        $user1=$entityManager->getRepository(User::class)->findOne($idU);
        $user=$this->getUser();
        if(($user->getRoles()[0]=="ROLE_CHANTIER" && $user1==$user->getUser())||$user->getRoles()[0]!="ROLE_CHANTIER"){
        $spreadsheet = new Spreadsheet();
        $writer = new Xlsx($spreadsheet);        
        
        $Interventions = $entityManager->getRepository(Intervention::class)->search($idU,$date,$idT,$idF,$idP);
        $arrayData =[['Nom','Prenom','Temps passé',"Date de l'intervention",'Projet','Famille de taches','Taches','Problèmes rencontrés']];
        foreach($Interventions as $inter ){
            $array1=[
               $inter->getleUser()->getNom(),
               $inter->getleUser()->getPrenom(),
               $inter->getDuree()->format('H:i'),
               $inter->getDate()->format('d/m/Y'),
               $inter->getTache()->getFamille()->getIdProjet()->getNom(),
               $inter->getTache()->getFamille()->getNom(),
               $inter->getTache()->getNom(),
               $inter->getPb()];
            array_push($arrayData, $array1);
        }
        $spreadsheet->getActiveSheet()
        ->fromArray(
            $arrayData,   
            NULL,        
            'A1'         
                         
        );
       
       
        
       
        
        if($user->getRoles()[0]=="ROLE_CHANTIER"){

            $writer->save($this->getParameter('photo_directory')."export/Exportation de ".$user->getUser()->getNom()." ".$user->getUser()->getPrenom().".xlsx");
            $filePath = "export/Exportation de ".$user->getUser()->getNom().$user->getUser()->getPrenom().".xlsx";
            $response = new BinaryFileResponse($filePath);
            $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT,'Exportation de '.$user->getUser()->getNom()." ".$user->getUser()->getPrenom().'.xlsx');
        }
        else{
            $date=new DateTime();

            $writer->save($this->getParameter('photo_directory')."export/Exportation du ".$date->format("d-m-Y").".xlsx");
            $filePath = "export/Exportation du ".$date->format("d-m-Y").".xlsx";
            $response = new BinaryFileResponse($filePath);
            $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT,'Exportation du '.$date->format("d-m-Y").'.xlsx');
        }
        $fs = new FileSystem(); 
        
        return $response;
    }else{
    return $this->redirectToRoute("Interventions");
    }
    }
}
