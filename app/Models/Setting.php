<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $primaryKey = 'key';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    /**
     * What the site shows until the owner writes his own. Read through get(),
     * never cached on their own, so a new default reaches a site whose
     * settings were cached before it existed.
     */
    public const DEFAULTS = [
        'site_name' => 'Xonçacı',
        'phone' => '+994 50 000 00 00',
        'whatsapp' => '994500000000',
        'instagram' => 'xoncaci.az',
        'address' => 'Bakı şəhəri',
        'working_hours' => 'Hər gün 09:00 – 21:00',
        'hero_title' => 'Ən gözəl xonçalar — ən xoş gününüz üçün',
        'hero_subtitle' => 'Toy, nişan və xınayaxdı üçün əl işi xonçaları icarəyə götürün. İçini özünüz seçin və 3D-də necə görünəcəyinə elə indi baxın.',
        'delivery_fee' => '15',
        'free_delivery_from' => '300',
        'days_before' => '1',
        'days_after' => '1',
        'min_days_ahead' => '2',
        'telegram_token' => '',
        'telegram_chat' => '',
        'districts' => "Binəqədi\nXətai\nXəzər\nQaradağ\nNərimanov\nNəsimi\nNizami\nSabunçu\nSəbail\nSuraxanı\nYasamal\nPirallahı\nAbşeron (Xırdalan)\nSumqayıt",
    ];

    public static function get(string $key, mixed $default = null): mixed
    {
        $all = Cache::rememberForever('settings', fn () => static::query()->pluck('value', 'key')->all());

        $value = $all[$key] ?? null;

        return ($value === null || $value === '') ? ($default ?? self::DEFAULTS[$key] ?? null) : $value;
    }

    public static function put(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget('settings');
    }

    /** @return list<string> */
    public static function districts(): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\r?\n/', (string) static::get('districts')))));
    }
}
