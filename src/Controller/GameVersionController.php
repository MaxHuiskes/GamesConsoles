<?php

namespace App\Controller;

use App\Entity\Game;
use App\Entity\GameVersion;
use App\Form\GameVersionType;
use App\Security\Voter\CollectionVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/games/{game}/versions')]
class GameVersionController extends AbstractController
{
    #[Route('/new', name: 'app_game_version_new', methods: ['GET', 'POST'])]
    public function new(Request $request, Game $game, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted(CollectionVoter::EDIT, $game);

        $version = new GameVersion();
        $version->setGame($game);
        $form = $this->createForm(GameVersionType::class, $version);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->handleFotoUpload($form->get('fotoFile')->getData(), $version);
            $entityManager->persist($version);
            $entityManager->flush();

            return $this->redirectToRoute('app_game_show', ['id' => $game->getId()]);
        }

        return $this->render('game_version/new.html.twig', [
            'game' => $game,
            'version' => $version,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_game_version_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Game $game,
        GameVersion $version,
        EntityManagerInterface $entityManager,
    ): Response {
        $this->denyAccessUnlessGranted(CollectionVoter::EDIT, $version);

        $form = $this->createForm(GameVersionType::class, $version);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->handleFotoUpload($form->get('fotoFile')->getData(), $version);
            $entityManager->flush();

            return $this->redirectToRoute('app_game_show', ['id' => $game->getId()]);
        }

        return $this->render('game_version/edit.html.twig', [
            'game' => $game,
            'version' => $version,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_game_version_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        Game $game,
        GameVersion $version,
        EntityManagerInterface $entityManager,
    ): Response {
        $this->denyAccessUnlessGranted(CollectionVoter::EDIT, $version);

        if ($this->isCsrfTokenValid('delete'.$version->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($version);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_game_show', ['id' => $game->getId()]);
    }

    private function handleFotoUpload(?UploadedFile $file, GameVersion $version): void
    {
        if ($file instanceof UploadedFile) {
            $version->setFoto(file_get_contents($file->getPathname()));
        }
    }
}
