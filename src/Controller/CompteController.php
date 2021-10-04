<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

//Controlleur pour la redirection en fonction du rôle du compte après authentification
class CompteController extends AbstractController
{
    
    /**
     *
     * @Route("/role", name="role")
     */
    public function role(): Response
    {   $user=$this->getUser();
        $Role=$user->getRoles();
        
            if($Role[0]=="ROLE_ADMIN"){
                return $this->redirectToRoute("projets");
            }
            elseif($Role[0]=="ROLE_CHANTIER" && $user){
                return $this->redirectToRoute("bienvenue");
            }
            elseif($Role[0]=="ROLE_CHEF_CHANTIER"){
                return $this->redirectToRoute("listuser");
            }
            else{
                $this->addFlash('message','Error');
                return $this->redirectToRoute("app_logout");
            }
        
        
    }

   
}
