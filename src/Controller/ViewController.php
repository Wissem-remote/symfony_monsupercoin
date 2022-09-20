<?php

namespace App\Controller;

use App\Entity\Annonce;
use App\Entity\Mail;
use App\Entity\User;
use App\Form\FormViewType;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;



class ViewController extends AbstractController
{
    #[Route('/', name: 'view')]
    public function index(ManagerRegistry $doctrine): Response
    {
        //dump($this->getUser()->getRoles()[0]);

        $annonces = $doctrine->getRepository(Annonce::class)->findAll();
        return $this->render('view/index.html.twig', [
            'annonces' => $annonces
        ]);
    }

    #[Route('/view/annonce/{id}', name: 'view_annonce')]
    public function annonce(Annonce $annonce, ManagerRegistry $doctrine): Response
    {
        
        return $this->render('view/annonce.html.twig', [
            'annonce' => $annonce,
            'carousel' => "on"
        ]);
    }

    #[Route('/profile', name: 'view_profile')]
    public function profile(ManagerRegistry $doctrine): Response
    {
        
        return $this->render('view/profile.html.twig', [
            'infos' => $this->getUser()->getAnnonces(),
            'user' => $this->getUser()
        ]);
    }


    #[Route('profile/annonce/delete/{id}', name: 'delete_annonce_profile')]
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

        $this->addFlash('success_delete', 'Votre annonce à été suprimer');

        return $this->redirectToRoute('view_profile');
    }


    #[Route('profile/annonce/add', name: 'add_annonce_profile')]
    public function addAnnonce(EntityManagerInterface $em, Request $request, SluggerInterface $slugger): Response
    {
        $annonces = new Annonce;
        $form = $this->createForm(FormViewType::class, $annonces);
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
            $annonces->setUser($this->getUser());
            $annonces->setCreatedAt(new \DateTimeImmutable);
            $annonces->setUpdateAt(new \DateTimeImmutable);
            $em->persist($annonces);
            $em->flush();
            // do anything else you need here, like send an email
            $this->addFlash('success_update', 'Votre annonce à été ajouter');

            return $this->redirectToRoute('view_profile');
        }

        return $this->render('view/annonce_add.html.twig', [
            'form' => $form->createView()
        ]);
    }


    #[Route('profile/annonce/update/{id}', name: 'update_annonce_profile')]
    public function updateAnnonce(Annonce $annonces, EntityManagerInterface $em, Request $request): Response
    {
        $forms = $this->createFormBuilder($annonces)
            ->add('content')
            ->add('title')
            ->add('prix')
            ->getForm();

        $forms->handleRequest($request);

        if ($forms->isSubmitted() && $forms->isValid()) {
            // encode the plain password


            $annonces->setUpdateAt(new \DateTimeImmutable);

            $em->flush();
            // do anything else you need here, like send an email

            $this->addFlash('success_update', 'Votre annonce à été modifier');

            return $this->redirectToRoute('view_profile');
        }

        return $this->render('view/annonce_add.html.twig', [
            'form' => $forms->createView(),
            'update' => 'hello'
        ]);
    }

    #[Route('/registration', name: 'app_register_view')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
            //$user->setRoles(['ROLE_ADMIN']);
            $user->setCreateAt(new \DateTimeImmutable);
            $user->setUpdateAt(new \DateTimeImmutable);
            $entityManager->persist($user);
            $entityManager->flush();
            // do anything else you need here, like send an email

            return $this->redirectToRoute('view');
        }

        return $this->render('view/register.html.twig', [
            'registrationForm' => $form->createView(),
            'carousel' => "on"
        ]);
    }


    #[Route('/contact', name: 'mail_view')]
    public function contact(Request $request, ManagerRegistry $doctrine): Response
    {
        $mail = new Mail();
        $em = $doctrine->getManager();
        $forms = $this->createFormBuilder($mail)
            ->add('email',EmailType::class)
            ->add('objet')
            ->add('name')
            ->add('content')
            ->getForm();


        return $this->render('mail/index.html.twig', [
            'form' => $forms->createView(),
            'carousel' => "on"
        ]);

    }



}
