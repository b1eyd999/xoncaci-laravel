<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Booking extends Model
{
    public const STATUSES = [
        'new' => 'Yeni',
        'confirmed' => 'Təsdiqləndi',
        'preparing' => 'Hazırlanır',
        'delivered' => 'Çatdırıldı',
        'returned' => 'Geri qaytarıldı',
        'cancelled' => 'Ləğv edildi',
    ];

    public const STATUS_COLORS = [
        'new' => 'warning',
        'confirmed' => 'info',
        'preparing' => 'primary',
        'delivered' => 'success',
        'returned' => 'gray',
        'cancelled' => 'danger',
    ];

    /** Statuses in which the trays are off the shelf. */
    public const HOLDING = ['new', 'confirmed', 'preparing', 'delivered'];

    protected $guarded = [];

    protected $casts = [
        'deposit_returned' => 'boolean',
        'total' => 'float',
        'xonca_total' => 'float',
        'fill_total' => 'float',
        'delivery_fee' => 'float',
        'deposit_total' => 'float',
    ];

    /**
     * Kept as a bare Y-m-d. Stored with a time ("2026-10-06 00:00:00") the
     * day would sort after the string "2026-10-06", and the availability
     * check — a whereBetween on dates — would miss the order on its last day.
     */
    protected function eventDate(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: fn ($value) => $value ? \Illuminate\Support\Carbon::parse($value)->startOfDay() : null,
            set: fn ($value) => $value ? \Illuminate\Support\Carbon::parse($value)->toDateString() : null,
        );
    }

    protected static function booted(): void
    {
        static::creating(function (Booking $booking) {
            if (! $booking->code) {
                do {
                    $code = 'XN-' . strtoupper(Str::random(6));
                } while (static::where('code', $code)->exists());
                $booking->code = $code;
            }
        });
    }

    public function items(): HasMany
    {
        return $this->hasMany(BookingItem::class);
    }

    public function occasion(): BelongsTo
    {
        return $this->belongsTo(Occasion::class);
    }

    public function getRouteKeyName(): string
    {
        return 'code';
    }

    /** Totals follow the lines; called after every save in the admin. */
    public function recalculate(): void
    {
        $this->load('items');
        foreach ($this->items as $item) {
            $fillTotal = collect($item->fill ?? [])->sum(fn ($f) => (float) ($f['price'] ?? 0) * (int) ($f['qty'] ?? 1));
            if ((float) $item->fill_total !== (float) $fillTotal) {
                $item->forceFill(['fill_total' => $fillTotal])->saveQuietly();
            }
        }
        $this->forceFill([
            'xonca_total' => $this->items->sum('price'),
            'fill_total' => $this->items->sum('fill_total'),
            'deposit_total' => $this->items->sum('deposit'),
        ]);
        $this->total = $this->xonca_total + $this->fill_total + (float) $this->delivery_fee;
        $this->saveQuietly();
    }

    public function daysLeft(): int
    {
        return (int) now()->startOfDay()->diffInDays($this->event_date, false);
    }

    public function whatsappUrl(): string
    {
        $digits = preg_replace('/\D+/', '', $this->phone);
        if (str_starts_with($digits, '0')) {
            $digits = '994' . substr($digits, 1);
        } elseif (strlen($digits) === 9) {
            $digits = '994' . $digits;
        }

        return 'https://wa.me/' . $digits;
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }
}
