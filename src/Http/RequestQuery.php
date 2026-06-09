<?php

namespace App\Http;

use Symfony\Component\HttpFoundation\Request;

final class RequestQuery
{
    public static function optionalPositiveInt(Request $request, string $key): ?int
    {
        if (!$request->query->has($key)) {
            return null;
        }

        $value = $request->query->get($key);
        if (null === $value || '' === $value) {
            return null;
        }

        $int = filter_var($value, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);

        return null !== $int && $int > 0 ? $int : null;
    }
}
