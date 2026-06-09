<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\BrandRepository;
use App\Repository\ConsoleRepository;
use App\Repository\ConsoleVersionRepository;
use App\Repository\FriendshipRepository;
use App\Repository\GameRepository;
use App\Repository\GameVersionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Route('/friends')]
class FriendController extends AbstractController
{
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

        if ($friend->getId() === $user->getId()) {
            return $this->redirectToRoute('app_home');
        }

        if (!$friendshipRepository->areFriends($user, $friend)) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('friend/show.html.twig', [
            'friend' => $friend,
            'brandCount' => $brandRepository->countByOwner($friend),
            'consoleCount' => $consoleRepository->countByOwner($friend),
            'consoleVersionCount' => $consoleVersionRepository->countByOwner($friend),
            'gameCount' => $gameRepository->countByOwner($friend),
            'gameVersionCount' => $gameVersionRepository->countByOwner($friend),
            'consoles' => $consoleRepository->findByOwner($friend),
            'games' => $gameRepository->findByOwner($friend),
        ]);
    }
}
