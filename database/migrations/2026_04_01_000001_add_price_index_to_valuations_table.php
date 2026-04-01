<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('valuations', function (Blueprint $table) {
            $table->index('price');
            $table->index(['price', 'year']);
        });
    }

    public function down(): void
    {
        Schema::table('valuations', function (Blueprint $table) {
            $table->dropIndex(['price']);
            $table->dropIndex(['price', 'year']);
        });
    }
};
