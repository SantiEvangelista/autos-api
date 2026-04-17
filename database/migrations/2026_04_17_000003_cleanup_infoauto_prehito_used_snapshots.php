<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;


return new class extends Migration
{
    public function up(): void
    {
        DB::table('price_snapshots')
            ->where('source', 'infoauto')
            ->where('recorded_at', '<', '2026-04-17')
            ->where('year', '>', 0)
            ->delete();
    }

    public function down(): void
    {
    }
};
