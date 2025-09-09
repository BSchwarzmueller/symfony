<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class IndexController extends AbstractController
{
    #[Route('/', name: 'app.index')]
    public function index(Request $request): Response
    {
        $user = $this->getUser();

        return $this->render('index/index.html.twig', [
            'user' => $user,
        ]);
    }
}
