<?php

namespace App\Service;

use App\Entity\User;
use App\Model\BrandStats;
use App\Model\RecentItem;
use App\Repository\BrandRepository;
use App\Repository\ConsoleRepository;
use App\Repository\ConsoleVersionRepository;
use App\Repository\GameRepository;
use App\Repository\GameVersionRepository;

final class DashboardService
{
    private const RECENT_LIMIT = 12;

    public function __construct(
        private readonly BrandRepository $brandRepository,
        private readonly ConsoleRepository $consoleRepository,
        private readonly ConsoleVersionRepository $consoleVersionRepository,
        private readonly GameRepository $gameRepository,
        private readonly GameVersionRepository $gameVersionRepository,
    ) {
    }

    /** @return list<RecentItem> */
    public function getRecentItems(User $owner): array
    {
        $items = [];

        foreach ($this->brandRepository->findRecentByOwner($owner, self::RECENT_LIMIT) as $brand) {
            $items[] = new RecentItem(
                $brand->getCreatedAt(),
                'Brand',
                $brand->getName(),
                'app_brand_edit',
                ['id' => $brand->getId()],
            );
        }

        foreach ($this->consoleRepository->findRecentByOwner($owner, self::RECENT_LIMIT) as $console) {
            $items[] = new RecentItem(
                $console->getCreatedAt(),
                'Console',
                sprintf('%s · %s', $console->getBrand(), $console->getName()),
                'app_console_show',
                ['id' => $console->getId()],
            );
        }

        foreach ($this->consoleVersionRepository->findRecentByOwner($owner, self::RECENT_LIMIT) as $version) {
            $console = $version->getConsole();
            if (null === $console || null === $console->getId()) {
                continue;
            }

            $items[] = new RecentItem(
                $version->getCreatedAt(),
                'Console version',
                sprintf('%s · %s', $console, $version->getName()),
                'app_console_show',
                ['id' => $console->getId()],
            );
        }

        foreach ($this->gameRepository->findRecentByOwner($owner, self::RECENT_LIMIT) as $game) {
            $items[] = new RecentItem(
                $game->getCreatedAt(),
                'Game',
                $game->getName(),
                'app_game_show',
                ['id' => $game->getId()],
            );
        }

        foreach ($this->gameVersionRepository->findRecentByOwner($owner, self::RECENT_LIMIT) as $version) {
            $game = $version->getGame();
            if (null === $game || null === $game->getId()) {
                continue;
            }

            $items[] = new RecentItem(
                $version->getCreatedAt(),
                'Game version',
                sprintf('%s · %s', $game->getName(), $version->getName()),
                'app_game_show',
                ['id' => $game->getId()],
            );
        }

        usort(
            $items,
            fn (RecentItem $a, RecentItem $b) => $b->createdAt <=> $a->createdAt,
        );

        return array_slice($items, 0, self::RECENT_LIMIT);
    }

    /** @return list<BrandStats> */
    public function getBrandStats(User $owner): array
    {
        $stats = [];

        foreach ($this->brandRepository->findByOwner($owner) as $brand) {
            $gameIds = [];

            foreach ($brand->getConsoles() as $console) {
                foreach ($console->getGames() as $game) {
                    if ($game->getOwner()?->getId() === $owner->getId()) {
                        $gameIds[$game->getId()] = true;
                    }
                }
            }

            $stats[] = new BrandStats(
                $brand,
                $brand->getConsoles()->count(),
                count($gameIds),
            );
        }

        usort(
            $stats,
            fn (BrandStats $a, BrandStats $b) => strcasecmp($a->brand->getName(), $b->brand->getName()),
        );

        return $stats;
    }
}
