<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('source_version_links', function (Blueprint $table) {
            $table->id();
            $table->string('source_family', 20); // 'infoauto' | 'acara' (namespace para extensión futura)
            $table->string('source_system', 20);
            $table->string('external_id', 32);   // 'ia_<id>' cuando source_family='infoauto'
            $table->foreignId('version_id')
                ->constrained('versions')
                ->cascadeOnDelete();
            $table->string('status', 20);        // 'suggested' | 'validated' | 'rejected'
            $table->string('confidence', 10);    // 'high' | 'medium' | 'low'
            $table->decimal('score', 5, 2)->nullable();
            $table->text('match_reason')->nullable();
            $table->timestamp('validated_at')->nullable();
            $table->timestamps();

            $table->unique(
                ['source_family', 'source_system', 'external_id', 'version_id'],
                'uniq_source_version_links_identity'
            );
            $table->index('version_id', 'idx_source_version_links_version_id');
            $table->index(['source_family', 'source_system', 'external_id'], 'idx_source_version_links_external');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('source_version_links');
    }
};
