<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api')]
class UserController extends AbstractController
{
    private $userRepo;
    private $em;
    private $serializer;

    public function __construct(UserRepository $userRepo, EntityManagerInterface $em, SerializerInterface $serializer)
    {
        $this->userRepo = $userRepo;
        $this->em = $em;
        $this->serializer = $serializer;
    }

    #[Route('/users', name: 'get_users', methods: ['GET'])]
    public function getAllUsers(): Response
    {
        $users = $this->userRepo->findAll();

        $serializedUsers = $this->serializer->serialize($users, 'json', ['groups' => 'main']);

        return new Response($serializedUsers, 200, ['Content-Type' => 'application/json']);
    }

    #[Route('/users/{id}', name: 'get_user', methods: ['GET'])]
    public function getIndUser($id): Response
    {
        $user = $this->userRepo->find($id);

        if (!$user) {
            return $this->json(['message' => 'Cet utilisateur n\'existe pas'], Response::HTTP_NOT_FOUND);
        }

        $serializedUser = $this->serializer->serialize($user, 'json', ['groups' => 'main']);

        return new Response($serializedUser, 200, ['Content-Type' => 'application/json']);
    }

    #[Route('/users/{id}', name: 'update_user', methods: ['PUT'])]
    public function updateUser($id, Request $request): JsonResponse
    {
        $user = $this->userRepo->find($id);

        if (!$user) {
            return $this->json(['message' => 'Cet utilisateur n\'existe pas'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        $user->setEmail($data['email'] ?? $user->getEmail());
        $user->setPassword($user->getPassword());
        $user->setBanned($data['isBanned'] ?? $user->isBanned());
        $data['isAdmin'] ? $user->setRoles(['ROLE_ADMIN']) : $user->setRoles(['ROLE_USER']);
        
        $this->em->persist($user);
        $this->em->flush();

        return $this->json([
            'message' => 'Utilisateur mis à jour avec succès',
            // 'user' => $user
        ], 200);
    }

    #[Route('/users/{id}', name: 'delete_user', methods: ['DELETE'])]
    public function deleteUser($id): JsonResponse
    {
        $user = $this->userRepo->find($id);

        if (!$user) {
            return $this->json(['message' => 'Cet utilisateur n\'existe pas'], Response::HTTP_NOT_FOUND);
        }

        $this->em->remove($user);
        $this->em->flush();

        return $this->json(['message' => 'Utilisateur supprimé avec succès'], 200);
    }
}
