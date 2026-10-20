<?php

namespace App\Models;

use App\Models\Concerns\Translatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class FillItem extends Model
{
    use Translatable;

    /** The line drawings an item can be shown with (App\Support\Icons). */
    public const MODELS = [
        'kelle_qend' => 'Kəllə qənd',
        'candle' => 'Şam',
        'candles3' => 'Üç şam',
        'pakhlava' => 'Paxlava',
        'shekerbura' => 'Şəkərbura',
        'sweets_bowl' => 'Konfet qabı',
        'gift_box' => 'Hədiyyə qutusu',
        'ring_box' => 'Üzük qutusu',
        'henna_bowl' => 'Xına qabı',
        'mirror' => 'Güzgü',
        'perfume' => 'Ətir',
        'fabric' => 'Parça (bükülü)',
        'shawl' => 'Kəlağayı / şal',
        'apple' => 'Alma',
        'pomegranate' => 'Nar',
        'orange' => 'Portağal',
        'grapes' => 'Üzüm',
        'nuts_bowl' => 'Qoz-fındıq qabı',
        'flowers' => 'Gül dəstəsi',
        'cake' => 'Tort',
        'chocolate' => 'Şokolad qutusu',
        'necklace' => 'Boyunbağı',
        'shoes' => 'Ayaqqabı qutusu',
        'tea' => 'Çay qutusu',
    ];

    protected $guarded = [];

    protected $casts = ['is_active' => 'boolean', 'price' => 'float', 'scale' => 'float'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(FillCategory::class, 'fill_category_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort')->orderBy('id');
    }

    public function imageUrl(): ?string
    {
        return $this->image ? Storage::disk('public')->url($this->image) : null;
    }

    /** What the 3D viewer and the builder need about this item. */
    public function toViewer(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->tr('name'),
            'price' => (float) $this->price,
            'model' => $this->model,
            'color' => $this->color,
            'color2' => $this->color2,
            'scale' => (float) $this->scale,
            'max' => (int) $this->max_qty,
            'category' => $this->fill_category_id,
            'image' => $this->imageUrl(),
            'description' => $this->tr('description'),
            'icon' => \App\Support\Icons::svg($this->model, 'size-6'),
        ];
    }
}
