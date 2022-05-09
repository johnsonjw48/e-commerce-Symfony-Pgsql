<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderDetails;
use App\Form\OrderType;
use App\Repository\OrderDetailsRepository;
use App\Repository\OrderRepository;
use App\Repository\ProduitsRepository;
use DateTime;
use Exception;
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

   
       if(count($cart) === 0){
                return $this->redirectToRoute('app_cart');
       }else{
                return $this->render('order/index.html.twig', [
                    'form'=>$form->createView(),
                    'total'=>$total,
                    'cart'=>$cart
    ]);
       }

   
       
    }

    /**
     * @Route("order/recap", name="order_recap", methods={"POST"})
     */
    public function add(OrderRepository $orderRepository, OrderDetailsRepository $orderDetailsRepository,Request $request, SessionInterface $session, ProduitsRepository $produitsRepository): Response
    {
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction(); 

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

            try {
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
    $order->setStatus("En cours de préparation");

    $em->persist($order);

    $cancel = 0;

       ### Enregistrer les produits en bdd
       foreach ($cart as $prod) {
        $orderDetails = new OrderDetails();
        $orderDetails->setCommand($order);
        $orderDetails->setProduct($prod['product']->getName());
        $orderDetails->setQuantity($prod['quantity']);

        $item = $produitsRepository->find($prod['product']->getId());

        $newStock = $item->getStock() - $prod['quantity'];

        $item->setStock($newStock);

        if ($newStock < 0) {
            $cancel++;
        }

        $orderDetails->setPrice($prod['product']->getPrice());
        $orderDetails->setTotal($prod['product']->getPrice() * $prod['quantity']);
        $em->persist($orderDetails);
        $em->persist($item);

        
    }

    if ($cancel > 0) {
        throw new Exception('Commande annuler car Stock insuffisant');
    }


    $em->flush();
    $em->getConnection()->commit();

    $session->remove('panier');
  
   
   
        return $this->render('order/add.html.twig', [
            'cart'=>$cart,
            'total'=>$total,
            'carrier'=>$form->get('carriers')->getData(),
            'delivery'=>$delivery
        ]);
            } catch (Exception $e) {
                $em->getConnection()->rollBack();

                return $this->render('order/cancel.html.twig', [
                    'message' => $e->getMessage()
                ]);
            }

    }

        return $this->redirectToRoute('app_cart');
  }


  /**
   * @Route("/account/commands", name="account_commands")
   */
  public function myOrders(): Response
  {

      return $this->render('order/command.html.twig', []);
  }

    /**
   * @Route("/account/commands/{id}", name="account_commands_details")
   */
  public function myOrderDetails($id, OrderDetailsRepository $orderDetailsRepository): Response
  {

    
      return $this->render('order/details.html.twig', [
          'order'=> $orderDetailsRepository->findBy(
            array('command' => $id),        // $where 
         
        ),
      ]);
  }

}