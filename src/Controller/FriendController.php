<?php

namespace App\Controller;

use App\Entity\Console;
use App\Entity\Game;
use App\Entity\User;
use App\Repository\BrandRepository;
use App\Repository\ConsoleRepository;
use App\Repository\ConsoleVersionRepository;
use App\Repository\FriendshipRepository;
use App\Repository\GameRepository;
use App\Repository\GameVersionRepository;
use App\Service\CollectionCompareService;
use App\Security\Voter\CollectionVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Route('/friends')]
class FriendController extends AbstractController
{
    private function assertFriendAccess(User $user, User $friend, FriendshipRepository $friendshipRepository): void
    {
        if ($friend->getId() === $user->getId()) {
            throw $this->createAccessDeniedException();
        }

        if (!$friendshipRepository->areFriends($user, $friend)) {
            throw $this->createAccessDeniedException();
        }
    }
    #[Route('', name: 'app_friend_index', methods: ['GET'])]
    public function index(
        FriendshipRepository $friendshipRepository,
        EntityManagerInterface $entityManager,
        UrlGeneratorInterface $urlGenerator,
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        $user->ensureConnectToken();
        $entityManager->flush();

        $connectUrl = $urlGenerator->generate(
            'app_connect',
            ['token' => $user->getConnectToken()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        return $this->render('friend/index.html.twig', [
            'friends' => $friendshipRepository->findFriends($user),
            'connectUrl' => $connectUrl,
        ]);
    }

    #[Route('/{id}/compare', name: 'app_friend_compare', methods: ['GET'])]
    public function compare(
        User $friend,
        FriendshipRepository $friendshipRepository,
        CollectionCompareService $collectionCompareService,
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        if ($friend->getId() === $user->getId()) {
            return $this->redirectToRoute('app_home');
        }

        if (!$friendshipRepository->areFriends($user, $friend)) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('friend/compare.html.twig', [
            'friend' => $friend,
            'comparison' => $collectionCompareService->compare($user, $friend),
    #[Route('/{id}/pick-console/{consoleId}', name: 'app_friend_pick_console_games', methods: ['GET'])]
    public function pickConsoleGames(
        User $friend,
        #[MapEntity(mapping: ['consoleId' => 'id'])] Console $console,
        FriendshipRepository $friendshipRepository,
        GameRepository $gameRepository,
    ): Response {
        /** @var User $user */
        $user = $this->getUser();
        $this->assertFriendAccess($user, $friend, $friendshipRepository);
        $this->denyAccessUnlessGranted(CollectionVoter::VIEW, $console);

        if ($console->getBrand()?->getOwner()?->getId() !== $friend->getId()) {
            throw $this->createNotFoundException();
        }

        return $this->render('friend/pick_console_games.html.twig', [
            'friend' => $friend,
            'console' => $console,
            'games' => $gameRepository->findByConsoleForOwner($console, $friend),
        ]);
    }

    #[Route('/{id}/games/{gameId}', name: 'app_friend_game_show', methods: ['GET'])]
    public function showGame(
        User $friend,
        #[MapEntity(mapping: ['gameId' => 'id'])] Game $game,
        Request $request,
        FriendshipRepository $friendshipRepository,
        ConsoleRepository $consoleRepository,
    ): Response {
        /** @var User $user */
        $user = $this->getUser();
        $this->assertFriendAccess($user, $friend, $friendshipRepository);
        $this->denyAccessUnlessGranted(CollectionVoter::VIEW, $game);

        if ($game->getOwner()?->getId() !== $friend->getId()) {
            throw $this->createNotFoundException();
        }

        $pickConsole = null;
        $consoleId = $request->query->getInt('console');
        if ($consoleId > 0) {
            $pickConsole = $consoleRepository->find($consoleId);
        }

        return $this->render('game/show.html.twig', [
            'game' => $game,
            'friend' => $friend,
            'pickConsole' => $pickConsole,
        ]);
    }

    #[Route('/{id}', name: 'app_friend_show', methods: ['GET'])]
    public function show(
        User $friend,
        FriendshipRepository $friendshipRepository,
        BrandRepository $brandRepository,
        ConsoleRepository $consoleRepository,
        ConsoleVersionRepository $consoleVersionRepository,
        GameRepository $gameRepository,
        GameVersionRepository $gameVersionRepository,
    ): Response {
        /** @var User $user */
        $user = $this->getUser();
        $this->assertFriendAccess($user, $friend, $friendshipRepository);

        return $this->render('friend/show.html.twig', [
            'friend' => $friend,
            'brandCount' => $brandRepository->countByOwner($friend),
            'consoleCount' => $consoleRepository->countByOwner($friend),
            'consoleVersionCount' => $consoleVersionRepository->countByOwner($friend),
            'gameCount' => $gameRepository->countByOwner($friend),
            'gameVersionCount' => $gameVersionRepository->countByOwner($friend),
            'consoles' => $consoleRepository->findByOwner($friend),
        ]);
    }
}
