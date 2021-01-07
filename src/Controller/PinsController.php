<?php

namespace App\Controller;

use App\Entity\Pin;
use App\Form\PinType;
use App\Repository\PinRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\String\Slugger\SluggerInterface;


class PinsController extends AbstractController
{
    //private $em;
    //public function __construct(EntityManagerInterface $em){
    //    $this->em = $em;
   // }

    /**
     * @Route("/pin/create", name="app_createpin", methods={"GET","POST"})
     */
    public function createPin(Request $request, EntityManagerInterface $em, UserRepository $userRepo, SluggerInterface $slugger): Response
    {
        if(!$this->getUser()){
            return $this->redirectToRoute('app_signin');
        }

        $pin = new Pin;
        $form = $this->createForm(PinType::class, $pin);
        $form->handleRequest($request);
        
        if($form->isSubmitted() && $form->isValid()){
            /** @var UploadedFile $imageFile */
            $imageFile = $form->get('image')->getData();

             // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $imageFile->move(
                        $this->getParameter('images_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    throw $e;
                }
                 // updates the 'brochureFilename' property to store the PDF or JPEG file name
                // instead of its contents
                $pin->setImage($newFilename);
            }

            //$pin->setCreatedAt(new \DateTime());
            $pin->setPins($this->getUser());
            $em->persist($pin);
            $em->flush();

            $this->addFlash('success', 'Le pint a été crée avec succès !');

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
     * @Route("/showOne/{id<[0-9]+>}", name="app_onePinShow", methods={"GET"})
     */
     public function showOnePin(Pin $pin): Response
     {
        if(!$this->getUser()){
            return $this->redirectToRoute('app_signin');
        }
        return $this->render('pins/showOnePin.html.twig', compact('pin'));
     }

      /**
     * @Route("/showOne/{id<[0-9]+>}/edit", name="app_editPin", methods={"GET", "PUT"})
     */
     public function editPin(Request $request, EntityManagerInterface $em, Pin $pin, SluggerInterface $slugger): Response
     {

        $form = $this->createForm(PinType::class, $pin, [
            'method' => 'PUT'
        ]);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){  
             /** @var UploadedFile $imageFile */
             $imageFile = $form->get('image')->getData();

             // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $imageFile->move(
                        $this->getParameter('images_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    throw $e;
                }
                 // updates the 'image' property to store the PDF or JPEG file name
                // instead of its contents
                $pin->setImage($newFilename);
            }          
            $em->flush();

            $this->addFlash('success', 'Le pint a été modifié avec succès !');

            return $this->redirectToRoute('app_displaypins');
        }
         return $this->render('pins/editPin.html.twig', [
             'pin' => $pin,
            'form' => $form->createView()
         ]);
     }

      /**
     * @Route("/showOne/{id<[0-9]+>}/delete", name="app_deletePin", methods={"DELETE"})
     */
     public function delete(Request $request, Pin $pin, EntityManagerInterface $em): Response
     {
         
         if($this->isCsrfTokenValid('delete_pint_' . $pin->getId(), $request->request->get('csrf_token'))){
         $imageName = $pin->getImage();
         //delete file in directory
         if($imageName){
         unlink($this->getParameter('images_directory').'/'.$imageName);
         }
         //delete file in database
         $em->remove($pin);
         $em->flush();
         
         $this->addFlash('info', 'Le pint a été supprimé avec succès !');
         }
         return $this->redirectToRoute('app_displaypins');      
    }
}
