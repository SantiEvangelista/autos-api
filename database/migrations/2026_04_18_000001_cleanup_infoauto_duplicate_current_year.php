<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Cleanup de duplicados en price_snapshots: filas source='infoauto' con year=current_year
 * que tienen un gemelo year=0 con identico raw_price_ars_thousands en la misma fecha.
 *
 * Contexto (Fix C, 2026-04-18):
 *   Cuando el max year de la grilla es el ano
 *   actual, ambas filas tienen el mismo raw -> la fila year=YYYY queda redundante.
 *
 * La fila year=0 se prioriza como canonica porque:
 *   - Matchea con valuations.year=0 (semantica CCA).
 *   - Es la que consume PriceResolverService y SearchController.
 *   - El year=YYYY literal es un artefacto del transporte, no aporta info adicional.
 *
 * No reversible (los datos son redundantes).
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement(<<<'SQL'
DELETE FROM price_snapshots ps1
WHERE ps1.source = 'infoauto'
  AND ps1.year > 0
  AND ps1.year = EXTRACT(YEAR FROM ps1.recorded_at)::int
  AND EXISTS (
      SELECT 1 FROM price_snapshots ps2
      WHERE ps2.version_id = ps1.version_id
        AND ps2.source = 'infoauto'
        AND ps2.year = 0
        AND ps2.recorded_at = ps1.recorded_at
        AND ps2.raw_price_ars_thousands IS NOT DISTINCT FROM ps1.raw_price_ars_thousands
  );
SQL);
    }

    public function down(): void
    {
        // No-op: datos redundantes, no se restauran.
    }
};
