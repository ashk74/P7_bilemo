<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\SerializationContext;
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

    #[Route('/api/products', name: 'product_list', methods: ['GET'])]
    public function list(ProductRepository $productRepository): JsonResponse
    {
        $serializerContext = SerializationContext::create()->setGroups(['product:list']);
        $jsonProducts = $this->serializer->serialize($productRepository->findAll(), 'json', $serializerContext);

        return new JsonResponse($jsonProducts, JsonResponse::HTTP_OK, [], true);
    }

    #[Route('/api/products/{id}', name: 'product_show', methods: ['GET'])]
    public function show(?Product $product): JsonResponse
    {
        $serializerContext = SerializationContext::create()->setGroups(['product:show']);
        $jsonProduct = $this->serializer->serialize($product, 'json', $serializerContext);

        return new JsonResponse($jsonProduct, jsonResponse::HTTP_OK, [], true);
    }
}
