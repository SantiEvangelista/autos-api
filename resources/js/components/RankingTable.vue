<template>
  <section v-if="rankings" class="py-12 sm:py-16 lg:py-20">
    <p class="font-mono text-[10px] sm:text-xs text-cream/30 tracking-[0.2em] uppercase mb-2 sm:mb-3"></p>
    <h2 class="text-2xl sm:text-3xl lg:text-4xl font-light text-cream mb-1.5 sm:mb-2">Estadisticas del Mercado</h2>
    <p class="text-xs sm:text-sm text-cream/30 mb-6 sm:mb-10">Datos curiosos del mercado automotor argentino</p>

    <!-- Mobile: stacked cards -->
    <div class="md:hidden space-y-3">
      <div
        v-for="(item, i) in items"
        :key="item.key"
        class="rounded-lg bg-cream/[0.03] border border-cream/6 p-4 ranking-card"
        :style="{ animationDelay: `${i * 60}ms` }"
      >
        <div class="flex items-start justify-between gap-3">
          <div class="min-w-0 flex-1">
            <p class="text-[11px] text-cream/40 tracking-wide uppercase mb-1.5">{{ item.label }}</p>
            <p
              class="text-sm font-medium leading-snug"
              :class="item.colorClass || 'text-gold'"
            >
              {{ item.value }}
            </p>
          </div>
          <span class="font-mono text-[10px] text-cream/15 shrink-0 mt-0.5">#{{ i + 1 }}</span>
        </div>
      </div>
    </div>

    <!-- Desktop: table with alternating rows -->
    <div class="hidden md:block">
      <!-- Header -->
      <div class="grid grid-cols-[1fr_auto] gap-4 px-5 py-2.5 mb-1">
        <span class="font-mono text-[11px] text-cream/30 uppercase tracking-wider"></span>
        <span class="font-mono text-[11px] text-cream/30 uppercase tracking-wider text-right"></span>
      </div>

      <!-- Rows -->
      <div
        v-for="(item, i) in items"
        :key="item.key"
        class="grid grid-cols-[1fr_auto] gap-4 items-center px-5 py-3.5 rounded-lg ranking-row"
        :class="i % 2 === 0 ? 'bg-cream/[0.03]' : 'bg-cream/[0.06]'"
        :style="{ animationDelay: `${i * 50}ms` }"
      >
        <span class="text-sm text-cream/50">{{ item.label }}</span>
        <span
          class="text-sm font-medium text-right whitespace-nowrap"
          :class="item.colorClass || 'text-gold'"
        >
          {{ item.value }}
        </span>
      </div>
    </div>

    <!-- Meta footer -->
    <div v-if="rankings.meta?.current_date && rankings.meta?.previous_date" class="mt-5 sm:mt-6">
      <p class="font-mono text-[10px] sm:text-[11px] text-cream/20">
        Comparativa entre {{ formatDate(rankings.meta.previous_date) }} y {{ formatDate(rankings.meta.current_date) }}
      </p>
    </div>
  </section>
</template>

<script setup>
import { ref, computed } from 'vue'

const rankings = ref(window.__RANKINGS__ || null)

function fmt(price) {
  if (price == null) return '—'
  return Number(price).toLocaleString('es-AR', { minimumFractionDigits: 0, maximumFractionDigits: 0 })
}

function formatDate(dateStr) {
  if (!dateStr) return '—'
  const d = new Date(dateStr + 'T00:00:00')
  return d.toLocaleDateString('es-AR', { day: 'numeric', month: 'short', year: 'numeric' })
}

const items = computed(() => {
  if (!rankings.value) return []

  const r = rankings.value
  const pending = 'Disponible con el proximo mes de datos'
  return [
    {
      key: 'cheapest_0km',
      label: 'Auto 0km mas barato',
      value: r.cheapest_0km
        ? `${r.cheapest_0km.brand} ${r.cheapest_0km.model} — US$${fmt(r.cheapest_0km.price_usd)}`
        : 'Sin datos',
      colorClass: r.cheapest_0km ? null : 'text-cream/20',
    },
    {
      key: 'most_expensive_0km',
      label: 'Auto 0km mas caro',
      value: r.most_expensive_0km
        ? `${r.most_expensive_0km.brand} ${r.most_expensive_0km.model} — US$${fmt(r.most_expensive_0km.price_usd)}`
        : 'Sin datos',
      colorClass: r.most_expensive_0km ? null : 'text-cream/20',
    },
    {
      key: 'cheapest_overall',
      label: 'Auto mas barato del mercado',
      value: r.cheapest_overall
        ? `${r.cheapest_overall.brand} ${r.cheapest_overall.model} — US$${fmt(r.cheapest_overall.price_usd)} (${r.cheapest_overall.year})`
        : 'Sin datos',
      colorClass: r.cheapest_overall ? null : 'text-cream/20',
    },
    {
      key: 'most_expensive_overall',
      label: 'Auto mas caro del mercado',
      value: r.most_expensive_overall
        ? `${r.most_expensive_overall.brand} ${r.most_expensive_overall.model} — US$${fmt(r.most_expensive_overall.price_usd)} (${r.most_expensive_overall.year})`
        : 'Sin datos',
      colorClass: r.most_expensive_overall ? null : 'text-cream/20',
    },
    {
      key: 'brand_most_variety',
      label: 'Marca con mas variedad',
      value: r.brand_most_variety
        ? `${r.brand_most_variety.brand} (${r.brand_most_variety.models_count} modelos, ${r.brand_most_variety.versions_count} versiones)`
        : 'Sin datos',
      colorClass: r.brand_most_variety ? null : 'text-cream/20',
    },
    {
      key: 'biggest_price_increase',
      label: 'Auto que mas subio de precio',
      value: r.biggest_price_increase
        ? `${r.biggest_price_increase.brand} ${r.biggest_price_increase.model}  \u00b7  +${r.biggest_price_increase.diff_pct}%`
        : pending,
      colorClass: r.biggest_price_increase ? 'text-red-400' : 'text-cream/20',
    },
    {
      key: 'biggest_price_decrease',
      label: 'Auto que mas bajo de precio',
      value: r.biggest_price_decrease
        ? `${r.biggest_price_decrease.brand} ${r.biggest_price_decrease.model}  \u00b7  ${r.biggest_price_decrease.diff_pct}%`
        : pending,
      colorClass: r.biggest_price_decrease ? 'text-emerald-400' : 'text-cream/20',
    },
    {
      key: 'most_aggressive_depreciation',
      label: 'Mayor depreciacion (0km vs 1 año)',
      value: r.most_aggressive_depreciation
        ? `${r.most_aggressive_depreciation.brand} ${r.most_aggressive_depreciation.model}  \u00b7  ${r.most_aggressive_depreciation.diff_pct}%`
        : 'Sin datos',
      colorClass: r.most_aggressive_depreciation ? null : 'text-cream/20',
    },
    {
      key: 'least_depreciation',
      label: 'Menor depreciacion (0km vs 1 año)',
      value: r.least_depreciation
        ? `${r.least_depreciation.brand} ${r.least_depreciation.model}  \u00b7  ${r.least_depreciation.diff_pct}%`
        : 'Sin datos',
      colorClass: r.most_aggressive_depreciation ? null : 'text-cream/20',
    },
    {
      key: 'average_0km_price',
      label: 'Precio promedio 0km',
      value: r.average_0km_price
        ? `US$${fmt(r.average_0km_price.usd)}${r.average_0km_price.ars ? `  |  ARS $${fmt(r.average_0km_price.ars)}` : ''}`
        : 'Sin datos',
      colorClass: r.average_0km_price ? null : 'text-cream/20',
    },
    {
      key: 'most_expensive_brand_avg',
      label: 'Marca mas cara en promedio',
      value: r.most_expensive_brand_avg
        ? `${r.most_expensive_brand_avg.brand} — US$${fmt(r.most_expensive_brand_avg.avg_price_usd)}`
        : 'Sin datos',
      colorClass: r.most_expensive_brand_avg ? null : 'text-cream/20',
    },
    {
      key: 'most_affordable_brand_avg',
      label: 'Marca mas accesible en promedio',
      value: r.most_affordable_brand_avg
        ? `${r.most_affordable_brand_avg.brand} — US$${fmt(r.most_affordable_brand_avg.avg_price_usd)}`
        : 'Sin datos',
      colorClass: r.most_affordable_brand_avg ? null : 'text-cream/20',
    },
    {
      key: 'biggest_mom_drop',
      label: 'Mayor baja vs mes anterior',
      value: r.biggest_mom_drop
        ? `${r.biggest_mom_drop.brand} ${r.biggest_mom_drop.model}  \u00b7  ${r.biggest_mom_drop.diff_pct}%`
        : pending,
      colorClass: r.biggest_mom_drop ? 'text-emerald-400' : 'text-cream/20',
    },
    {
      key: 'market_price_change_pct',
      label: 'Variacion de precios del mercado',
      value: r.market_price_change_pct
        ? `${r.market_price_change_pct.pct > 0 ? '+' : ''}${r.market_price_change_pct.pct}%`
        : pending,
      colorClass: r.market_price_change_pct
        ? (r.market_price_change_pct.direction === 'down' ? 'text-emerald-400' : 'text-red-400')
        : 'text-cream/20',
    },
  ]
})
</script>

<style scoped>
.ranking-card {
  animation: rankingFadeUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) both;
}

.ranking-row {
  animation: rankingFadeIn 0.4s cubic-bezier(0.16, 1, 0.3, 1) both;
}

@keyframes rankingFadeUp {
  from {
    opacity: 0;
    transform: translateY(12px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes rankingFadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}
</style>
