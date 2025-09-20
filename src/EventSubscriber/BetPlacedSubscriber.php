<?php

namespace App\EventSubscriber;

use App\Entity\Game;
use App\Entity\GameStats;
use App\Event\BetPlacedEvent;
use App\Repository\GameStatsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class BetPlacedSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly GameStatsRepository $gameStatsRepository
    )
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            BetPlacedEvent::NAME => 'onBetPlaced',
        ];
    }

    /**
     * @throws ORMException
     */
    public function onBetPlaced(BetPlacedEvent $event): void
    {
        $gameId = $event->getGameId();
        $homeGoals = $event->getHomeGoals();
        $awayGoals = $event->getAwayGoals();

        $game = $this->entityManager->getRepository(Game::class)->findOneBy(['id' => $gameId]);
        $gameStats = $this->gameStatsRepository->findOneBy(['game' => $game]);

        if($gameStats === null) {
            $this->gameStatsRepository->store($gameId, $homeGoals, $awayGoals);
        } else {
            $this->gameStatsRepository->update($gameId, $homeGoals, $awayGoals);;
        }
    }
}
