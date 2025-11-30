<?php
namespace App\Controller;

use App\Repository\BusinessRepository;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class HomeController extends AbstractController
{
    #[Route('/', name: 'client_home')]
    public function index(BusinessRepository $businessRepo, CategoryRepository $categoryRepo, Request $request): Response
    {
        $search = $request->query->get('search');   // valeur du champ de recherche
        $categoryId = $request->query->get('category');

        $categories = $categoryRepo->findAll();

        // Filtrage des businesses
        $businesses = $businessRepo->findBySearchAndCategory($search, $categoryId);

        return $this->render('home/home.html.twig', [
            'businesses' => $businesses,
            'categories' => $categories,
        ]);
    }



}
