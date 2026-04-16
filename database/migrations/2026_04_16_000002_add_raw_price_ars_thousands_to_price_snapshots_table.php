<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('price_snapshots', function (Blueprint $table) {
            $table->decimal('raw_price_ars_thousands', 12, 2)
                ->nullable()
                ->after('price');
        });
    }

    public function down(): void
    {
        Schema::table('price_snapshots', function (Blueprint $table) {
            $table->dropColumn('raw_price_ars_thousands');
        });
    }
};
