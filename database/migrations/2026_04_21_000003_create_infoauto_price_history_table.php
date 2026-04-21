<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('infoauto_price_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('infoauto_catalog_id')
                ->constrained('infoauto_catalog')
                ->cascadeOnDelete();
            $table->smallInteger('year');
            $table->decimal('price_ars_thousands', 12, 2);
            $table->decimal('price_usd', 12, 2)->nullable();
            $table->decimal('exchange_rate', 10, 4)->nullable();
            $table->string('origin', 20);
            $table->string('source', 20);
            $table->date('recorded_at');
            $table->string('source_file', 255)->nullable();
            $table->jsonb('raw_payload')->nullable();
            $table->timestamps();

            $table->unique(
                ['infoauto_catalog_id', 'year', 'source', 'recorded_at'],
                'uniq_infoauto_price_history_catalog_year_source_date'
            );
            $table->index(['infoauto_catalog_id', 'year'], 'idx_infoauto_price_history_catalog_year');
            $table->index('recorded_at', 'idx_infoauto_price_history_recorded_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('infoauto_price_history');
    }
};
