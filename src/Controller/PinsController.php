<?php

namespace App\Controller;

use App\Entity\Pin;
use App\Form\PinType;
use App\Repository\PinRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class PinsController extends AbstractController
{
    //private $em;
    //public function _construct(EntityManagerInterface $em){
    //    $this->em = $em;
   // }

    /**
     * @Route("/pin/create", name="app_createpin", methods={"GET","POST"})
     */
    public function createPin(Request $request, EntityManagerInterface $em, UserRepository $userRepo): Response
    {

        $pin = new Pin;
        $form = $this->createForm(PinType::class, $pin);
        $form->handleRequest($request);
        
        if($form->isSubmitted() && $form->isValid()){
            $pin->setCreatedAt(new \DateTime());
            $pin->setPins($this->getUser());
            $em->persist($pin);
            $em->flush();

            return $this->redirectToRoute('app_displaypins');
        }

        return $this->render('pins/createPin.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/", name="app_displaypins", methods={"GET"})
     */
    public function displayPins(PinRepository $repo): Response
    {

        return $this->render('pins/displayPins.html.twig', [
            'pins' => $repo->findBy([], ['createdAt' => 'DESC'])
        ]);
    }

    /**
     * @Route("/showOne/{id<[0-9]>}", name="app_onePinShow", methods={"GET"})
     */
     public function showOnePin(Pin $pin): Response
     {
        return $this->render('pins/showOnePin.html.twig', compact('pin'));
     }

      /**
     * @Route("/showOne/{id<[0-9]>}/edit", name="app_editPin", methods={"GET", "PUT"})
     */
     public function editPin(Request $request, EntityManagerInterface $em, Pin $pin): Response
     {
         
        $form = $this->createForm(PinType::class, $pin, [
            'method' => 'PUT'
        ]);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){            
            $em->flush();

            return $this->redirectToRoute('app_displaypins');
        }
         return $this->render('pins/editPin.html.twig', [
             'pin' => $pin,
            'form' => $form->createView()
         ]);
     }
}
