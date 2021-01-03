<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProfileController extends AbstractController
{
    /**
     * @Route("/profile/{id<[0-9]>}", name="app_profile")
     */
    public function displayProfile(User $user): Response
    {
        
        return $this->render('profile/user.html.twig', compact('user'));
    }
}
