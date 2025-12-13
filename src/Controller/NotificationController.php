<?php
// src/Controller/NotificationController.php

namespace App\Controller;

use App\Entity\Notification;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class NotificationController extends AbstractController
{
    #[Route('/notifications', name: 'notification_index')]
    public function index(EntityManagerInterface $em): Response
    {
        $notifications = $em->getRepository(Notification::class)
            ->findBy(['user' => $this->getUser()], ['createdAt' => 'DESC']);

        return $this->render('notification/index.html.twig', [
            'notifications' => $notifications,
        ]);
    }

    #[Route('/notification/{id}', name: 'notification_show')]
    public function show(Notification $notification, EntityManagerInterface $em): Response
    {
        // Marquer comme lu
        if (!$notification->isRead()) {
            $notification->setIsRead(true);
            $em->flush();
        }

        // Rediriger vers l'action appropriée selon le type
        if (str_contains($notification->getTitle(), 'propriétaire')) {
            return $this->redirectToRoute('user_index');
        }

        return $this->render('notification/show.html.twig', [
            'notification' => $notification,
        ]);
    }

    #[Route('/notifications/mark-all-read', name: 'notification_mark_all_read')]
    public function markAllAsRead(EntityManagerInterface $em): Response
    {
        $notifications = $em->getRepository(Notification::class)
            ->findBy(['user' => $this->getUser(), 'isRead' => false]);

        foreach ($notifications as $notification) {
            $notification->setIsRead(true);
        }

        $em->flush();

        $this->addFlash('success', 'Toutes les notifications ont été marquées comme lues.');

        return $this->redirectToRoute('notification_index');
    }

    #[Route('/notifications/count', name: 'notification_count')]
    public function count(EntityManagerInterface $em): JsonResponse
    {
        $count = $em->getRepository(Notification::class)
            ->count(['user' => $this->getUser(), 'isRead' => false]);

        return new JsonResponse(['count' => $count]);
    }
}