<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Repository\BusinessRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    #[Route('/admin/dashboard', name: 'admin_dashboard')]
    public function index(UserRepository $userRepo, BusinessRepository $businessRepo): Response
    {
        // Total users
        $totalUsers = $userRepo->count([]);

        // Total businesses
        $totalBusinesses = $businessRepo->count([]);

        // Total Clients (CUSTOMER)
        $totalCustomers = $userRepo->count(['role' => 'CUSTOMER']);

        // Total Service Owners
        $totalOwners = $userRepo->count(['role' => 'SERVICE_OWNER']);

        // Inscriptions per month (last 12 months)
        $inscriptionsByMonth = array_fill(0, 12, 0); // 0 à 11
        foreach ($userRepo->countRegistrationsByMonth() as $data) {
            $monthIndex = (int)$data['month'] - 1; // 0 = Janvier
            $inscriptionsByMonth[$monthIndex] = (int)$data['total'];
        }


        return $this->render('dashboard/dashbord.html.twig', [
            'totalUsers' => $totalUsers,
            'totalBusinesses' => $totalBusinesses,
            'totalCustomers' => $totalCustomers,
            'totalOwners' => $totalOwners,
            'inscriptions' => $inscriptionsByMonth,
        ]);
    }

}
