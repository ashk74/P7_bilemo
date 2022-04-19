<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\Pagination;
use OpenApi\Annotations as OA;
use JMS\Serializer\SerializerInterface;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializationContext;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserController extends AbstractController
{
    private SerializerInterface $serializer;
    private ValidatorInterface $validator;
    private EntityManagerInterface $em;

    public function __construct(SerializerInterface $serializer, ValidatorInterface $validator, EntityManagerInterface $em)
    {
        $this->serializer = $serializer;
        $this->validator = $validator;
        $this->em = $em;
    }

    /**
     * @OA\Post(
     *   tags={"Users"},
     *   summary="Create a new user",
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(
     *         type="object",
     *         @OA\Property(
     *           property="firstname",
     *           description="Username for user identification",
     *           type="string",
     *           default="John"
     *         ),
     *         @OA\Property(
     *           property="lastname",
     *           description="User's choosen password",
     *           type="string",
     *           default="Doe"
     *         ),
     *         @OA\Property(
     *           property="email",
     *           description="User's first name",
     *           type="string",
     *           default="john.doe@email.com"
     *         ),
     *       )
     *     )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Created user",
     *     @OA\JsonContent(
     *       type="array",
     *       @OA\Items(ref=@Model(type=User::class, groups={"user:show"}))
     *     )
     *   ),
     *   @OA\Response(response=400, description="JSON field validation failed"),
     *   @OA\Response(
     *     response=401,
     *     description="JWT unauthorized error"
     *   ),
     *   @OA\Response(response=500, description="JSON syntax error or no JSON sent in the request body"),
     * )
     */
    #[Route('/api/users', name: 'user_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $user = $this->serializer->deserialize($request->getContent(), User::class, 'json');
        $user->setCustomer($this->getUser());
        $user->setCreatedAt(new \DatetimeImmutable());

        $errors = $this->validator->validate($user);

        if (count($errors) > 0) {
            return $this->json($errors, JsonResponse::HTTP_BAD_REQUEST);
        }

        $this->em->persist($user);
        $this->em->flush();

        $jsonUser = $this->serializer->serialize(
            $user,
            'json',
            SerializationContext::create()->setGroups(['user:show'])
        );
        return new JsonResponse($jsonUser, JsonResponse::HTTP_OK, [], true);
    }

    /**
     * @OA\Get(
     *   tags={"Users"},
     *   summary="Get all users owned by the current customer",
     *   @OA\Response(response=200, description="All users owned by the current customer"),
     *   @OA\Response(response=401, description="JWT unauthorized error"),
     *   @OA\Response(response=404, description="No user found"),
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
     *   )
     * )
     */
    #[Route('/api/users', name: 'user_list', methods: ['GET'])]
    public function list(Pagination $paginator, Request $request): JsonResponse
    {
        $result = $paginator->paginate(
            'SELECT user
            FROM App\Entity\User user
            WHERE user.customer = :id
            ORDER BY user.id DESC',
            ['id' => $this->getUser()->getId()]
        );

        $serializerContext = SerializationContext::create()->setGroups(['user:list']);
        $jsonUsers = $this->serializer->serialize($result, 'json', $serializerContext);

        $response = new JsonResponse($jsonUsers, JsonResponse::HTTP_OK, [], true);
        $response->setEtag(md5($response->getContent()));
        $response->setPublic();
        $response->isNotModified($request);

        return $response;
    }

    /**
     * @OA\Get(
     *   tags={"Users"},
     *   summary="Get a user by ID",
     *   @OA\Response(response=200, description="User details"),
     *   @OA\Response(response=401, description="JWT unauthorized error"),
     *   @OA\Response(response=404, description="No user found with this ID"),
     *   @OA\PathParameter(
     *     name="id",
     *     description="ID of the user you want to recover"
     *   )
     * )
     */
    #[Route('/api/users/{id}', name: 'user_show', methods: ['GET'])]
    public function show(?User $user, Request $request): JsonResponse
    {
        $this->userNotExist($user);
        $this->isNotOwner(
            'USER_SHOW',
            $user,
            'You are not authorized to see this content'
        );

        $serializerContext = SerializationContext::create()->setGroups(['user:show']);
        $jsonUser = $this->serializer->serialize(
            $user,
            'json',
            $serializerContext
        );

        $response = new JsonResponse($jsonUser, JsonResponse::HTTP_OK, [], true);
        $response->setEtag(md5($response->getContent()));
        $response->setPublic();
        $response->isNotModified($request);

        return $response;
    }

    /**
     * @OA\Put(
     *   tags={"Users"},
     *   summary="Update a user by ID",
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(
     *         type="object",
     *         @OA\Property(
     *           property="firstname",
     *           description="Username for user identification",
     *           type="string",
     *           default="John"
     *         ),
     *         @OA\Property(
     *           property="lastname",
     *           description="User's choosen password",
     *           type="string",
     *           default="Doe"
     *         ),
     *         @OA\Property(
     *           property="email",
     *           description="User's first name",
     *           type="string",
     *           default="john.doe@email.com"
     *         ),
     *       )
     *     )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="User successfully updated",
     *     @OA\JsonContent(
     *       type="array",
     *       @OA\Items(ref=@Model(type=User::class, groups={"user:show"}))
     *     )
     *   ),
     *   @OA\Response(response=400, description="JSON field validation failed"),
     *   @OA\Response(
     *         response=401,
     *         description="JWT unauthorized error"
     *   ),
     *   @OA\Response(response=404, description="No user found with this ID"),
     *   @OA\Response(response=500, description="JSON syntax error or no JSON sent in the request body"),
     * )
     */
    #[Route('/api/users/{id}', name: 'user_update', methods: ['PUT'])]
    public function update(Request $request, ?User $user): JsonResponse
    {
        $this->userNotExist($user);
        $this->isNotOwner('USER_UPDATE', $user, 'You are not authorized to see this content');

        $jsonReceived = $request->getContent();
        $userUpdated = $this->serializer->deserialize($jsonReceived, User::class, 'json');

        $user->getFirstname() == $userUpdated->getFirstname() ?: $user->setFirstname($userUpdated->getFirstname());
        $user->getLastname() == $userUpdated->getLastname() ?: $user->setLastname($userUpdated->getLastname());
        $user->getEmail() == $userUpdated->getEmail() ?: $user->setEmail($userUpdated->getEmail());

        $errors = $this->validator->validate($user);

        if (count($errors) > 0) {
            return $this->json($errors, JsonResponse::HTTP_BAD_REQUEST);
        }

        $this->em->flush();

        $serializerContext = SerializationContext::create()->setGroups(['user:show']);
        $jsonUser = $this->serializer->serialize($user, 'json', $serializerContext);
        return new JsonResponse($jsonUser, JsonResponse::HTTP_OK, [], true);
    }

    /**
     * @OA\Delete(
     *   tags={"Users"},
     *   summary="Delete a user by ID",
     *   @OA\Response(response=200, description="Success message"),
     *   @OA\Response(response=401, description="JWT unauthorized error"),
     *   @OA\Response(response=404, description="No user found with this ID"),
     *   @OA\PathParameter(
     *     name="id",
     *     description="ID of the user you want to delete"
     *   )
     * )
     */
    #[Route('/api/users/{id}', name: 'user_delete', methods: ['DELETE'])]
    public function delete(?User $user): JsonResponse
    {
        $this->userNotExist($user);
        $this->isNotOwner('USER_DELETE', $user, 'You are not authorized to delete this content');

        $this->em->remove($user);
        $this->em->flush();

        return new JsonResponse([
            'code' => JsonResponse::HTTP_OK,
            'message' => 'The user has been successfully deleted'
        ], JsonResponse::HTTP_OK);
    }

    public function userNotExist(?User $user)
    {
        if (!$user) {
            throw new NotFoundHttpException("No user found with this ID");
        }
    }

    public function isNotOwner(string $attribute, User $user, string $message)
    {
        // If current customer is not the owner return an exception
        if (!$this->isGranted($attribute, $user)) {
            throw new HttpException(JsonResponse::HTTP_UNAUTHORIZED, $message);
        }
    }
}
