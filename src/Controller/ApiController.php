<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class ApiController extends AbstractController
{
    #[Route('/api/products/', name: 'app_products')]
    public function listProducts(ProductRepository $productRepos): JsonResponse
    {
        $products=$productRepos->findAll();
        return $this->json([

            $products,
            200,
            [],
            ['groups'=>['productLinked']]
        ]);
        // 7/return $this->json($genreRepository->findAll(), 200, [], [AbstractNormalizer::IGNORED_ATTRIBUTES ['shows']])
    }

    #[Route('/api/orders/', name: 'app_orders')]
    public function listOrders(OrderRepository $orderRepository): JsonResponse
    {
        $orders=$orderRepository->findAll();
        return $this->json([
            $orders,
            200,
            [],
            ['groups'=>['order']]
        ]);
    }
}
