<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\BrandRepository;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use App\Serializer\ProductNormalizer;
use App\Repository\CustomerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\Context\Normalizer\ObjectNormalizerContextBuilder;

class ApiController extends AbstractController
{
    #[Route('/api/products/', name: 'app_products',methods: ['GET'])]
    public function listProducts(ProductRepository $productRepos,SerializerInterface $serializer): JsonResponse
    {
        $products=$productRepos->findAll();
        
    return $this->json(
            $products ,
            Response::HTTP_OK,
            [],
            ["groups"=>['product','brandLinked']]
        );
    }

    #[Route('/api/products/', name: 'app_product_create', methods: ['POST'])]
    public function create(
    // Request $request, 
    SerializerInterface $serializer, 
    EntityManagerInterface $entityManager, 
    // ValidatorInterface $validator,
    ProductNormalizer $productNormalizer,
    BrandRepository $brandRepos): JsonResponse
    {

// $jsonTest= 
// {
// "name"	: "pomme",
// "brand_id"	: 1,
// "price"	:1.2	
// "color"	:"rouge" ,
// "available": true

// }


        // je récupère le json en brut dans la requête
        // $data = $request->getContent();
        $brand=$brandRepos->find(1);
        // dd($brand);
        $data=$productNormalizer->getSupportedTypes("coucou");
        //  gérer le cas ou le json n'est pas au bon format
        try {
            // je transforme le json brut en entité show
            $product = $serializer->deserialize($data, product::class, 'json');
        } catch (NotEncodableValueException $exception) {
            return $this->json([
                "error" =>
                ["message" => $exception->getMessage()]
            ], Response::HTTP_BAD_REQUEST);
        }

        // // on check s'il y a des erreurs de validations
        // $errors = $validator->validate($show);
        // if (count($errors) > 0) {

        //     $dataErrors = [];
        //     // si je suis la c'est que j'ai forcement un tableau d'erreur donc je boucle dessus
        //     foreach ($errors as $error) {
        //         // j'ajoute le message d'erreur à l'index correspondant à l'attribut ou il y a un soucis
        //         $dataErrors[$error->getPropertyPath()] = $error->getMessage();
        //     }

        //     return $this->json(["error" => ["message" => $dataErrors]], Response::HTTP_UNPROCESSABLE_ENTITY);
        // }

        $entityManager->persist($product);

        $entityManager->flush();

        //  appeler les films en bdd
        return $this->json($product, Response::HTTP_CREATED, ["Location" => $this->generateUrl("app_products")]);
    }




    #[Route('/api/orders/', name: 'app_orders',methods: ['GET'])]
    public function listOrders(OrderRepository $orderRepository): JsonResponse
    {
        $orders=$orderRepository->findAll();
        return $this->json(
            $orders,
            200,
            [],
            ['groups'=>['order','customerLinked','productLinked']]
        );
    }

    #[Route('/api/customers/', name: 'app_customers')]
    public function listCustomers(CustomerRepository $customerRepository): JsonResponse
    {
        $customers=$customerRepository->findAll();
        return $this->json([
            $customers,
            200,
            [],
            ['groups'=>['customer']]
        ]);
    }
}
