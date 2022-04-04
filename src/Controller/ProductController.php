<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProductController extends AbstractController
{
    private $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    #[Route('/api/products', name: 'app_product_list', methods: ['GET'])]
    public function list(ProductRepository $productRepository): JsonResponse
    {
        $jsonProducts = $this->serializer->serialize($productRepository->findAll(), 'json');

        return new JsonResponse($jsonProducts, JsonResponse::HTTP_OK, [], true);
    }

    #[Route('/api/products/{id}', name: 'app_product_show', methods: ['GET'])]
    public function show(?Product $product): JsonResponse
    {
        $jsonProduct = $this->serializer->serialize($product, 'json');

        return new JsonResponse($jsonProduct, jsonResponse::HTTP_OK, [], true);
    }
}
