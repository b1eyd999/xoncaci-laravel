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
        'working_hours' => 'Hər gün 09:00-dan 21:00-dək',
        'hero_title' => 'Ən xoş gününüz üçün | zərif xonçalar',
        'hero_title_ru' => 'Для самого счастливого дня | изысканные хончи',
        'hero_title_en' => 'For your happiest day | exquisite khonchas',
        'hero_subtitle' => 'Toy, nişan və xınayaxdı üçün əl ilə bəzədilmiş xonçaların icarəsi. İçini özünüz seçin, biz gətirir, qurur və məclisdən sonra özümüz götürürük.',
        'hero_subtitle_ru' => 'Аренда хонч ручной работы для свадьбы, помолвки и хынаяхды. Наполнение выбираете вы, а мы привозим, оформляем и забираем после торжества.',
        'hero_subtitle_en' => 'Hand-dressed khonchas to rent for weddings, engagements and henna nights. You choose what goes inside; we deliver, set them up and collect them afterwards.',
        'address_ru' => 'Баку',
        'address_en' => 'Baku',
        'working_hours_ru' => 'Ежедневно с 09:00 до 21:00',
        'working_hours_en' => 'Daily, 9 am to 9 pm',
        'hero_image' => '',
        'craft_image' => '',
        'cta_image' => '',
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

    /** The Russian or English copy of a text setting, Azerbaijani if there is none. */
    public static function local(string $key): mixed
    {
        $locale = app()->getLocale();
        if ($locale !== 'az') {
            $value = static::get($key . '_' . $locale);
            if (filled($value)) {
                return $value;
            }
        }

        return static::get($key);
    }

    /** @return list<string> */
    public static function districts(): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\r?\n/', (string) static::get('districts')))));
    }
}
