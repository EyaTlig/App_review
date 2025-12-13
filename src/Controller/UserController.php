<?php
namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

#[IsGranted('ROLE_ADMIN')]
#[Route('/user')]
class UserController extends AbstractController
{
    #[Route('/', name: 'user_index')]
    public function index(EntityManagerInterface $em) {
        $users = $em->getRepository(User::class)->findAll();
        return $this->render('user/manage.html.twig', ['users' => $users]);
    }

    #[Route('/add', name: 'user_add', methods: ['POST'])]
    public function add(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher) {

            $name = $request->request->get('name');
            $email = $request->request->get('email');
            $password = $request->request->get('password');
            $role = $request->request->get('role');
            $cin = $request->request->get('cin');

            if ($em->getRepository(User::class)->findOneBy(['email' => $email])) {
                $this->addFlash('error', 'Email déjà utilisé.');
                return $this->redirectToRoute('app_register');
            }

            $user = new User();
            $user->setName($name)->setEmail($email)->setRole($role)->setCreatedAt(new \DateTime());

            if ($role === 'SERVICE_OWNER') {
                $user->setCin($cin);
                $user->setIsValidated(false);
            } else {
                $user->setIsValidated(true);
            }

            $photoFile = $request->files->get('photo');
            if ($photoFile) {
                $newFilename = uniqid() . '.' . $photoFile->guessExtension();
                $photoFile->move($this->getParameter('kernel.project_dir') . '/public/uploads', $newFilename);
                $user->setPhoto($newFilename);
            }

            $hashedPassword = $passwordHasher->hashPassword($user, $password);
            $user->setPassword($hashedPassword);

            $em->persist($user);
            $em->flush();


        $this->addFlash('success', 'Utilisateur ajouté.');
        return $this->redirectToRoute('user_index');
    }

    #[Route('/edit/{id}', name: 'user_edit', methods:['POST'])]
    public function edit(
        User $user,
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher,
        SluggerInterface $slugger
    ) {
        // Mise à jour des champs de base
        $user->setName($request->request->get('name'));
        $user->setEmail($request->request->get('email'));
        $user->setRole($request->request->get('role'));

        // Mise à jour du mot de passe si fourni
        $password = $request->request->get('password');
        if ($password) {
            $user->setPassword($passwordHasher->hashPassword($user, $password));
        }

        // Mise à jour du CIN si role SERVICE_OWNER
        if ($user->getRole() === 'SERVICE_OWNER') {
            $user->setCin($request->request->get('cin'));
        } else {
            $user->setCin(null); // supprimer CIN si ce n'est plus un Service Owner
        }

        // Gestion de l'image
        $photoFile = $request->files->get('photo');
        if ($photoFile) {
            $originalFilename = pathinfo($photoFile->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename.'-'.uniqid().'.'.$photoFile->guessExtension();

            try {
                $photoFile->move($this->getParameter('kernel.project_dir') . '/public/uploads', $newFilename);

                $user->setPhoto($newFilename);
            } catch (FileException $e) {
                $this->addFlash('error', 'Erreur lors de l\'upload de la photo.');
            }
        }

        $em->flush();
        $this->addFlash('success', 'Utilisateur modifié avec succès.');
        return $this->redirectToRoute('user_index');
    }


    #[Route('/delete/{id}', name:'user_delete')]
    public function delete(User $user, EntityManagerInterface $em) {
        $em->remove($user); $em->flush();
        $this->addFlash('success','Utilisateur supprimé');
        return $this->redirectToRoute('user_index');
    }



    #[Route('/validate/{id}', name: 'user_validate')]
    public function validate(User $user, EntityManagerInterface $em, MailerInterface $mailer): Response
    {
        if ($user->getRole() !== 'SERVICE_OWNER') {
            $this->addFlash('error', 'Seuls les Service Owners doivent être validés.');
            return $this->redirectToRoute('user_index');
        }

        $user->setIsValidated(true);
        $em->flush();

        try {
            $email = (new Email())
                ->from('admin@app-review.com')
                ->to($user->getEmail())
                ->subject('Validation de votre compte Service Owner')
                ->html('<p>Bonjour '.$user->getName().',</p>
                <p>Votre compte Service Owner a été validé par l\'administrateur. Vous pouvez maintenant vous connecter.</p>');

            $mailer->send($email);

            $this->addFlash('success', 'Le compte a été validé et l\'utilisateur a été notifié par email.');

        } catch (TransportExceptionInterface $e) {
            $this->addFlash('warning', 'Le compte a été validé mais l\'email n\'a pas pu être envoyé: '.$e->getMessage());
        }

        return $this->redirectToRoute('user_index');
    }


    #[Route('/toggle/{id}', name:'user_toggle')]
    public function toggle(User $user, EntityManagerInterface $em) {
        $user->setIsActive(!$user->isActive());
        $em->flush();
        $this->addFlash('success', $user->isActive() ? 'Activé' : 'Désactivé');
        return $this->redirectToRoute('user_index');
    }
    #[Route('/admin/settings', name: 'admin_settings')]
    public function settings(): Response
    {
        return $this->render('admin/settings.html.twig');
    }



}
