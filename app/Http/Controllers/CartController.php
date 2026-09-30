<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Occasion;
use App\Models\Setting;
use App\Support\Availability;
use App\Support\Quote;
use App\Support\Telegram;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CartController extends Controller
{
    public function page()
    {
        return view('site.cart', [
            'items' => SiteController::itemCatalog(),
            'occasions' => Occasion::active()->get(),
            'districts' => Setting::districts(),
            'earliest' => Availability::earliest()->toDateString(),
        ]);
    }

    /** The browser asks what its cart costs; the answer is always the server's. */
    public function quote(Request $request)
    {
        $cart = $this->cart($request);
        if (! $cart) {
            return ['lines' => [], 'total' => 0];
        }
        $quote = Quote::make($cart, $request->input('delivery_type', 'delivery'));
        $date = $request->input('event_date');

        return [
            'lines' => collect($quote->lines)->map(fn ($l) => [
                'xonca_id' => $l['xonca_id'],
                'price' => $l['price'],
                'fill_total' => $l['fill_total'],
                'deposit' => $l['deposit'],
                'free' => $date ? Availability::free($l['xonca'], $date) : null,
            ]),
            'xonca_total' => $quote->xoncaTotal,
            'fill_total' => $quote->fillTotal,
            'delivery_fee' => $quote->deliveryFee,
            'deposit_total' => $quote->depositTotal,
            'total' => $quote->total,
        ];
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_name' => 'required|string|max:120',
            'phone' => ['required', 'string', 'max:30', 'regex:/^[0-9+()\s-]{7,}$/'],
            'email' => 'nullable|email|max:120',
            'occasion_id' => 'nullable|exists:occasions,id',
            'event_date' => 'required|date|after_or_equal:' . Availability::earliest()->toDateString(),
            'event_time' => 'nullable|string|max:20',
            'delivery_type' => 'required|in:delivery,pickup',
            'district' => 'nullable|required_if:delivery_type,delivery|string|max:80',
            'address' => 'nullable|required_if:delivery_type,delivery|string|max:255',
            'note' => 'nullable|string|max:1000',
        ], [
            'event_date.after_or_equal' => 'Bu tarixə artıq çatdıra bilmirik — ən tezi ' . Availability::earliest()->format('d.m.Y') . '.',
            'phone.regex' => 'Telefon nömrəsini düzgün yazın.',
            'district.required_if' => 'Rayonu seçin.',
            'address.required_if' => 'Ünvanı yazın.',
        ]);

        $cart = $this->cart($request);
        if (! $cart) {
            throw ValidationException::withMessages(['cart' => 'Səbət boşdur.']);
        }

        $booking = DB::transaction(function () use ($data, $cart) {
            $quote = Quote::make($cart, $data['delivery_type']);

            // Checked inside the transaction so two customers cannot take the
            // last tray of a day at the same moment.
            $wanted = collect($quote->lines)->groupBy('xonca_id');
            foreach ($wanted as $lines) {
                $xonca = $lines->first()['xonca'];
                $free = Availability::free($xonca, $data['event_date']);
                if ($free < $lines->count()) {
                    throw ValidationException::withMessages(['cart' => '«' . $xonca->name . '» bu tarixə ' . ($free ? "yalnız {$free} ədəd" : 'artıq') . ' boşdur. Başqa tarix və ya başqa xonça seçin.']);
                }
            }

            $booking = Booking::create($data + [
                'xonca_total' => $quote->xoncaTotal,
                'fill_total' => $quote->fillTotal,
                'delivery_fee' => $quote->deliveryFee,
                'deposit_total' => $quote->depositTotal,
                'total' => $quote->total,
                'status' => 'new',
            ]);

            foreach ($quote->lines as $line) {
                $booking->items()->create([
                    'xonca_id' => $line['xonca_id'],
                    'xonca_name' => $line['xonca_name'],
                    'fill_mode' => $line['fill_mode'],
                    'ribbon_color' => $line['ribbon_color'],
                    'price' => $line['price'],
                    'deposit' => $line['deposit'],
                    'fill' => $line['fill'],
                    'fill_total' => $line['fill_total'],
                ]);
            }

            return $booking;
        });

        Telegram::booking($booking);

        return response()->json(['redirect' => route('booking.done', $booking)]);
    }

    public function done(Booking $booking)
    {
        return view('site.done', ['booking' => $booking->load('items', 'occasion'), 'items' => SiteController::itemCatalog()]);
    }

    /** @return list<array<string, mixed>> */
    private function cart(Request $request): array
    {
        $cart = $request->input('cart', []);
        if (is_string($cart)) {
            $cart = json_decode($cart, true) ?: [];
        }

        return array_slice(array_values(array_filter((array) $cart, 'is_array')), 0, 30);
    }
}
