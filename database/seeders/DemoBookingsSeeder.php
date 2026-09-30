<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Occasion;
use App\Models\Xonca;
use App\Support\Quote;
use Illuminate\Database\Seeder;

/**
 * Invented orders for trying the admin locally. Never run it on the live
 * site: `php artisan db:seed --class=DemoBookingsSeeder`.
 */
class DemoBookingsSeeder extends Seeder
{
    public function run(): void
    {
        $names = ['Aytən Məmmədova', 'Rəşad Əliyev', 'Günel Həsənova', 'Leyla Quliyeva', 'Elvin Kərimov', 'Nigar İsmayılova', 'Orxan Hüseynov', 'Səbinə Abbasova', 'Tural Nəsirov', 'Fidan Rzayeva', 'Kamran Bağırov', 'Aysel Cəfərova'];
        $districts = ['Nəsimi', 'Yasamal', 'Xətai', 'Nərimanov', 'Binəqədi', 'Səbail'];
        $statuses = ['returned', 'returned', 'returned', 'returned', 'delivered', 'confirmed', 'preparing', 'new', 'new', 'confirmed', 'cancelled', 'confirmed'];
        $xoncas = Xonca::pluck('id')->all();
        $occasions = Occasion::pluck('id')->all();

        foreach (range(0, 23) as $i) {
            $offset = $i < 14 ? -rand(3, 330) : rand(1, 40);
            $status = $offset < 0 ? ($i % 7 === 0 ? 'cancelled' : 'returned') : $statuses[$i % count($statuses)];
            if ($offset > 0 && $offset < 3) {
                $status = 'delivered';
            }

            $cart = collect(range(1, rand(1, 5)))->map(fn () => [
                'xonca_id' => $xoncas[array_rand($xoncas)],
                'fill_mode' => ['ours', 'ours', 'custom', 'empty'][rand(0, 3)],
            ])->all();
            $quote = Quote::make($cart, $i % 5 ? 'delivery' : 'pickup');

            $b = Booking::create([
                'customer_name' => $names[$i % count($names)],
                'phone' => '+994 5' . rand(0, 5) . ' ' . rand(200, 999) . ' ' . rand(10, 99) . ' ' . rand(10, 99),
                'occasion_id' => $occasions[array_rand($occasions)],
                'event_date' => now()->addDays($offset)->toDateString(),
                'event_time' => ['17:00', '18:00', '19:00', null][rand(0, 3)],
                'delivery_type' => $i % 5 ? 'delivery' : 'pickup',
                'district' => $i % 5 ? $districts[array_rand($districts)] : null,
                'address' => $i % 5 ? 'Nümunə küç. ' . rand(1, 120) : null,
                'xonca_total' => $quote->xoncaTotal,
                'fill_total' => $quote->fillTotal,
                'delivery_fee' => $quote->deliveryFee,
                'deposit_total' => $quote->depositTotal,
                'total' => $quote->total,
                'status' => $status,
                'deposit_returned' => $status === 'returned',
                'created_at' => now()->addDays(min($offset, 0) - rand(3, 20)),
            ]);
            foreach ($quote->lines as $line) {
                $b->items()->create(collect($line)->except('xonca')->all());
            }
        }
    }
}
