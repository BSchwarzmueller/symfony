<?php

namespace App\Controller\api;

use App\Repository\BetRepository;
use Exception;
use Psr\Cache\InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Cache\CacheInterface;

class BetController extends AbstractController {

    #[Route(path: '/api/v1/bet/create', name: 'api.bet.create', methods: ['POST'])]
    final public function createBet(
        Security $security,
        Request $request,
        BetRepository $betRepository,
        CacheInterface $cache
    ): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        try {
            $data = json_decode($request->getContent(), true);

            if(!isset($data['userId'], $data['gameId'], $data['homeGoals'], $data['awayGoals'])) {
                return $this->json(['error' => 'Missing required fields'], 400);
            }

            if(!$betRepository->store(
                (int)$data['userId'],
                (int)$data['gameId'],
                (int)$data['homeGoals'],
                (int)$data['awayGoals']
            )) {
                return $this->json(['error' => 'Failed to create bet'], 500);
            }

            $cacheKeyCurrentPlayerBet = sprintf('player.currentBet.%d', $this->getUser()->getId());
            $cache->delete($cacheKeyCurrentPlayerBet);

            return $this->json(['message' => 'Bet created successfully'], 201);
        } catch (Exception | InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }
}
