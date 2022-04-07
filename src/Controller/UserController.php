<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\Pagination;
use JMS\Serializer\SerializerInterface;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializationContext;
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

    #[Route('/api/users', name: 'user_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $jsonReceived = $request->getContent();

        $user = $this->serializer->deserialize($jsonReceived, User::class, 'json');
        $user->setCustomer($this->getUser());
        $user->setCreatedAt(new \DatetimeImmutable());

        $errors = $this->validator->validate($user);

        if (count($errors) > 0) {
            return $this->json($errors, JsonResponse::HTTP_BAD_REQUEST);
        }

        $this->em->persist($user);
        $this->em->flush();

        $serializerContext = SerializationContext::create()->setGroups(['user:show']);
        $jsonUser = $this->serializer->serialize($user, 'json', $serializerContext);
        return new JsonResponse($jsonUser, JsonResponse::HTTP_OK, [], true);
    }

    #[Route('/api/users', name: 'user_list', methods: ['GET'])]
    #[Route('/api/users/page/{page}', name: 'user_list_paginated', methods: ['GET'])]
    #[Route('/api/users/page/{page}/limit/{limit}', name: 'user_list_paginated_limit', methods: ['GET'])]
    public function list(Pagination $paginator, int $page = 1, int $limit = 10): JsonResponse
    {
        $result = $paginator->paginate(
            'SELECT user FROM App\Entity\User user WHERE user.customer = :id ORDER BY user.id DESC',
            $page,
            $limit,
            ['id' => $this->getUser()->getId()]
        );

        $serializerContext = SerializationContext::create()->setGroups(['user:list']);
        $jsonUsers = $this->serializer->serialize($result, 'json', $serializerContext);

        return new JsonResponse($jsonUsers, JsonResponse::HTTP_OK, [], true);
    }

    #[Route('/api/users/{id}', name: 'user_show', methods: ['GET'])]
    public function show(?User $user): JsonResponse
    {
        $this->userNotExist($user);
        $this->isNotOwner('USER_SHOW', $user, 'You are not authorized to see this content');

        $serializerContext = SerializationContext::create()->setGroups(['user:show']);
        $jsonUser = $this->serializer->serialize($user, 'json', $serializerContext);
        return new JsonResponse($jsonUser, JsonResponse::HTTP_OK, [], true);
    }

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
