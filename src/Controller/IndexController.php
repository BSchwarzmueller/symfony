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
        Request $request,
        GameRepository $gameRepository,
        ConfigService $configService,
        CacheInterface $cache,
        DataFormatService $dataFormatService
    ): Response {
        $session = $request->getSession();
        $session->set('user', $this->getUser());

        $matchday = $configService->get('currentMatchday');
        $lastMatchday = $matchday === 1 ? 1 : $matchday - 1;

        $cacheKeyMatchday = sprintf('games.view.matchday.%d', $matchday);
        $cacheKeyLastMatchday = sprintf('games.view.matchday.%d', $lastMatchday);

        $currentMatchday = $cache->get($cacheKeyMatchday, function (ItemInterface $item) use ($gameRepository, $matchday) {
            $item->expiresAfter(60*60);
            return $gameRepository->findByMatchday($matchday);
        });

        $lastMatchday = $cache->get($cacheKeyLastMatchday, function (ItemInterface $item) use ($gameRepository, $lastMatchday) {
            $item->expiresAfter(60*60);
            return $gameRepository->findByMatchday($lastMatchday);
        });

        return $this->render('index/index.html.twig', [
            'currentMatchday' => $dataFormatService->createMatchData($currentMatchday),
            'lastMatchday' => $dataFormatService->createMatchData($lastMatchday),
        ]);
    }
}
