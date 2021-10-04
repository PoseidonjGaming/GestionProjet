<?php



namespace App\Controller;



use DateTime;

use App\Entity\User;

use App\Entity\Taches;

use App\Entity\Projet;

use App\Entity\Intervention;

use App\Entity\FamilleTache;

use App\Form\ProjetFormType;

use App\Form\FamilleTacheFormType;

use App\Form\TacheFormType;

use App\Form\Projet2FormType;

use App\Form\FamilleTache2FormType;

use App\Service\ConversionH;

use App\Service\Boucle;

use PhpOffice\PhpSpreadsheet\Spreadsheet;

use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\Filesystem\Filesystem;

use Symfony\Component\HttpFoundation\BinaryFileResponse;

use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\HttpFoundation\ResponseHeaderBag;

use Symfony\Component\HttpFoundation\StreamedResponse;

use Symfony\Component\HttpFoundation\File\UploadedFile;

use Symfony\Component\HttpFoundation\File\Exception\FileException;

use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\Security\Core\User\UserInterface;


//Controlleur pour administrer les projets
class ProjetController extends AbstractController

{    

    /**

     * @IsGranted("ROLE_ADMIN")

     * @Route("/projets", name="projets")

     */

    public function index(ConversionH $conv, Boucle $bcl): Response

    {

        $rep=$this->getDoctrine()->getRepository(Projet::class);

        $projets=$rep->OrderASC();



        $dureeRel=$bcl->dureeRelP($projets, $conv);

        $dureeEst=$bcl->dureeEstP($projets,$conv);





        return $this->render('projet/projets.html.twig', [

            'projets'=>$projets,

            'dureeRel'=>$dureeRel,

            'dureeEst'=>$dureeEst

        ]);

    }



    /**

     * @IsGranted("ROLE_ADMIN")

     * @Route("/projets/archives", name="archives")

     */

    public function archives(ConversionH $conv, Boucle $bcl): Response

    {

        $rep=$this->getDoctrine()->getRepository(Projet::class);

        $projets=$rep->OrderASC();



        $dureeRel=$bcl->dureeRelP($projets, $conv);

        $dureeEst=$bcl->dureeEstP($projets,$conv);



        return $this->render('projet/archives.html.twig', [

            'projets'=>$projets,

            'dureeRel'=>$dureeRel,

            'dureeEst'=>$dureeEst

        ]);

    }



        /**

     * @IsGranted("ROLE_ADMIN")

     * @Route("/projets/archives/{idP}", name="desarchivage")

     */

    public function desarchives($idP): Response

    {   

        $entityManager=$this->getDoctrine()->getManager();

        $rep=$this->getDoctrine()->getRepository(Projet::class);       

        $projet=$rep->FindOne($idP);

        $projet->setArchive(false);

        $entityManager->flush();



        return $this->redirectToRoute('archives');

    }





    /**

     * @IsGranted("ROLE_ADMIN")

     * @Route("/projet/ajouter", name="ajouterProjet")

     */

    public function insert(Request $request,UserInterface $user): Response

    {   

        $manager=$this->getDoctrine()->getManager();

        $projet=new Projet();

        $form=$this->createForm(ProjetFormType::Class, $projet);

        $form->handleRequest($request);

        $rep=$this->getDoctrine()->getRepository(Projet::class);

        $projet->setArchive(false);

        $projet->setCreateurId($user);

        $projet->setDateCrea(new \DateTime());

        $ajout=true;

        $var=$form->get('avancee')->getData();



        if($form->isSubmitted() && $form->isValid() ){

           

            if ($var <= 100 && $var>0){

                $projet->setAvancee($var);

            }

            else{

                $projet->setAvancee(0);

            }

            $projet->setClient($form->get('Client')->getData());     

            $manager->persist($projet);

            $manager->flush();

            return $this->redirectToRoute("importTache",array('idP' => $projet->getId(), 'nom'=>'Autres.xlsx'));

        }



        

        return $this->render('projet/ajout.html.twig', [

            'title_projet'=>'Ajouter un projet',

            "form_projet"=>$form->createView(),

            'projet'=>$projet,

            'ajout'=>$ajout,

        ]);          

    }



    /**

     * @IsGranted("ROLE_ADMIN")

     * @Route("/projet/{idP}/user/{idU}", name="assosProjet")

     */

    public function assosProjete_User($idP,$idU): Response

    {

        $manager=$this->getDoctrine()->getManager();

        $projet=$this->getDoctrine()->getRepository(Projet::class)->findOne($idP);

        $user=$this->getDoctrine()->getRepository(User::class)->findOne($idU);



        $user->addProjet($projet);



        $manager->persist($user);

        $manager->flush();



        return $this->redirectToRoute("ajoutFT",array('idP' => $idP));

    }



    /**

     * @IsGranted("ROLE_ADMIN")

     * @Route("/projet/modifier/{id}", name="modifierProjet")

     */

    public function update(Request $request, int $id, ConversionH $conv, Boucle $bcl): Response

    {  

       

        $manager=$this->getDoctrine()->getManager();

        $repP=$this->getDoctrine()->getRepository(Projet::class);

        $rep=$this->getDoctrine()->getRepository(FamilleTache::class);

        

        $projet=$repP->findOne($id);

        $familles=$rep->OrderASCNull($id);

        $familleTache=new FamilleTache();

        

        $form=$this->createForm(Projet2FormType::class, $projet);

        $form->handleRequest($request); 

        $formFamille=$this->createForm(FamilleTacheFormType::Class, $familleTache);

        $formFamille->handleRequest($request);

        $var=$form->get('avancee')->getData();



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





        if($form->isSubmitted() && $form->isValid() ){

            if ($var <= 100 && $var>0){

                $projet->setAvancee($var);

            }

            else{

                $projet->setAvancee(0);

            }

            $manager->persist($projet);

            $manager->flush();



           return $this->render('famille_tache/projetFamille.html.twig', [

            'title_projet'=>'Modifier le projet',

            "form_projet"=>$form->createView(),

            'title_famille'=>'Ajouter une famille de tâches',

            "form_famille"=>$formFamille->createView(),

            'idP'=>$id,

            'projet'=>$projet,

            'famille_tache'=>$familleTache, 

            'familles'=>$familles,

            'couleurF'=>$couleurF,

            'etatF'=>$etatF,

            

        ]);

        }

        else{

            return $this->render('projet/ajout.html.twig',[

                'title_projet'=>'Modifier le projet',

                "form_projet"=>$form->createView(),

                'idP'=>$id,

                'projet'=>$projet,



            ] );

        } 

        

        

    }



    /**

     * @IsGranted("ROLE_ADMIN")

     * @Route("/projet/supprimer/{id}", name="supprimerProjet")

     */

    public function supprimer($id): Response

    {

        $manager=$this->getDoctrine()->getManager();

        $projet=$manager->getRepository(Projet::class)->findOne($id);

        

        $manager->remove($projet);

        $manager->flush();

        if($projet->getArchive()){return $this->redirectToRoute('archives');}   

        else{return $this->redirectToRoute('projets');}

    }


    //Permet d'archiver les projets terminés (de les exporter)
    /**

     * @IsGranted("ROLE_ADMIN")

     * @Route("/projet/archivage/{idP}", name="archivage")

     */

    public function archivage($idP){

        $entityManager = $this->getDoctrine()->getManager();

        $spreadsheet = new Spreadsheet();

        $writer = new Xlsx($spreadsheet);

        $Interventions = $entityManager->getRepository(Intervention::class)->findAll();

        $Projet = $entityManager->getRepository(Projet::class)->findOne($idP);

        $Projet->setArchive(true);

        $entityManager->flush();



        $response = new StreamedResponse();        

        $arrayData =[['Projet','Famille','Tache', 'Nom du technicien', 'Prénom du technicien','Temps passé','Heure de début','Heure de fin',"Date de l'intervention",'Problèmes rencontrés']];

        foreach($Interventions as $inter ){    

            if($inter->getTache()->getFamille()->getIdProjet()->getId()==$Projet->getId()){

                $array1=[

                $Projet->getNom(),

                $inter->getTache()->getFamille()->getNom(),

                $inter->getTache()->getNom(),

                $inter->getleUser()->getNom(),

                $inter->getleUser()->getPrenom(),

                $inter->getDuree()->format('H:i'),

                $inter->getDate()->format('d/m/Y'), 

                $inter->getPb()

                ];

            array_push($arrayData, $array1);

           }

        }

       $spreadsheet->getActiveSheet()

        ->fromArray(

            $arrayData,   

            NULL,       

            'A1'                            

        );

    

       $writer->save($this->getParameter('photo_directory').'/export/'.$Projet->getNom().'.xlsx');     

       $filePath = 'export/'.$Projet->getNom().'.xlsx';

       $fs = new FileSystem();

       $response = new BinaryFileResponse($filePath);

       $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT,$Projet->getNom().'.xlsx');

       return $response;

       

        

    }

}