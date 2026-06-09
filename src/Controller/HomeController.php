<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\BrandRepository;
use App\Repository\ConsoleRepository;
use App\Repository\ConsoleVersionRepository;
use App\Repository\GameRepository;
use App\Repository\GameVersionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(
        BrandRepository $brandRepository,
        ConsoleRepository $consoleRepository,
        ConsoleVersionRepository $consoleVersionRepository,
        GameRepository $gameRepository,
        GameVersionRepository $gameVersionRepository,
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        return $this->render('home/index.html.twig', [
            'brandCount' => $brandRepository->countByOwner($user),
            'consoleCount' => $consoleRepository->countByOwner($user),
            'consoleVersionCount' => $consoleVersionRepository->countByOwner($user),
            'gameCount' => $gameRepository->countByOwner($user),
            'gameVersionCount' => $gameVersionRepository->countByOwner($user),
        ]);
    }
}
