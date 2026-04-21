<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SourceVersionLink extends Model
{
    protected $table = 'source_version_links';

    protected $fillable = [
        'source_family',
        'source_system',
        'external_id',
        'version_id',
        'status',
        'confidence',
        'score',
        'match_reason',
        'validated_at',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'decimal:2',
            'validated_at' => 'datetime',
        ];
    }

    public function version(): BelongsTo
    {
        return $this->belongsTo(Version::class);
    }
}
