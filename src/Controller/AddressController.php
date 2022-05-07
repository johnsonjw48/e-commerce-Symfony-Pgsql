<?php

namespace App\Controller;

use App\Entity\Address;
use App\Form\AddressType;
use App\Repository\AddressRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AddressController extends AbstractController
{
    /**
     * @Route("/account/address", name="app_address")
     */
    public function index(): Response
    {
       
        return $this->render('address/index.html.twig', [
            
        ]);
    }


    /**
     * @Route("account/address/add", name="address_add")
     */
    public function add(Request $request, AddressRepository $addressRepository)
    {
        $address = new Address();
        $form = $this->createForm(AddressType::class, $address);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $address->setPerson($this->getUser());
            $addressRepository->add($address);

            return $this->redirectToRoute('app_address');
        }

        return $this->render('address/new.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
