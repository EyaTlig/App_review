<?php

namespace App\Controller;

use App\Entity\Business;
use App\Entity\BusinessPhoto;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

#[Route('/business/photo')]
class BusinessPhotoController extends AbstractController
{
    #[Route('/add/global', name: 'business_photo_add_global')]
    public function addGlobal(Request $request, EntityManagerInterface $em): Response
    {
        $businesses = $em->getRepository(Business::class)->findAll();

        if ($request->isMethod('POST')) {
            $businessId = $request->request->get('business');
            $business = $em->getRepository(Business::class)->find($businessId);
            $files = $request->files->get('photos');

            if (!$business || !$files) {
                $this->addFlash('error', 'Veuillez choisir un business et des photos.');
                return $this->redirectToRoute('business_photo_add_global');
            }

            foreach ($files as $file) {
                $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = preg_replace('/[^a-zA-Z0-9_-]/', '', $originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();

                try {
                    $file->move(
                        $this->getParameter('business_photos_directory'),
                        $newFilename
                    );
                } catch (\Exception $e) {
                    continue;
                }

                $photo = new BusinessPhoto();
                $photo->setFilename($newFilename);
                $photo->setBusiness($business);
                $em->persist($photo);
            }

            $em->flush();
            $this->addFlash('success', 'Photos ajoutées avec succès !');
            return $this->redirectToRoute('business_photo_all');
        }

        return $this->render('business/photo_add.html.twig', [
            'businesses' => $businesses,
        ]);
    }


    #[Route('/add/{businessId}', name: 'business_photo_add', methods: ['GET','POST'])]
    public function add(int $businessId, Request $request, EntityManagerInterface $em): Response
    {
        $business = $em->getRepository(Business::class)->find($businessId);
        if (!$business) {
            $this->addFlash('error', 'Business introuvable.');
            return $this->redirectToRoute('business_index');
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
                            $this->getParameter('business_photos_directory'),
                            $newFilename
                        );
                    } catch (FileException $e) {
                        $this->addFlash('error', 'Erreur lors de l\'upload de l\'image');
                        continue;
                    }

                    $photo = new BusinessPhoto();
                    $photo->setFilename($newFilename);
                    $photo->setBusiness($business);
                    $em->persist($photo);
                }
                $em->flush();
                $this->addFlash('success', 'Photos ajoutées avec succès !');
            }

            return $this->redirectToRoute('business_photo_all', ['businessId' => $business->getId()]);
        }

        return $this->render('business/photo_add.html.twig', [
            'business' => $business,
            'photos' => $business->getPhotos(),
        ]);
    }

    #[Route('/edit/{id}', name: 'business_photo_edit', methods: ['GET','POST'])]
    public function edit(int $id, Request $request, EntityManagerInterface $em): Response
    {
        $photo = $em->getRepository(BusinessPhoto::class)->find($id);
        if (!$photo) {
            $this->addFlash('error', 'Photo introuvable.');
            return $this->redirectToRoute('business_photo_all');
        }

        $businesses = $em->getRepository(Business::class)->findAll();

        if ($request->isMethod('POST')) {
            $businessId = $request->request->get('business');
            $business = $em->getRepository(Business::class)->find($businessId);
            $file = $request->files->get('photo');

            if ($file) {
                $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = preg_replace('/[^a-zA-Z0-9_-]/', '', $originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();

                try {
                    $file->move(
                        $this->getParameter('business_photos_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload de la photo.');
                    return $this->redirectToRoute('business_photo_all');
                }

                $photo->setFilename($newFilename);
            }

            if ($business) {
                $photo->setBusiness($business);
            }

            $em->flush();
            $this->addFlash('success', 'Photo modifiée avec succès !');
            return $this->redirectToRoute('business_photo_all');
        }

        return $this->render('business/photo_edit.html.twig', [
            'photo' => $photo,
            'businesses' => $businesses,
        ]);
    }


    #[Route('/all', name: 'business_photo_all')]
    public function all(EntityManagerInterface $em): Response
    {
        $photos = $em->getRepository(BusinessPhoto::class)->findAll();

        return $this->render('business/photo_list.html.twig', [
            'photos' => $photos,
        ]);
    }

    #[Route('/delete/{id}', name: 'business_photo_delete')]
    public function delete(int $id, EntityManagerInterface $em): Response
    {
        $photo = $em->getRepository(BusinessPhoto::class)->find($id);
        if ($photo) {
            $em->remove($photo);
            $em->flush();
            $this->addFlash('success', 'Photo supprimée avec succès !');
        } else {
            $this->addFlash('error', 'Photo introuvable.');
        }

        return $this->redirectToRoute('business_photo_all');
    }
}
