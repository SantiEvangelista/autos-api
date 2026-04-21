<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InfoautoPriceHistory extends Model
{
    protected $table = 'infoauto_price_history';

    protected $fillable = [
        'infoauto_catalog_id',
        'year',
        'price_ars_thousands',
        'price_usd',
        'exchange_rate',
        'origin',
        'source',
        'recorded_at',
        'source_file',
        'raw_payload',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'price_ars_thousands' => 'decimal:2',
            'price_usd' => 'decimal:2',
            'exchange_rate' => 'decimal:4',
            'recorded_at' => 'date',
            'raw_payload' => 'array',
        ];
    }

    public function catalog(): BelongsTo
    {
        return $this->belongsTo(InfoautoCatalog::class, 'infoauto_catalog_id');
    }
}
