<?php

namespace App\Controller;


use App\Entity\Produits;
use App\Data\SearchData;
use App\Form\SearchType;
use App\Repository\ProduitsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/produits")
 */
class ProduitsController extends AbstractController
{
    /**
     * @Route("/", name="app_produits_index" , methods={"GET"})
     */
    public function index(ProduitsRepository $produitsRepository, Request $request): Response
    {
        
        $data= new SearchData();
        $form= $this->createForm(SearchType::class, $data);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $products= $produitsRepository->getSearchQuery($data);
        }else{
            $products = $produitsRepository->findAll();
        }

        return $this->render('produits/index.html.twig', [
            'produits' => $products,
            'form'=>$form->createView()
        ]);
    }

   

    /**
     * @Route("/{id}", name="app_produits_show", methods={"GET"})
     */
    public function show(Produits $produit): Response
    {
        return $this->render('produits/show.html.twig', [
            'produit' => $produit,
        ]);
    }

   

   
}
