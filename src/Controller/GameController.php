<?php

namespace App\Controller;

use App\Entity\Game;
use App\Entity\User;
use App\Form\GameType;
use App\Repository\ConsoleVersionRepository;
use App\Repository\GameRepository;
use App\Security\Voter\CollectionVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/games')]
class GameController extends AbstractController
{
    #[Route('', name: 'app_game_index', methods: ['GET'])]
    public function index(GameRepository $gameRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->render('game/index.html.twig', [
            'games' => $gameRepository->findByOwner($user),
        ]);
    }

    #[Route('/new', name: 'app_game_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        ConsoleVersionRepository $consoleVersionRepository,
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        $game = new Game();
        $form = $this->createForm(GameType::class, $game, ['owner' => $user]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $game->setOwner($user);
            $entityManager->persist($game);
            $entityManager->flush();

            return $this->redirectToRoute('app_game_show', ['id' => $game->getId()]);
        }

        return $this->render('game/new.html.twig', [
            'game' => $game,
            'form' => $form,
            'consoleVersionConsoleMap' => $consoleVersionRepository->getConsoleIdMapForOwner($user),
        ]);
    }

    #[Route('/{id}', name: 'app_game_show', methods: ['GET'])]
    public function show(Game $game): Response
    {
        $this->denyAccessUnlessGranted(CollectionVoter::VIEW, $game);

        return $this->render('game/show.html.twig', [
            'game' => $game,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_game_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Game $game,
        EntityManagerInterface $entityManager,
        ConsoleVersionRepository $consoleVersionRepository,
    ): Response {
        $this->denyAccessUnlessGranted(CollectionVoter::EDIT, $game);

        /** @var User $user */
        $user = $this->getUser();

        $form = $this->createForm(GameType::class, $game, ['owner' => $user]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_game_show', ['id' => $game->getId()]);
        }

        return $this->render('game/edit.html.twig', [
            'game' => $game,
            'form' => $form,
            'consoleVersionConsoleMap' => $consoleVersionRepository->getConsoleIdMapForOwner($user),
        ]);
    }

    #[Route('/{id}/delete', name: 'app_game_delete', methods: ['POST'])]
    public function delete(Request $request, Game $game, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted(CollectionVoter::EDIT, $game);

        if ($this->isCsrfTokenValid('delete'.$game->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($game);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_game_index');
    }
}
