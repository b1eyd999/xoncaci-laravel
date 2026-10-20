<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingItem extends Model
{
    public const FILL_MODES = [
        'ours' => 'Ustalarımızın dolduruşu',
        'custom' => 'Öz seçimi',
        'empty' => 'Boş xonça',
    ];

    protected $guarded = [];

    protected $casts = ['fill' => 'array', 'price' => 'float', 'fill_total' => 'float', 'deposit' => 'float'];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function xonca(): BelongsTo
    {
        return $this->belongsTo(Xonca::class);
    }

    public function fillSummary(): string
    {
        return collect($this->fill ?? [])
            ->map(fn ($f) => $f['name'] . ' × ' . $f['qty'])
            ->implode(', ');
    }
}
