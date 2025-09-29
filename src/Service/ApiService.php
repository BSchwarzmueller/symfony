<?php


namespace App\Service;

use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ApiService {

    const GET_CLUBS_BY_COMPETITION_AND_SEASON_URL = 'https://api.openligadb.de/getavailableteams/%competition%/%season%';
    // const GET_TABLE_BY_COMPETITION_AND_SEASON_URL = 'https://api.openligadb.de/getbltable/%competition%/%season%';
    const GET_GAMES_BY_COMPETITION_SEASON_MATCH_DAY_URL = 'https://api.openligadb.de/getmatchdata/%competition%/%season%/%matchDay%';


    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly LoggerInterface $logger,
    )
    {
    }

    public function getGamesByMatchDay(string $competition, string $season, int $matchDay): array
    {
        $url = $this->getUrl(self::GET_GAMES_BY_COMPETITION_SEASON_MATCH_DAY_URL, $competition, $season, $matchDay);
        return $this->fetch($url);
    }

    public function getClubs(string $competition, string $season): array
    {
        $url = $this->getUrl(self::GET_CLUBS_BY_COMPETITION_AND_SEASON_URL, $competition, $season);
        return $this->fetch($url);
    }

    private function getUrl(string $baseUrl, string $competition, string $season, ?int $matchDay = null): string
    {
        if ($matchDay === null) {
            return str_replace(['%competition%', '%season%'], [$competition, $season], $baseUrl);
        }
        return str_replace(['%matchDay%', '%competition%', '%season%'], [$matchDay, $competition, $season], $baseUrl);
    }

    private function fetch(string $url): array
    {
        $this->logger->info('Fetching games from API', ['url' => $url]);
        try {
            $response = $this->httpClient->request('GET', $url);
            return $response->toArray();
        } catch (Exception $e) {
            $this->logger->error('Error while fetching games from API', ['error' => $e->getMessage()]);
        }
    }
}