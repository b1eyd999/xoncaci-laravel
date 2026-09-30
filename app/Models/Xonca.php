<?php

namespace App\Models;

use App\Support\Availability;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Xonca extends Model
{
    public const SHAPES = [
        'round' => 'Dairəvi',
        'oval' => 'Oval',
        'rect' => 'Düzbucaqlı',
        'octagon' => 'Səkkizbucaqlı',
        'heart' => 'Ürək',
    ];

    public const COVERS = [
        'tulle' => 'Tül örtük',
        'glass' => 'Şüşə gümbəz',
        'none' => 'Örtüksüz',
    ];

    public const STYLES = [
        'klassik' => 'Klassik',
        'modern' => 'Modern',
        'lux' => 'Lüks',
    ];

    /** The ribbon palette customers choose from unless a xonça has its own. */
    public const RIBBONS = ['#6e1a26', '#c2a064', '#e6c7c2', '#f4eee4', '#1f4a3d', '#1c2340'];

    protected $guarded = [];

    protected $casts = [
        'images' => 'array',
        'ribbon_choices' => 'array',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'fill_allowed' => 'boolean',
        'price' => 'float',
        'deposit' => 'float',
        'empty_discount' => 'float',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function occasions(): BelongsToMany
    {
        return $this->belongsToMany(Occasion::class);
    }

    public function defaultFills(): HasMany
    {
        return $this->hasMany(XoncaDefaultFill::class);
    }

    public function bookingItems(): HasMany
    {
        return $this->hasMany(BookingItem::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort')->orderBy('id');
    }

    /** @return list<string> */
    public function imageUrls(): array
    {
        return collect($this->images ?? [])->map(fn ($p) => Storage::url($p))->values()->all();
    }

    /** @return list<string> */
    public function ribbonChoices(): array
    {
        $choices = array_values(array_filter($this->ribbon_choices ?? []));

        return $choices ?: array_values(array_unique([$this->ribbon_color, ...self::RIBBONS]));
    }

    public function defaultFillTotal(): float
    {
        $this->loadMissing('defaultFills.item');

        return (float) $this->defaultFills->sum(fn ($f) => ($f->item?->price ?? 0) * $f->qty);
    }

    /** The tray, its cover and what it carries by default — for the 3D viewer. */
    public function toViewer(): array
    {
        $this->loadMissing('defaultFills.item');

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'price' => (float) $this->price,
            'deposit' => (float) $this->deposit,
            'emptyDiscount' => (float) $this->empty_discount,
            'fillAllowed' => (bool) $this->fill_allowed,
            'shape' => $this->shape,
            'size' => (int) $this->size_cm,
            'trayColor' => $this->tray_color,
            'rimColor' => $this->rim_color,
            'coverType' => $this->cover_type,
            'coverColor' => $this->cover_color,
            'ribbonColor' => $this->ribbon_color,
            'ribbonChoices' => $this->ribbonChoices(),
            'fill' => $this->defaultFills
                ->filter(fn ($f) => $f->item && $f->item->is_active)
                ->map(fn ($f) => ['id' => $f->fill_item_id, 'qty' => (int) $f->qty])
                ->values()->all(),
        ];
    }

    public function availableOn(string $date): int
    {
        return Availability::free($this, $date);
    }
}
