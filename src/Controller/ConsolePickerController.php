<?php

namespace App\Controller;

use App\Entity\Console;
use App\Entity\User;
use App\Repository\ConsoleRepository;
use App\Repository\GameRepository;
use App\Security\Voter\CollectionVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ConsolePickerController extends AbstractController
{
    #[Route('/pick-console', name: 'app_console_picker')]
    public function index(ConsoleRepository $consoleRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->render('console_picker/index.html.twig', [
            'consoles' => $consoleRepository->findByOwner($user),
        ]);
    }

    #[Route('/pick-console/random', name: 'app_console_picker_random')]
    public function random(ConsoleRepository $consoleRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $console = $consoleRepository->findRandomForOwner($user);

        if ($console === null) {
            return $this->render('console_picker/random.html.twig');
        }

        return $this->redirectToRoute('app_console_picker_games', ['id' => $console->getId()]);
    }

    #[Route('/pick-console/{id}', name: 'app_console_picker_games')]
    public function games(Console $console, GameRepository $gameRepository): Response
    {
        $this->denyAccessUnlessGranted(CollectionVoter::VIEW, $console);

        /** @var User $user */
        $user = $this->getUser();

        return $this->render('console_picker/games.html.twig', [
            'console' => $console,
            'games' => $gameRepository->findByConsoleForOwner($console, $user),
        ]);
    }
}
