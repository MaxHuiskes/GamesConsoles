<?php

namespace App\Service;

use App\Collection\Condition;
use App\Entity\Brand;
use App\Entity\Console;
use App\Entity\ConsoleVersion;
use App\Entity\Game;
use App\Entity\GameVersion;
use App\Entity\Tag;
use App\Entity\User;
use App\Repository\BrandRepository;
use App\Repository\ConsoleRepository;
use App\Repository\GameRepository;
use App\Repository\TagRepository;
use Doctrine\ORM\EntityManagerInterface;

final class CollectionImportService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly BrandRepository $brandRepository,
        private readonly ConsoleRepository $consoleRepository,
        private readonly GameRepository $gameRepository,
        private readonly TagRepository $tagRepository,
    ) {
    }

    /** @return array{created: int, skipped: int} */
    public function importJson(User $owner, string $json): array
    {
        /** @var array<string, mixed> $data */
        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        return $this->importArray($owner, $data);
    }

    /** @return array{created: int, skipped: int} */
    public function importCsv(User $owner, string $csv): array
    {
        $handle = fopen('php://temp', 'r+');
        if (false === $handle) {
            throw new \RuntimeException('Unable to open import stream.');
        }

        fwrite($handle, $csv);
        rewind($handle);

        $header = fgetcsv($handle);
        if (false === $header) {
            fclose($handle);

            throw new \InvalidArgumentException('CSV file is empty.');
        }

        $data = [
            'brands' => [],
            'consoles' => [],
            'consoleVersions' => [],
            'tags' => [],
            'games' => [],
        ];

        $gamesByName = [];

        while (($row = fgetcsv($handle)) !== false) {
            if ([] === array_filter($row)) {
                continue;
            }

            $type = $row[0] ?? '';
            match ($type) {
                'brand' => $data['brands'][] = ['name' => $row[1] ?? ''],
                'console' => $data['consoles'][] = ['brand' => $row[1] ?? '', 'name' => $row[2] ?? ''],
                'console_version' => $data['consoleVersions'][] = [
                    'brand' => $row[1] ?? '',
                    'console' => $row[2] ?? '',
                    'name' => $row[3] ?? '',
                    'condition' => $row[6] ?? Condition::GOOD,
                    'description' => $row[7] !== '' ? $row[7] : null,
                ],
                'tag' => $data['tags'][] = ['name' => $row[8] ?? ''],
                'game' => $this->appendGameRow($gamesByName, $row[4] ?? '', $row[8] ?? '', null),
                'game_version' => $this->appendGameRow(
                    $gamesByName,
                    $row[4] ?? '',
                    $row[8] ?? '',
                    [
                        'name' => $row[5] ?? 'Default',
                        'condition' => $row[6] ?? Condition::GOOD,
                        'description' => $row[7] !== '' ? $row[7] : null,
                    ],
                ),
                default => null,
            };
        }

        fclose($handle);
        $data['games'] = array_values($gamesByName);

        return $this->importArray($owner, $data);
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array{created: int, skipped: int}
     */
    private function importArray(User $owner, array $data): array
    {
        $created = 0;
        $skipped = 0;

        $this->entityManager->wrapInTransaction(function () use ($owner, $data, &$created, &$skipped): void {
            $brandMap = [];
            $consoleMap = [];
            $tagMap = [];

            foreach ($data['brands'] ?? [] as $row) {
                $name = trim((string) ($row['name'] ?? ''));
                if ('' === $name) {
                    ++$skipped;
                    continue;
                }

                $brandMap[$this->key($name)] = $this->findOrCreateBrand($owner, $name, $created, $skipped);
            }

            foreach ($data['consoles'] ?? [] as $row) {
                $brandName = trim((string) ($row['brand'] ?? ''));
                $name = trim((string) ($row['name'] ?? ''));
                if ('' === $brandName || '' === $name) {
                    ++$skipped;
                    continue;
                }

                $brand = $brandMap[$this->key($brandName)] ?? $this->findOrCreateBrand($owner, $brandName, $created, $skipped);
                $brandMap[$this->key($brandName)] = $brand;
                $consoleMap[$this->consoleKey($brandName, $name)] = $this->findOrCreateConsole($owner, $brand, $name, $created, $skipped);
            }

            foreach ($data['consoleVersions'] ?? [] as $row) {
                $brandName = trim((string) ($row['brand'] ?? ''));
                $consoleName = trim((string) ($row['console'] ?? ''));
                $versionName = trim((string) ($row['name'] ?? ''));
                $condition = (string) ($row['condition'] ?? Condition::GOOD);

                if ('' === $brandName || '' === $consoleName || '' === $versionName || !Condition::isValid($condition)) {
                    ++$skipped;
                    continue;
                }

                $brand = $brandMap[$this->key($brandName)] ?? $this->findOrCreateBrand($owner, $brandName, $created, $skipped);
                $console = $consoleMap[$this->consoleKey($brandName, $consoleName)]
                    ?? $this->findOrCreateConsole($owner, $brand, $consoleName, $created, $skipped);

                if ($this->hasConsoleVersion($console, $versionName, $condition)) {
                    ++$skipped;
                    continue;
                }

                $version = new ConsoleVersion();
                $version->setName($versionName);
                $version->setCondition($condition);
                $version->setDescription($row['description'] ?? null);
                $console->addVersion($version);
                $this->entityManager->persist($version);
                ++$created;
            }

            foreach ($data['tags'] ?? [] as $row) {
                $name = trim((string) ($row['name'] ?? ''));
                if ('' === $name) {
                    ++$skipped;
                    continue;
                }

                $tagMap[$this->key($name)] = $this->findOrCreateTag($owner, $name, $created, $skipped);
            }

            foreach ($data['games'] ?? [] as $row) {
                $name = trim((string) ($row['name'] ?? ''));
                if ('' === $name) {
                    ++$skipped;
                    continue;
                }

                $game = $this->gameRepository->findOneByOwnerAndNormalizedName($owner, $name);
                if (null === $game) {
                    $game = new Game();
                    $game->setOwner($owner);
                    $game->setName($name);
                    $this->entityManager->persist($game);
                    ++$created;
                } else {
                    ++$skipped;
                }

                foreach ($row['consoles'] ?? [] as $consoleRow) {
                    if (is_string($consoleRow)) {
                        continue;
                    }

                    $brandName = trim((string) ($consoleRow['brand'] ?? ''));
                    $consoleName = trim((string) ($consoleRow['name'] ?? ''));
                    if ('' === $brandName || '' === $consoleName) {
                        continue;
                    }

                    $brand = $brandMap[$this->key($brandName)] ?? $this->findOrCreateBrand($owner, $brandName, $created, $skipped);
                    $console = $consoleMap[$this->consoleKey($brandName, $consoleName)]
                        ?? $this->findOrCreateConsole($owner, $brand, $consoleName, $created, $skipped);
                    $game->addConsole($console);
                }

                foreach ($row['tags'] ?? [] as $tagName) {
                    $tagName = trim((string) $tagName);
                    if ('' === $tagName) {
                        continue;
                    }

                    $tag = $tagMap[$this->key($tagName)] ?? $this->findOrCreateTag($owner, $tagName, $created, $skipped);
                    $game->addTag($tag);
                }

                foreach ($row['versions'] ?? [] as $versionRow) {
                    $versionName = trim((string) ($versionRow['name'] ?? ''));
                    $condition = (string) ($versionRow['condition'] ?? Condition::GOOD);
                    if ('' === $versionName || !Condition::isValid($condition)) {
                        ++$skipped;
                        continue;
                    }

                    if ($this->hasGameVersion($game, $versionName, $condition)) {
                        ++$skipped;
                        continue;
                    }

                    $version = new GameVersion();
                    $version->setName($versionName);
                    $version->setCondition($condition);
                    $version->setDescription($versionRow['description'] ?? null);
                    $game->addVersion($version);
                    $this->entityManager->persist($version);
                    ++$created;
                }
            }
        });

        return ['created' => $created, 'skipped' => $skipped];
    }

    /** @param array<string, array<string, mixed>> $gamesByName */
    private function appendGameRow(array &$gamesByName, string $name, string $tags, ?array $version): void
    {
        $name = trim($name);
        if ('' === $name) {
            return;
        }

        $key = $this->key($name);
        if (!isset($gamesByName[$key])) {
            $gamesByName[$key] = [
                'name' => $name,
                'consoles' => [],
                'tags' => array_values(array_filter(array_map('trim', explode('|', $tags)))),
                'versions' => [],
            ];
        }

        if (null !== $version) {
            $gamesByName[$key]['versions'][] = $version;
        }
    }

    private function findOrCreateBrand(User $owner, string $name, int &$created, int &$skipped): Brand
    {
        foreach ($this->brandRepository->findByOwner($owner) as $brand) {
            if ($this->key($brand->getName()) === $this->key($name)) {
                return $brand;
            }
        }

        $brand = new Brand();
        $brand->setOwner($owner);
        $brand->setName($name);
        $this->entityManager->persist($brand);
        ++$created;

        return $brand;
    }

    private function findOrCreateConsole(User $owner, Brand $brand, string $name, int &$created, int &$skipped): Console
    {
        $existing = $this->consoleRepository->findOneByOwnerBrandAndNormalizedName($owner, $brand, $name);
        if (null !== $existing) {
            return $existing;
        }

        $console = new Console();
        $console->setBrand($brand);
        $console->setName($name);
        $brand->addConsole($console);
        $this->entityManager->persist($console);
        ++$created;

        return $console;
    }

    private function findOrCreateTag(User $owner, string $name, int &$created, int &$skipped): Tag
    {
        foreach ($this->tagRepository->findByOwner($owner) as $tag) {
            if ($this->key($tag->getName()) === $this->key($name)) {
                return $tag;
            }
        }

        $tag = new Tag();
        $tag->setOwner($owner);
        $tag->setName($name);
        $this->entityManager->persist($tag);
        ++$created;

        return $tag;
    }

    private function hasConsoleVersion(Console $console, string $name, string $condition): bool
    {
        foreach ($console->getVersions() as $version) {
            if ($this->key($version->getName()) === $this->key($name) && $version->getCondition() === $condition) {
                return true;
            }
        }

        return false;
    }

    private function hasGameVersion(Game $game, string $name, string $condition): bool
    {
        foreach ($game->getVersions() as $version) {
            if ($this->key($version->getName()) === $this->key($name) && $version->getCondition() === $condition) {
                return true;
            }
        }

        return false;
    }

    private function key(string $value): string
    {
        return mb_strtolower(trim($value));
    }

    private function consoleKey(string $brand, string $console): string
    {
        return $this->key($brand).'|'.$this->key($console);
    }
}
