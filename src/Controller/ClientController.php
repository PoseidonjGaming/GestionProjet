<?php

namespace App\Controller;

use App\Entity\Client;
use App\Form\ClientFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
//Controlleur pour administrer les clients
class ClientController extends AbstractController
{
    /** 
     * @IsGranted("ROLE_ADMIN")
     * @Route("/Client/AjouterClient", name="ajouterClient")
     **/
    public function addClient(Request $request): Response
    {
        $client = new Client();
        $form = $this->createForm(ClientFormType::class,$client);
        $form->handleRequest($request);



        if($form->isSubmitted() && $form->isValid())
        {
            
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($client);
            $entityManager->flush();
            return $this->redirectToRoute('clients');
        }

        return $this->render("client/client-form.html.twig", [
            "form_title" => "Ajouter un client",
            "form_client" => $form->createView(),
        ]);
    }


    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/clients", name="clients")
     **/
    public function clients(): Response
    {
        $rep=$this->getDoctrine()->getRepository(Client::class);
        $clients=$rep->findAll();


        return $this->render('client/clients.html.twig', [
            'clients'=>$clients
        ]);
    }
    
    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/client/Modifier/{id}", name="modifierClient")
     */
    public function update(Request $request, int $id): Response
    {
        $rep=$this->getDoctrine()->getRepository(Client::class);  
        
        $manager=$this->getDoctrine()->getManager();
        $client=$rep->findOne($id);
        $form=$this->createForm(ClientFormType::class, $client);
        $form->handleRequest($request);
    

        if($form->isSubmitted() && $form->isValid()){
            $manager->flush();
            return $this->redirectToRoute('clients');
        }

        return $this->render('client/client-form.html.twig', [
            "form_title"=>"Modifier un client",
            "form_client"=>$form->createView(),
            'clients'=>$client
        ]);
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/client/supprimer/{id}", name="supprimerClient")
     */
    public function supprimer($id): Response
    {
        $manager=$this->getDoctrine()->getManager();
        $client=$manager->getRepository(Client::class)->findOne($id);

        $manager->remove($client);
        $manager->flush();

        return $this->redirectToRoute('clients');
    }
}
