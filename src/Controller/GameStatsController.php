<?php

namespace App\Controller;

use App\Entity\Game;
use App\Repository\GameStatsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class GameStatsController extends AbstractController
{
    public function __construct(
        private readonly GameStatsRepository    $gameStatsRepository,
        private readonly EntityManagerInterface $entityManager
    )
    {
    }

    #[Route(path: 'game/stats/{gameId}', name: 'app.game.stats', methods: ['GET'])]
    public function getGameStats(int $gameId): Response
    {
        $game = $this->entityManager->getRepository(Game::class)->find($gameId);

        if (!$game) {
            return $this->json(null);
        }

        $gameStats = $this->gameStatsRepository->findOneBy(['game' => $game]);

        if ($gameStats === null) {
            return $this->json(null);
        }

        return $this->json([
            'homeGoals' => $gameStats->getAvgHomeGoals(),
            'awayGoals' => $gameStats->getAvgAwayGoals(),
            'numberOfVotes' => $gameStats->getNumberOfVotes(),
        ]);
    }
}
