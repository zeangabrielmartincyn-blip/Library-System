<?php

namespace Illuminate\Support;

if (! function_exists(__NAMESPACE__.'\\mb_split')) {
    /**
     * Compatibility shim for environments where ext-mbstring is present but mb_split() is unavailable.
     *
     * Laravel uses mb_split() in Illuminate\Support\Str, so we provide a small fallback that
     * behaves like a regex-based whitespace splitter for the app's boot path.
     */
    function mb_split(string $pattern, string $string, int $limit = -1): array
    {
        $delimiter = '/';
        $regex = $delimiter.trim($pattern, $delimiter).$delimiter.'u';

        $result = preg_split($regex, $string, $limit);

        return $result === false ? [] : $result;
    }
}
