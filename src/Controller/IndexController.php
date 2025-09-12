<?php

namespace App\Controller;

use App\Repository\GameRepository;
use App\Service\ConfigService;
use App\Service\DataFormatService;
use Psr\Cache\InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

final class IndexController extends AbstractController
{
    /**
     * @throws InvalidArgumentException
     */
    #[Route('/', name: 'app.index')]
    public function index(
        Request           $request,
        GameRepository    $gameRepository,
        ConfigService     $configService,
        CacheInterface    $cache,
        DataFormatService $dataFormatService
    ): Response
    {
        $session = $request->getSession();
        $session->set('user', $this->getUser());

        $currentMatchday = $configService->get('currentMatchday');
        $lastMatchday = $currentMatchday - 1;

        $cacheKeyCurrentMatchday = sprintf('games.view.matchday.%d', $currentMatchday);
        $currentMatchdayGames = $cache->get($cacheKeyCurrentMatchday, function (ItemInterface $item) use ($gameRepository, $currentMatchday) {
            $item->expiresAfter(60 * 60);
            return $gameRepository->findByMatchday($currentMatchday);
        });

        if ($lastMatchday > 0) {
            $cacheKeyLastMatchday = sprintf('games.view.matchday.%d', $lastMatchday);
            $lastMatchdayGames = $cache->get($cacheKeyLastMatchday, function (ItemInterface $item) use ($gameRepository, $lastMatchday) {
                $item->expiresAfter(60 * 60);
                return $gameRepository->findByMatchday($lastMatchday);
            });
        }

        return $this->render('index/index.html.twig', [
            'currentMatchday' => $currentMatchday,
            'lastMatchday' => $lastMatchday,
            'currentMatchdayGames' => $dataFormatService->createMatchData($currentMatchdayGames),
            'lastMatchdayGames' => $lastMatchday > 0 ? $dataFormatService->createMatchData($lastMatchdayGames) : [],
        ]);
    }
}
