<?php

namespace App\Controller\Admin;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractController
{
    #[Route(path: '/admin', name: 'admin.dashboard.index')]
    public function index(EntityManagerInterface $entityManager): Response
    {

        return $this->render('admin/dashboard.html.twig', [
            'users' => $entityManager->getRepository(User::class)->findAll(),
        ]);
    }
}
