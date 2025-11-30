<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[IsGranted('ROLE_ADMIN')]
#[Route('/user')]
class UserController extends AbstractController
{
    #[Route('/', name: 'user_index')]
    public function index(EntityManagerInterface $em): Response
    {
        $users = $em->getRepository(User::class)->findAll();

        return $this->render('user/manage.html.twig', [
            'users' => $users
        ]);
    }

    #[Route('/add', name: 'user_add', methods: ['POST'])]
    public function add(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): Response
    {
        $name = trim($request->request->get('name', ''));
        $email = trim($request->request->get('email', ''));
        $password = $request->request->get('password', '');
        $role = $request->request->get('role', 'CUSTOMER');

        // Validation
        if (!$name || !$email || !$password) {
            $this->addFlash('error', 'Tous les champs obligatoires doivent être remplis.');
            return $this->redirectToRoute('user_index');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->addFlash('error', 'L\'adresse email est invalide.');
            return $this->redirectToRoute('user_index');
        }

        // Vérifier email dupliqué
        if ($em->getRepository(User::class)->findOneBy(['email' => $email])) {
            $this->addFlash('error', 'Cet email est déjà utilisé.');
            return $this->redirectToRoute('user_index');
        }

        $user = new User();
        $user->setName($name)
            ->setEmail($email)
            ->setRole($role)
            ->setCreatedAt(new \DateTime());

        // Utilisation du password hasher de Symfony
        $hashedPassword = $passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);

        $em->persist($user);
        $em->flush();

        $this->addFlash('success', 'Utilisateur "' . $name . '" ajouté avec succès !');
        return $this->redirectToRoute('user_index');
    }

    #[Route('/edit/{id}', name: 'user_edit', methods: ['POST'])]
    public function edit(User $user, Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): Response
    {
        $name = $request->request->get('name');
        $email = $request->request->get('email');
        $password = $request->request->get('password');
        $role = $request->request->get('role');

        // Vérifier email dupliqué
        $existingUser = $em->getRepository(User::class)->findOneBy(['email' => $email]);
        if ($existingUser && $existingUser->getId() !== $user->getId()) {
            $this->addFlash('error', 'Cet email est déjà utilisé.');
            return $this->redirectToRoute('user_index');
        }

        $user->setName($name);
        $user->setEmail($email);
        $user->setRole($role);
        if ($password) {
            $hashedPassword = $passwordHasher->hashPassword($user, $password);
            $user->setPassword($hashedPassword);
        }

        $em->flush();

        $this->addFlash('success', 'Utilisateur modifié avec succès !');
        return $this->redirectToRoute('user_index');
    }

    #[Route('/delete/{id}', name: 'user_delete')]
    public function delete(User $user, EntityManagerInterface $em): Response
    {
        $em->remove($user);
        $em->flush();
        $this->addFlash('success', 'Utilisateur supprimé avec succès !');
        return $this->redirectToRoute('user_index');
    }
}
