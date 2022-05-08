<?php

namespace App\Controller;

use App\Entity\Order;
use App\Form\OrderType;
use App\Repository\ProduitsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class OrderController extends AbstractController
{
    /**
     * @Route("/order", name="app_order")
     */
    public function index(Request $request,SessionInterface $session, ProduitsRepository $produitsRepository): Response
    {
        if ($this->getUser()->getAddresses()->getValues() === []) {
            return $this->redirectToRoute('address_add');
        }

        $form = $this->createForm(OrderType::class, null, [
            'user' => $this->getUser()
        ]);

        ######## Code à refactoriser, créer un service dédié

        $panier = $session->get("panier", []);

        $cart = [];

        $total = 0;

        foreach ($panier as $id => $quantity) {

            if (!$produitsRepository->find($id)) {
                unset($panier[$id]);
                
            }else{
                $cart[] = [
                    'product' => $produitsRepository->find($id) ,
                    'quantity' => $quantity
                ];
    
            }
        }

        foreach ($cart as $item) {
            $totalItem = $item['product']->getPrice() * $item['quantity'];
            $total += $totalItem;
        }

    ## Retour au formulaire ###

    $form->handleRequest($request);

     ########## fin Code à refactoriser, créer un service dédié

     if ($form->isSubmitted() && $form->isValid()) {
         dd($form->getData());
     }

        return $this->render('order/index.html.twig', [
                'form'=>$form->createView(),
                'cart'=>$cart
        ]);
    }
}
