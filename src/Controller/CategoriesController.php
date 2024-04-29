<?php

namespace App\Controller;

use App\Repository\CategoriesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api')]
class CategoriesController extends AbstractController
{
    private $categoriesRepo;

    public function __construct(CategoriesRepository $categoriesRepo) {
        $this->categoriesRepo = $categoriesRepo;
    }

    #[Route('/categories', name: 'get_categories', methods: ['GET'])]
    public function getCategories(SerializerInterface $serializer): Response
    {
        $categories = $this->categoriesRepo->findAll();

        $serializedCategories = $serializer->serialize($categories, 'json', ['groups' => ['main']]);
        
        return new Response($serializedCategories, 200, ['Content-Type' => 'application/json']);
    }
}
