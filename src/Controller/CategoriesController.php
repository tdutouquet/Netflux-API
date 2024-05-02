<?php

namespace App\Controller;

use App\Entity\Categories;
use App\Repository\CategoriesRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Util\Json;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api')]
class CategoriesController extends AbstractController
{
    private $categoriesRepo;
    private $em;

    public function __construct(CategoriesRepository $categoriesRepo, EntityManagerInterface $em) {
        $this->categoriesRepo = $categoriesRepo;
        $this->em = $em;
    }

    #[Route('/categories', name: 'get_categories', methods: ['GET'])]
    public function getCategories(SerializerInterface $serializer): Response
    {
        $categories = $this->categoriesRepo->findAll();

        $serializedCategories = $serializer->serialize($categories, 'json', ['groups' => ['main']]);
        
        return new Response($serializedCategories, 200, ['Content-Type' => 'application/json']);
    }

    #[Route('/categories/{id}', name: 'get_category', methods: ['GET'])]
    public function getCategory(SerializerInterface $serializer, int $id): Response
    {
        $category = $this->categoriesRepo->find($id);

        if (!$category) {
            return new JsonResponse(['message' => 'Category not found'], 404);
        }

        $serializedCategory = $serializer->serialize($category, 'json', ['groups' => ['main']]);

        return new Response($serializedCategory, 200, ['Content-Type' => 'application/json']);
    }

    #[Route('/categories', name: 'add_category', methods: ['POST'])]
    public function addCategory(SerializerInterface $serializer, Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        $category = $this->categoriesRepo->findOneBy(['name' => $data['name']]);

        if ($category) {
            return new JsonResponse(['message' => 'La catégorie existe déjà'], 400);
        }

        $newCat = new Categories();
        $newCat->setName($data['name']);
        $this->em->persist($newCat);
        $this->em->flush();

        $serializedCategory = $serializer->serialize($newCat, 'json', ['groups' => ['main']]);

        return new Response($serializedCategory, 201, ['Content-Type' => 'application/json']);
    }
    
    #[Route('/categories/{id}', name: 'update_category', methods: ['PUT'])]
    public function updateCategory(SerializerInterface $serializer, Request $request, int $id): Response
    {
        $data = json_decode($request->getContent(), true);
        $category = $this->categoriesRepo->find($id);

        if (!$category) {
            return new JsonResponse(['message' => 'Categorie non trouvée'], 404);
        }

        $category->setName($data['name'] ?? $category->getName());
        $this->em->persist($category);
        $this->em->flush();

        $serializedCategory = $serializer->serialize($category, 'json', ['groups' => ['main']]);

        return new Response($serializedCategory, 200, ['Content-Type' => 'application/json']);
    }
    
    #[Route('/categories/{id}', name: 'delete_category', methods: ['DELETE'])]
    public function deleteCategory(int $id): Response
    {
        $category = $this->categoriesRepo->find($id);

        if (!$category) {
            return new JsonResponse(['message' => 'Categorie non trouvée'], 404);
        }

        $this->em->remove($category);
        $this->em->flush();

        return new JsonResponse(['message' => 'Categorie supprimée'], 200);
    }
}
