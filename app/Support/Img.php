<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

/**
 * An image the site shows is either a file the owner uploaded or, until he
 * has his own photographs, a free Unsplash photo addressed by URL.
 */
class Img
{
    /** Atmosphere photos used until the owner uploads his own. */
    public const STOCK = [
        'hero' => 'https://images.unsplash.com/photo-1718463384055-738905bdd16e',
        'cta' => 'https://images.unsplash.com/photo-1718463383973-5db9edea7d34',
        'craft' => 'https://images.unsplash.com/photo-1769812343176-8c960e415ab2',
        'toy' => 'https://images.unsplash.com/photo-1769230361357-8278572302d2',
        'nisan' => 'https://images.unsplash.com/photo-1549487027-8c93657755e0',
        'xinayaxdi' => 'https://images.unsplash.com/photo-1656702678285-59bde376828f',
        'soz-kesme' => 'https://images.unsplash.com/photo-1623241237041-67ece20b63de',
        'dogum-gunu' => 'https://images.unsplash.com/photo-1612351641432-20a0f196086c',
        'detail' => 'https://images.unsplash.com/photo-1574709755254-fcd942d09d5a',
    ];

    public static function url(?string $value, int $width = 1600): ?string
    {
        if (blank($value)) {
            return null;
        }
        if (str_starts_with($value, 'https://images.unsplash.com/')) {
            return strtok($value, '?') . '?auto=format&fit=crop&q=78&w=' . $width;
        }
        if (str_starts_with($value, 'http')) {
            return $value;
        }

        return Storage::disk('public')->url($value);
    }

    /** A srcset so phones do not download the desktop picture. */
    public static function srcset(?string $value): ?string
    {
        if (! $value || ! str_starts_with($value, 'https://images.unsplash.com/')) {
            return null;
        }

        return collect([640, 1024, 1600, 2400])->map(fn ($w) => self::url($value, $w) . " {$w}w")->implode(', ');
    }
}
