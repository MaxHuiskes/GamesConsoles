<?php

namespace App\Model;

use App\Entity\Console;
use App\Entity\Game;

final class CollectionComparison
{
    /**
     * @param list<array{name: string, mine: Game, friend: Game}> $overlapGames
     * @param list<Game> $uniqueMineGames
     * @param list<Game> $uniqueFriendGames
     * @param list<array{label: string, mine: Console, friend: Console}> $overlapConsoles
     * @param list<Console> $uniqueMineConsoles
     * @param list<Console> $uniqueFriendConsoles
     */
    public function __construct(
        public readonly array $overlapGames,
        public readonly array $uniqueMineGames,
        public readonly array $uniqueFriendGames,
        public readonly array $overlapConsoles,
        public readonly array $uniqueMineConsoles,
        public readonly array $uniqueFriendConsoles,
    ) {
    }
}
