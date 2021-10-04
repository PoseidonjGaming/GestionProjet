<?php

namespace App\Controller;

use App\Entity\Compte;
use App\Form\LoginFormType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

//Controlleur pour administrer les comptes
class LoginController extends AbstractController
{
    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('role');
         }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /** 
     * @IsGranted("ROLE_ADMIN")
     * @Route("/login/AjouterCompte", name="ajouterCompte")
     **/
    public function addCompte(Request $request, UserPasswordEncoderInterface $encoder): Response
    { 
	$entityManager = $this->getDoctrine()->getManager();
        $compte = new Compte();
        $form = $this->createForm(LoginFormType::class,$compte);
        $form->handleRequest($request);
       	$error="";

        //Permet la vérification de la complexité du mot de passe
        if($form->isSubmitted() && $form->isValid())
        {if(preg_match("#([\d\w\s\@\#\!\^ \$\(\)\[\]\{\}\?\+\*\.+]){8}#",$compte->getPassword())){
             $encoded = $encoder->encodePassword($compte, $compte->getPassword());
             $compte->setPassword($encoded);
	     $entityManager->persist($compte);
             $entityManager->flush(); 
             return $this->redirectToRoute('logins');
            }
        else{
            $error="Entrez un mot de passe d'au moins 8 caractères !!!";
            return $this->render('compte/login-form.html.twig', [
                "form_title"=>"Ajouter un compte",
                "form_login"=>$form->createView(),
                'logins'=>$compte,
                'error'=>$error, 
            ]);
        }
        }

        return $this->render("compte/login-form.html.twig", [
            "form_title" => "Ajouter un compte",
            "form_login" => $form->createView(),
	    'error'=>$error, 

        ]);
    }


    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/comptes", name="logins")
     **/
    public function index(): Response
    {
        $rep=$this->getDoctrine()->getRepository(Compte::class);
        $logins=$rep->findAll();


        return $this->render('compte/logins.html.twig', [
            'logins'=>$logins
        ]);
    }
    
    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/login/Modifier/{id}", name="modifierLogin")
     */
    public function update(Request $request, int $id, UserPasswordEncoderInterface $encoder): Response
    {
        $rep=$this->getDoctrine()->getRepository(Compte::class);  
        
        $manager=$this->getDoctrine()->getManager();
        $login=$rep->findOne($id);
        $form=$this->createForm(LoginFormType::class, $login);
        $form->handleRequest($request);
        $error="";

        if($form->isSubmitted() && $form->isValid()){
        if(preg_match("#([\d\w\s\@\#\!\^ \$\(\)\[\]\{\}\?\+\*\.+]){8}#",$login->getPassword())){
             $encoded = $encoder->encodePassword($login, $login->getPassword());
             $login->setPassword($encoded);
             $manager->flush(); 
             return $this->redirectToRoute('logins');
            }
        else{
            $error="Entrez un mot de passe d'au moins 8 caractères !!!";
            return $this->render('compte/login-form.html.twig', [
                "form_title"=>"Modifier un login",
                "form_login"=>$form->createView(),
                'logins'=>$login,
                'error'=>$error, 
            ]);
        }
           
           
        }

        return $this->render('compte/login-form.html.twig', [
            "form_title"=>"Modifier un login",
            "form_login"=>$form->createView(),
            'logins'=>$login,
            'error'=>$error,
        ]);
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/login/supprimer/{id}", name="supprimerLogin")
     */
    public function supprimer($id): Response
    {
        $manager=$this->getDoctrine()->getManager();
        $login=$manager->getRepository(Compte::class)->findOne($id);

        $manager->remove($login);
        $manager->flush();

        return $this->redirectToRoute('logins');
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
        return $this->redirectToRoute('login');
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
