<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('price_snapshots', function (Blueprint $table) {
            $table->string('confidence', 10)->nullable()->after('source');
            $table->string('prediction_rule', 100)->nullable()->after('confidence');
        });
    }

    public function down(): void
    {
        Schema::table('price_snapshots', function (Blueprint $table) {
            $table->dropColumn(['confidence', 'prediction_rule']);
        });
    }
};
