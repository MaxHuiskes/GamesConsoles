<?php

namespace App\Model;

use App\Entity\Brand;

final class BrandStats
{
    public function __construct(
        public readonly Brand $brand,
        public readonly int $consoleCount,
        public readonly int $gameCount,
    ) {
    }
}
