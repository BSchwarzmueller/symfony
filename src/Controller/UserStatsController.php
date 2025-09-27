<?php

namespace App\Controller;

use App\Repository\GameStatsRepository;
use App\Repository\UserStatsRepository;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class UserStatsController extends AbstractController
{

    public function __construct( private readonly UserStatsRepository $userStatsRepository)
    {
    }

    #[Route(path: '/user/stats/leaderboard', name: 'app.stats.leaderboard', methods: ['GET'])]
    public function getLeaderBoard(): Response
    {
        try {
            $leaderBoardData = $this->userStatsRepository->getLeaders();
            return $this->json($leaderBoardData, Response::HTTP_OK);
    }catch (Exception $e) {
            return $this->json('Error fetching Leaderboard: ' . $e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

}