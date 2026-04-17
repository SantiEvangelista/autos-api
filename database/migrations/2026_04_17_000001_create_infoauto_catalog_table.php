<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('infoauto_catalog', function (Blueprint $table) {
            $table->id();
            $table->string('codia', 100)->unique();
            $table->string('brand_name', 100);
            $table->unsignedInteger('brand_id_autazo');
            $table->string('model_name', 150);
            $table->unsignedInteger('model_id_autazo');
            $table->string('version_name', 255);
            $table->json('years');
            $table->timestamps();

            $table->index(['brand_name', 'model_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('infoauto_catalog');
    }
};
