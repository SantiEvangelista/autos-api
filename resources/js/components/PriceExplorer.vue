<template>
  <section class="py-12 sm:py-16 lg:py-20">
    <h2 class="text-2xl sm:text-3xl lg:text-4xl font-light text-cream mb-2 sm:mb-3">Qué puedo comprar con...</h2>
    <p class="text-xs sm:text-sm text-cream/30 mb-8 sm:mb-10">Deslizá el control para ver qué vehículos entran en tu presupuesto.</p>

    <!-- Budget display -->
    <div class="text-center mb-6 sm:mb-8">
      <span class="text-3xl sm:text-4xl lg:text-5xl font-light text-gold">US$ {{ formatPrice(budget) }}</span>
    </div>

    <!-- Range slider -->
    <div class="mb-6 sm:mb-8 px-1">
      <input
        type="range"
        v-model.number="budget"
        @input="onBudgetChange"
        min="0"
        max="200000"
        step="1000"
        class="price-slider w-full h-2 rounded-lg appearance-none cursor-pointer bg-cream/10"
      />
      <div class="flex justify-between mt-2">
        <span class="font-mono text-[10px] sm:text-xs text-cream/25">US$ 0</span>
        <span class="font-mono text-[10px] sm:text-xs text-cream/25">US$ 200.000</span>
      </div>
    </div>

    <!-- Filters -->
    <div class="grid grid-cols-2 gap-x-4 gap-y-3 sm:flex sm:items-center sm:gap-4 mb-6 sm:mb-8">

      <!-- Year dropdown -->
      <div class="relative flex items-center">
        <span class="font-mono text-[10px] sm:text-xs text-cream/30 w-20 sm:w-auto shrink-0">Año:</span>
        <button
          @click="toggleDropdown('year')"
          class="font-mono text-[10px] sm:text-xs px-3 py-1 rounded border transition-colors inline-flex items-center gap-1.5 cursor-pointer"
          :class="budgetYear !== null ? 'bg-gold/90 text-navy-deep border-gold/90' : 'border-cream/15 text-cream/50 hover:border-cream/30 hover:text-cream/70'"
        >
          {{ budgetYear === null ? 'Todos' : (budgetYear === 0 ? '0km' : budgetYear) }}
          <svg class="w-3 h-3 transition-transform" :class="openDropdown === 'year' ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
        </button>
        <div
          v-if="openDropdown === 'year'"
          class="absolute left-0 top-full mt-1 z-20 w-36 max-h-52 overflow-y-auto rounded-lg bg-[#0d1426] border border-cream/10 shadow-xl"
        >
          <button
            v-for="y in availableYears"
            :key="y ?? 'all'"
            @click="budgetYear = y; fetchByPrice(); closeDropdown()"
            class="w-full text-left px-3 py-2 font-mono text-xs transition-colors"
            :class="budgetYear === y ? 'bg-gold/15 text-gold' : 'text-cream/50 hover:bg-cream/[0.06] hover:text-cream/70'"
          >{{ y === null ? 'Todos' : (y === 0 ? '0km' : y) }}</button>
        </div>
      </div>

      <!-- Body type dropdown -->
      <div v-if="availableBodyTypes.length" class="relative flex items-center">
        <span class="font-mono text-[10px] sm:text-xs text-cream/30 w-20 sm:w-auto shrink-0">Tipo:</span>
        <button
          @click="toggleDropdown('bodyType')"
          class="font-mono text-[10px] sm:text-xs px-3 py-1 rounded border transition-colors inline-flex items-center gap-1.5 cursor-pointer"
          :class="selectedBodyType ? 'bg-gold/90 text-navy-deep border-gold/90' : 'border-cream/15 text-cream/50 hover:border-cream/30 hover:text-cream/70'"
        >
          {{ selectedBodyType ? availableBodyTypes.find(bt => bt.value === selectedBodyType)?.label : 'Todos' }}
          <svg class="w-3 h-3 transition-transform" :class="openDropdown === 'bodyType' ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
        </button>
        <div
          v-if="openDropdown === 'bodyType'"
          class="absolute left-0 top-full mt-1 z-20 w-40 max-h-52 overflow-y-auto rounded-lg bg-[#0d1426] border border-cream/10 shadow-xl"
        >
          <button
            @click="setBodyType(null); closeDropdown()"
            class="w-full text-left px-3 py-2 font-mono text-xs transition-colors"
            :class="selectedBodyType === null ? 'bg-gold/15 text-gold' : 'text-cream/50 hover:bg-cream/[0.06] hover:text-cream/70'"
          >Todos</button>
          <button
            v-for="bt in availableBodyTypes"
            :key="bt.value"
            @click="setBodyType(bt.value); closeDropdown()"
            class="w-full text-left px-3 py-2 font-mono text-xs transition-colors"
            :class="selectedBodyType === bt.value ? 'bg-gold/15 text-gold' : 'text-cream/50 hover:bg-cream/[0.06] hover:text-cream/70'"
          >{{ bt.label }}</button>
        </div>
      </div>

      <!-- Fuel dropdown -->
      <div v-if="availableFuels.length > 1" class="relative flex items-center">
        <span class="font-mono text-[10px] sm:text-xs text-cream/30 w-20 sm:w-auto shrink-0">Combustible:</span>
        <button
          @click="toggleDropdown('fuel')"
          class="font-mono text-[10px] sm:text-xs px-3 py-1 rounded border transition-colors inline-flex items-center gap-1.5 cursor-pointer"
          :class="selectedFuel ? 'bg-gold/90 text-navy-deep border-gold/90' : 'border-cream/15 text-cream/50 hover:border-cream/30 hover:text-cream/70'"
        >
          {{ selectedFuel ? availableFuels.find(f => f.value === selectedFuel)?.label : 'Todos' }}
          <svg class="w-3 h-3 transition-transform" :class="openDropdown === 'fuel' ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
        </button>
        <div
          v-if="openDropdown === 'fuel'"
          class="absolute left-0 top-full mt-1 z-20 w-36 max-h-52 overflow-y-auto rounded-lg bg-[#0d1426] border border-cream/10 shadow-xl"
        >
          <button
            @click="setFuel(null); closeDropdown()"
            class="w-full text-left px-3 py-2 font-mono text-xs transition-colors"
            :class="selectedFuel === null ? 'bg-gold/15 text-gold' : 'text-cream/50 hover:bg-cream/[0.06] hover:text-cream/70'"
          >Todos</button>
          <button
            v-for="f in availableFuels"
            :key="f.value"
            @click="setFuel(f.value); closeDropdown()"
            class="w-full text-left px-3 py-2 font-mono text-xs transition-colors"
            :class="selectedFuel === f.value ? 'bg-gold/15 text-gold' : 'text-cream/50 hover:bg-cream/[0.06] hover:text-cream/70'"
          >{{ f.label }}</button>
        </div>
      </div>

      <!-- Brand dropdown (with search) -->
      <div v-if="availableBrands.length" class="relative flex items-center">
        <span class="font-mono text-[10px] sm:text-xs text-cream/30 w-20 sm:w-auto shrink-0">Marca:</span>
        <button
          @click="toggleDropdown('brand')"
          class="font-mono text-[10px] sm:text-xs px-3 py-1 rounded border transition-colors inline-flex items-center gap-1.5 cursor-pointer"
          :class="selectedBrand ? 'bg-gold/90 text-navy-deep border-gold/90' : 'border-cream/15 text-cream/50 hover:border-cream/30 hover:text-cream/70'"
        >
          {{ selectedBrand ? availableBrands.find(b => b.value === selectedBrand)?.label : 'Todas' }}
          <svg class="w-3 h-3 transition-transform" :class="openDropdown === 'brand' ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
        </button>
        <div
          v-if="openDropdown === 'brand'"
          class="absolute left-0 top-full mt-1 z-20 w-56 max-h-52 overflow-y-auto rounded-lg bg-[#0d1426] border border-cream/10 shadow-xl"
        >
          <div class="sticky top-0 bg-[#0d1426] p-2 border-b border-cream/6">
            <input
              v-model="brandSearch"
              type="text"
              placeholder="Buscar marca..."
              class="w-full bg-cream/[0.04] border border-cream/10 rounded px-2 py-1 text-xs text-cream placeholder-cream/20 outline-none focus:border-gold/40 font-mono"
            />
          </div>
          <button
            @click="setBrand(null); closeDropdown()"
            class="w-full text-left px-3 py-2 font-mono text-xs transition-colors"
            :class="selectedBrand === null ? 'bg-gold/15 text-gold' : 'text-cream/50 hover:bg-cream/[0.06] hover:text-cream/70'"
          >Todas las marcas</button>
          <button
            v-for="b in filteredBrandOptions"
            :key="b.value"
            @click="setBrand(b.value); closeDropdown()"
            class="w-full text-left px-3 py-2 font-mono text-xs transition-colors"
            :class="selectedBrand === b.value ? 'bg-gold/15 text-gold' : 'text-cream/50 hover:bg-cream/[0.06] hover:text-cream/70'"
          >{{ b.label }}</button>
        </div>
      </div>

    </div>

    <!-- Click-outside overlay to close any dropdown -->
    <div v-if="openDropdown" @click="closeDropdown()" class="fixed inset-0 z-10"></div>

    <!-- Loading -->
    <div v-if="loading" class="flex items-center gap-2.5 mb-6">
      <div class="w-3.5 h-3.5 border border-gold/50 border-t-gold rounded-full animate-spin"></div>
      <span class="font-mono text-xs sm:text-sm text-cream/30">Buscando...</span>
    </div>

    <!-- Currency toggle -->
    <div v-if="results.length" class="flex items-center gap-2 mb-5 sm:mb-4">
      <span class="font-mono text-[10px] sm:text-xs text-cream/30">Moneda:</span>
      <button
        @click="setCurrency('USD')"
        class="font-mono text-[10px] sm:text-xs px-2 py-0.5 rounded transition-colors"
        :class="currency === 'USD' ? 'bg-gold/90 text-navy-deep' : 'text-cream/40 hover:text-cream/70'"
      >USD</button>
      <button
        @click="setCurrency('ARS')"
        class="font-mono text-[10px] sm:text-xs px-2 py-0.5 rounded transition-colors"
        :class="currency === 'ARS' ? 'bg-gold/90 text-navy-deep' : 'text-cream/40 hover:text-cream/70'"
      >ARS</button>
      <span v-if="currency === 'ARS' && exchangeRate" class="font-mono text-[10px] sm:text-[11px] text-cream/25 ml-2">
        Dólar oficial: ${{ formatPrice(exchangeRate) }} ARS/USD
      </span>
    </div>

    <!-- Results count -->
    <div v-if="hasSearched && !loading" class="mb-4">
      Autos encontrados: {{ resultCount }}
    </div>

    <!-- Results -->
    <div v-if="filteredResults.length" class="space-y-2">
      <div
        v-for="r in paginatedResults"
        :key="r.version_id"
        class="flex items-center gap-3 sm:gap-4 px-4 sm:px-5 py-3 sm:py-3.5 rounded-lg bg-cream/[0.03] border border-cream/6"
      >
        <div class="flex flex-col min-w-0 flex-1">
          <div class="flex items-baseline gap-2 sm:gap-2.5 min-w-0">
            <span class="text-sm sm:text-base text-gold/80 shrink-0">{{ r.brand }}</span>
            <span class="text-cream/15">&middot;</span>
            <span class="text-sm sm:text-base text-cream/70 truncate">{{ r.model }}</span>
          </div>
          <span class="text-xs sm:text-sm text-cream/40 mt-1 truncate">{{ r.version }}</span>
        </div>
        <div v-if="r.price" class="shrink-0 text-right">
          <span class="font-mono text-sm sm:text-base text-gold">{{ currency === 'USD' ? 'US$' : '$' }}{{ formatDisplayPrice(r.price) }}</span>
          <span class="block font-mono text-[10px] sm:text-[11px] text-cream/25 mt-0.5">{{ r.price_year === 0 ? '0 km' : r.price_year }}</span>
        </div>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="totalPages > 1" class="flex items-center justify-center gap-1.5 mt-6">
      <button
        @click="goToPage(currentPage - 1)"
        :disabled="currentPage === 1"
        class="px-2.5 py-1.5 rounded text-cream/40 hover:text-cream/70 disabled:opacity-30 disabled:cursor-not-allowed transition-colors cursor-pointer"
      >
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
      </button>
      <button
        v-for="page in totalPages"
        :key="page"
        @click="goToPage(page)"
        class="font-mono text-xs px-2.5 py-1 rounded transition-colors cursor-pointer"
        :class="page === currentPage ? 'bg-gold/90 text-navy-deep' : 'text-cream/40 hover:text-cream/70'"
      >{{ page }}</button>
      <button
        @click="goToPage(currentPage + 1)"
        :disabled="currentPage === totalPages"
        class="px-2.5 py-1.5 rounded text-cream/40 hover:text-cream/70 disabled:opacity-30 disabled:cursor-not-allowed transition-colors cursor-pointer"
      >
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg>
      </button>
    </div>

    <!-- Empty state -->
    <div v-if="hasSearched && !loading && !filteredResults.length" class="text-center py-10 sm:py-12">
      <p class="text-cream/30 text-sm sm:text-base">No se encontraron vehículos con los filtros seleccionados.</p>
    </div>
  </section>
</template>

<script setup>
import { ref, computed } from 'vue'
import { usePriceExplorer } from '../composables/usePriceExplorer'

const openDropdown = ref(null)
const brandSearch = ref('')

function toggleDropdown(name) {
  if (openDropdown.value === name) {
    openDropdown.value = null
    brandSearch.value = ''
  } else {
    openDropdown.value = name
    brandSearch.value = ''
  }
}

function closeDropdown() {
  openDropdown.value = null
  brandSearch.value = ''
}

const {
  results,
  loading,
  hasSearched,
  budget,
  budgetYear,
  availableYears,
  selectedBodyType,
  selectedBrand,
  selectedFuel,
  currentPage,
  currency,
  exchangeRate,
  filteredResults,
  paginatedResults,
  totalPages,
  availableBrands,
  availableBodyTypes,
  availableFuels,
  resultCount,
  onBudgetChange,
  fetchByPrice,
  setBodyType,
  setBrand,
  setFuel,
  goToPage,
  setCurrency,
  formatPrice,
  formatDisplayPrice,
} = usePriceExplorer()

const filteredBrandOptions = computed(() => {
  if (!brandSearch.value) return availableBrands.value
  const q = brandSearch.value.toUpperCase()
  return availableBrands.value.filter(b => b.label.toUpperCase().includes(q))
})
</script>
