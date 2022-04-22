<?php

namespace App\Controller;

use App\Entity\Product;
use App\Service\Pagination;
use OpenApi\Annotations as OA;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\SerializationContext;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


class ProductController extends AbstractController
{
    private SerializerInterface $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @OA\Get(
     *   tags={"Products"},
     *   summary="Get all products",
     *   @OA\Parameter(
     *     name="page",
     *     description="Current page number",
     *     in="query",
     *     required=false,
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\Parameter(
     *     name="limit",
     *     description="Limit items per page",
     *     in="query",
     *     required=false,
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\Response(response=200, description="All products"),
     *   @OA\Response(response=401, description="JWT unauthorized error"),
     *   @OA\Response(response=404, description="No product found")
     * )
     */
    #[Route('/api/products', name: 'product_list', methods: ['GET'])]
    public function list(Pagination $paginator, Request $request): JsonResponse
    {
        $result = $paginator->paginate('SELECT product FROM App\Entity\Product product ORDER BY product.id ASC');

        $serializerContext = SerializationContext::create()->setGroups(['product:list']);
        $jsonProducts = $this->serializer->serialize($result, 'json', $serializerContext);

        $response = new JsonResponse($jsonProducts, JsonResponse::HTTP_OK, [], true);
        $response->setEtag(md5($response->getContent()));
        $response->setPublic();
        $response->isNotModified($request);

        return $response;
    }

    /**
     * @OA\Get(
     *   tags={"Products"},
     *   summary="Get a product by ID",
     *   @OA\PathParameter(
     *     name="id",
     *     description="ID of the product you want to recover"
     *   ),
     *   @OA\Response(response=200, description="Product details"),
     *   @OA\Response(response=401, description="JWT unauthorized error"),
     *   @OA\Response(response=404, description="No product found with this ID")
     * )
     */
    #[Route('/api/products/{id}', name: 'product_show', methods: ['GET'])]
    public function show(?Product $product, Request $request): JsonResponse
    {
        if (!$product) {
            throw new NotFoundHttpException("No product found with this ID");
        }

        $serializerContext = SerializationContext::create()->setGroups(['product:show']);
        $jsonProduct = $this->serializer->serialize($product, 'json', $serializerContext);

        $response = new JsonResponse($jsonProduct, JsonResponse::HTTP_OK, [], true);
        $response->setEtag(md5($response->getContent()));
        $response->setPublic();
        $response->isNotModified($request);

        return $response;
    }
}
