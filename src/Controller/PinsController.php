<?php

namespace App\Controller;

use App\Entity\Pin;
use App\Form\PinType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class PinsController extends AbstractController
{
    /**
     * @Route("/", name="app_pins")
     */
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        $pin = new Pin;
        $form = $this->createForm(PinType::class, $pin);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $em->persist($pin);
            $em->flush();
        }

        return $this->render('pins/index.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
