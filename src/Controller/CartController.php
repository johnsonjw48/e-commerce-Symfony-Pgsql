<?php

namespace App\Controller;

use App\Repository\ProduitsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CartController extends AbstractController
{
    /**
     * @Route("/cart", name="app_cart")
     */
    public function index(SessionInterface $session, ProduitsRepository $produitsRepository)
    {
        $panier = $session->get("panier", []);

        $cart = [];

        $total = 0;

        foreach ($panier as $id => $quantity) {
            $cart[] = [
                'product' => $produitsRepository->find($id) ,
                'quantity' => $quantity
            ];

        }

        foreach ($cart as $item) {
            $totalItem = $item['product']->getPrice() * $item['quantity'];
            $total += $totalItem;
        }

        return $this->render('cart/index.html.twig', [
            'products' => $cart,
            'total' => $total
        ]);
    }

     /**
     * @Route("/panier/add/{id}", name="cart_add")
     */
    public function add($id, SessionInterface $session)
    {
        $panier = $session->get("panier", []);
    
        if (!empty($panier[$id] )) {
            $panier[$id]++;
        }else{
            $panier[$id] = 1;
        }

        $session->set("panier", $panier);

        return $this->redirectToRoute("app_produits_index");
    }

    /**
     * @Route("/panier/remove/{id}", name="cart_remove")
     */
    public function remove($id, SessionInterface $session)
    {
        $panier = $session->get('panier', []);

        if (!empty($panier[$id])) {
            unset($panier[$id]);
        }

        $session->set('panier', $panier);

        return $this->redirectToRoute('app_cart');
    }
}
