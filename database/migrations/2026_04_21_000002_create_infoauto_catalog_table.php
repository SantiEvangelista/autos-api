<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('infoauto_catalog');

        Schema::create('infoauto_catalog', function (Blueprint $table) {
            $table->id();
            $table->string('source_system', 20);
            $table->string('codia', 128)->nullable();
            $table->integer('product_id')->nullable();
            $table->string('brand_name', 100);
            $table->integer('brand_id_source')->nullable();
            $table->string('model_name', 150);
            $table->integer('submodel_id_source')->nullable();
            $table->string('version_name_raw', 255);
            $table->string('version_name_public', 255)->nullable();
            $table->jsonb('years')->nullable();
            $table->timestamp('first_seen_at');
            $table->timestamp('last_seen_at');
            $table->date('discontinued_at')->nullable();
            $table->timestamps();

            $table->index(['brand_name', 'model_name'], 'idx_infoauto_catalog_brand_model');
        });

        DB::statement('CREATE UNIQUE INDEX uniq_infoauto_catalog_codia ON infoauto_catalog (codia) WHERE codia IS NOT NULL');
        DB::statement('CREATE UNIQUE INDEX uniq_infoauto_catalog_product_id ON infoauto_catalog (product_id) WHERE product_id IS NOT NULL');
        DB::statement('CREATE INDEX idx_infoauto_catalog_brand_trgm ON infoauto_catalog USING gin (brand_name gin_trgm_ops)');
        DB::statement('CREATE INDEX idx_infoauto_catalog_model_trgm ON infoauto_catalog USING gin (model_name gin_trgm_ops)');
        DB::statement('CREATE INDEX idx_infoauto_catalog_version_trgm ON infoauto_catalog USING gin (version_name_raw gin_trgm_ops)');
    }

    public function down(): void
    {
        Schema::dropIfExists('infoauto_catalog');
    }
};
