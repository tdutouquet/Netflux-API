<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

#[Route('/api')]
class SecurityController extends AbstractController
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    #[Route('/register', name: 'app_register', methods: ['POST'])]
    public function register(Request $request, UserPasswordHasherInterface $passwordHasher): Response
    {
        $data = json_decode($request->getContent(), true);

        $user = new User();
        $user->setEmail($data['email']);
        $user->setPassword(
            $passwordHasher->hashPassword(
                $user,
                $data['password']
            )
        );
        $user->setRoles(['ROLE_USER']);

        $this->em->persist($user);
        $this->em->flush();

        return $this->json([
            'message' => 'Utilisateur créé avec succès',
            // 'user' => $user
        ], 201);
    }

    #[Route('/login', name: 'app_login', methods: ['POST'])]
    public function login(Request $request, UserRepository $userRepo, UserPasswordHasherInterface $passwordHasher, JWTTokenManagerInterface $JWTManager, SerializerInterface $serializer): Response
    {
        $data = json_decode($request->getContent(), true);

        $user = $userRepo->findOneByEmail($data['email']);

        if (!$user || !$passwordHasher->isPasswordValid($user, $data['password'])) {
            return $this->json([
                'message' => 'Informations de connexion incorrectes'
            ], JsonResponse::HTTP_UNAUTHORIZED);
        }

        $token = $JWTManager->create($user);

        $serializedUser = $serializer->serialize($user, 'json', ['groups' => 'main']);

        $response = new Response($serializedUser, 200, ['Content-Type' => 'application/json']);

        // $response = $this->json([
        //     'message' => 'Connexion réussie',
        //     'user' => $serializedUser
        // ], 200);

        $response->headers->setCookie(new Cookie('BEARER', $token, time() + 3600, '/', null, true, true));
        return $response;
    }

    #[Route('/logout', name: 'app_logout', methods: ['POST'])]
    public function logout(): JsonResponse
    {
        $response = $this->json([
            'message' => 'Déconnexion réussie'
        ], 200);

        $response->headers->clearCookie('BEARER');
        return $response;
    }
}
