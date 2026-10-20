<?php

namespace App\Support;

use App\Models\Faq;
use App\Models\FillCategory;
use App\Models\FillItem;
use App\Models\Occasion;
use App\Models\Review;
use App\Models\Xonca;

/**
 * Russian and English for the starting catalogue, and its Azerbaijani
 * texts without dashes. Rows are found by their Azerbaijani name/code; a row
 * the owner has already translated or renamed is left alone.
 */
class CatalogText
{
    public const OCCASIONS = [
        'toy' => [['Свадьба', 'Торжественные хончи для дома жениха и невесты'], ['Wedding', 'Ceremonial khonchas for the homes of the bride and groom']],
        'nisan' => [['Помолвка', 'Изящные хончи для кольца, сладостей и подарков'], ['Engagement', 'Graceful khonchas for the ring, sweets and gifts']],
        'xinayaxdi' => [['Хынаяхды', 'Хончи для традиционного вечера со свечами и хной'], ['Henna night', 'Khonchas for the traditional evening of candles and henna']],
        'soz-kesme' => [['Сватовство', 'Просто и со вкусом для первой встречи семей'], ['Betrothal', 'Simple and tasteful, for the first meeting of the families']],
        'dogum-gunu' => [['День рождения', 'Хонча в подарок любимому человеку'], ['Birthday', 'A khoncha as a gift for someone you love']],
    ];

    public const CATEGORIES = [
        'Şirniyyat' => ['Сладости', 'Sweets'], 'Meyvə' => ['Фрукты', 'Fruit'], 'Şam və xına' => ['Свечи и хна', 'Candles and henna'],
        'Hədiyyələr' => ['Подарки', 'Gifts'], 'Parça və geyim' => ['Ткани и одежда', 'Fabric and clothing'], 'Bəzək' => ['Декор', 'Decor'],
    ];

    public const ITEMS = [
        'Kəllə qənd' => ['Сахарная голова', 'Sugar loaf'], 'Bakı paxlavası' => ['Бакинская пахлава', 'Baku pakhlava'],
        'Şəkərbura' => ['Шекербура', 'Shakarbura'], 'Konfet qabı' => ['Ваза с конфетами', 'Bowl of sweets'],
        'Şokolad (ürək qutu)' => ['Шоколад в коробке-сердце', 'Chocolates in a heart box'], 'Üçmərtəbəli tort' => ['Трёхъярусный торт', 'Three-tier cake'],
        'Qoz-fındıq' => ['Орехи', 'Nuts'], 'Qoz və fındıq' => ['Орехи', 'Nuts'], 'Nar' => ['Гранат', 'Pomegranate'],
        'Qırmızı alma' => ['Красное яблоко', 'Red apple'], 'Yaşıl alma' => ['Зелёное яблоко', 'Green apple'], 'Portağal' => ['Апельсин', 'Orange'],
        'Üzüm salxımı' => ['Гроздь винограда', 'Bunch of grapes'], 'Ağ şam' => ['Белая свеча', 'White candle'], 'Qırmızı şam' => ['Красная свеча', 'Red candle'],
        'Üç şam dəsti' => ['Набор из трёх свечей', 'Set of three candles'], 'Xına qabı (şamlı)' => ['Чаша для хны со свечами', 'Henna bowl with candles'],
        'Üzük qutusu' => ['Футляр для кольца', 'Ring box'], 'Boyunbağı qutusu' => ['Футляр для колье', 'Necklace box'], 'Ətir' => ['Парфюм', 'Perfume'],
        'Hədiyyə qutusu' => ['Подарочная коробка', 'Gift box'], 'Ayaqqabı qutusu' => ['Коробка с обувью', 'Shoe box'], 'Çay qutusu' => ['Чай в жестяной банке', 'Tea tin'],
        'Kəlağayı' => ['Келагаи, шёлковый платок', 'Kalaghayi silk scarf'], 'Parça topu' => ['Отрез ткани', 'Roll of fabric'],
        'Güzgü' => ['Зеркало', 'Mirror'], 'Gül dəstəsi' => ['Букет цветов', 'Bouquet of flowers'],
    ];

    /** code => [az description, [ru name, ru description], [en name, en description]] */
    public const XONCAS = [
        'XN-001' => ['Zərif oyma naxışlı klassik qızıl xonça. Tül örtük və tünd qırmızı atlaz lentlə.', ['Золотой дворец', 'Классическая золотая хонча с тонкой гравировкой, под тюлем, с атласными лентами.'], ['Golden Palace', 'A classic gold khoncha with fine engraving, under tulle with satin ribbons.']],
        'XN-002' => ['Gümüşü sini, şüşə gümbəz və pudra rəngli lentlər. Müasir nişan üçün.', ['Серебряная луна', 'Серебряный поднос, стеклянный купол и пудровые ленты. Для современной помолвки.'], ['Silver Moon', 'A silver tray, a glass dome and powder pink ribbons, for a modern engagement.']],
        'XN-003' => ['Xınayaxdı üçün: ortada şamlı xına qabı, ətrafında şirniyyat və şamlar.', ['Ночь хны', 'Для хынаяхды: в центре чаша с хной и свечами, вокруг сладости и свечи.'], ['Henna Night', 'For the henna night: a henna bowl with candles at the centre, sweets and candles around it.']],
        'XN-004' => ['Ürək formalı sini, sevgi dolu hədiyyə üçün.', ['Сердце', 'Поднос в форме сердца для подарка с любовью.'], ['Heart', 'A heart shaped tray for a gift full of love.']],
        'XN-005' => ['Böyük oval lüks sini, şüşə gümbəz. Gəlin evinin başlıca xonçası.', ['Шахский поднос', 'Большой овальный поднос класса люкс под стеклянным куполом. Главная хонча дома невесты.'], ['The Shah\'s Tray', 'A large oval luxury tray under a glass dome, the centrepiece of the bride\'s home.']],
        'XN-006' => ['Düzbucaqlı sini: kəlağayı, parça və ayaqqabı, gəlin payı üçün.', ['Келагаи', 'Прямоугольный поднос: келагаи, ткань и обувь, подарки для невесты.'], ['Kalaghayi', 'A rectangular tray of kalaghayi, fabric and shoes, gifts for the bride.']],
        'XN-007' => ['Nar, alma, portağal və üzüm. Bol meyvəli açıq xonça.', ['Фруктовый сад', 'Гранаты, яблоки, апельсины и виноград. Открытая хонча, полная фруктов.'], ['Orchard', 'Pomegranates, apples, oranges and grapes: an open khoncha full of fruit.']],
        'XN-008' => ['Səkkizbucaqlı antik sini, qızılı lent. Güzgü və şamlarla.', ['Восемь звёзд', 'Антикварный восьмиугольный поднос с золотой лентой, зеркалом и свечами.'], ['Eight Stars', 'An antique octagonal tray with a gold ribbon, a mirror and candles.']],
        'XN-009' => ['Çəhrayı qızılı sini və çəhrayı tül. İncə və romantik.', ['Розовая мечта', 'Поднос цвета розового золота и розовый тюль. Нежно и романтично.'], ['Rose Dream', 'A rose gold tray under pink tulle. Delicate and romantic.']],
        'XN-010' => ['Söz kəsmə üçün: çay, qənd, şirniyyat, qoz və fındıq.', ['Чайный стол', 'Для сватовства: чай, сахар, сладости и орехи.'], ['Tea Table', 'For the betrothal: tea, sugar, sweets and nuts.']],
        'XN-011' => ['Ağ və gümüşü, şüşə gümbəz altında tort və şamlar.', ['Белый лебедь', 'Белое с серебром: торт и свечи под стеклянным куполом.'], ['White Swan', 'White and silver: a cake and candles under a glass dome.']],
        'XN-012' => ['Ürək formalı sini, narlar və qırmızı şamlarla.', ['Цветок граната', 'Поднос в форме сердца с гранатами и красными свечами.'], ['Pomegranate Blossom', 'A heart shaped tray with pomegranates and red candles.']],
    ];

    /** az question => [az answer, [ru q, ru a], [en q, en a]] */
    public const FAQS = [
        'Xonçanı neçə günlük icarəyə verirsiniz?' => ['Xonçanı tədbirdən bir gün əvvəl çatdırırıq və tədbirdən bir gün sonra geri götürürük. Qiymətə bu günlər daxildir.', ['На сколько дней сдаётся хонча?', 'Мы привозим хончу за день до торжества и забираем на следующий день после. Эти дни входят в цену.'], ['How long is a khoncha rented for?', 'We deliver it the day before your event and collect it the day after. Those days are included in the price.']],
        'İçini özüm doldura bilərəmmi?' => ['Bəli. Üç yol var: ustalarımızın hazır dolduruşu ilə götürün, saytda əşyaları özünüz seçin və ya boş götürüb evdə özünüz doldurun. Boş xonça daha ucuzdur.', ['Можно ли наполнить хончу самому?', 'Да. Есть три варианта: взять с готовым наполнением от наших мастеров, выбрать всё самому на сайте или взять пустую и наполнить дома. Пустая хонча дешевле.'], ['Can I fill it myself?', 'Yes. There are three ways: take it with our masters\' filling, choose every item yourself on the site, or take it empty and fill it at home. An empty khoncha costs less.']],
        'Depozit nədir?' => ['Depozit xonça geri qaytarılanda tam geri verilir. Xonça zədələnərsə, bərpa xərci depozitdən çıxılır.', ['Что такое депозит?', 'Депозит полностью возвращается, когда хонча возвращена. Если хонча повреждена, стоимость ремонта вычитается из депозита.'], ['What is the deposit?', 'The deposit is returned in full when the khoncha comes back. If it is damaged, the repair cost is taken from the deposit.']],
        'Çatdırılma pulludur?' => ['Bakı daxilində çatdırılma haqqı səbətdə göstərilir, müəyyən məbləğdən yuxarı sifarişlərdə isə çatdırılma pulsuzdur. İstəsəniz özünüz də götürə bilərsiniz.', ['Доставка платная?', 'Стоимость доставки по Баку видна в корзине, а при заказе от определённой суммы доставка бесплатна. Можно также забрать самим.'], ['Is delivery charged?', 'The delivery fee within Baku is shown in the basket, and above a certain amount delivery is free. You can also collect it yourself.']],
        'Neçə gün əvvəl sifariş etməliyəm?' => ['Ən azı 2 gün əvvəl. Toy mövsümündə məşhur xonçalar tez tutulur, ona görə tarixi erkən seçməyi tövsiyə edirik.', ['За сколько дней нужно заказывать?', 'Минимум за 2 дня. В сезон свадеб популярные хончи быстро бронируют, поэтому советуем выбрать дату заранее.'], ['How far ahead should I order?', 'At least 2 days ahead. In wedding season the favourite khonchas are booked quickly, so choose your date early.']],
        'Ödənişi necə edirəm?' => ['Sifarişdən sonra sizinlə əlaqə saxlayıb təsdiqləyirik. Ödəniş çatdırılma zamanı nağd və ya kartla edilir.', ['Как оплатить?', 'После заказа мы связываемся с вами и подтверждаем его. Оплата при доставке, наличными или картой.'], ['How do I pay?', 'After you order we call to confirm. You pay on delivery, in cash or by card.']],
    ];

    /** reviewer name => [az text, [ru occasion, ru text], [en occasion, en text]] */
    public const REVIEWS = [
        'Aytən' => ['Xonçalar şəkildəkindən də gözəl idi. Saytda seçdiyimiz kimi gətirdilər, qonaqlar heyran qaldı.', ['Помолвка', 'Хончи были даже красивее, чем на фото. Привезли ровно то, что мы выбрали на сайте, гости были в восторге.'], ['Engagement', 'The khonchas were even lovelier than in the photos. They brought exactly what we chose on the site, and our guests were enchanted.']],
        'Rəşad' => ['Yeddi xonça sifariş etdik, vaxtında çatdırdılar və ertəsi gün özləri götürdülər. Çox rahat.', ['Свадьба', 'Заказали семь хонч, привезли вовремя и на следующий день сами забрали. Очень удобно.'], ['Wedding', 'We ordered seven khonchas. They arrived on time and were collected the next day. So easy.']],
        'Günel' => ['Şamlı xına qabı gecənin ən gözəl anı oldu. İçini özümüz seçdik, qiyməti də əlverişli idi.', ['Хынаяхды', 'Чаша с хной и свечами стала самым красивым моментом вечера. Наполнение выбрали сами, и цена приятная.'], ['Henna night', 'The henna bowl with candles was the most beautiful moment of the night. We chose the filling ourselves, and the price was fair.']],
        'Leyla' => ['Çay xonçası çox zövqlü idi. Qaynanam hələ də soruşur haradan almışıq.', ['Сватовство', 'Чайная хонча была очень изысканной. Свекровь до сих пор спрашивает, где мы её взяли.'], ['Betrothal', 'The tea khoncha was so elegant. My mother in law still asks where we found it.']],
        'Elvin' => ['Ürək formalı xonçanı həyat yoldaşıma hədiyyə etdim, göz yaşlarını saxlaya bilmədi.', ['День рождения', 'Подарил жене хончу в форме сердца, она не смогла сдержать слёз.'], ['Birthday', 'I gave my wife the heart shaped khoncha, and she could not hold back her tears.']],
        'Nigar' => ['Kəlağayı xonçası ənənəvi, amma müasir görünürdü. Hamıya tövsiyə edirəm.', ['Свадьба', 'Хонча с келагаи выглядела традиционно, но современно. Всем рекомендую.'], ['Wedding', 'The kalaghayi khoncha looked traditional yet modern. I recommend it to everyone.']],
    ];

    public static function apply(): void
    {
        $fresh = fn ($m) => empty($m->translations);

        foreach (Occasion::all() as $o) {
            if (isset(self::OCCASIONS[$o->slug]) && $fresh($o)) {
                [$ru, $en] = self::OCCASIONS[$o->slug];
                $o->update(['translations' => ['ru' => ['name' => $ru[0], 'tagline' => $ru[1]], 'en' => ['name' => $en[0], 'tagline' => $en[1]]]]);
            }
        }
        foreach (FillCategory::all() as $c) {
            if (isset(self::CATEGORIES[$c->name]) && $fresh($c)) {
                [$ru, $en] = self::CATEGORIES[$c->name];
                $c->update(['translations' => ['ru' => ['name' => $ru], 'en' => ['name' => $en]]]);
            }
        }
        foreach (FillItem::all() as $i) {
            if ($i->name === 'Qoz-fındıq') {
                $i->name = 'Qoz və fındıq';
            }
            if (isset(self::ITEMS[$i->name]) && $fresh($i)) {
                [$ru, $en] = self::ITEMS[$i->name];
                $i->translations = ['ru' => ['name' => $ru], 'en' => ['name' => $en]];
            }
            $i->save();
        }
        foreach (Xonca::all() as $x) {
            if (isset(self::XONCAS[$x->code]) && $fresh($x)) {
                [$az, $ru, $en] = self::XONCAS[$x->code];
                $x->update([
                    'description' => $az,
                    'short_description' => $az,
                    'translations' => [
                        'ru' => ['name' => $ru[0], 'short_description' => $ru[1], 'description' => $ru[1]],
                        'en' => ['name' => $en[0], 'short_description' => $en[1], 'description' => $en[1]],
                    ],
                ]);
            }
        }
        foreach (Faq::all() as $f) {
            if (isset(self::FAQS[$f->question]) && $fresh($f)) {
                [$az, $ru, $en] = self::FAQS[$f->question];
                $f->update(['answer' => $az, 'translations' => ['ru' => ['question' => $ru[0], 'answer' => $ru[1]], 'en' => ['question' => $en[0], 'answer' => $en[1]]]]);
            }
        }
        foreach (Review::all() as $r) {
            if (isset(self::REVIEWS[$r->name]) && $fresh($r)) {
                [$az, $ru, $en] = self::REVIEWS[$r->name];
                $r->update(['text' => $az, 'translations' => ['ru' => ['occasion' => $ru[0], 'text' => $ru[1]], 'en' => ['occasion' => $en[0], 'text' => $en[1]]]]);
            }
        }
    }
}
