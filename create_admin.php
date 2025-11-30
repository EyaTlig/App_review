<?php

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

// Charger l’autoload Symfony
require __DIR__ . '/vendor/autoload.php';

// Boot Symfony Kernel
$kernel = new \App\Kernel('dev', true);
$kernel->boot();
$container = $kernel->getContainer();

/** @var EntityManagerInterface $em */
$em = $container->get('doctrine')->getManager();

// Vérifier si l’utilisateur existe déjà
$existingUser = $em->getRepository(User::class)->findOneBy(['email' => 'admin@example.com']);
if ($existingUser) {
    echo "L'utilisateur admin existe déjà.\n";
    exit;
}

// Créer un nouvel utilisateur admin
$user = new User();
$user->setName('Admin Test');
$user->setEmail('admin@example.com');
$user->setPassword(password_hash('123', PASSWORD_BCRYPT));
$user->setRole('ADMIN');

$em->persist($user);
$em->flush();

echo "Utilisateur admin créé avec succès !\n";
