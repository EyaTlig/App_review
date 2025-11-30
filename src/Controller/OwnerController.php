<?php
namespace App\Controller;

use App\Entity\Business;
use App\Entity\User;
use App\Form\BusinessType;
use App\Repository\BusinessRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;



class OwnerController extends AbstractController
{
    #[Route('/owner/{id}', name: 'owner_profile')]
    public function profile(User $owner, Request $request, BusinessRepository $businessRepo): Response
    {
        $search = $request->query->get('search', '');

        $businesses = $businessRepo->findByOwnerAndSearch($owner, $search);

        return $this->render('owner/profile.html.twig', [
            'owner' => $owner,
            'businesses' => $businesses
        ]);
    }




}
