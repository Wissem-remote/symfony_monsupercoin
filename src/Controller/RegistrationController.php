<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
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
            $user->setCreateAt(new \DateTimeImmutable);
            $user->setUpdateAt(new \DateTimeImmutable);
            $entityManager->persist($user);
            $entityManager->flush();
            // do anything else you need here, like send an email

            return $this->redirectToRoute('home');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
    #[Route('/user/show/{id}', name: 'show_register')]
    public function showRegister(User $user): Response
    {


        return $this->render('registration/show.html.twig', [
            'user' => $user
        ]);
    }

    #[Route('/user/delete/{id}', name: 'delete_register')]
    public function deleteRegister(User $user, ManagerRegistry $doctrine): Response
    {


        $em = $doctrine->getManager();

        $em->remove($user);

        $em->flush();

        $this->addFlash('success_delete', 'votre utilisateur à été suprimer');

        return $this->redirectToRoute('annonce');
    }

    #[Route('/user/{id}', name: 'update_register')]
    public function updateRegister(User $user,Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        
        $form =
        $this->createFormBuilder($user)
            ->add('name')
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            
            $user->setUpdateAt(new \DateTimeImmutable);
            $entityManager->persist($user);
            $entityManager->flush();
            // do anything else you need here, like send an email

            return $this->redirectToRoute('home');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
            'update' => 'hello'
        ]);
    }
}
