<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FillCategory extends Model
{
    protected $guarded = [];

    public function items(): HasMany
    {
        return $this->hasMany(FillItem::class)->orderBy('sort')->orderBy('id');
    }
}
