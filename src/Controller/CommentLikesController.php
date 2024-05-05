<?php

namespace App\Controller;

use App\Entity\CommentLikes;
use App\Repository\UserRepository;
use App\Repository\MoviesRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\CommentLikesRepository;
use App\Repository\CommentsRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api')]
class CommentLikesController extends AbstractController
{
    private $commentLikesRepo;
    private $em;

    public function __construct(CommentLikesRepository $commentLikesRepo, EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->commentLikesRepo = $commentLikesRepo;
    }

    #[Route('/comment-likes', name: 'add_comment_likes')]
    public function addCommentLike(Request $request, UserRepository $userRepo, CommentsRepository $commentsRepo): Response
    {
        $data = json_decode($request->getContent(), true);

        $user = $userRepo->findOneByEmail($data['userEmail']);
        if (!$user) {
            return $this->json(['message' => 'Cet utilisateur n\'existe pas'], Response::HTTP_NOT_FOUND);
        }

        $comment = $commentsRepo->findOneById($data['commentId']);
        if (!$comment) {
            return $this->json(['message' => 'Ce commentaire n\'existe pas'], Response::HTTP_NOT_FOUND);
        }

        $existingCommentLike = $this->commentLikesRepo->findOneBy([
            'user' => $user,
            'comment' => $comment
        ]);
        if ($existingCommentLike) {
            return $this->json(['message' => 'Like déjà comptabilisé'], Response::HTTP_BAD_REQUEST);
        }

        $commentLike = new CommentLikes();
        $commentLike->setUser($user);
        $commentLike->setComment($comment);
        $this->em->persist($commentLike);
        $this->em->flush();

        return $this->json(['message' => 'Like comptabilisé'], Response::HTTP_CREATED);
    }

    #[Route('/comment-likes/{id}', name: 'delete_comment_like', methods: ['DELETE'])]
    public function deleteCommentLike(int $id): Response
    {
        $commentLike = $this->commentLikesRepo->findOneById($id);
        
        if (!$commentLike) {
            return $this->json(['message' => 'Like non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $this->em->remove($commentLike);
        $this->em->flush();

        return $this->json(['message' => 'Like supprimé'], Response::HTTP_OK);
    }
}
