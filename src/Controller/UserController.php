<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
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
    private $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    #[Route('/api/users', name: 'user_create', methods: ['POST'])]
    public function create(Request $request, ValidatorInterface $validator, EntityManagerInterface $em): JsonResponse
    {
        $jsonReceived = $request->getContent();

        $user = $this->serializer->deserialize($jsonReceived, User::class, 'json');
        $user->setCustomer($this->getUser());
        $user->setCreatedAt(new \DatetimeImmutable());

        $errors = $validator->validate($user);

        if (count($errors) > 0) {
            return $this->json($errors, JsonResponse::HTTP_BAD_REQUEST);
        }

        $em->persist($user);
        $em->flush();

        $serializerContext = SerializationContext::create()->setGroups(['user:show']);
        $jsonUser = $this->serializer->serialize($user, 'json', $serializerContext);
        return new JsonResponse($jsonUser, JsonResponse::HTTP_OK, [], true);
    }

    #[Route('/api/users', name: 'user_list', methods: ['GET'])]
    public function list(UserRepository $userRepository): JsonResponse
    {
        $users = $userRepository->findBy(['customer' => $this->getUser()->getId()]);
        $serializerContext = SerializationContext::create()->setGroups(['user:list']);
        $jsonUsers = $this->serializer->serialize($users, 'json', $serializerContext);

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

    #[Route('/api/users/{id}', name: 'user_delete', methods: ['DELETE'])]
    public function delete(EntityManagerInterface $em, ?User $user): JsonResponse
    {
        $this->userNotExist($user);
        $this->isNotOwner('USER_DELETE', $user, 'You are not authorized to delete this content');

        $em->remove($user);
        $em->flush();

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
