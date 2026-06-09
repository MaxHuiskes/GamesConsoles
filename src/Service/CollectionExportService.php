<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\BrandRepository;
use App\Repository\ConsoleRepository;
use App\Repository\ConsoleVersionRepository;
use App\Repository\GameRepository;
use App\Repository\GameVersionRepository;
use App\Repository\TagRepository;

final class CollectionExportService
{
    public function __construct(
        private readonly BrandRepository $brandRepository,
        private readonly ConsoleRepository $consoleRepository,
        private readonly ConsoleVersionRepository $consoleVersionRepository,
        private readonly GameRepository $gameRepository,
        private readonly GameVersionRepository $gameVersionRepository,
        private readonly TagRepository $tagRepository,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(User $owner): array
    {
        $brands = $this->brandRepository->findByOwner($owner);
        $consoles = $this->consoleRepository->findByOwner($owner);
        $consoleVersions = $this->consoleVersionRepository->findByOwner($owner);
        $tags = $this->tagRepository->findByOwner($owner);
        $games = $this->gameRepository->findByOwner($owner);

        return [
            'version' => 1,
            'exportedAt' => (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM),
            'brands' => array_map(
                fn ($brand) => ['name' => $brand->getName()],
                $brands,
            ),
            'consoles' => array_map(
                fn ($console) => [
                    'brand' => $console->getBrand()?->getName(),
                    'name' => $console->getName(),
                ],
                $consoles,
            ),
            'consoleVersions' => array_map(
                fn ($version) => [
                    'brand' => $version->getConsole()?->getBrand()?->getName(),
                    'console' => $version->getConsole()?->getName(),
                    'name' => $version->getName(),
                    'condition' => $version->getCondition(),
                    'description' => $version->getDescription(),
                ],
                $consoleVersions,
            ),
            'tags' => array_map(
                fn ($tag) => ['name' => $tag->getName()],
                $tags,
            ),
            'games' => array_map(function ($game) {
                return [
                    'name' => $game->getName(),
                    'consoles' => array_map(
                        fn ($console) => [
                            'brand' => $console->getBrand()?->getName(),
                            'name' => $console->getName(),
                        ],
                        $game->getConsoles()->toArray(),
                    ),
                    'tags' => array_map(
                        fn ($tag) => $tag->getName(),
                        $game->getTags()->toArray(),
                    ),
                    'versions' => array_map(
                        fn ($version) => [
                            'name' => $version->getName(),
                            'condition' => $version->getCondition(),
                            'description' => $version->getDescription(),
                        ],
                        $game->getVersions()->toArray(),
                    ),
                ];
            }, $games),
        ];
    }

    public function toJson(User $owner): string
    {
        return json_encode($this->toArray($owner), JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
    }

    public function toCsv(User $owner): string
    {
        $handle = fopen('php://temp', 'r+');
        if (false === $handle) {
            throw new \RuntimeException('Unable to open export stream.');
        }

        fputcsv($handle, [
            'record_type',
            'brand',
            'console',
            'console_version',
            'game',
            'game_version',
            'condition',
            'description',
            'tags',
        ]);

        foreach ($this->brandRepository->findByOwner($owner) as $brand) {
            fputcsv($handle, ['brand', $brand->getName(), '', '', '', '', '', '', '']);
        }

        foreach ($this->consoleRepository->findByOwner($owner) as $console) {
            fputcsv($handle, [
                'console',
                $console->getBrand()?->getName(),
                $console->getName(),
                '',
                '',
                '',
                '',
                '',
                '',
            ]);
        }

        foreach ($this->consoleVersionRepository->findByOwner($owner) as $version) {
            fputcsv($handle, [
                'console_version',
                $version->getConsole()?->getBrand()?->getName(),
                $version->getConsole()?->getName(),
                $version->getName(),
                '',
                '',
                $version->getCondition(),
                $version->getDescription(),
                '',
            ]);
        }

        foreach ($this->tagRepository->findByOwner($owner) as $tag) {
            fputcsv($handle, ['tag', '', '', '', '', '', '', '', $tag->getName()]);
        }

        foreach ($this->gameRepository->findByOwner($owner) as $game) {
            $tags = implode('|', array_map(fn ($tag) => $tag->getName(), $game->getTags()->toArray()));

            if ($game->getVersions()->isEmpty()) {
                fputcsv($handle, ['game', '', '', '', $game->getName(), '', '', '', $tags]);

                continue;
            }

            foreach ($game->getVersions() as $version) {
                fputcsv($handle, [
                    'game_version',
                    '',
                    '',
                    '',
                    $game->getName(),
                    $version->getName(),
                    $version->getCondition(),
                    $version->getDescription(),
                    $tags,
                ]);
            }
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return $csv ?: '';
    }
}
