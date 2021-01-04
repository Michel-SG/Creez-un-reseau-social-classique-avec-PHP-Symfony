<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use App\Form\RegistrationType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class SecurityController extends AbstractController
{
    /**
     * @Route("/signup", name="app_signup", methods={"GET","POST"})
     */
    public function signup(Request $request, EntityManagerInterface $em, UserPasswordEncoderInterface $encoder ): Response
    {
        //if($this->getUser()){
          //  $this->addFlash('error', 'Vous êtes déjà connecté !');
         //   return  $this->redirectToRoute('app_displaypins');
        //}
        $user = new User;
        $form = $this->createForm(RegistrationType::class, $user);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            $hash = $encoder->encodePassword($user, $user->getPassword());
            $user->setConfirmPassword($hash);
            $user->setPassword($hash);
            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'Votre compte a été crée avec succès !');
            return $this->redirectToRoute('app_signin');
        }

        return $this->render('security/signup.html.twig', [
            'form' => $form->createView()
        ]);
    }


    /**
     * @Route("/", name="app_signin", methods={"GET", "POST"})
     */
    public function signin(): Response
    {
        if($this->getUser()){
            $this->addFlash('error', 'Vous êtes déjà connecté !');
            return  $this->redirectToRoute('app_displaypins');
        }
        
        return $this->render('security/signin.html.twig');
    }

     /**
     * @Route("/logout", name="app_logout")
     */
     public function logout(): Response{}
}
