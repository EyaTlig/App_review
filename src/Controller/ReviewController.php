<?php

namespace App\Controller;

use App\Entity\Review;
use App\Entity\User;
use App\Entity\ReviewPhoto;

use App\Entity\Business;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;


#[Route('/review')]

class ReviewController extends AbstractController
{
    #[Route('/', name: 'review_index')]
    #[IsGranted('ROLE_ADMIN')]

    public function index(EntityManagerInterface $em): Response
    {
        $reviews = $em->getRepository(Review::class)->findAll();
        $users = $em->getRepository(User::class)->findAll();
        $businesses = $em->getRepository(Business::class)->findAll();

        return $this->render('review/review.html.twig', [
            'reviews' => $reviews,
            'users' => $users,
            'businesses' => $businesses
        ]);
    }

    #[Route('/add', name: 'add_review', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]

    public function add(Request $request, EntityManagerInterface $em): Response
    {
        $user = $em->getRepository(User::class)->find($request->request->get('user'));
        $business = $em->getRepository(Business::class)->find($request->request->get('business'));
        $content = $request->request->get('content');
        $rating = $request->request->get('rating');

        $review = new Review();
        $review->setUser($user);
        $review->setBusiness($business);
        $review->setComment($content);
        $review->setRating($rating);

        $em->persist($review);
        $em->flush();

        // ✅ Vérifier si la case “Ajouter des photos maintenant” est cochée
        $redirectPhotos = $request->request->get('addPhotos') !== null;
        if ($redirectPhotos) {
            return $this->redirectToRoute('review_photo_add', ['reviewId' => $review->getId()]);
        }

        $this->addFlash('success', 'Review ajoutée avec succès !');
        return $this->redirectToRoute('review_index');
    }

    #[Route('/edit/{id}', name: 'review_edit', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]

    public function edit(Review $review, Request $request, EntityManagerInterface $em): Response
    {
        $user = $em->getRepository(User::class)->find($request->request->get('user'));
        $business = $em->getRepository(Business::class)->find($request->request->get('business'));
        $content = $request->request->get('content');
        $rating = $request->request->get('rating');

        $review->setUser($user);
        $review->setBusiness($business);
        $review->setComment($content);
        $review->setRating($rating);

        $em->flush();

        // ✅ Vérifier si la case “Ajouter des photos maintenant” est cochée
        $redirectPhotos = $request->request->get('addPhotos') !== null;
        if ($redirectPhotos) {
            return $this->redirectToRoute('review_photo_add', ['reviewId' => $review->getId()]);
        }

        $this->addFlash('success', 'Review modifiée avec succès !');
        return $this->redirectToRoute('review_index');
    }

    #[Route('/delete/{id}', name: 'review_delete')]
    #[IsGranted('ROLE_ADMIN')]

    public function delete(Review $review, EntityManagerInterface $em): Response
    {
        $em->remove($review);
        $em->flush();

        $this->addFlash('success', 'Review supprimée avec succès !');
        return $this->redirectToRoute('review_index');
    }

    #[Route('/business/{id}/review/add', name: 'review_add', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]

    public function addReview(Request $request, Business $business, EntityManagerInterface $em): Response
    {
        $review = new Review();
        $review->setBusiness($business);
        $review->setUser($this->getUser());
        $review->setRating($request->request->get('rating'));
        $review->setComment($request->request->get('comment'));

        $em->persist($review);

        $uploadedFiles = $request->files->get('photos');
        if ($uploadedFiles) {
            foreach ($uploadedFiles as $file) {
                if ($file) {
                    $filename = uniqid() . '.' . $file->guessExtension();
                    $file->move(
                        $this->getParameter('review_photos_directory'),
                        $filename
                    );

                    $reviewPhoto = new ReviewPhoto();
                    $reviewPhoto->setReview($review);
                    $reviewPhoto->setUser($this->getUser());
                    $reviewPhoto->setUrl($filename);

                    $em->persist($reviewPhoto);
                }
            }
        }

        $em->flush();

        return $this->redirectToRoute('client_home');
    }

}
