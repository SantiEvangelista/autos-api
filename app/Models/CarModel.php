<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CarModel extends Model
{
    protected $fillable = ['brand_id', 'name', 'slug'];

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function versions(): HasMany
    {
        return $this->hasMany(Version::class);
    }
}
