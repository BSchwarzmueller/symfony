<?php

namespace App\Controller;

use App\Entity\Club;
use App\Entity\Game;
use App\Entity\User;
use App\Repository\ClubRepository;
use App\Repository\GameRepository;
use App\Repository\GameStatsRepository;
use App\Repository\UserRepository;
use App\Service\ApiService;
use App\Service\CachingService;
use App\Service\ConfigService;
use App\Service\FactoryService;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Cache\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class DashboardController extends AbstractController
{

    const GET_GAMES_API_URL = 'https://api.openligadb.de/getmatchdata/bl1/2025/';

    public function __construct(
        private readonly FactoryService     $factoryService,
        private readonly LoggerInterface    $logger,
        private readonly ValidatorInterface $validator
    ) {
    }

    #[
        Route(path: '/admin', name: 'admin.dashboard.index')]
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
    public function getClubsFromApi(
        Request        $request,
        ClubRepository $clubRepository,
        ApiService     $api
    ): RedirectResponse {
        $form = $this->createFormBuilder()
            ->add('competition', TextType::class,
                ['label' => 'Competition', 'required' => true,]
            )->add('season', TextType::class,
                ['label' => 'Season', 'required' => true, 'data' => '2025']
            )->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $competition = $form->get('competition')->getData();
            $season      = $form->get('season')->getData();

            $clubs = $api->getClubs($competition, $season);

            if (empty($clubs)) {
                $this->logger->error('No clubs from API');
            }
            $clubRepository->storeClubArray($clubs);
        }
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
            $this->logger->error('Error while fetching games', ['error' => $e->getMessage()]);
        } finally {
            return $this->render('admin/games/index.html.twig', [
                'games' => $games ?? [],
            ]);
        }
    }

    /**
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     * @throws InvalidArgumentException
     * @throws \Exception
     */
    #[Route(path: 'admin/games/update', name: 'admin.games.update')]
    public function updateGamesByMatchDay(
        Request        $request,
        ApiService     $api,
        GameRepository $gameRepository,
        CachingService $cache,
    ): Response {
        $form = $this->createFormBuilder()
            ->add('matchDay', NumberType::class,
                ['label' => 'MatchDay', 'required' => true,]
            )->add('competition', TextType::class,
                ['label' => 'Competition', 'required' => true]
            )->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $competition  = $form->get('competition')->getData();
            $matchDay     = $form->get('matchDay')->getData();
            $games        = $this->getGamesFromApi($matchDay, $competition, $api);
            $gameDtoArray = $this->factoryService->createGameDtoArray($games);

            if ($this->validateGameDtoArray($gameDtoArray)) {
                $gameRepository->createOrUpdateGames($games);
                $cache->deleteCurrentMatchdayCache();
            }

            return $this->redirectToRoute('admin.games.index');
        }
        return $this->render('admin/games/update.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    private function getGamesFromApi(int $matchday, string $competition, ApiService $api): array
    {
        $games = $api->getGamesByMatchDay($competition, '2025', $matchday);

        if (empty($games)) {
            $this->logger->error('No games from API');
        }

        return $games;
    }

    private function validateGameDtoArray(array $gameDtoArray): bool
    {
        foreach ($gameDtoArray as $gameDto) {
            $errors = $this->validator->validate($gameDto);
            if (count($errors) > 0) {
                return false;
            }
        }
        return true;
    }
}
