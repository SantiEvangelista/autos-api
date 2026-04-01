<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('price_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('version_id')->constrained()->cascadeOnDelete();
            $table->smallInteger('year');
            $table->decimal('price', 12, 2);
            $table->string('source', 20)->default('infoauto');
            $table->date('recorded_at');
            $table->timestamps();

            $table->unique(['version_id', 'year', 'source', 'recorded_at']);
            $table->index('source');
            $table->index('recorded_at');
            $table->index(['version_id', 'year', 'source']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('price_snapshots');
    }
};
