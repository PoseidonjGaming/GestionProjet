<?php
namespace App\Controller;

use DateTime;
use App\Entity\User;
use App\Entity\Compte;
use App\Entity\Projet;
use App\Entity\Intervention;
use App\Entity\Planning;
use App\Form\UserFormType;
use App\Form\Login2FormType;
use App\Service\Boucle;
use App\Service\ConversionH;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

//Controlleur pour administrer les utilisateur
class UserController extends AbstractController{
   

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/Utilisateur/Ajouter", name="ajouterUser")
     */
    public function addUser(Request $request): Response
    {
        $compte = new User();
        $form = $this->createForm(UserFormType::class,$compte);
        $form->handleRequest($request);
        $error=' ';
       

        if ($form->isSubmitted() && $form->isValid()) {
           

            $images=$form->get('photo')->getData();
            $imgExt=$images->guessClientExtension();
            $ext=array('png','jpeg','jpg','gif','svg');
            $fileName =$images->getClientOriginalName();
            if(in_array($imgExt,$ext,$strict=false)){
                $images->move(
                    $this->getParameter('photo_directory').'/photo',
                    $fileName);

            $compte->setPhoto($fileName);
            
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($compte);
            $entityManager->flush(); 
        
            return $this->redirectToRoute('users');
            }
            else{
                $error="Veuillez sélectionner un format d'image valide (.png,.jpeg,.jpg,.gif,.svg...)";
                return $this->render("user/ajout.html.twig", [
                    "form_title" => "Ajouter un technicien",
                    "form_user" => $form->createView(),
                    'error'=>$error,
                    'path'=>'ajouterUser',
              
                ]);
            
            
            }
        }
        return $this->render("user/ajout.html.twig", [
            "form_title" => "Ajouter un technicien",
            "form_user" => $form->createView(),
            'error'=>$error,
            'path'=>'ajouterUser'
            
      
        ]);
        
    }

    //Permet de lier un utilisateur à un compte
    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/Utilisateur/Lier/{idU}", name="LierUser")
     */
    public function LierUser(Request $request,$idU, UserPasswordEncoderInterface $encoder): Response
    {
    $compte= new Compte();
    $user = $this->getDoctrine()->getRepository(User::class)->findOne($idU);
    $form = $this->createForm(Login2FormType::class,$compte);
    $form->handleRequest($request);
    $entityManager = $this->getDoctrine()->getManager();
    $error="";

    if ($form->isSubmitted() && $form->isValid()) {
        $compte->setUser($user);
        if(preg_match("#[.{8}+]#",$compte->getPassword())){
        $encoded = $encoder->encodePassword($compte, $compte->getPassword());
        $compte->setPassword($encoded);
        $entityManager->persist($compte);
        $entityManager->flush();
        return $this->redirectToRoute('users');
        }
        else{
            $error="Entrez un mot de passe d'au moins 8 caractères !!!";
            return $this->render('compte/login-form.html.twig', [
                "form_title"=>"Modifier un login",
                "form_login"=>$form->createView(),
                'user'=>$user,
                'error'=>$error, 
            ]);
        }
    }
    return $this->render("user/lier.html.twig", [
        "form_title" => "Lier un compte",
        "form_login" => $form->createView(),
        'user'=>$user,
        'error'=>$error,
    ]);
}


    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/Utilisateurs", name="users")
     */
    public function index(): Response
    {
        $rep=$this->getDoctrine()->getRepository(User::class);
        $users=$rep->findAll();
      

        return $this->render('user/users.html.twig', [
            'users'=>$users,
          
        ]);
    }

    
 
    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/Utilisateur/Modifier/{id}", name="modifierUser")
     */
    public function update(Request $request, int $id): Response
    { 
        $manager=$this->getDoctrine()->getManager();
        $rep=$this->getDoctrine()->getRepository(User::class);  
        $user=$rep->findOne($id);
        $form=$this->createForm(UserFormType::class, $user);
        $form->handleRequest($request);
        $error=' ';
        
       

        if($form->isSubmitted() && $form->isValid()){
            $photo=$user->getPhoto();

            $user->setNom($form->get('nom')->getData());
            $user->setPrenom($form->get('prenom')->getData());
            

            $images=$form->get('photo')->getData();
            if($images!=null){
                $imgExt=$images->guessClientExtension();

                $ext=array('png','jpeg','jpg','gif','svg');
                $fileName =$images->getClientOriginalName();

                if(in_array($imgExt,$ext,$strict=false)){
                    $images->move(
                        $this->getParameter('photo_directory')."photo/",
                        $fileName);

                    $user->setPhoto($fileName);
            
                    $entityManager = $this->getDoctrine()->getManager();
                    $entityManager->persist($user);
                    $entityManager->flush();
                    $users=$this->getDoctrine()->getrepository(User::class)->findAll();
                    $util=false;
                    unlink($this->getParameter('photo_directory')."photo/".$photo);
                
                    return $this->redirectToRoute('users');
                }
                else{
                    $error="Veuillez sélectionner un format d'image valide (.png,.jpeg,.jpg,.gif,.svg)";
                    return $this->render("user/ajout.html.twig", [
                        "form_title" => "Ajouter un technicien",
                        "form_user" => $form->createView(),
                        'error'=>$error,
                        'path'=>'modifierUser',
                        'id'=>$id,
                    
              
                    ]);
                }
            }
            else{
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($user);
                $entityManager->flush();
                return $this->redirectToRoute('users');
            }
            
        }

        return $this->render('user/ajout.html.twig', [
            "form_title"=>"Modifier un utilisateur",
            "form_user"=>$form->createView(),
            'user'=>$user, 
            'error'=>$error,
            'path'=>'modifierUser',
            'id'=>$id,
            'photo'=>$user->getPhoto()
        ]);
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/user/supprimer/{id}", name="supprimerUser")
     */
    public function supprimer($id): Response
    {
        $util=false;
        $manager=$this->getDoctrine()->getManager();
        $user=$this->getDoctrine()->getRepository(User::class)->findOne($id);

        $users=$this->getDoctrine()->getrepository(User::class)->findAll();
        $photo=$user->getPhoto();
        $Inter=$this->getDoctrine()->getRepository(Intervention::class)->search($id,"null","null","null","null");

        

        foreach($users as $unuser){
            if($unuser->getPhoto()==$photo && $unuser->getId()!=$id){
                $util=true;
            }
        }
        
        foreach($Inter as $uneInter){
            $manager->remove($uneInter);
        }

        if($util==false){unlink($this->getParameter('photo_directory')."photo/".$photo);}

        $manager->remove($user);
        $manager->flush();
       
        return $this->redirectToRoute('users');
    }
    
    //Ecran principale lorsqu'un utilisateur général se connect
    /**
     * @IsGranted("ROLE_CHANTIER")
     * @Route("/technicien", name="bienvenue")
     */
    public function Bienvenue(ConversionH $conv,Boucle $bcl): Response
    {   
        $user=$this->getUser();
        $date=new DateTime();
        $PTaches=$this->getDoctrine()->getrepository(Planning::class)->findSemaineUser(intval($date->format("W"))+1, $user->getUser()->getId());
        $couleurT=[];
        $etatT=[];
        $listtaches=[];
        $tot=[];

        foreach($PTaches as $PT){
           $total=0;
           $uneTache=$PT->getTache();
           foreach($uneTache->getIntervention() as $inter ){
               if($inter->getLeUser()==$user->getUser()){
                   $total=$total+ $conv->duree_to_secondes($inter->getDuree()->format("H:i:s"));
               }
             }
                    array_push($listtaches,$uneTache);
                    if(count($uneTache->getIntervention())==0){
                         array_push($couleurT, "#DDDDDD");
                         array_push($etatT, "Pas d'interventions");}
                    elseif($uneTache->getFini()){
                         array_push($couleurT, "white");
                         array_push($etatT, "Tâche finie");
                    }
                    else{
                        $etat=$bcl->boucleetatPl($PT,$user->getUser(), $conv);
                        array_push($etatT, $etat);
                        $color=$bcl->bouclecouleurPl($PT,$user->getUser(), $conv);
                        array_push($couleurT, $color);}
                        array_push($tot, $conv->secondes_to_duree(intval($total)));
            }
       

        if($user->getUser()){
            return $this->render('user/accueil.html.twig',[
                'planning'=>$PTaches,
                'couleurT'=>$couleurT,
                'etatT'=>$etatT,
                'total'=>$tot,
            ]);
        }
        else{
            return $this->redirectToRoute('app_login');
        }
    }


    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/supprPhoto/{idU}", name="supprPhoto")
     */
    public function supprPhoto($idU): Response
    {
        $manager=$this->getDoctrine()->getManager();
        $repo=$this->getDoctrine()->getRepository(User::class);
        $user=$repo->findOne($idU);
        $photo=$user->getPhoto();
        $users=$repo->findAll();
        $util=false;
        
        foreach($users as $unuser){
            if($unuser->getPhoto()==$photo && $unuser->getId()!=$idU){
                $util=true;
            }
        }

        if($util==false){unlink($this->getParameter('photo_directory')."photo/".$photo);}
        $user->setPhoto(NULL);
        $manager->persist($user);
        $manager->flush();
        return $this->redirectToRoute('modifierUser', array('id'=>$idU));
    }
}