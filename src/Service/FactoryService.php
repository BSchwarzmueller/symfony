<?php

namespace App\Service;

use App\Entity\Bet;
use App\Entity\Game;
use App\Entity\User;
use App\Event\BetPlacedEvent;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

readonly class FactoryService
{
    public const OPEN_BET_STATUS = 'open';
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    /**
     * @throws Exception
     */
    public function createBet(int $userId, int $gameId, int $homeGoals, int $awayGoals): Bet
    {
        $bet = new Bet();

        try {
            $user = $this->entityManager->getRepository(User::class)->find($userId);
            $game = $this->entityManager->getRepository(Game::class)->find($gameId);
        } catch (Exception $e) {
            throw new Exception('User or game not found');
        }

        $bet->setUser($user);
        $bet->setGame($game);
        $bet->setHomeGoals($homeGoals);
        $bet->setAwayGoals($awayGoals);
        $bet->setStatus(self::OPEN_BET_STATUS);
        $bet->setCreatedAt(new DateTimeImmutable());

        return $bet;
    }

    public function createBetPlacedEvent(int $userId, int $gameId, int $homeGoals, int $awayGoals): BetPlacedEvent
    {
        return new BetPlacedEvent($gameId, $homeGoals, $awayGoals);
    }

}