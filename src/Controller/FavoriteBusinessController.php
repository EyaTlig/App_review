<?php

namespace App\Controller;

use App\Entity\Business;
use App\Entity\FavoriteBusiness;
use App\Repository\FavoriteBusinessRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class FavoriteBusinessController extends AbstractController
{
    #[Route('/business/{id}/favorite-toggle', name: 'business_favorite_toggle', methods: ['POST'])]
    public function toggle(Business $business, FavoriteBusinessRepository $repo, EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'Not authenticated'], 401);
        }

        $existing = $repo->findOneBy([
            'business' => $business,
            'user' => $user
        ]);

        if ($existing) {
            $em->remove($existing);
            $em->flush();
            return new JsonResponse(['status' => 'removed']);
        }

        $fav = new FavoriteBusiness();
        $fav->setBusiness($business);
        $fav->setUser($user);

        $em->persist($fav);
        $em->flush();

        return new JsonResponse(['status' => 'added']);
    }
}