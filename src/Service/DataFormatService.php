<?php

namespace App\Service;


readonly class DataFormatService
{
    public function __construct(ConfigService $configService)
    {
    }

    public function GamesForBetView(array $openGames, array $closedBets, array $openBets, int $userId): array
    {
        $out = [];

        foreach ($openGames as $game) {
            $out[] = [
                'type' => 'openGame',
                'gameId' => $game['id'],
                'userId' => $userId,
                'homeClub' => $game['homeClub']['name'],
                'awayClub' => $game['awayClub']['name'],
                'homeGoals' => $game['homeGoals'],
                'awayGoals' => $game['awayGoals'],
                'competition' => $game['competition'],
                'season' => $game['season'],
                'matchday' => $game['matchday'],
                'date' => $game['date'],
                'betHomeGoals' => -1,
                'betAwayGoals' => -1,
                'betStatus' => null,
                'betPoints' => null,
            ];
        }
        foreach ($closedBets as $bet) {
            $out[] = [
                'type' => 'closedBet',
                'gameId' => $bet['gameId']['id'],
                'userId' => $userId,
                'homeClub' => $bet['gameId']['homeClub']['name'],
                'awayClub' => $bet['gameId']['awayClub']['name'],
                'homeGoals' => $bet['gameId']['homeGoals'],
                'awayGoals' => $bet['gameId']['awayGoals'],
                'competition' => $bet['gameId']['competition'],
                'season' => $bet['gameId']['season'],
                'matchday' => $bet['gameId']['matchday'],
                'date' => $bet['gameId']['date'],
                'betHomeGoals' => $bet['homeGoals'],
                'betAwayGoals' => $bet['awayGoals'],
                'betStatus' => $bet['status'],
                'betPoints' => $bet['points'],
                'betId' => $bet['id'],
            ];
        }
        foreach ($openBets as $bet) {
            $out[] = [
                'type' => 'openBet',
                'gameId' => $bet['gameId']['id'],
                'userId' => $userId,
                'homeClub' => $bet['gameId']['homeClub']['name'],
                'awayClub' => $bet['gameId']['awayClub']['name'],
                'homeGoals' => $bet['gameId']['homeGoals'],
                'awayGoals' => $bet['gameId']['awayGoals'],
                'competition' => $bet['gameId']['competition'],
                'season' => $bet['gameId']['season'],
                'matchday' => $bet['gameId']['matchday'],
                'date' => $bet['gameId']['date'],
                'betHomeGoals' => $bet['homeGoals'],
                'betAwayGoals' => $bet['awayGoals'],
                'betStatus' => $bet['status'],
                'betPoints' => $bet['points'],
                'betId' => $bet['id'],
            ];
        }
        return $out;
    }
}
