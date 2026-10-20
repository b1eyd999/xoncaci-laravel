<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\FillItem;
use App\Models\Setting;
use App\Models\Xonca;
use App\Support\Availability;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Setting::put('delivery_fee', '15');
        Setting::put('free_delivery_from', '0');
        Setting::put('min_days_ahead', '2');
        Setting::put('days_before', '1');
        Setting::put('days_after', '1');
    }

    private function order(array $cart, array $extra = [])
    {
        return $this->postJson('/sifaris', $extra + [
            'customer_name' => 'Test Müştəri',
            'phone' => '+994 50 123 45 67',
            'event_date' => now()->addDays(10)->toDateString(),
            'delivery_type' => 'delivery',
            'district' => 'Nəsimi',
            'address' => 'Test küç. 1',
            'cart' => $cart,
        ]);
    }

    public function test_pages_open(): void
    {
        $x = Xonca::first();
        $this->get('/')->assertOk()->assertSee($x->name);
        $this->get('/xoncalar')->assertOk();
        $this->get('/xoncalar/toy')->assertOk();
        $this->get('/xonca/' . $x->slug)->assertOk()->assertSee('Lentin rəngi', false);
        $this->get('/sebet')->assertOk();
    }

    public function test_three_languages(): void
    {
        $x = Xonca::where('code', 'XN-001')->first();
        $this->get('/?lang=ru')->assertOk()->assertSee('Золотой дворец')->assertSee('Коллекция')->assertCookie('lang', 'ru', false);
        $this->get('/xonca/' . $x->slug . '?lang=en')->assertOk()->assertSee('Golden Palace')->assertSee('Ribbon colour');
        $this->get('/xoncalar')->assertOk()->assertSee('Kolleksiya');
        $this->withUnencryptedCookie('lang', 'ru')->get('/xoncalar')->assertOk()->assertSee('Коллекция');
    }

    public function test_no_dashes_on_the_site(): void
    {
        $x = Xonca::first();
        foreach (['az', 'ru', 'en'] as $lang) {
            foreach (['/', '/xoncalar', '/xonca/' . $x->slug, '/sebet'] as $url) {
                $html = $this->get($url . '?lang=' . $lang)->assertOk()->getContent();
                $text = preg_replace('/\s+/u', ' ', strip_tags(preg_replace('#<(script|style).*?</>#is', '', $html)));
                $this->assertDoesNotMatchRegularExpression('/[—–]/u', $text, "dash on {$url} ({$lang})");
            }
        }
    }

    public function test_owner_uploads_a_360_view_in_batches(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        $x = Xonca::first();
        $this->actingAs(\App\Models\User::first());
        $frame = fn ($i) => \Illuminate\Http\UploadedFile::fake()->image("{$i}.jpg", 40, 50);
        for ($i = 0; $i < 12; $i += 4) {
            $this->post("/admin/xonca-spin/{$x->id}", [
                'token' => 'abcdefgh12', 'start' => $i,
                'frames' => [$frame($i), $frame($i + 1), $frame($i + 2), $frame($i + 3)],
                'finish' => $i === 8 ? '1' : null,
            ], ['Accept' => 'application/json'])->assertOk();
        }
        $this->assertCount(12, $x->fresh()->spin);
        $this->get('/xonca/' . $x->slug)->assertOk()->assertSee('data-spin', false)->assertSee('360°');

        auth()->logout();
        $this->post("/admin/xonca-spin/{$x->id}", ['token' => 'zzzzzzzz12', 'start' => 0], ['Accept' => 'application/json'])->assertUnauthorized();
    }

    public function test_prices_come_from_the_server_not_the_browser(): void
    {
        $x = Xonca::first();
        $item = FillItem::where('price', '>', 0)->first();

        $this->order([[
            'xonca_id' => $x->id, 'fill_mode' => 'custom', 'price' => 1,
            'fill' => [['id' => $item->id, 'qty' => 2, 'price' => 0]],
        ]])->assertOk()->assertJsonStructure(['redirect']);

        $b = Booking::latest('id')->first();
        $this->assertEquals($x->price, $b->xonca_total);
        $this->assertEquals($item->price * 2, $b->fill_total);
        $this->assertEquals($x->price + $item->price * 2 + 15, $b->total);
        $this->assertSame('new', $b->status);
    }

    public function test_empty_mode_is_cheaper_and_carries_nothing(): void
    {
        $x = Xonca::where('empty_discount', '>', 0)->first();
        $this->order([['xonca_id' => $x->id, 'fill_mode' => 'empty']], ['delivery_type' => 'pickup', 'district' => null, 'address' => null])->assertOk();
        $b = Booking::latest('id')->first();
        $this->assertEquals($x->price - $x->empty_discount, $b->total);
        $this->assertSame([], $b->items->first()->fill);
    }

    public function test_a_fully_booked_xonca_cannot_be_booked_again_nearby(): void
    {
        $x = Xonca::first();
        $x->update(['stock' => 1]);
        $day = now()->addDays(10)->toDateString();

        $this->order([['xonca_id' => $x->id]], ['event_date' => $day])->assertOk();

        // The same day, and the days its rental window touches, are taken.
        foreach ([0, 1, 2, -2] as $shift) {
            $this->assertSame(0, Availability::free($x, now()->addDays(10 + $shift)->toDateString()), "shift {$shift}");
        }
        $this->assertSame(1, Availability::free($x, now()->addDays(13)->toDateString()));

        $this->order([['xonca_id' => $x->id]], ['event_date' => now()->addDays(12)->toDateString()])
            ->assertStatus(422)->assertJsonValidationErrors('cart');
    }

    public function test_a_cancelled_order_frees_the_xonca(): void
    {
        $x = Xonca::first();
        $x->update(['stock' => 1]);
        $this->order([['xonca_id' => $x->id]])->assertOk();
        Booking::latest('id')->first()->update(['status' => 'cancelled']);
        $this->assertSame(1, Availability::free($x, now()->addDays(10)->toDateString()));
    }

    public function test_too_soon_dates_and_missing_fields_are_refused(): void
    {
        $x = Xonca::first();
        $this->order([['xonca_id' => $x->id]], ['event_date' => now()->toDateString()])->assertStatus(422)->assertJsonValidationErrors('event_date');
        $this->order([['xonca_id' => $x->id]], ['address' => ''])->assertStatus(422)->assertJsonValidationErrors('address');
        $this->order([])->assertStatus(422)->assertJsonValidationErrors('cart');
    }

    public function test_admin_pages_open(): void
    {
        $this->seed(\Database\Seeders\DemoBookingsSeeder::class);
        $user = \App\Models\User::first();
        $b = Booking::first();
        $this->actingAs($user);
        foreach (['/admin', '/admin/bookings', '/admin/bookings/create', "/admin/bookings/{$b->code}", "/admin/bookings/{$b->code}/edit",
            "/admin/bookings/{$b->code}/print", '/admin/calendar', '/admin/calendar?view_mode=stock', '/admin/xoncas', '/admin/xoncas/create',
            '/admin/xoncas/' . Xonca::first()->slug . '/edit', '/admin/fill-items', '/admin/fill-items/' . FillItem::first()->id . '/edit',
            '/admin/fill-categories', '/admin/occasions', '/admin/reviews', '/admin/faqs', '/admin/users', '/admin/settings'] as $url) {
            $status = $this->get($url)->status();
            if ($status !== 200) {
                $failed[] = "{$url} → {$status}";
            }
        }
        $this->assertSame([], $failed ?? []);
    }
}
