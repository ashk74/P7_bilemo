<?php

namespace App\Controller;

use App\Entity\Product;
use App\Service\Pagination;
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
    #[Route('/api/products/page/{page}', name: 'product_list_paginated', methods: ['GET'])]
    #[Route('/api/products/page/{page}/limit/{limit}', name: 'product_list_paginated_limit', methods: ['GET'])]
    public function list(Pagination $paginator, int $page = 1, int $limit = 10): JsonResponse
    {
        $result = $paginator->paginate(
            'SELECT product FROM App\Entity\Product product ORDER BY product.id ASC',
            $page,
            $limit,
            []
        );

        $serializerContext = SerializationContext::create()->setGroups(['product:list']);
        $json = $this->serializer->serialize($result, 'json', $serializerContext);
        return new JsonResponse($json, JsonResponse::HTTP_OK, [], true);
    }

    #[Route('/api/products/{id}', name: 'product_show', methods: ['GET'])]
    public function show(?Product $product): JsonResponse
    {
        $serializerContext = SerializationContext::create()->setGroups(['product:show']);
        $jsonProduct = $this->serializer->serialize($product, 'json', $serializerContext);

        return new JsonResponse($jsonProduct, jsonResponse::HTTP_OK, [], true);
    }
}
