<?php

namespace App\Service;

use App\Dto\CreateBetDto;
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
    public function createBet(CreateBetDto $betDto): Bet
    {
        $bet = new Bet();

        try {
            $user = $this->entityManager->getRepository(User::class)->find($betDto->getUserId());
            $game = $this->entityManager->getRepository(Game::class)->find($betDto->getGameId());
        } catch (Exception $e) {
            throw new Exception('User or game not found');
        }

        $bet->setUser($user);
        $bet->setGame($game);
        $bet->setHomeGoals($betDto->getHomeGoals());
        $bet->setAwayGoals($betDto->getAwayGoals());
        $bet->setStatus(self::OPEN_BET_STATUS);
        $bet->setCreatedAt(new DateTimeImmutable());

        return $bet;
    }

    public function createBetPlacedEvent(CreateBetDto $betDto): BetPlacedEvent
    {
        return new BetPlacedEvent(
            $betDto->getGameId(),
            $betDto->getHomeGoals(),
            $betDto->getAwayGoals()
        );
    }

    public function createBetDtoFromRequestData(array $data): ?CreateBetDto
    {
        if (!isset($data['userId'], $data['gameId'], $data['homeGoals'], $data['awayGoals'])) {
            return null;
        }

        $createBetDto = new CreateBetDto();
        $createBetDto->setUserId($data['userId']);
        $createBetDto->setGameId($data['gameId']);
        $createBetDto->setHomeGoals($data['homeGoals']);
        $createBetDto->setAwayGoals($data['awayGoals']);

        return $createBetDto;
    }
}