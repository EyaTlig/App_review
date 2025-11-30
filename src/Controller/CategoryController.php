<?php

namespace App\Controller;

use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/category')]
class CategoryController extends AbstractController
{
    #[Route('/', name: 'category_index')]
    public function index(EntityManagerInterface $em): Response
    {
        $categories = $em->getRepository(Category::class)->findAll();

        return $this->render('category/category.html.twig', [
            'categories' => $categories,
        ]);
    }

    #[Route('/add', name: 'category_add', methods: ['POST'])]
    public function add(Request $request, EntityManagerInterface $em): Response
    {
        $name = $request->request->get('name');
        if (!$name) {
            $this->addFlash('error', 'Le nom est obligatoire');
            return $this->redirectToRoute('category_index');
        }

        $category = new Category();
        $category->setName($name);

        $em->persist($category);
        $em->flush();

        $this->addFlash('success', 'Catégorie ajoutée avec succès !');

        return $this->redirectToRoute('category_index');
    }

    #[Route('/edit/{id}', name: 'category_edit', methods: ['POST'])]
    public function edit(int $id, Request $request, EntityManagerInterface $em): Response
    {
        $category = $em->getRepository(Category::class)->find($id);
        if (!$category) {
            $this->addFlash('error', 'Catégorie non trouvée.');
            return $this->redirectToRoute('category_index');
        }

        $name = $request->request->get('name');
        if (!$name) {
            $this->addFlash('error', 'Le nom est obligatoire');
            return $this->redirectToRoute('category_index');
        }

        $category->setName($name);
        $em->flush();

        $this->addFlash('success', 'Catégorie modifiée avec succès !');

        return $this->redirectToRoute('category_index');
    }

    #[Route('/delete/{id}', name: 'category_delete')]
    public function delete(int $id, EntityManagerInterface $em): Response
    {
        $category = $em->getRepository(Category::class)->find($id);
        if ($category) {
            $em->remove($category);
            $em->flush();
            $this->addFlash('success', 'Catégorie supprimée avec succès !');
        } else {
            $this->addFlash('error', 'Catégorie non trouvée.');
        }

        return $this->redirectToRoute('category_index');
    }
}
