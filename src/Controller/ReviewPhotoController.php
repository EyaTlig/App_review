<?php

namespace App\Controller;

use App\Entity\Review;
use App\Entity\ReviewPhoto;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

#[Route('/review/photo')]
class ReviewPhotoController extends AbstractController
{
    #[Route('/all', name: 'review_photo_list', methods: ['GET', 'POST'])]
    public function list(Request $request, EntityManagerInterface $em): Response
    {
        $reviews = $em->getRepository(Review::class)->findAll();
        $users = $em->getRepository(User::class)->findAll();
        $photos = $em->getRepository(ReviewPhoto::class)->findAll();

        // Gestion de l'ajout via POST
        if ($request->isMethod('POST')) {
            $reviewId = $request->request->get('review');
            $userId = $request->request->get('user');
            $file = $request->files->get('photo');

            $review = $em->getRepository(Review::class)->find($reviewId);
            $user = $em->getRepository(User::class)->find($userId);

            if (!$review || !$user || !$file) {
                $this->addFlash('error', 'Veuillez remplir tous les champs et choisir une photo.');
                return $this->redirectToRoute('review_photo_list');
            }

            $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = preg_replace('/[^a-zA-Z0-9_-]/', '', $originalFilename);
            $newFilename = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();

            try {
                $file->move(
                    $this->getParameter('review_photos_directory'),
                    $newFilename
                );
            } catch (FileException $e) {
                $this->addFlash('error', 'Erreur lors de l\'upload de la photo.');
                return $this->redirectToRoute('review_photo_list');
            }

            $photo = new ReviewPhoto();
            $photo->setReview($review)
                ->setUser($user)
                ->setUrl($newFilename);

            $em->persist($photo);
            $em->flush();

            $this->addFlash('success', 'Photo ajoutée avec succès !');
            return $this->redirectToRoute('review_photo_list');
        }

        return $this->render('review_photo/list.html.twig', [
            'photos' => $photos,
            'reviews' => $reviews,
            'users' => $users
        ]);
    }

    #[Route('/add/{reviewId}', name: 'review_photo_add', methods:['GET','POST'])]
    public function add(int $reviewId, Request $request, EntityManagerInterface $em): Response
    {
        $review = $em->getRepository(Review::class)->find($reviewId);
        if (!$review) {
            $this->addFlash('error', 'Review introuvable.');
            return $this->redirectToRoute('review_index');
        }

        if ($request->isMethod('POST')) {
            $files = $request->files->get('photos');

            if ($files) {
                foreach ($files as $file) {
                    $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                    $safeFilename = preg_replace('/[^a-zA-Z0-9_-]/', '', $originalFilename);
                    $newFilename = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();

                    try {
                        $file->move(
                            $this->getParameter('review_photos_directory'),
                            $newFilename
                        );
                    } catch (FileException $e) {
                        $this->addFlash('error', 'Erreur lors de l\'upload de l\'image');
                        continue;
                    }
                    $photo = new ReviewPhoto();
                    $photo->setUrl($newFilename);
                    $photo->setReview($review);
                    $photo->setUser($review->getUser());
                    $em->persist($photo);
                }
                $em->flush();
                $this->addFlash('success', 'Photos ajoutées avec succès !');
            }

            return $this->redirectToRoute('review_index');
        }

        return $this->render('review/photo_add.html.twig', [
            'review' => $review,
            'photos' => $review->getPhotos(),
        ]);
    }


    #[Route('/delete/{id}', name: 'review_photo_delete')]
    public function delete(int $id, EntityManagerInterface $em): Response
    {
        $photo = $em->getRepository(ReviewPhoto::class)->find($id);
        if ($photo) {
            $em->remove($photo);
            $em->flush();
            $this->addFlash('success', 'Photo supprimée avec succès !');
        } else {
            $this->addFlash('error', 'Photo introuvable.');
        }

        return $this->redirectToRoute('review_photo_list');
    }
    #[Route('/edit', name: 'review_photo_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, EntityManagerInterface $em): Response
    {
        $id = $request->request->get('id');
        $photo = $em->getRepository(ReviewPhoto::class)->find($id);

        if (!$photo) {
            $this->addFlash('error', 'Photo introuvable.');
            return $this->redirectToRoute('review_photo_list');
        }

        // Récupérer toutes les reviews et users pour les formulaires
        $reviews = $em->getRepository(Review::class)->findAll();
        $users = $em->getRepository(User::class)->findAll();

        if ($request->isMethod('POST')) {
            // Récupérer les données du formulaire
            $reviewId = $request->request->get('review');
            $userId = $request->request->get('user');
            $file = $request->files->get('photo');

            // Validation des données
            if (!$reviewId || !$userId) {
                $this->addFlash('error', 'Veuillez sélectionner un avis et un utilisateur.');
                return $this->redirectToRoute('review_photo_edit', ['id' => $id]);
            }

            $review = $em->getRepository(Review::class)->find($reviewId);
            $user = $em->getRepository(User::class)->find($userId);

            if (!$review || !$user) {
                $this->addFlash('error', 'Avis ou utilisateur introuvable.');
                return $this->redirectToRoute('review_photo_edit', ['id' => $id]);
            }

            // Mettre à jour les relations
            $photo->setReview($review);
            $photo->setUser($user);

            // Gestion de la nouvelle photo si fournie
            if ($file && $file->getError() === UPLOAD_ERR_OK) {
                // Validation du fichier
                $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                if (!in_array($file->getMimeType(), $allowedMimeTypes)) {
                    $this->addFlash('error', 'Format de fichier non supporté. Utilisez JPG, PNG, GIF ou WEBP.');
                    return $this->redirectToRoute('review_photo_edit', ['id' => $id]);
                }

                // Taille maximale : 5MB
                if ($file->getSize() > 5 * 1024 * 1024) {
                    $this->addFlash('error', 'La taille du fichier ne doit pas dépasser 5MB.');
                    return $this->redirectToRoute('review_photo_edit', ['id' => $id]);
                }

                // Générer un nom de fichier unique
                $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = preg_replace('/[^a-zA-Z0-9_-]/', '', $originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();

                try {
                    // Supprimer l'ancienne photo si elle existe
                    $oldFilename = $photo->getUrl();
                    if ($oldFilename) {
                        $oldFilePath = $this->getParameter('review_photos_directory') . '/' . $oldFilename;
                        if (file_exists($oldFilePath)) {
                            unlink($oldFilePath);
                        }
                    }

                    // Déplacer la nouvelle photo
                    $file->move(
                        $this->getParameter('review_photos_directory'),
                        $newFilename
                    );

                    // Mettre à jour le nom du fichier
                    $photo->setUrl($newFilename);

                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload de la nouvelle photo.');
                    return $this->redirectToRoute('review_photo_edit', ['id' => $id]);
                }
            }


            try {
                $em->flush();
                $this->addFlash('success', 'Photo modifiée avec succès !');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de la modification de la photo.');
            }

            return $this->redirectToRoute('review_photo_list');
        }

        return $this->render('review_photo/edit.html.twig', [
            'photo' => $photo,
            'reviews' => $reviews,
            'users' => $users,
            'currentReviewId' => $photo->getReview()->getId(),
            'currentUserId' => $photo->getUser()->getId()
        ]);
    }
}
