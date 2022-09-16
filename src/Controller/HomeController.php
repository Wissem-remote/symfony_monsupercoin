<?php

namespace App\Controller;

use App\Entity\Annonce;
use App\Entity\User;
use App\Form\AnnoceType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class HomeController extends AbstractController
{
    #[Route('/admin', name: 'home')]
    public function index(ManagerRegistry $doctrine): Response
    {
        dump($this->getUser());
        $users = $doctrine->getRepository(User::class)->findAll();
      
        return $this->render('registration/index.html.twig', [
            'infos' => $users,
        ]);
    }

    #[Route('admin/annonce', name: 'annonce')]
    public function annonce(ManagerRegistry $doctrine): Response
    {
        
        $annonces = $doctrine->getRepository(Annonce::class)->findAll();
        return $this->render('home/index.html.twig', [
            'infos' => $annonces,
            'annonce' => 'hello'
        ]);
    }

    #[Route('admin/annonce/show/{id}', name: 'show_annonce')]
    public function showAnnonce(Annonce $annonces): Response
    {

        
        return $this->render('home/show.html.twig', [
            'annonce' => $annonces
        ]);
    }

    #[Route('admin/annonce/delete/{id}', name: 'delete_annonce')]
    public function deleteAnnonce(Annonce $annonces, ManagerRegistry $doctrine): Response
    {

        
        if ($annonces->getImage() != null) {
            $filesystem = new Filesystem();
            $projectDir = $this->getParameter('kernel.project_dir');
            $filesystem->remove($projectDir . '/public/uploads/article/' . $annonces->getImage());
        }
        
        $em = $doctrine->getManager();

        $em->remove($annonces);

        $em->flush();

        $this->addFlash('success_delete','Votre annonce à été suprimer');

        return $this->redirectToRoute('annonce');
    }


    #[Route('admin/annonce/add', name: 'add_annonce')]
    public function addAnnonce(EntityManagerInterface $em, Request $request, SluggerInterface $slugger): Response
    {
        $annonces = new Annonce;
        $form = $this->createForm(AnnoceType::class,$annonces);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Image
            $brochureFile = $form->get('image')->getData();

            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($brochureFile) {
                $originalFilename = pathinfo($brochureFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $brochureFile->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $brochureFile->move(
                        $this->getParameter('annonce_image'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                $annonces->setImage($newFilename);
            }
            
            
            // encode the plain password
            //$annonces->setUser($this->getUser());
            $annonces->setCreatedAt(new \DateTimeImmutable);
            $annonces->setUpdateAt(new \DateTimeImmutable);
            $em->persist($annonces);
            $em->flush();
            // do anything else you need here, like send an email
            $this->addFlash('success_update', 'Votre annonce à été ajouter');

            return $this->redirectToRoute('annonce');
        }

        return $this->render('home/annonce_add.html.twig', [
            'form' => $form->createView(),
            
        ]);
    }


    #[Route('admin/annonce/update/{id}', name: 'update_annonce')]

    public function updateAnnonce(Annonce $annonces,EntityManagerInterface $em, Request $request): Response
    {
        $forms = $this->createFormBuilder($annonces)
                ->add('content')
                ->add('title')
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
