<?php

namespace App\Service;

use App\Entity\Brand;
use App\Entity\Console;
use App\Entity\Game;
use App\Entity\User;
use App\Repository\ConsoleRepository;
use App\Repository\GameRepository;
use Symfony\Component\HttpFoundation\Request;

final class DuplicateChecker
{
    public function __construct(
        private readonly GameRepository $gameRepository,
        private readonly ConsoleRepository $consoleRepository,
    ) {
    }

    public function findDuplicateGame(User $owner, string $name, ?int $excludeId, Request $request): ?Game
    {
        if ($request->request->getBoolean('confirm_duplicate')) {
            return null;
        }

        return $this->gameRepository->findOneByOwnerAndNormalizedName($owner, $name, $excludeId);
    }

    public function findDuplicateConsole(User $owner, Brand $brand, string $name, ?int $excludeId, Request $request): ?Console
    {
        if ($request->request->getBoolean('confirm_duplicate')) {
            return null;
        }

        return $this->consoleRepository->findOneByOwnerBrandAndNormalizedName($owner, $brand, $name, $excludeId);
    }
}
