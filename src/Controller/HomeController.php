<?php

namespace App\Controller;

use App\Entity\Annonce;
use App\Entity\User;
use App\Form\AnnoceType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(ManagerRegistry $doctrine): Response
    {
        $users = $doctrine->getRepository(User::class)->findAll();
      
        return $this->render('registration/index.html.twig', [
            'infos' => $users,
        ]);
    }

    #[Route('/annonce', name: 'annonce')]
    public function annonce(ManagerRegistry $doctrine): Response
    {
        
        $annonces = $doctrine->getRepository(Annonce::class)->findAll();
        return $this->render('home/index.html.twig', [
            'infos' => $annonces,
            'annonce' => 'hello'
        ]);
    }

    #[Route('/annonce/show/{id}', name: 'show_annonce')]
    public function showAnnonce(Annonce $annonces): Response
    {

        
        return $this->render('home/show.html.twig', [
            'annonce' => $annonces
        ]);
    }

    #[Route('/annonce/delete/{id}', name: 'delete_annonce')]
    public function deleteAnnonce(Annonce $annonces, ManagerRegistry $doctrine): Response
    {


        $em = $doctrine->getManager();

        $em->remove($annonces);

        $em->flush();

        $this->addFlash('success_delete','Votre annonce à été suprimer');

        return $this->redirectToRoute('annonce');
    }


    #[Route('/annonce/add', name: 'add_annonce')]
    public function addAnnonce(EntityManagerInterface $em, Request $request): Response
    {
        $annonces = new Annonce;
        $form = $this->createForm(AnnoceType::class,$annonces);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            
            $annonces->setCreatedAt(new \DateTimeImmutable);
            $annonces->setUpdateAt(new \DateTimeImmutable);
            $em->persist($annonces);
            $em->flush();
            // do anything else you need here, like send an email
            $this->addFlash('success_update', 'Votre annonce à été ajouter');

            return $this->redirectToRoute('annonce');
        }

        return $this->render('home/annonce_add.html.twig', [
            'form' => $form->createView()
        ]);
    }


    #[Route('/annonce/update/{id}', name: 'update_annonce')]

    public function updateAnnonce(Annonce $annonces,EntityManagerInterface $em, Request $request): Response
    {
         $forms = $this->createFormBuilder($annonces)
                ->add('content')
                ->add('prix' )
                ->getForm();
        
        $forms->handleRequest($request);

        if ($forms->isSubmitted() && $forms->isValid()) {
            // encode the plain password

            
            $annonces->setUpdateAt(new \DateTimeImmutable);
            
            $em->flush();
            // do anything else you need here, like send an email

            $this->addFlash('success_update', 'Votre annonce à été modifier');

            return $this->redirectToRoute('annonce');
        }

        return $this->render('home/annonce_add.html.twig', [
            'form' => $forms->createView(),
            'update' => 'hello'
        ]);
    }

}
