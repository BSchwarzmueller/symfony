<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class NavigationController extends AbstractController
{
    #[Route('/navigation', name: 'app_navigation')]
    public function index(): Response
    {
        return $this->render('navigation/index.html.twig', [
            'controller_name' => 'NavigationController',
        ]);
    }
}
