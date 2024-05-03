<?php

namespace App\Controller;

use App\Entity\Comments;
use App\Repository\UserRepository;
use App\Repository\MoviesRepository;
use App\Repository\CommentsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api')]
class CommentsController extends AbstractController
{
    private $commentsRepo;
    private $serializer;
    private $em;

    public function __construct(CommentsRepository $commentsRepo, SerializerInterface $serializer, EntityManagerInterface $em)
    {
        $this->commentsRepo = $commentsRepo;
        $this->serializer = $serializer;
        $this->em = $em;
    }

    #[Route('/comments', name: 'get_comments', methods: ['GET'])]
    public function getAllComments(): Response
    {
        $comments = $this->commentsRepo->findAll();

        $serializedComments = $this->serializer->serialize($comments, 'json', ['groups' => ['admin']]);

        return new Response($serializedComments, 200, ['Content-Type' => 'application/json']);
    }

    #[Route('/comments', name: 'add_comment', methods: ['POST'])]
    public function addComment(Request $request, UserRepository $userRepo, MoviesRepository $moviesRepo): JsonResponse
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

        $comment = new Comments();
        $comment->setContent($data['content']);
        $comment->setUser($user);
        $comment->setMovie($movie);
        $this->em->persist($comment);
        $this->em->flush();

        return new JsonResponse(['message' => 'Commentaire ajouté'], Response::HTTP_CREATED);
    }

    #[Route('/comments/{id}', name: 'delete_comment', methods: ['DELETE'])]
    public function deleteComment(int $id): JsonResponse
    {
        $comment = $this->commentsRepo->find($id);

        if (!$comment) {
            return $this->json(['message' => 'Ce commentaire n\'existe pas'], Response::HTTP_NOT_FOUND);
        }

        $this->em->remove($comment);
        $this->em->flush();

        return new JsonResponse(['message' => 'Commentaire supprimé'], Response::HTTP_OK);
    }
}
