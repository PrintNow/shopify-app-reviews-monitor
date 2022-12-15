<?php

namespace App\Lib\Shopify;

class AppName
{
    public static function name(string $slug): string
    {
        return match ($slug) {
            default    => $slug
        };
    }
}
