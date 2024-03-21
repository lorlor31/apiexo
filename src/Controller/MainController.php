<?php

namespace App\Controller;

use App\Entity\Order;
use App\Form\OrderType;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class MainController extends AbstractController
{
    #[Route('/orders/add', name: 'app_order_add',methods:['GET','POST'])]
    public function add(EntityManagerInterface $em, Request $request)
    {
        $order= new Order ;
        $form = $this->createForm(OrderType::class,$order) ;
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
        $em->persist($order);
        $em->flush();
        dd($order);
        }
        return $this->render('order/add.html.twig', ['orderForm' => $form]);
    }

    #[Route('/', name: 'app_home',methods:['GET'])]
    public function home(EntityManagerInterface $em, Request $request, ProductRepository $productRepos)
    {
        $product= $productRepos->find(1);
        return $this->render('home.html.twig', ['product' => $product]);
    }
}
