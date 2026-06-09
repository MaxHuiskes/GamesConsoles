<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\BrandRepository;
use App\Repository\ConsoleRepository;
use App\Repository\ConsoleVersionRepository;
use App\Repository\GameRepository;
use App\Repository\GameVersionRepository;
use App\Service\DashboardService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard', methods: ['GET'])]
    public function index(
        DashboardService $dashboardService,
        BrandRepository $brandRepository,
        ConsoleRepository $consoleRepository,
        ConsoleVersionRepository $consoleVersionRepository,
        GameRepository $gameRepository,
        GameVersionRepository $gameVersionRepository,
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        return $this->render('dashboard/index.html.twig', [
            'brandCount' => $brandRepository->countByOwner($user),
            'consoleCount' => $consoleRepository->countByOwner($user),
            'consoleVersionCount' => $consoleVersionRepository->countByOwner($user),
            'gameCount' => $gameRepository->countByOwner($user),
            'gameVersionCount' => $gameVersionRepository->countByOwner($user),
            'recentItems' => $dashboardService->getRecentItems($user),
            'brandStats' => $dashboardService->getBrandStats($user),
        ]);
    }
}
