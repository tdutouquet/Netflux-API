<?php

namespace App\Controller;

use App\Entity\Movies;
use App\Repository\MoviesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api')]
class MoviesController extends AbstractController
{
    private $moviesRepo;
    private $em;

    public function __construct(MoviesRepository $moviesRepo, EntityManagerInterface $em) {
        $this->moviesRepo = $moviesRepo;
        $this->em = $em;
    }

    #[Route('/movies', name: 'get_movies', methods: ['GET'])]
    public function getAllMovies(): JsonResponse
    {
        $movies = $this->moviesRepo->findAll();

        return $this->json($movies);
    }

    #[Route('/movies/{id}', name: 'get_movie', methods: ['GET'])]
    public function getMovie($id): JsonResponse
    {
        $movie = $this->moviesRepo->find($id);

        if(!$movie) {
            return $this->json(['message' => 'Ce film n\'existe pas'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($movie);
    }

    #[Route('/movies', name: 'add_movie', methods: ['POST'])]
    public function addMovie(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $movie = new Movies();
        $movie->setTitle($data['title']);
        $movie->setDescription($data['description']);
        $movie->setDate($data['date']);
        $movie->setDirector($data['director']);

        $this->em->persist($movie);
        $this->em->flush();

        return $this->json($movie);
    }

    #[Route('/movies/{id}', name: 'update_movie', methods: ['PUT'])]
    public function updateMovie($id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $movie = $this->moviesRepo->find($id);

        if(!$movie) {
            return $this->json(['message' => 'Ce film n\'existe pas'], Response::HTTP_NOT_FOUND);
        }

        $movie->setTitle($data['title'] ?? $movie->getTitle());
        $movie->setDescription($data['description'] ?? $movie->getDescription());
        $movie->setDate($data['date'] ?? $movie->getDate());
        $movie->setDirector($data['director'] ?? $movie->getDirector());

        $this->em->persist($movie);
        $this->em->flush();

        return $this->json($movie);
    }

    #[Route('/movies/{id}', name: 'delete_movie', methods: ['DELETE'])]
    public function deleteMovie($id): JsonResponse
    {
        $movie = $this->moviesRepo->find($id);

        if(!$movie) {
            return $this->json(['message' => 'Ce film n\'existe pas'], Response::HTTP_NOT_FOUND);
        }

        $this->em->remove($movie);
        $this->em->flush();

        return new JsonResponse(['message' => 'Film supprimé'], Response::HTTP_OK);
    }
}
