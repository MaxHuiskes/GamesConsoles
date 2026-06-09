<?php

namespace App\Model;

use App\Entity\Brand;
use App\Entity\Console;

final class QuickAddData
{
    public const TYPE_GAME = 'game';
    public const TYPE_CONSOLE = 'console';

    public string $type = self::TYPE_GAME;
    public string $name = '';
    public ?Brand $brand = null;
    public ?Console $console = null;
}
