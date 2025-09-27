<?php

namespace App\Controller;

use App\Entity\Bet;
use App\Repository\BetRepository;
use App\Repository\GameRepository;
use App\Repository\UserStatsRepository;
use App\Service\ConfigService;
use App\Service\DataFormatService;
use Doctrine\Common\Collections\Collection;
use Psr\Cache\InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

final class IndexController extends AbstractController
{
    private const CACHE_TTL = 3600;

    public function __construct(
        private readonly ConfigService       $configService,
        private readonly UserStatsRepository $userStatsRepository
    ) {
    }

    /**
     * @throws InvalidArgumentException
     */
    #[Route('/', name: 'app.index')]
    public function index(): Response
    {
        $currentMatchday = (int)$this->configService->get('currentMatchday');
        $leaderBoardData = $this->getLeaderBoardData();

        return $this->render('index/index.html.twig', [
            'currentMatchday' => $currentMatchday,
            'leaderBoardData' => $leaderBoardData,
        ]);
    }

    private function getLeaderBoardData(): array
    {
        return $this->userStatsRepository->getLeaders();
    }
}
