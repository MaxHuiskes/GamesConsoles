<?php

namespace App\Model;

final class RecentItem
{
    public function __construct(
        public readonly \DateTimeImmutable $createdAt,
        public readonly string $type,
        public readonly string $label,
        public readonly string $routeName,
        /** @var array<string, int|string> */
        public readonly array $routeParams,
    ) {
    }
}
