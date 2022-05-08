<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderDetails;
use App\Form\OrderType;
use App\Repository\OrderDetailsRepository;
use App\Repository\OrderRepository;
use App\Repository\ProduitsRepository;
use DateTime;
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

   

   
        return $this->render('order/index.html.twig', [
                'form'=>$form->createView(),
                'total'=>$total,
                'cart'=>$cart
        ]);
    }

    /**
     * @Route("order/recap", name="order_recap", methods={"POST"})
     */
    public function add(OrderRepository $orderRepository, OrderDetailsRepository $orderDetailsRepository,Request $request, SessionInterface $session, ProduitsRepository $produitsRepository): Response
    {
        $em = $this->getDoctrine()->getManager();
        $form = $this->createForm(OrderType::class, null, [
            'user' => $this->getUser()
        ]);

        $form->handleRequest($request);

   

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

  ######## fin Code à refactoriser, créer un service dédié



  ##### Retour au formulaire

  if ($form->isSubmitted() && $form->isValid()) {
    $date = new DateTime();
    $carriers= $form->get('carriers')->getData();
    $address= $form->get('addresses')->getData();
    $delivery = $address->getFirstName().' '.$address->getLastName();
    $delivery .= '<br/>'.$address->getPhone();
   
    if ($address->getCompany()) {
        $delivery .= '<br/>'.$address->getCompany();
    }
    $delivery .= '<br/>'.$address->getAddress();
    $delivery .= '<br/>'.$address->getPostal().' '.$address->getCity();
    $delivery .= '<br/>'.$address->getCountry();


    

    $order = new Order();
    $order->setPerson($this->getUser());
    $order->setCreatedAt($date);
    $order->setCarrierName($carriers->getName());
    $order->setCarrierPrice($carriers->getPrice());
    $order->setDelivery($delivery);
    $order->setIsPaid(1);

    $orderRepository->add($order);


       ### Enregistrer les produits en bdd
       foreach ($cart as $prod) {
        $orderDetails = new OrderDetails();
        $orderDetails->setCommand($order);
        $orderDetails->setProduct($prod['product']->getName());
        $orderDetails->setQuantity($prod['quantity']);

        $item = $produitsRepository->find($prod['product']->getId());

        $newStock = $item->getStock() - $prod['quantity'];

        $item->setStock($newStock);



        $orderDetails->setPrice($prod['product']->getPrice());
        $orderDetails->setTotal($prod['product']->getPrice() * $prod['quantity']);
        $orderDetailsRepository->add($orderDetails);
        $produitsRepository->add($item);
    }

    $session->remove('panier');
   
        return $this->render('order/add.html.twig', [
            'cart'=>$cart,
            'total'=>$total,
            'carrier'=>$form->get('carriers')->getData(),
            'delivery'=>$delivery
            ]);
        }

        return $this->redirectToRoute('app_cart');
    }


}
