<?php

namespace App\Controller;

use App\Entity\Likes;
use App\Repository\UserRepository;
use App\Repository\LikesRepository;
use App\Repository\MoviesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api')]
class LikesController extends AbstractController
{
    private $likesRepo;
    private $em;

    public function __construct(LikesRepository $likesRepo, EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->likesRepo = $likesRepo;
    }

    #[Route('/likes', name: 'add_like', methods: ['POST'])]
    public function addLike(Request $request, UserRepository $userRepo, MoviesRepository $moviesRepo): Response
    {
        $data = json_decode($request->getContent(), true);

        $user = $userRepo->findOneByEmail($data['userEmail']);
        if (!$user) {
            return $this->json(['message' => 'Cet utilisateur n\'existe pas'], Response::HTTP_NOT_FOUND);
        }

        $movie = $moviesRepo->findOneById($data['movieId']);
        if (!$movie) {
            return $this->json(['message' => 'Ce film n\'existe pas'], Response::HTTP_NOT_FOUND);
        }

        $existingLike = $this->likesRepo->findOneBy([
            'user' => $user,
            'movie' => $movie
        ]);
        if ($existingLike) {
            return $this->json(['message' => 'Like déjà comptabilisé'], Response::HTTP_BAD_REQUEST);
        }

        $like = new Likes();
        $like->setUser($user);
        $like->setMovie($movie);
        $this->em->persist($like);
        $this->em->flush();

        return $this->json(['message' => 'Like comptabilisé'], Response::HTTP_CREATED);
    }

    #[Route('/likes/{id}', name: 'delete_like', methods: ['DELETE'])]
    public function deleteLike($id): JsonResponse
    {
        $like = $this->likesRepo->findOneById($id);

        if (!$like) {
            return $this->json(['message' => 'Ce like n\'existe pas'], Response::HTTP_NOT_FOUND);
        }

        $this->em->remove($like);
        $this->em->flush();

        return $this->json(['message' => 'Like supprimé'], Response::HTTP_OK);
    }
}
