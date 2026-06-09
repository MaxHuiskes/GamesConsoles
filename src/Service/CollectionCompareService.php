<?php

namespace App\Service;

use App\Entity\Console;
use App\Entity\Game;
use App\Entity\User;
use App\Model\CollectionComparison;
use App\Repository\ConsoleRepository;
use App\Repository\GameRepository;

final class CollectionCompareService
{
    public function __construct(
        private readonly GameRepository $gameRepository,
        private readonly ConsoleRepository $consoleRepository,
    ) {
    }

    public function compare(User $me, User $friend): CollectionComparison
    {
        $games = $this->compareGames(
            $this->gameRepository->findByOwner($me),
            $this->gameRepository->findByOwner($friend),
        );

        $consoles = $this->compareConsoles(
            $this->consoleRepository->findByOwner($me),
            $this->consoleRepository->findByOwner($friend),
        );

        return new CollectionComparison(
            $games['overlap'],
            $games['uniqueMine'],
            $games['uniqueFriend'],
            $consoles['overlap'],
            $consoles['uniqueMine'],
            $consoles['uniqueFriend'],
        );
    }

    /**
     * @param list<Game> $mine
     * @param list<Game> $theirs
     *
     * @return array{overlap: list<array{name: string, mine: Game, friend: Game}>, uniqueMine: list<Game>, uniqueFriend: list<Game>}
     */
    private function compareGames(array $mine, array $theirs): array
    {
        $theirsByKey = $this->indexByKey($theirs, fn (Game $game) => $this->gameKey($game));
        $mineKeys = [];

        $overlap = [];
        $uniqueMine = [];

        foreach ($mine as $game) {
            $key = $this->gameKey($game);
            $mineKeys[$key] = true;

            if (isset($theirsByKey[$key])) {
                $overlap[] = [
                    'name' => $game->getName(),
                    'mine' => $game,
                    'friend' => $theirsByKey[$key],
                ];
            } else {
                $uniqueMine[] = $game;
            }
        }

        $uniqueFriend = [];
        foreach ($theirs as $game) {
            $key = $this->gameKey($game);
            if (!isset($mineKeys[$key])) {
                $uniqueFriend[] = $game;
            }
        }

        usort($overlap, fn (array $a, array $b) => strcasecmp($a['name'], $b['name']));
        usort($uniqueMine, fn (Game $a, Game $b) => strcasecmp($a->getName(), $b->getName()));
        usort($uniqueFriend, fn (Game $a, Game $b) => strcasecmp($a->getName(), $b->getName()));

        return [
            'overlap' => $overlap,
            'uniqueMine' => $uniqueMine,
            'uniqueFriend' => $uniqueFriend,
        ];
    }

    /**
     * @param list<Console> $mine
     * @param list<Console> $theirs
     *
     * @return array{overlap: list<array{label: string, mine: Console, friend: Console}>, uniqueMine: list<Console>, uniqueFriend: list<Console>}
     */
    private function compareConsoles(array $mine, array $theirs): array
    {
        $theirsByKey = $this->indexByKey($theirs, fn (Console $console) => $this->consoleKey($console));
        $mineKeys = [];

        $overlap = [];
        $uniqueMine = [];

        foreach ($mine as $console) {
            $key = $this->consoleKey($console);
            $mineKeys[$key] = true;

            if (isset($theirsByKey[$key])) {
                $overlap[] = [
                    'label' => $this->consoleLabel($console),
                    'mine' => $console,
                    'friend' => $theirsByKey[$key],
                ];
            } else {
                $uniqueMine[] = $console;
            }
        }

        $uniqueFriend = [];
        foreach ($theirs as $console) {
            $key = $this->consoleKey($console);
            if (!isset($mineKeys[$key])) {
                $uniqueFriend[] = $console;
            }
        }

        usort($overlap, fn (array $a, array $b) => strcasecmp($a['label'], $b['label']));
        usort($uniqueMine, fn (Console $a, Console $b) => strcasecmp($this->consoleLabel($a), $this->consoleLabel($b)));
        usort($uniqueFriend, fn (Console $a, Console $b) => strcasecmp($this->consoleLabel($a), $this->consoleLabel($b)));

        return [
            'overlap' => $overlap,
            'uniqueMine' => $uniqueMine,
            'uniqueFriend' => $uniqueFriend,
        ];
    }

    /**
     * @template T
     *
     * @param list<T> $items
     * @param callable(T): string $keyFn
     *
     * @return array<string, T>
     */
    private function indexByKey(array $items, callable $keyFn): array
    {
        $indexed = [];

        foreach ($items as $item) {
            $key = $keyFn($item);
            if (!isset($indexed[$key])) {
                $indexed[$key] = $item;
            }
        }

        return $indexed;
    }

    private function gameKey(Game $game): string
    {
        return mb_strtolower(trim($game->getName()));
    }

    private function consoleKey(Console $console): string
    {
        return mb_strtolower(trim($console->getBrand()?->getName() ?? ''))
            .'|'
            .mb_strtolower(trim($console->getName()));
    }

    private function consoleLabel(Console $console): string
    {
        return sprintf('%s · %s', $console->getBrand(), $console->getName());
    }
}
