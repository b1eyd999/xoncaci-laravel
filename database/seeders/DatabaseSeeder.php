<?php

namespace Database\Seeders;

use App\Models\Faq;
use App\Models\FillCategory;
use App\Models\FillItem;
use App\Models\Occasion;
use App\Models\Review;
use App\Models\User;
use App\Models\Xonca;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * A starting catalogue so the site looks alive from the first run. Every
 * row is the owner's to change or delete in the admin.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Local admin. Change the password in the admin after the first login.
        User::updateOrCreate(['email' => 'admin@xoncaci.az'], [
            'name' => 'Admin',
            'password' => env('ADMIN_PASSWORD', 'xonca-local-2026'),
        ]);

        $occasions = collect([
            ['Toy', 'toy', 'Bəy və gəlin evi üçün təntənəli xonçalar', '#6e1a26'],
            ['Nişan', 'nisan', 'Üzük, şirniyyat və hədiyyələr üçün zərif xonçalar', '#b0476b'],
            ['Xınayaxdı', 'xinayaxdi', 'Şamlar və xına ilə ənənəvi gecənin xonçaları', '#7a4a12'],
            ['Söz kəsmə', 'soz-kesme', 'Ailələrin ilk görüşü üçün sadə və zövqlü', '#3d6b4f'],
            ['Doğum günü', 'dogum-gunu', 'Sevdiyiniz insana hədiyyə xonçası', '#5b3f8a'],
        ])->mapWithKeys(fn ($o, $i) => [$o[1] => Occasion::updateOrCreate(['slug' => $o[1]], [
            'name' => $o[0], 'tagline' => $o[2], 'accent' => $o[3], 'sort' => $i,
        ])]);

        $cats = collect([
            ['Şirniyyat', 'sirniyyat'], ['Meyvə', 'meyve'], ['Şam və xına', 'sam-xina'],
            ['Hədiyyələr', 'hediyyeler'], ['Parça və geyim', 'parca'], ['Bəzək', 'bezek'],
        ])->mapWithKeys(fn ($c, $i) => [$c[1] => FillCategory::updateOrCreate(['slug' => $c[1]], ['name' => $c[0], 'sort' => $i])]);

        $items = collect([
            // [category, name, model, price, color, color2, max, scale]
            ['sirniyyat', 'Kəllə qənd', 'kelle_qend', 12, '#6e1a26', '#d4af37', 2, 1],
            ['sirniyyat', 'Bakı paxlavası', 'pakhlava', 18, '#c98a3c', '#5b3a1e', 4, 1],
            ['sirniyyat', 'Şəkərbura', 'shekerbura', 14, '#eac78d', '#c9954f', 4, 1],
            ['sirniyyat', 'Konfet qabı', 'sweets_bowl', 16, '#e84a5f', '#ffd166', 3, 1],
            ['sirniyyat', 'Şokolad (ürək qutu)', 'chocolate', 22, '#6e1a26', '#d4af37', 4, 1],
            ['sirniyyat', 'Üçmərtəbəli tort', 'cake', 45, '#fbf3ea', '#d4af37', 1, 1],
            ['sirniyyat', 'Qoz-fındıq', 'nuts_bowl', 12, '#8b5a2b', '#d4af37', 3, 1],
            ['meyve', 'Nar', 'pomegranate', 2, '#7a1f2b', '#7d1020', 12, 1],
            ['meyve', 'Qırmızı alma', 'apple', 1.5, '#c0392b', '#3f8f3a', 12, 1],
            ['meyve', 'Yaşıl alma', 'apple', 1.5, '#8dc63f', '#3f8f3a', 12, 1],
            ['meyve', 'Portağal', 'orange', 1.5, '#f28c1b', '#2f7d32', 12, 1],
            ['meyve', 'Üzüm salxımı', 'grapes', 4, '#5b2a6e', '#6b4a2b', 6, 1],
            ['sam-xina', 'Ağ şam', 'candle', 3, '#fff7e6', null, 12, 1],
            ['sam-xina', 'Qırmızı şam', 'candle', 3, '#6e1a26', null, 12, 1],
            ['sam-xina', 'Üç şam dəsti', 'candles3', 9, '#fff7e6', '#e6c7c2', 4, 1],
            ['sam-xina', 'Xına qabı (şamlı)', 'henna_bowl', 25, '#5c6b2b', '#d4af37', 1, 1],
            ['hediyyeler', 'Üzük qutusu', 'ring_box', 10, '#6e1a26', '#e6c26b', 2, 1],
            ['hediyyeler', 'Boyunbağı qutusu', 'necklace', 15, '#1d3557', '#e6c26b', 2, 1],
            ['hediyyeler', 'Ətir', 'perfume', 8, '#f0b6c8', '#d4af37', 4, 1],
            ['hediyyeler', 'Hədiyyə qutusu', 'gift_box', 10, '#e6c7c2', '#d4af37', 4, 1],
            ['hediyyeler', 'Ayaqqabı qutusu', 'shoes', 10, '#ffffff', '#d4af37', 2, 1],
            ['hediyyeler', 'Çay qutusu', 'tea', 6, '#1f4a3d', '#d4af37', 4, 1],
            ['parca', 'Kəlağayı', 'shawl', 20, '#ffffff', '#6e1a26', 3, 1],
            ['parca', 'Parça topu', 'fabric', 18, '#5a1a2a', '#d4af37', 3, 1],
            ['bezek', 'Güzgü', 'mirror', 20, '#d4af37', '#6e1a26', 1, 1],
            ['bezek', 'Gül dəstəsi', 'flowers', 15, '#8e3a4a', '#fdf1f3', 2, 1],
        ])->map(fn ($r, $i) => FillItem::updateOrCreate(['slug' => Str::slug($r[1])], [
            'fill_category_id' => $cats[$r[0]]->id, 'name' => $r[1], 'model' => $r[2], 'price' => $r[3],
            'color' => $r[4], 'color2' => $r[5], 'max_qty' => $r[6], 'scale' => $r[7], 'sort' => $i,
        ]))->keyBy('slug');

        $f = fn (string $name) => $items[Str::slug($name)]->id;

        $xoncas = [
            ['XN-001', 'Qızıl Saray', 'round', 'klassik', 60, '#c9a14a', '#e8c77a', 'tulle', '#fff4f4', '#6e1a26', 60, 30, 2, true, ['toy', 'nisan'],
                'Zərif oyma naxışlı klassik qızıl xonça. Tül örtük və tünd qırmızı atlaz lentlə.',
                [['Kəllə qənd', 1], ['Bakı paxlavası', 1], ['Nar', 4], ['Ağ şam', 4], ['Konfet qabı', 1]]],
            ['XN-002', 'Gümüş Ay', 'round', 'modern', 55, '#c7c9cc', '#e9ecef', 'glass', '#ffffff', '#e6c7c2', 55, 30, 2, true, ['nisan', 'dogum-gunu'],
                'Gümüşü sini, şüşə gümbəz və çəhrayı lentlər — müasir nişan üçün.',
                [['Üzük qutusu', 1], ['Ətir', 2], ['Şokolad (ürək qutu)', 2], ['Gül dəstəsi', 1]]],
            ['XN-003', 'Xına Gecəsi', 'round', 'klassik', 65, '#b8862b', '#d9a441', 'tulle', '#fff1d6', '#6e1a26', 60, 30, 2, true, ['xinayaxdi'],
                'Xınayaxdı üçün: ortada şamlı xına qabı, ətrafında şirniyyat və şamlar.',
                [['Xına qabı (şamlı)', 1], ['Qırmızı şam', 6], ['Şəkərbura', 1], ['Bakı paxlavası', 1]]],
            ['XN-004', 'Ürək', 'heart', 'modern', 55, '#d8b36a', '#f0d59a', 'tulle', '#ffe8ee', '#8e3a4a', 70, 30, 1, true, ['nisan', 'dogum-gunu'],
                'Ürək formalı sini — sevgi dolu hədiyyə üçün.',
                [['Gül dəstəsi', 1], ['Şokolad (ürək qutu)', 2], ['Ətir', 1], ['Üzük qutusu', 1]]],
            ['XN-005', 'Şah Sinisi', 'oval', 'lux', 70, '#c9a14a', '#f3d98b', 'glass', '#ffffff', '#6e1a26', 90, 50, 1, true, ['toy'],
                'Böyük oval lüks sini, şüşə gümbəz. Gəlin evinin başlıca xonçası.',
                [['Üçmərtəbəli tort', 1], ['Kəllə qənd', 2], ['Nar', 4], ['Üç şam dəsti', 2]]],
            ['XN-006', 'Kəlağayı', 'rect', 'klassik', 65, '#b99552', '#e3c47f', 'tulle', '#fdf6ec', '#5a1a2a', 65, 30, 2, false, ['toy', 'soz-kesme'],
                'Düzbucaqlı sini: kəlağayı, parça və ayaqqabı — gəlin payı üçün.',
                [['Kəlağayı', 2], ['Parça topu', 1], ['Ayaqqabı qutusu', 1], ['Ətir', 1]]],
            ['XN-007', 'Meyvə Bağı', 'round', 'klassik', 60, '#c9a14a', '#e8c77a', 'none', '#ffffff', '#1f4a3d', 45, 20, 3, false, ['soz-kesme', 'toy'],
                'Nar, alma, portağal və üzüm — bol meyvəli açıq xonça.',
                [['Nar', 5], ['Qırmızı alma', 5], ['Portağal', 4], ['Üzüm salxımı', 3]]],
            ['XN-008', 'Səkkiz Ulduz', 'octagon', 'lux', 60, '#a8792a', '#d9b25c', 'tulle', '#fff4f4', '#d4af37', 75, 40, 1, true, ['toy', 'nisan'],
                'Səkkizbucaqlı antik sini, qızılı lent. Güzgü və şamlarla.',
                [['Güzgü', 1], ['Ağ şam', 4], ['Boyunbağı qutusu', 1], ['Hədiyyə qutusu', 2]]],
            ['XN-009', 'Çəhrayı Xəyal', 'round', 'modern', 50, '#e0b8a0', '#f4d8c8', 'tulle', '#ffe4ec', '#e6c7c2', 45, 20, 3, false, ['nisan', 'dogum-gunu'],
                'Rose-gold sini, çəhrayı tül — incə və romantik.',
                [['Konfet qabı', 1], ['Hədiyyə qutusu', 2], ['Ətir', 2], ['Gül dəstəsi', 1]]],
            ['XN-010', 'Çay Süfrəsi', 'rect', 'klassik', 60, '#c9a14a', '#e8c77a', 'none', '#ffffff', '#1f4a3d', 40, 20, 2, false, ['soz-kesme'],
                'Söz kəsmə üçün: çay, qənd, şirniyyat və qoz-fındıq.',
                [['Çay qutusu', 2], ['Kəllə qənd', 1], ['Qoz-fındıq', 1], ['Şəkərbura', 1], ['Bakı paxlavası', 1]]],
            ['XN-011', 'Ağ Qu', 'oval', 'lux', 65, '#dcdcdc', '#ffffff', 'glass', '#ffffff', '#ffffff', 80, 40, 1, false, ['toy', 'nisan'],
                'Ağ və gümüşü, şüşə gümbəz altında tort və şamlar.',
                [['Üçmərtəbəli tort', 1], ['Ağ şam', 4], ['Gül dəstəsi', 1]]],
            ['XN-012', 'Nar Çiçəyi', 'heart', 'klassik', 55, '#c9a14a', '#e8c77a', 'tulle', '#fff0f0', '#7a1f2b', 60, 30, 2, false, ['xinayaxdi', 'toy'],
                'Ürək formalı sini, narlar və qırmızı şamlarla.',
                [['Nar', 6], ['Qırmızı şam', 4], ['Kəllə qənd', 1]]],
        ];

        foreach ($xoncas as $i => [$code, $name, $shape, $style, $size, $tray, $rim, $cover, $coverColor, $ribbon, $price, $deposit, $stock, $featured, $occ, $desc, $fills]) {
            $x = Xonca::updateOrCreate(['code' => $code], [
                'name' => $name, 'slug' => Str::slug($name), 'shape' => $shape, 'style' => $style, 'size_cm' => $size,
                'tray_color' => $tray, 'rim_color' => $rim, 'cover_type' => $cover, 'cover_color' => $coverColor,
                'ribbon_color' => $ribbon, 'price' => $price, 'deposit' => $deposit, 'stock' => $stock,
                'empty_discount' => 10, 'is_featured' => $featured, 'sort' => $i,
                'short_description' => Str::limit($desc, 70), 'description' => $desc,
            ]);
            $x->occasions()->sync($occasions->only($occ)->pluck('id'));
            $x->defaultFills()->delete();
            foreach ($fills as [$item, $qty]) {
                $x->defaultFills()->create(['fill_item_id' => $f($item), 'qty' => $qty]);
            }
        }

        if (! Review::exists()) {
            foreach ([
                ['Aytən', 'Nişan', 'Xonçalar şəkildəkindən də gözəl idi. 3D-də seçdiyimiz kimi gətirdilər, qonaqlar heyran qaldı.'],
                ['Rəşad', 'Toy', 'Yeddi xonça sifariş etdik, vaxtında çatdırdılar və ertəsi gün özləri götürdülər. Çox rahat.'],
                ['Günel', 'Xınayaxdı', 'Şamlı xına qabı gecənin ən gözəl anı oldu. İçini özümüz seçdik, qiyməti də əlverişli.'],
                ['Leyla', 'Söz kəsmə', 'Çay xonçası çox zövqlü idi. Qaynanam hələ də soruşur haradan almışıq :)'],
                ['Elvin', 'Doğum günü', 'Ürək formalı xonçanı həyat yoldaşıma hədiyyə etdim — göz yaşlarını saxlaya bilmədi.'],
                ['Nigar', 'Toy', 'Kəlağayı xonçası ənənəvi, amma müasir görünürdü. Hamıya tövsiyə edirəm.'],
            ] as $i => [$name, $occasion, $text]) {
                Review::create(['name' => $name, 'occasion' => $occasion, 'text' => $text, 'sort' => $i]);
            }
        }

        if (! Faq::exists()) {
            foreach ([
                ['Xonçanı neçə günlük icarəyə verirsiniz?', 'Xonçanı tədbirdən bir gün əvvəl çatdırırıq və tədbirdən bir gün sonra geri götürürük. Qiymətə bu günlər daxildir.'],
                ['İçini özüm doldura bilərəmmi?', 'Bəli. Üç yol var: bizim hazır dolduruşumuzla götürün, saytda 3D qurucuda özünüz seçin, ya da boş götürüb evdə özünüz doldurun — boş xonça daha ucuzdur.'],
                ['Depozit nədir?', 'Depozit xonça geri qaytarılanda tam geri verilir. Xonça zədələnərsə, bərpa xərci depozitdən çıxılır.'],
                ['Çatdırılma pulludur?', 'Bakı daxilində çatdırılma haqqı səbətdə göstərilir; müəyyən məbləğdən yuxarı sifarişlərdə çatdırılma pulsuzdur. İstəsəniz özünüz də götürə bilərsiniz.'],
                ['Neçə gün əvvəl sifariş etməliyəm?', 'Ən azı 2 gün əvvəl. Toy mövsümündə məşhur xonçalar tez tutulur, ona görə tarixi erkən seçməyi tövsiyə edirik.'],
                ['Ödənişi necə edirəm?', 'Sifarişdən sonra sizinlə əlaqə saxlayıb təsdiqləyirik. Ödəniş çatdırılma zamanı nağd və ya kartla edilir.'],
            ] as $i => [$q, $a]) {
                Faq::create(['question' => $q, 'answer' => $a, 'sort' => $i]);
            }
        }
    }
}
