<?php

namespace App\Helpers;

use Illuminate\Support\Str;

class SeoHelper
{
    /**
     * Turn rich-text/HTML content (event descriptions, blog bodies, bios) into a
     * plain-text excerpt safe for <meta name="description">, og:description, etc.
     */
    public static function excerpt(?string $html, int $limit = 160): string
    {
        $text = trim(html_entity_decode(strip_tags((string) $html), ENT_QUOTES));
        $text = preg_replace('/\s+/', ' ', $text) ?? '';

        return Str::limit($text, $limit);
    }
}
