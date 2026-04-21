<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InfoautoCatalog extends Model
{
    protected $table = 'infoauto_catalog';

    protected $fillable = [
        'source_system',
        'codia',
        'product_id',
        'brand_name',
        'brand_id_source',
        'model_name',
        'submodel_id_source',
        'version_name_raw',
        'version_name_public',
        'years',
        'first_seen_at',
        'last_seen_at',
        'discontinued_at',
    ];

    protected function casts(): array
    {
        return [
            'years' => 'array',
            'brand_id_source' => 'integer',
            'submodel_id_source' => 'integer',
            'product_id' => 'integer',
            'first_seen_at' => 'datetime',
            'last_seen_at' => 'datetime',
            'discontinued_at' => 'date',
        ];
    }

    public function getExternalIdAttribute(): string
    {
        return 'ia_' . $this->id;
    }

    public function priceHistory(): HasMany
    {
        return $this->hasMany(InfoautoPriceHistory::class, 'infoauto_catalog_id');
    }
}
