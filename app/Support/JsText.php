<?php

namespace App\Support;

/**
 * Sentences the browser scripts print (builder, cart, 3D window), in the
 * page's language. Keys are what the scripts ask for; the Azerbaijani text
 * is the translation key in lang/ru.json and lang/en.json.
 */
class JsText
{
    public static function all(): array
    {
        return collect([
            'ribbon.#6e1a26' => 'Bordo',
            'ribbon.#c2a064' => 'Şampan qızılı',
            'ribbon.#e6c7c2' => 'Pudra',
            'ribbon.#f4eee4' => 'Fil sümüyü',
            'ribbon.#1f4a3d' => 'Zümrüd',
            'ribbon.#1c2340' => 'Gecə mavisi',
            'ribbon.#8e3a4a' => 'Moruq',
            'ribbon.#5a1a2a' => 'Albalı',
            'ribbon.#7a1f2b' => 'Nar',
            'ribbon.#ffffff' => 'Ağ',
            'ribbon.#d4af37' => 'Qızılı',
            'max' => ':name: ən çox :n ədəd',
            'too_soon' => 'Bu tarixə hazırlamağa vaxt qalmır. Bir az gec bir gün seçin.',
            'taken' => 'Bu xonça həmin günə artıq rezerv olunub.',
            'checking' => 'Yoxlanılır…',
            'free' => 'Bu tarixə boşdur.',
            'nothing_chosen' => 'Heç nə seçməmisiniz. Əşya əlavə edin və ya «Boş xonça»nı seçin.',
            'added' => '«:name» səbətə əlavə olundu',
            'to_cart' => 'Səbətə keç',
            'save_changes' => 'Dəyişikliyi saxla',
            'mode.ours' => 'Ustalarımızın dolduruşu',
            'mode.custom' => 'Öz seçimim',
            'mode.empty' => 'Boş xonça',
            'line_taken' => 'Bu tarixə boş deyil',
            'line_free' => 'Tarixə boşdur',
            'edit' => 'Dəyiş',
            'duplicate' => 'Eynisindən',
            'remove' => 'Sil',
            'free_delivery' => 'Pulsuz',
            'delivery_is_free' => 'Çatdırılma pulsuzdur.',
            'delivery_left' => 'Daha :sum əlavə etsəniz, çatdırılma pulsuz olacaq',
            'some_taken' => 'Bəzi xonçalar bu tarixə boş deyil. Onları dəyişin və ya başqa tarix seçin.',
            'all_free' => 'Bütün xonçalar bu tarixə boşdur.',
            'sending' => 'Göndərilir…',
            'send' => 'Sifarişi göndər',
            'too_many' => 'Çox tez cəhd edirsiniz, bir dəqiqə gözləyin.',
            'error' => 'Xəta baş verdi, yenidən cəhd edin.',
            'model_failed' => '3D model açılmadı. Səhifəni yeniləyin.',
        ])->map(fn ($az) => __($az))->all();
    }
}
