<?php
namespace App\Controller;

use App\Entity\Business;
use App\Entity\BusinessPhoto;
use App\Entity\Category;
use App\Entity\User;
use App\Repository\BusinessRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

class HomeController extends AbstractController
{
    #[Route('/', name: 'client_home')]
    public function index(
        BusinessRepository $businessRepo,
        CategoryRepository $categoryRepo,
        Request $request,
        UserInterface $user = null
    ): Response
    {
        $search = $request->query->get('search');
        $categoryId = $request->query->get('category');

        // Conversion de string à int/null
        $categoryId = $categoryId !== null && $categoryId !== '' ? (int)$categoryId : null;

        $filters = [
            'search' => $search,
            'category' => $categoryId,
            'sort' => $request->query->get('sort', 'newest'),
            'min_rating' => $request->query->get('min_rating'),
            'has_website' => $request->query->get('has_website') === '1',
        ];

        $categories = $categoryRepo->findAll();

        $businesses = $businessRepo->findWithFilters($filters);

        // Vérifier les favoris si l'utilisateur est connecté
        $favorites = [];
        if ($user) {
            foreach ($user->getFavorites() as $fav) {
                $favorites[] = $fav->getId();
            }
        }

        return $this->render('home/home.html.twig', [
            'businesses' => $businesses,
            'categories' => $categories,
            'favorites' => $favorites,
            'activeFilters' => $filters, // Pour afficher les badges
        ]);
    }


    #[Route('/profile', name: 'user_profile')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')] // accessible par tout utilisateur connecté
    public function profile(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): Response
    {
        /** @var User $user */
        $user = $this->getUser(); // récupère l'utilisateur connecté

        if ($request->isMethod('POST')) {
            // Modifier les infos
            $user->setName($request->request->get('name'));
            $user->setEmail($request->request->get('email'));

            $password = $request->request->get('password');
            if ($password) {
                $user->setPassword($passwordHasher->hashPassword($user, $password));
            }

            // Photo
            $photoFile = $request->files->get('photo');
            if ($photoFile) {
                $newFilename = uniqid() . '.' . $photoFile->guessExtension();
                $photoFile->move($this->getParameter('kernel.project_dir') . '/public/uploads', $newFilename);
                $user->setPhoto($newFilename);
            }

            $em->flush();
            $this->addFlash('success', 'Profil mis à jour avec succès !');

            return $this->redirectToRoute('client_home');
        }

        return $this->render('home/profile.html.twig', [
            'user' => $user,
        ]);

    }
    #[Route('/business/add/owner', name: 'business_add_owner')]
    #[IsGranted('ROLE_SERVICE_OWNER')]
    public function add(Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $name = trim($request->request->get('name', ''));
        $address = trim($request->request->get('address', ''));
        $phone = trim($request->request->get('phone', ''));
        $website = trim($request->request->get('website', ''));
        $description = trim($request->request->get('description', ''));
        $categoryId = $request->request->get('category');

        $owner = $this->getUser();

        // Validation rapide
        if (!$name || !$address || !$phone || !$owner || !$categoryId) {
            $this->addFlash('error', 'Tous les champs obligatoires doivent être remplis.');
            return $this->redirectToRoute('client_home');
        }

        if (!preg_match('/^[\d\s\+\(\)\.-]+$/', $phone)) {
            $this->addFlash('error', 'Le format du téléphone est invalide.');
            return $this->redirectToRoute('client_home');
        }

        $category = $em->getRepository(Category::class)->find($categoryId);
        if (!$category) {
            $this->addFlash('error', 'Catégorie invalide.');
            return $this->redirectToRoute('client_home');
        }

        // Vérifier doublon
        if ($em->getRepository(Business::class)->findOneBy(['name' => $name])) {
            $this->addFlash('error', 'Un business avec ce nom existe déjà.');
            return $this->redirectToRoute('client_home');
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

        // Gestion des photos (input type="file" multiple)
        $photos = $request->files->get('photos');
        if ($photos) {
            foreach ($photos as $photo) {
                if ($photo) {
                    $originalFilename = pathinfo($photo->getClientOriginalName(), PATHINFO_FILENAME);
                    $safeFilename = $slugger->slug($originalFilename);
                    $newFilename = $safeFilename.'-'.uniqid().'.'.$photo->guessExtension();

                    try {
                        $photo->move(
                            $this->getParameter('business_photos_directory'),
                            $newFilename
                        );

                        $businessPhoto = new \App\Entity\BusinessPhoto();
                        $businessPhoto->setFilename($newFilename);
                        $businessPhoto->setBusiness($business);
                        $em->persist($businessPhoto);

                    } catch (FileException $e) {
                        $this->addFlash('error', 'Impossible d\'uploader la photo : '.$photo->getClientOriginalName());
                    }
                }
            }
        }

        $em->flush();

        $this->addFlash('success', 'Business "' . $name . '" créé avec succès !');
        return $this->redirectToRoute('client_home');
    }
    #[Route('/owner/businesses', name: 'owner_businesses')]
    #[IsGranted('ROLE_SERVICE_OWNER')]
    public function ownerBusinesses(EntityManagerInterface $em, CategoryRepository $categoryRepo): Response
    {
        $owner = $this->getUser();
        $businesses = $em->getRepository(Business::class)->findBy(['owner' => $owner]);
        $categories = $categoryRepo->findAll();

        return $this->render('home/owner_list.html.twig', [
            'businesses' => $businesses,
            'categories' => $categories,

        ]);
    }

    #[Route('/business/delete/{id}', name: 'business_delete', methods: ['POST'])]
    #[IsGranted('ROLE_SERVICE_OWNER')]
    public function deleteBusiness(Business $business, EntityManagerInterface $em): Response
    {
        // Vérifie que le business appartient bien au user
        if ($business->getOwner() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $em->remove($business);
        $em->flush();

        $this->addFlash('success', 'Business supprimé avec succès !');
        return $this->redirectToRoute('owner_businesses');
    }
    #[Route('/business/owner/edit/{id}', name: 'business_edit_owner', methods: ['POST'])]
    #[IsGranted('ROLE_SERVICE_OWNER')]
    public function edit(
        Business $business,
        Request $request,
        EntityManagerInterface $em,
        CategoryRepository $categoryRepo,
        SluggerInterface $slugger
    ): Response {
        if ($business->getOwner() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas modifier ce business.');
        }

        $business->setName($request->request->get('name'));
        $business->setAddress($request->request->get('address'));
        $business->setPhone($request->request->get('phone'));
        $business->setWebsite($request->request->get('website'));
        $business->setDescription($request->request->get('description'));

        $categoryId = $request->request->get('category');
        if ($categoryId) {
            $category = $categoryRepo->find($categoryId);
            $business->setCategory($category);
        }

        // Gestion des images
        $photos = $request->files->get('photos');
        if ($photos) {
            foreach ($photos as $photo) {
                if ($photo) {
                    $originalFilename = pathinfo($photo->getClientOriginalName(), PATHINFO_FILENAME);
                    $safeFilename = $slugger->slug($originalFilename);
                    $newFilename = $safeFilename.'-'.uniqid().'.'.$photo->guessExtension();

                    try {
                        $photo->move(
                            $this->getParameter('business_photos_directory'),
                            $newFilename
                        );
                    } catch (FileException $e) {
                        $this->addFlash('error', 'Impossible d\'uploader l\'image.');
                        continue;
                    }

                    $businessPhoto = new BusinessPhoto();
                    $businessPhoto->setBusiness($business);
                    $businessPhoto->setFilename($newFilename);
                    $em->persist($businessPhoto);
                }
            }
        }

        $em->persist($business);
        $em->flush();

        $this->addFlash('success', 'Business mis à jour avec succès !');

        return $this->redirectToRoute('owner_businesses');
    }


}
