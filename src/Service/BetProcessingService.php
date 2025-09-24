<?php

namespace App\Service;

use App\Entity\Bet;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

readonly class BetProcessingService
{
    const CLOSED_BET_STATUS = 'closed';

    public function __construct(private readonly LoggerInterface $logger, private readonly EntityManagerInterface $entityManager)
    {
    }

    public function processBet(Bet $bet): int
    {
        try {
            $points    = $this->calculatePoints($bet);
            $userStats = $bet->getUser()->getUserStats();

            $userStats->setPoints($userStats->getPoints() + $points);
            $userStats->setNumberOfBets($userStats->getNumberOfBets() + 1);

            $this->entityManager->persist($userStats);
            $this->entityManager->flush();

            return $points;
        } catch (\Exception $e) {
            $this->logger->error('Error processing bets', ['error' => $e->getMessage()]);
            return -1;
        }

    }

    private function calculatePoints(Bet $bet): int
    {
        $resultAwayGoals = $bet->getGame()->getAwayGoals();
        $resultHomeGoals = $bet->getGame()->getHomeGoals();
        $betAwayGoals    = $bet->getAwayGoals();
        $betHomeGoals    = $bet->getHomeGoals();

        $points = 0;

        if ($resultAwayGoals === $betAwayGoals && $resultHomeGoals === $betHomeGoals) {
            return 6;
        }

        if (($resultHomeGoals <=> $resultAwayGoals) === ($betHomeGoals <=> $betAwayGoals)) {
            $points += 3;
        }

        if ($resultAwayGoals === $betAwayGoals || $resultHomeGoals === $betHomeGoals) {
            $points += 1;
        }

        return $points;
    }
}