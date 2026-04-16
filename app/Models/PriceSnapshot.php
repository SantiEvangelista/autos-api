<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PriceSnapshot extends Model
{
    protected $fillable = ['version_id', 'year', 'price', 'raw_price_ars_thousands', 'source', 'confidence', 'prediction_rule', 'recorded_at'];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'price' => 'decimal:2',
            'raw_price_ars_thousands' => 'decimal:2',
            'recorded_at' => 'date',
        ];
    }

    public function version(): BelongsTo
    {
        return $this->belongsTo(Version::class);
    }
}
