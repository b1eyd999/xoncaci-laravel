<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class XoncaDefaultFill extends Model
{
    public $timestamps = false;

    protected $guarded = [];

    public function xonca(): BelongsTo
    {
        return $this->belongsTo(Xonca::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(FillItem::class, 'fill_item_id');
    }
}
