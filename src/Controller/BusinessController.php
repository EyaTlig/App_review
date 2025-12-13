<?php

namespace App\Controller;

use App\Entity\Business;
use App\Entity\FavoriteBusiness;
use App\Entity\User;
use App\Entity\Category;
use App\Repository\FavoriteBusinessRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/business')]

class BusinessController extends AbstractController
{
    #[Route('/', name: 'business_index')]
    #[IsGranted('ROLE_ADMIN')]

    public function index(EntityManagerInterface $em): Response
    {
        $businesses = $em->getRepository(Business::class)->findAll();
        $users = $em->getRepository(User::class)->findBy(['role' => 'SERVICE_OWNER']);
        $categories = $em->getRepository(Category::class)->findAll();

        return $this->render('business/business.html.twig', [
            'businesses' => $businesses,
            'users' => $users,
            'categories' => $categories
        ]);
    }

    #[Route('/add', name: 'business_add', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]

    public function add(Request $request, EntityManagerInterface $em): Response
    {
        // Récupération et validation des données
        $name = trim($request->request->get('name', ''));
        $address = trim($request->request->get('address', ''));
        $phone = trim($request->request->get('phone', ''));
        $website = trim($request->request->get('website', ''));
        $description = trim($request->request->get('description', ''));
        $ownerId = $request->request->get('owner');
        $categoryId = $request->request->get('category');

        // Validation des champs obligatoires
        if (!$name || !$address || !$phone || !$ownerId || !$categoryId) {
            $this->addFlash('error', 'Tous les champs obligatoires doivent être remplis.');
            return $this->redirectToRoute('business_index');
        }

        // Validation du téléphone
        if (!preg_match('/^[\d\s\+\(\)\.-]+$/', $phone)) {
            $this->addFlash('error', 'Le format du téléphone est invalide.');
            return $this->redirectToRoute('business_index');
        }

        // Récupération des entités liées
        $owner = $em->getRepository(User::class)->find($ownerId);
        $category = $em->getRepository(Category::class)->find($categoryId);

        if (!$owner || !$category) {
            $this->addFlash('error', 'Propriétaire ou catégorie invalide.');
            return $this->redirectToRoute('business_index');
        }

        // Vérifier si un business avec le même nom existe déjà
        $existingBusiness = $em->getRepository(Business::class)->findOneBy(['name' => $name]);
        if ($existingBusiness) {
            $this->addFlash('error', 'Un business avec ce nom existe déjà.');
            return $this->redirectToRoute('business_index');
        }

        // Création du business
        $business = new Business();
        $business->setName($name)
            ->setAddress($address)
            ->setPhone($phone)
            ->setWebsite($website ?: null)
            ->setDescription($description ?: null)
            ->setOwner($owner)
            ->setCategory($category);

        $em->persist($business);
        $em->flush();

        // Redirection vers l'ajout de photos si demandé
        $redirectPhotos = $request->request->get('addPhotos') !== null;
        if ($redirectPhotos) {
            $this->addFlash('success', 'Business "' . $name . '" créé avec succès ! Vous pouvez maintenant ajouter des photos.');
            return $this->redirectToRoute('business_photo_add', ['businessId' => $business->getId()]);
        }

        $this->addFlash('success', 'Business "' . $name . '" créé avec succès !');
        return $this->redirectToRoute('business_index');
    }

    #[Route('/edit/{id}', name: 'business_edit', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]

    public function edit(Business $business, Request $request, EntityManagerInterface $em): Response
    {
        // Utilisation du param converter pour récupérer le business
        if (!$business) {
            $this->addFlash('error', 'Business non trouvé.');
            return $this->redirectToRoute('business_index');
        }

        // Récupération et validation des données
        $name = trim($request->request->get('name', ''));
        $address = trim($request->request->get('address', ''));
        $phone = trim($request->request->get('phone', ''));
        $website = trim($request->request->get('website', ''));
        $description = trim($request->request->get('description', ''));
        $ownerId = $request->request->get('owner');
        $categoryId = $request->request->get('category');

        // Validation des champs obligatoires
        if (!$name || !$address || !$phone || !$ownerId || !$categoryId) {
            $this->addFlash('error', 'Tous les champs obligatoires doivent être remplis.');
            return $this->redirectToRoute('business_index');
        }

        // Validation du téléphone
        if (!preg_match('/^[\d\s\+\(\)\.-]+$/', $phone)) {
            $this->addFlash('error', 'Le format du téléphone est invalide.');
            return $this->redirectToRoute('business_index');
        }

        // Récupération des entités liées
        $owner = $em->getRepository(User::class)->find($ownerId);
        $category = $em->getRepository(Category::class)->find($categoryId);

        if (!$owner || !$category) {
            $this->addFlash('error', 'Propriétaire ou catégorie invalide.');
            return $this->redirectToRoute('business_index');
        }

        // Vérifier si un autre business avec le même nom existe déjà
        $existingBusiness = $em->getRepository(Business::class)->findOneBy(['name' => $name]);
        if ($existingBusiness && $existingBusiness->getId() !== $business->getId()) {
            $this->addFlash('error', 'Un autre business avec ce nom existe déjà.');
            return $this->redirectToRoute('business_index');
        }

        // Mise à jour du business
        $oldName = $business->getName();
        $business->setName($name)
            ->setAddress($address)
            ->setPhone($phone)
            ->setWebsite($website ?: null)
            ->setDescription($description ?: null)
            ->setOwner($owner)
            ->setCategory($category);

        $em->flush();

        // Redirection vers l'ajout de photos si demandé
        $redirectPhotos = $request->request->get('addPhotos') !== null;
        if ($redirectPhotos) {
            $this->addFlash('success', 'Business "' . $oldName . '" modifié avec succès ! Vous pouvez maintenant ajouter des photos.');
            return $this->redirectToRoute('business_photo_add', ['businessId' => $business->getId()]);
        }

        $this->addFlash('success', 'Business "' . $oldName . '" modifié avec succès !');
        return $this->redirectToRoute('business_index');
    }

    #[Route('/delete/{id}', name: 'business_delete')]
    #[IsGranted('ROLE_ADMIN')]

    public function delete(Business $business, EntityManagerInterface $em): Response
    {
        // Utilisation du param converter
        if (!$business) {
            $this->addFlash('error', 'Business non trouvé.');
            return $this->redirectToRoute('business_index');
        }

        $businessName = $business->getName();

        // Vérifier s'il y a des reviews associées
        if ($business->getReviews()->count() > 0) {
            $this->addFlash('error', 'Impossible de supprimer le business "' . $businessName . '" car il a ' . $business->getReviews()->count() . ' review(s) associée(s).');
            return $this->redirectToRoute('business_index');
        }

        $em->remove($business);
        $em->flush();

        $this->addFlash('success', 'Business "' . $businessName . '" supprimé avec succès !');
        return $this->redirectToRoute('business_index');
    }
    #[Route('/{id}', name: 'business_show', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')] // accessible par tous les utilisateurs connectés

    public function show(Business $business): Response

    {
        $isFavorited = $business->isFavoritedByUser($this->getUser());

        return $this->render('business/modal_show.html.twig', [
            'business' => $business,
            'reviews' => $business->getReviews(),
            'rating' => $business->getAverageRating(),
            'isFavorited' => $isFavorited,

        ]);
    }

}