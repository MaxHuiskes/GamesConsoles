<?php

namespace App\Collection;

final class Condition
{
    public const MINT = 'mint';
    public const GOOD = 'good';
    public const FAIR = 'fair';
    public const POOR = 'poor';

    public const CHOICES = [
        'Mint' => self::MINT,
        'Good' => self::GOOD,
        'Fair' => self::FAIR,
        'Poor' => self::POOR,
    ];

    public static function sortRankDql(string $versionAlias): string
    {
        return sprintf(
            "CASE %s.condition WHEN 'mint' THEN 1 WHEN 'good' THEN 2 WHEN 'fair' THEN 3 WHEN 'poor' THEN 4 ELSE 5 END",
            $versionAlias
        );
    }

    public static function isValid(?string $value): bool
    {
        return null !== $value && in_array($value, self::CHOICES, true);
    }
}
