<?php

namespace App\Controller;

use App\Repository\ProduitsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="app_home")
     */
    public function index(ProduitsRepository $produitsRepository): Response
    {
        return $this->render('home/index.html.twig', [
            'products' => $produitsRepository->findBy(
                array(),        // $where 
                array(),        // $orderBy
                2,              // $limit
                0               // $offset
            ),
            'products2' => $produitsRepository -> findBy(
                array(),        // $where 
                array(),        // $orderBy
                2,              // $limit
                2               // $offset
            ),
        ]);
    }
}
