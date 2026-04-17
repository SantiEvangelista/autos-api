<?php

namespace App\Models;

use App\Services\VersionDisplayService;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Version extends Model
{
    protected $fillable = ['car_model_id', 'name', 'codia'];

    protected function displayName(): Attribute
    {
        return Attribute::get(fn () => VersionDisplayService::humanize($this->name));
    }

    public function carModel(): BelongsTo
    {
        return $this->belongsTo(CarModel::class);
    }

    public function valuations(): HasMany
    {
        return $this->hasMany(Valuation::class);
    }

    public function priceSnapshots(): HasMany
    {
        return $this->hasMany(PriceSnapshot::class);
    }
}
