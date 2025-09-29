<?php

namespace App\Controller;

use App\Entity\Club;
use App\Entity\Game;
use App\Entity\User;
use App\Repository\ClubRepository;
use App\Repository\GameRepository;
use App\Repository\UserRepository;
use App\Service\CachingService;
use App\Service\ConfigService;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Cache\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\HttpFoundation\RedirectResponse;
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

    public function __construct(private readonly HttpClientInterface $httpClient, private readonly LoggerInterface $logger)
    {
    }

    #[Route(path: '/admin', name: 'admin.dashboard.index')]
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->render('admin/dashboard.html.twig');
    }

    #[Route(path: '/admin/users', name: 'admin.users.index')]
    public function getUsers(UserRepository $userRepository): Response
    {
        try {
            $users = $userRepository->findAll();
        } catch (\Exception $e) {
            $this->addFlash('error', 'Error while fetching users: ' . $e->getMessage());
            $this->logger->error('Error while fetching users', ['error' => $e->getMessage()]);
        } finally {
            return $this->render('admin/users/index.html.twig', [
                'users' => $users ?? [],
            ]);
        }
    }

    #[Route(path: '/admin/clubs', name: 'admin.clubs.index')]
    public function getClubs(ClubRepository $clubRepository): Response
    {
        try {
            $clubs = $clubRepository->findAll();
        } catch (\Exception $e) {
            $this->addFlash('error', 'Error while fetching clubs: ' . $e->getMessage());
            $this->logger->error('Error while fetching clubs', ['error' => $e->getMessage()]);
        } finally {
            return $this->render('admin/clubs/index.html.twig', [
                'clubs' => $clubs ?? [],
            ]);
        }
    }

    /**
     * @throws TransportExceptionInterface
     */
    #[Route(path: '/admin/clubs/update', name: 'admin.clubs.update')]
    public function getClubsFromApi(ClubRepository $clubRepository, EntityManagerInterface $entityManager): RedirectResponse
    {
        try {
            $response = $this->httpClient->request('GET', self::GET_CLUBS_API_URL);
            $clubs    = $response->toArray();
        } catch (\Exception $e) {
            $this->addFlash('error', 'Error while fetching clubs from API: . ' . $e->getMessage());
        }

        if (empty($clubs)) {
            $this->addFlash('error', 'No clubs found.');
            return $this->redirectToRoute('admin.dashboard.index');
        }

        foreach ($clubs as $club) {
            $clubRepository->create($club['teamId'], $club['teamName'], $club['teamIconUrl']);
        }

        $this->addFlash('success', 'Clubs updated successfully.');
        return $this->redirectToRoute('admin.dashboard.index');
    }

    #[Route(path: '/admin/games', name: 'admin.games.index')]
    public function getGames(GameRepository $gameRepository): Response
    {
        try {
            $games = [
                'playedGames' => $gameRepository->getPlayedGames(),
                'futureGames' => $gameRepository->getFutureGames()
            ];
        } catch (\Exception $e) {
            $this->addFlash('error', 'Error while fetching games: ' . $e->getMessage());
            $this->logger->error('Error while fetching games', ['error' => $e->getMessage()]);
        } finally {
            return $this->render('admin/games/index.html.twig', [
                'games' => $games ?? [],
            ]);
        }
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws InvalidArgumentException
     */
    #[Route(path: 'admin/games/update', name: 'admin.games.update')]
    public function updateGamesByMatchday(
        Request        $request,
        GameRepository $gameRepository,
        ConfigService  $config,
        CachingService $cache
    ): Response {
        $form = $this->createFormBuilder()
            ->add('matchday', NumberType::class, [
                'label'    => 'Matchday',
                'required' => true,
            ])->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $matchday = (int)$form->get('matchday')->getData();
            $this->callApi($matchday, (int)$config->get('currentMatchday'), $gameRepository, $cache);
            return $this->redirectToRoute('admin.games.index');
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
     * @throws InvalidArgumentException
     */
    private function callApi(
        int            $matchday,
        int            $currentMatchday,
        GameRepository $gameRepository,
        CachingService $cache
    ): void {
        try {
            $response = $this->httpClient->request('GET', self::GET_GAMES_API_URL . $matchday);
            $games    = $response->toArray();
        } catch (\Exception $e) {
            $this->addFlash('error', 'Error while fetching games from API: . ' . $e->getMessage());
        }

        if (empty($games)) {
            $this->addFlash('error', 'No games found.');
        }

        if ($currentMatchday >= $matchday) {
            $gameRepository->updateGames($games);
        } else {
            $gameRepository->createGames($games, $matchday);
        }

        $cache->deleteCurrentMatchdayCache();

        $this->addFlash('success', 'Games updated successfully.');
    }
}
