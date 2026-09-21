<?php

namespace App\Support;

class ColorSwatch
{
    /**
     * Admin-entered color names that aren't valid CSS <named-color> keywords
     * (e.g. "Sky Blue", "Navy Blue") silently fail as a background-color and
     * render blank/white — indistinguishable from each other and from an
     * actual "White" swatch. Map the ones seen in product data to real hex
     * values; anything not listed falls through to the space-stripped guess
     * below, which already covers cases like "Sky Blue" -> "skyblue".
     */
    protected static array $map = [
        'sandal'      => '#c8a97e',
        'navy blue'   => '#000080',
        'olive green' => '#556b2f',
        'off white'   => '#f5f5f0',
        'sea green'   => '#2e8b57',
        'rose gold'   => '#b76e79',
        'mustard'     => '#e1ad01',
        'wine'        => '#722f37',
    ];

    public static function css(?string $name): string
    {
        $key = strtolower(trim((string) $name));

        if ($key === '') {
            return '#d1d5db';
        }

        if (isset(self::$map[$key])) {
            return self::$map[$key];
        }

        // "sky blue" -> "skyblue" is a valid CSS keyword even though the
        // spaced form typed into the admin panel isn't.
        return str_replace(' ', '', $key);
    }
}
