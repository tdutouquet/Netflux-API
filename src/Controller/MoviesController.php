<?php

namespace App\Controller;

use App\Entity\Movies;
use App\Entity\Categories;
use App\Repository\MoviesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api')]
class MoviesController extends AbstractController
{
    private $moviesRepo;
    private $em;
    private $serializer;

    public function __construct(MoviesRepository $moviesRepo, EntityManagerInterface $em, SerializerInterface $serializer)
    {
        $this->moviesRepo = $moviesRepo;
        $this->em = $em;
        $this->serializer = $serializer;
    }

    #[Route('/movies', name: 'get_movies', methods: ['GET'])]
    public function getAllMovies(): Response
    {
        $movies = $this->moviesRepo->findAll();

        $serializedMovies = $this->serializer->serialize($movies, 'json', ['groups' => 'main']);

        return new Response($serializedMovies, 200, ['Content-Type' => 'application/json']);
    }

    #[Route('/movies/{id}', name: 'get_movie', methods: ['GET'])]
    public function getMovie($id): Response
    {
        $movie = $this->moviesRepo->find($id);

        if (!$movie) {
            return $this->json(['message' => 'Ce film n\'existe pas'], Response::HTTP_NOT_FOUND);
        }

        $serializedMovie = $this->serializer->serialize($movie, 'json', ['groups' => 'main']);

        return new Response($serializedMovie, 200, ['Content-Type' => 'application/json']);
    }

    #[Route('/movies', name: 'add_movie', methods: ['POST'])]
    public function addMovie(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $categories = $this->em->getRepository(Categories::class)->findAll();

        $movie = new Movies();
        $movie->setTitle($data['title']);
        $movie->setDescription($data['description']);
        $movie->setDate($data['date']);
        $movie->setDirector($data['director']);
        foreach ($data['categories'] as $category) {
            $movie->addCategory($categories[$category]); // valable si on récupère les categs sous forme d'id
        }
        $this->em->persist($movie);
        $this->em->flush();

        $serializedMovie = $this->serializer->serialize($movie, 'json', ['groups' => 'main']);

        return $this->json([
            'message' => 'Film ajouté avec succès',
            'movie' => $serializedMovie
        ], 201);
    }

    #[Route('/movies/{id}', name: 'update_movie', methods: ['PUT'])]
    public function updateMovie($id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $movie = $this->moviesRepo->find($id);
        $categories = $this->em->getRepository(Categories::class)->findAll();

        if (!$movie) {
            return $this->json(['message' => 'Ce film n\'existe pas'], Response::HTTP_NOT_FOUND);
        }

        $movie->setTitle($data['title'] ?? $movie->getTitle());
        $movie->setDescription($data['description'] ?? $movie->getDescription());
        $movie->setDate($data['date'] ?? $movie->getDate());
        $movie->setDirector($data['director'] ?? $movie->getDirector());
        if (isset($data['categories'])) {
            foreach ($data['categories'] as $category) {
                $movie->addCategory($categories[$category]);
            }
        } else {
            $movie->getCategories();
        }

        $this->em->persist($movie);
        $this->em->flush();

        $serializedMovie = $this->serializer->serialize($movie, 'json', ['groups' => 'main']);

        return $this->json([
            'message' => 'Film modifié avec succès',
            'movie' => $serializedMovie
        ]);
    }

    #[Route('/movies/{id}', name: 'delete_movie', methods: ['DELETE'])]
    public function deleteMovie($id): JsonResponse
    {
        $movie = $this->moviesRepo->find($id);

        if (!$movie) {
            return $this->json(['message' => 'Ce film n\'existe pas'], Response::HTTP_NOT_FOUND);
        }

        $this->em->remove($movie);
        $this->em->flush();

        return new JsonResponse(['message' => 'Film supprimé'], Response::HTTP_OK);
    }
}
