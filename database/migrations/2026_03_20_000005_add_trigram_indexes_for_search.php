<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('CREATE EXTENSION IF NOT EXISTS pg_trgm');

        DB::statement('CREATE INDEX idx_versions_name_trgm ON versions USING gin (name gin_trgm_ops)');
        DB::statement('CREATE INDEX idx_car_models_name_trgm ON car_models USING gin (name gin_trgm_ops)');
        DB::statement('CREATE INDEX idx_brands_name_trgm ON brands USING gin (name gin_trgm_ops)');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS idx_versions_name_trgm');
        DB::statement('DROP INDEX IF EXISTS idx_car_models_name_trgm');
        DB::statement('DROP INDEX IF EXISTS idx_brands_name_trgm');
    }
};
