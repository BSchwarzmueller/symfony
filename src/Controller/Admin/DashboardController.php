<?php

namespace App\Controller\Admin;

use App\Entity\Club;
use App\Entity\Game;
use App\Entity\User;
use App\Repository\ClubRepository;
use App\Repository\GameRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class DashboardController extends AbstractController
{
    const GET_CLUBS_API_URL = 'https://api.openligadb.de/getavailableteams/bl1/2025';
    const GET_GAMES_API_URL = 'https://api.openligadb.de/getmatchdata/bl1/2025/';
    public function __construct(private HttpClientInterface $httpClient)
    {
    }

    #[Route(path: '/admin', name: 'admin.dashboard.index')]
    public function index(EntityManagerInterface $entityManager): Response
    {

        return $this->render('admin/dashboard.html.twig', [
            'users' => $entityManager->getRepository(User::class)->findAll(),
            'clubs' => $entityManager->getRepository(Club::class)->findAll(),
            'games' => [
                'playedGames' => $entityManager->getRepository(Game::class)->getPlayedGames(),
                'futureGames' => $entityManager->getRepository(Game::class)->getFutureGames()
            ],
        ]);
    }

    /**
     * @throws TransportExceptionInterface
     */
    #[Route(path: '/admin/clubs/update', name: 'admin.clubs.update')]
    public function getClubsFromApi(ClubRepository $clubRepository, EntityManagerInterface $entityManager) {
        try {
            $response = $this->httpClient->request('GET', self::GET_CLUBS_API_URL);
            $clubs = $response->toArray();
        } catch(\Exception $e) {
            $this->addFlash('error', 'Error while fetching clubs from API: . ' . $e->getMessage());
        }

        if(empty($clubs)) {
            $this->addFlash('error', 'No clubs found.');
            return $this->redirectToRoute('admin.dashboard.index');
        }

        foreach ($clubs as $club) {
            $clubRepository->create($club['teamId'], $club['teamName'], $club['teamIconUrl']);
        }

        $this->addFlash('success', 'Clubs updated successfully.');
        return $this->redirectToRoute('admin.dashboard.index');
    }

    #[Route(path: 'admin/games/update', name: 'admin.games.update')]
    public function getGamesFromApi(EntityManagerInterface $entityManager, Request $request, GameRepository $gameRepository): Response
    {
        $form = $this->createFormBuilder()
            ->add('matchday', NumberType::class, [
                'label' => 'Matchday',
                'required' => true,
            ])->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $matchday = (int)$form->get('matchday')->getData();
            $this->callApi($matchday, $entityManager, $gameRepository);
        }


        return $this->render('admin/games/update.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws \Exception
     */
    private function callApi(int $matchday, EntityManagerInterface $entityManager, GameRepository $gameRepository): void
    {
        $gamesForMatchday = $gameRepository->findByMatchday($matchday);
        if(!empty($gamesForMatchday)) {
            $this->addFlash('error', 'Games already exist for this matchday.');
            $this->redirectToRoute('admin.games.update');;
        }
        try {
            $response = $this->httpClient->request('GET', self::GET_GAMES_API_URL . $matchday);
            $games = $response->toArray();
        } catch(\Exception $e) {
            $this->addFlash('error', 'Error while fetching games from API: . ' . $e->getMessage());
        }
        if(empty($games)) {
            $this->addFlash('error', 'No games found.');
            $this->redirectToRoute('admin.dashboard.index');
            return;
        }
        foreach ($games as $game){

            $openLigaId = $game['matchID'];

            $homeId = $game['team1']['teamId'];
            $awayId = $game['team2']['teamId'];

            $homeScore = !empty($game['matchResults'][1]) ? $game['matchResults'][1]['pointsTeam1'] : null;
            $awayScore = !empty($game['matchResults'][1]) ? $game['matchResults'][1]['pointsTeam2'] : null;

            $date = new DateTimeImmutable($game['matchDateTime']);

            $gameRepository->create($openLigaId, $homeId, $awayId, $homeScore, $awayScore, $date, $matchday);
        }
        $this->addFlash('success', 'Games updated successfully.');
        $this->redirectToRoute('admin.games.update');
    }
}
