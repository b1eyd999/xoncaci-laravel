<?php

namespace App\Models;

use App\Models\Concerns\Translatable;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use Translatable;

    protected $guarded = [];

    protected $casts = ['is_active' => 'boolean'];
}
