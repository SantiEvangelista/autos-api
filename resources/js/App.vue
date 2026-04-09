<template>
  <div class="min-h-screen relative overflow-hidden">
    <!-- Ambient background -->
    <div class="fixed inset-0 pointer-events-none">
      <div class="absolute top-[-20%] right-[-10%] w-[400px] h-[400px] md:w-[600px] md:h-[600px] rounded-full bg-blue/8 blur-[100px] md:blur-[120px]"></div>
      <div class="absolute bottom-[-10%] left-[-5%] w-[250px] h-[250px] md:w-[400px] md:h-[400px] rounded-full bg-gold/5 blur-[80px] md:blur-[100px]"></div>
    </div>

    <div class="relative z-10 max-w-5xl mx-auto px-5 sm:px-6 lg:px-12">
      <!-- Nav -->
      <nav class="flex items-center justify-between py-4 sm:py-6">
        <div class="flex items-center gap-2.5">
          <div class="w-1.5 h-1.5 sm:w-2 sm:h-2 rounded-full bg-gold animate-pulse"></div>
          <span class="font-mono text-[11px] sm:text-xs text-cream/50 tracking-wider uppercase">v1.0</span>
        </div>
        <div class="flex items-center gap-4 sm:gap-5">
          <a href="/docs/api" class="font-mono text-[11px] sm:text-xs text-cream/40 active:text-gold hover:text-gold transition-colors duration-300">
            Documentación
          </a>
          <a href="https://github.com/SantiEvangelista/autos-api" target="_blank" rel="noopener" class="flex items-center gap-1.5 font-mono text-[11px] sm:text-xs text-cream/40 active:text-gold hover:text-gold transition-colors duration-300">
            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C5.37 0 0 5.37 0 12c0 5.31 3.435 9.795 8.205 11.385.6.105.825-.255.825-.57 0-.285-.015-1.23-.015-2.235-3.015.555-3.795-.735-4.035-1.41-.135-.345-.72-1.41-1.23-1.695-.42-.225-1.02-.78-.015-.795.945-.015 1.62.87 1.845 1.23 1.08 1.815 2.805 1.305 3.495.99.105-.78.42-1.305.765-1.605-2.67-.3-5.46-1.335-5.46-5.925 0-1.305.465-2.385 1.23-3.225-.12-.3-.54-1.53.12-3.18 0 0 1.005-.315 3.3 1.23.96-.27 1.98-.405 3-.405s2.04.135 3 .405c2.295-1.56 3.3-1.23 3.3-1.23.66 1.65.24 2.88.12 3.18.765.84 1.23 1.905 1.23 3.225 0 4.605-2.805 5.625-5.475 5.925.435.375.81 1.095.81 2.22 0 1.605-.015 2.895-.015 3.3 0 .315.225.69.825.57A12.02 12.02 0 0024 12c0-6.63-5.37-12-12-12z"/></svg>
            <span>GitHub</span>
            <span v-if="githubStars !== null" class="inline-flex items-center gap-1 bg-cream/10 px-1.5 py-0.5 rounded text-[10px] sm:text-[11px]">
              <svg class="w-3 h-3 text-gold/70" viewBox="0 0 16 16" fill="currentColor"><path d="M8 .25a.75.75 0 0 1 .673.418l1.882 3.815 4.21.612a.75.75 0 0 1 .416 1.279l-3.046 2.97.719 4.192a.75.75 0 0 1-1.088.791L8 12.347l-3.766 1.98a.75.75 0 0 1-1.088-.79l.72-4.194L.818 6.374a.75.75 0 0 1 .416-1.28l4.21-.611L7.327.668A.75.75 0 0 1 8 .25z"/></svg>
              {{ githubStars }}
            </span>
          </a>
        </div>
      </nav>

      <!-- Hero -->
      <header class="pt-10 sm:pt-16 lg:pt-24 pb-14 sm:pb-20">

        <h1 class="text-5xl leading-none sm:text-7xl lg:text-8xl xl:text-9xl font-light text-cream mb-6 sm:mb-8 hero-fade" style="animation-delay: 80ms">
          Arg <br>
          <span class="font-semibold text-gold">Autos </span>
        </h1>
        <p class="text-lg sm:text-2xl text-cream/50 max-w-md sm:max-w-2xl leading-relaxed font-light hero-fade" style="animation-delay: 160ms">
          Para todos los que queremos saber el precio de un auto en Argentina sin tener que ir a una concesionaria.
        </p>
        <!-- Stats -->
        <div class="flex gap-8 sm:gap-12 lg:gap-16 mt-10 sm:mt-14 hero-fade" style="animation-delay: 240ms">
          <div>
            <div class="text-3xl sm:text-4xl lg:text-5xl font-light text-cream">{{ stats.brands }}</div>
            <div class="font-mono text-[10px] sm:text-xs text-cream/30 tracking-wider uppercase mt-1">Marcas</div>
          </div>
          <div>
            <div class="text-3xl sm:text-4xl lg:text-5xl font-light text-cream">{{ stats.models }}</div>
            <div class="font-mono text-[10px] sm:text-xs text-cream/30 tracking-wider uppercase mt-1">Modelos</div>
          </div>
          <div>
            <div class="text-3xl sm:text-4xl lg:text-5xl font-light text-cream">{{ stats.versions }}</div>
            <div class="font-mono text-[10px] sm:text-xs text-cream/30 tracking-wider uppercase mt-1">Versiones</div>
          </div>
        </div>
      </header>

      <!-- Divider -->
      <div class="border-t border-cream/8"></div>

      <!-- Docs invitation (replaces endpoints section) -->
      <section class="py-10 sm:py-14 lg:py-16">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div>
            <p class="text-sm sm:text-base text-cream/50 font-light">
              Integrala en tu proyecto
            </p>
            <p class="text-xs sm:text-sm text-cream/25 mt-1">
              Endpoints, ejemplos y guía de uso disponibles en la documentación.
            </p>
          </div>
          <a
            href="/docs/api"
            class="inline-flex items-center gap-2 px-4 sm:px-5 py-2.5 sm:py-3 rounded-lg bg-gold/10 border border-gold/20 text-gold text-sm sm:text-base font-medium hover:bg-gold/15 hover:border-gold/30 active:bg-gold/20 transition-all duration-300 shrink-0 self-start sm:self-auto"
          >
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
              <polyline points="14 2 14 8 20 8"/>
              <line x1="16" y1="13" x2="8" y2="13"/>
              <line x1="16" y1="17" x2="8" y2="17"/>
              <polyline points="10 9 9 9 8 9"/>
            </svg>
            Ver documentación
          </a>
        </div>
      </section>

      <!-- Divider -->

      <p class="font-mono text-sm sm:text-base text-gold/80 py-6 sm:py-8 hero-fade" style="animation-delay: 200ms">Precios de referencia con datos de la CCA y Acara</p>
      <div class="border-t border-cream/8"></div>

      <!-- Search -->
      <section class="py-12 sm:py-16 lg:py-20">
        <p class="font-mono text-[10px] sm:text-xs text-cream/30 tracking-[0.2em] sm:tracking-[0.3em] uppercase mb-2 sm:mb-3"></p>
        <h2 class="text-2xl sm:text-3xl lg:text-4xl font-light text-cream mb-1.5 sm:mb-2">Buscar un auto </h2>
        <p class="text-xs sm:text-sm text-cream/30 mb-5 sm:mb-8">Toca un resultado para ver el detalle completo</p>

        <div class="relative">
          <input
            v-model="searchQuery"
            @input="onSearch"
            type="text"
            placeholder="ej: toyota corolla, hilux 4x4, bmw serie 3..."
            class="w-full bg-cream/[0.04] border border-cream/10 rounded-lg px-3.5 sm:px-4 py-3 sm:py-3.5 text-sm sm:text-base text-cream placeholder-cream/20 outline-none focus:border-gold/40 transition-colors duration-300"
          />
          <div v-if="searching" class="absolute right-3 sm:right-4 top-1/2 -translate-y-1/2">
            <div class="w-3.5 h-3.5 sm:w-4 sm:h-4 border border-gold/50 border-t-gold rounded-full animate-spin"></div>
          </div>
        </div>

        <!-- Search currency toggle -->
        <div v-if="searchResults.length" class="flex items-center gap-2 mt-4 sm:mt-6 mb-2 sm:mb-3">
          <span class="font-mono text-[10px] sm:text-xs text-cream/30">Moneda:</span>
          <button
            @click="setSearchCurrency('USD')"
            class="font-mono text-[10px] sm:text-xs px-2 py-0.5 rounded transition-colors"
            :class="searchCurrency === 'USD' ? 'bg-gold/90 text-navy-deep' : 'text-cream/40 hover:text-cream/70'"
          >USD</button>
          <button
            @click="setSearchCurrency('ARS')"
            class="font-mono text-[10px] sm:text-xs px-2 py-0.5 rounded transition-colors"
            :class="searchCurrency === 'ARS' ? 'bg-gold/90 text-navy-deep' : 'text-cream/40 hover:text-cream/70'"
          >ARS</button>
          <span v-if="searchCurrency === 'ARS' && searchExchangeRate" class="font-mono text-[10px] sm:text-[11px] text-cream/25 ml-2">
            Dólar oficial: ${{ formatPrice(searchExchangeRate) }} ARS/USD
          </span>
        </div>

        <!-- Search results (with reference price) -->
        <div v-if="searchResults.length" class="space-y-2.5 sm:space-y-3">
          <div
            v-for="r in paginatedSearchResults"
            :key="r.version_id"
            @click="openModal(r)"
            class="group flex items-center gap-4 sm:gap-5 px-4 sm:px-5 py-3.5 sm:py-4 rounded-lg bg-cream/[0.03] border border-cream/6 hover:bg-cream/[0.06] hover:border-gold/25 transition-all duration-300 cursor-pointer"
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
              <span class="font-mono text-sm sm:text-base text-gold">{{ searchCurrency === 'USD' ? 'US$' : '$' }}{{ formatSearchPrice(r.price) }}</span>
              <span class="block font-mono text-[10px] sm:text-[11px] text-cream/25 mt-0.5">{{ r.price_year === 0 ? '0 km' : r.price_year }}</span>
            </div>
            <svg class="w-4 h-4 shrink-0 text-cream/15 group-hover:text-gold/60 transition-colors duration-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg>
          </div>
        </div>

        <!-- Search pagination -->
        <div v-if="totalSearchPages > 1" class="flex items-center justify-center gap-1.5 mt-6">
          <button
            @click="goToSearchPage(currentSearchPage - 1)"
            :disabled="currentSearchPage === 1"
            class="px-2.5 py-1.5 rounded text-cream/40 hover:text-cream/70 disabled:opacity-30 disabled:cursor-not-allowed transition-colors cursor-pointer"
          >
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
          </button>
          <button
            v-for="page in totalSearchPages"
            :key="page"
            @click="goToSearchPage(page)"
            class="font-mono text-xs px-2.5 py-1 rounded transition-colors cursor-pointer"
            :class="page === currentSearchPage ? 'bg-gold/90 text-navy-deep' : 'text-cream/40 hover:text-cream/70'"
          >{{ page }}</button>
          <button
            @click="goToSearchPage(currentSearchPage + 1)"
            :disabled="currentSearchPage === totalSearchPages"
            class="px-2.5 py-1.5 rounded text-cream/40 hover:text-cream/70 disabled:opacity-30 disabled:cursor-not-allowed transition-colors cursor-pointer"
          >
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg>
          </button>
        </div>
        <div v-if="searchQuery.length >= 2 && !searching && !searchResults.length" class="mt-4 sm:mt-6">
          <p class="text-cream/30 text-sm sm:text-base">Sin resultados para "{{ searchQuery }}"</p>
        </div>
      </section>

      <!-- Divider -->
      <div class="border-t border-cream/8"></div>

      <!-- Price Explorer -->
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

        <!-- Year filter -->
        <div class="flex items-center gap-2 mb-6 sm:mb-8 flex-wrap">
          <span class="font-mono text-[10px] sm:text-xs text-cream/30">Año:</span>
          <button
            v-for="y in availableYears"
            :key="y ?? 'all'"
            @click="budgetYear = y; fetchByPrice()"
            class="font-mono text-[10px] sm:text-xs px-2 py-0.5 rounded transition-colors"
            :class="budgetYear === y ? 'bg-gold/90 text-navy-deep' : 'text-cream/40 hover:text-cream/70'"
          >
            {{ y === null ? 'Todos' : (y === 0 ? '0km' : y) }}
          </button>
        </div>

        <!-- Loading -->
        <div v-if="priceExplorer.loading" class="flex items-center gap-2.5 mb-6">
          <div class="w-3.5 h-3.5 border border-gold/50 border-t-gold rounded-full animate-spin"></div>
          <span class="font-mono text-xs sm:text-sm text-cream/30">Buscando...</span>
        </div>

        <!-- Price Explorer currency toggle -->
        <div v-if="priceExplorer.results.length" class="flex items-center gap-2 mb-4">
          <span class="font-mono text-[10px] sm:text-xs text-cream/30">Moneda:</span>
          <button
            @click="setPriceExplorerCurrency('USD')"
            class="font-mono text-[10px] sm:text-xs px-2 py-0.5 rounded transition-colors"
            :class="priceExplorerCurrency === 'USD' ? 'bg-gold/90 text-navy-deep' : 'text-cream/40 hover:text-cream/70'"
          >USD</button>
          <button
            @click="setPriceExplorerCurrency('ARS')"
            class="font-mono text-[10px] sm:text-xs px-2 py-0.5 rounded transition-colors"
            :class="priceExplorerCurrency === 'ARS' ? 'bg-gold/90 text-navy-deep' : 'text-cream/40 hover:text-cream/70'"
          >ARS</button>
          <span v-if="priceExplorerCurrency === 'ARS' && searchExchangeRate" class="font-mono text-[10px] sm:text-[11px] text-cream/25 ml-2">
            Dólar oficial: ${{ formatPrice(searchExchangeRate) }} ARS/USD
          </span>
        </div>

        <!-- Results count -->
        <div v-if="priceExplorer.hasSearched && !priceExplorer.loading" class="mb-4">
          Autos encontrados: {{ priceExplorer.results.length }}<span class="font-mono text-xs text-cream/40"> <br> Se ocultan autos mas economicos para no repetir resultados</span>
        </div>

        <!-- Results -->
        <div v-if="priceExplorer.results.length" class="space-y-2">
          <div
            v-for="r in paginatedPriceResults"
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
              <span class="font-mono text-sm sm:text-base text-gold">{{ priceExplorerCurrency === 'USD' ? 'US$' : '$' }}{{ formatPriceExplorerPrice(r.price) }}</span>
              <span class="block font-mono text-[10px] sm:text-[11px] text-cream/25 mt-0.5">{{ r.price_year === 0 ? '0 km' : r.price_year }}</span>
            </div>
          </div>
        </div>

        <!-- Price Explorer pagination -->
        <div v-if="totalPricePages > 1" class="flex items-center justify-center gap-1.5 mt-6">
          <button
            @click="goToPricePage(currentPricePage - 1)"
            :disabled="currentPricePage === 1"
            class="px-2.5 py-1.5 rounded text-cream/40 hover:text-cream/70 disabled:opacity-30 disabled:cursor-not-allowed transition-colors cursor-pointer"
          >
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
          </button>
          <button
            v-for="page in totalPricePages"
            :key="page"
            @click="goToPricePage(page)"
            class="font-mono text-xs px-2.5 py-1 rounded transition-colors cursor-pointer"
            :class="page === currentPricePage ? 'bg-gold/90 text-navy-deep' : 'text-cream/40 hover:text-cream/70'"
          >{{ page }}</button>
          <button
            @click="goToPricePage(currentPricePage + 1)"
            :disabled="currentPricePage === totalPricePages"
            class="px-2.5 py-1.5 rounded text-cream/40 hover:text-cream/70 disabled:opacity-30 disabled:cursor-not-allowed transition-colors cursor-pointer"
          >
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg>
          </button>
        </div>

        <!-- Empty state -->
        <div v-if="priceExplorer.hasSearched && !priceExplorer.loading && !priceExplorer.results.length" class="text-center py-10 sm:py-12">
          <p class="text-cream/30 text-sm sm:text-base">No se encontraron vehículos en este rango de precio.</p>
        </div>
      </section>

      <!-- Divider -->
      <div class="border-t border-cream/8"></div>

      <!-- Ranking -->
      <RankingTable />

      <!-- Divider -->
      <div class="border-t border-cream/8"></div>

      <!-- Live Explorer -->
      <section id="explorador" class="py-12 sm:py-16 lg:py-20">
        <h2 class="text-2xl sm:text-3xl lg:text-4xl font-light text-cream mb-1.5 sm:mb-2">Marcas, Modelos y Versiones</h2>
        <p class="text-xs sm:text-sm text-cream/30 mb-8 sm:mb-12">Seleccioná una Marca , Modelo y Versión para ver los precios y su fuente.</p>

        <!-- Breadcrumb (bigger font, chevrons instead of slashes) -->
        <div class="flex items-center gap-2 sm:gap-3 mb-6 sm:mb-8 flex-wrap" v-if="selected.brand">
          <button
            @click="resetTo('brands')"
            class="text-sm sm:text-base text-gold/70 active:text-gold hover:text-gold transition-colors font-medium"
          >
            Marcas
          </button>
          <template v-if="selected.brand">
            <svg class="w-4 h-4 text-cream/20 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
            <button
              @click="resetTo('models')"
              class="text-sm sm:text-base transition-colors font-medium"
              :class="selected.model ? 'text-gold/70 active:text-gold hover:text-gold' : 'text-cream/80'"
            >
              {{ selected.brand.name }}
            </button>
          </template>
          <template v-if="selected.model">
            <svg class="w-4 h-4 text-cream/20 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
            <button
              @click="resetTo('versions')"
              class="text-sm sm:text-base transition-colors font-medium"
              :class="selected.version ? 'text-gold/70 active:text-gold hover:text-gold' : 'text-cream/80'"
            >
              {{ selected.model.name }}
            </button>
          </template>
          <template v-if="selected.version">
            <svg class="w-4 h-4 text-cream/20 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
            <span class="text-sm sm:text-base text-cream/80 font-medium break-all">{{ selected.version.name }}</span>
          </template>
        </div>

        <!-- Currency toggle -->
        <div v-if="currentStep === 'valuations'" class="flex items-center gap-2 mb-2 sm:mb-3">
          <span class="font-mono text-[10px] sm:text-xs text-cream/30">Moneda:</span>
          <button
            @click="setCurrency('USD')"
            class="font-mono text-[10px] sm:text-xs px-2 py-0.5 rounded transition-colors"
            :class="activeCurrency === 'USD' ? 'bg-gold/90 text-navy-deep' : 'text-cream/40 hover:text-cream/70'"
          >USD</button>
          <button
            @click="setCurrency('ARS')"
            class="font-mono text-[10px] sm:text-xs px-2 py-0.5 rounded transition-colors"
            :class="activeCurrency === 'ARS' ? 'bg-gold/90 text-navy-deep' : 'text-cream/40 hover:text-cream/70'"
          >ARS</button>
        </div>

        <!-- Price source toggle -->
        <div v-if="currentStep === 'valuations'" class="flex items-center gap-2 mb-4 sm:mb-6">
          <span class="font-mono text-[10px] sm:text-xs text-cream/30">Fuente de precio:</span>
          <button
            @click="setSource('api')"
            class="font-mono text-[10px] sm:text-xs px-2 py-0.5 rounded transition-colors"
            :class="activeSource === 'api' ? 'bg-gold/90 text-navy-deep' : 'text-cream/40 hover:text-cream/70'"
          >API</button>
          <button
            @click="setSource('acara')"
            class="font-mono text-[10px] sm:text-xs px-2 py-0.5 rounded transition-colors"
            :class="activeSource === 'acara' ? 'bg-gold/90 text-navy-deep' : 'text-cream/40 hover:text-cream/70'"
          >ACARA</button>
        </div>

        <!-- Loading indicator -->
        <div v-if="loading" class="flex items-center gap-2.5 mb-6 sm:mb-8">
          <div class="w-3.5 h-3.5 border border-gold/50 border-t-gold rounded-full animate-spin"></div>
          <span class="font-mono text-xs sm:text-sm text-cream/30">Cargando...</span>
        </div>

        <!-- Error banner -->
        <div v-if="error" class="border border-red-500/20 rounded-lg px-3.5 sm:px-4 py-2.5 sm:py-3 mb-4 sm:mb-6 bg-red-500/5">
          <p class="font-mono text-[11px] sm:text-xs text-red-400/80">{{ error }}</p>
        </div>

        <!-- Results grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2.5 sm:gap-3">
          <!-- Brands -->
          <template v-if="currentStep === 'brands'">
            <button
              v-for="brand in explorer.brands"
              :key="brand.id"
              @click="selectBrand(brand)"
              class="text-left px-4 sm:px-5 py-3.5 sm:py-4 rounded-lg bg-cream/[0.03] border border-cream/6 active:border-gold/30 active:bg-cream/[0.06] hover:border-gold/30 hover:bg-cream/[0.06] transition-all duration-300 group"
            >
              <span class="text-sm sm:text-base text-cream/80 group-hover:text-cream transition-colors block truncate">{{ brand.name }}</span>
            </button>
          </template>

          <!-- Models -->
          <template v-if="currentStep === 'models'">
            <button
              v-for="model in explorer.models"
              :key="model.id"
              @click="selectModel(model)"
              class="text-left px-4 sm:px-5 py-3.5 sm:py-4 rounded-lg bg-cream/[0.03] border border-cream/6 active:border-gold/30 active:bg-cream/[0.06] hover:border-gold/30 hover:bg-cream/[0.06] transition-all duration-300 group"
            >
              <span class="text-sm sm:text-base text-cream/80 group-hover:text-cream transition-colors block truncate">{{ model.name }}</span>
            </button>
          </template>

          <!-- Versions -->
          <template v-if="currentStep === 'versions'">
            <button
              v-for="version in explorer.versions"
              :key="version.id"
              @click="selectVersion(version)"
              class="text-left px-4 sm:px-5 py-3.5 sm:py-4 rounded-lg bg-cream/[0.03] border border-cream/6 active:border-gold/30 active:bg-cream/[0.06] hover:border-gold/30 hover:bg-cream/[0.06] transition-all duration-300 group col-span-1 sm:col-span-1"
            >
              <span class="text-sm sm:text-base text-cream/80 group-hover:text-cream transition-colors block">{{ version.name }}</span>
            </button>
          </template>
        </div>

        <!-- Valuations table -->
        <div v-if="currentStep === 'valuations' && explorer.valuations.length" class="mt-2">
          <div class="bg-cream/[0.03] border border-cream/6 rounded-lg overflow-hidden">
            <div class="grid grid-cols-2 px-4 sm:px-5 py-2.5 sm:py-3 border-b border-cream/6">
              <span class="font-mono text-[11px] sm:text-xs text-cream/30 uppercase tracking-wider">Año</span>
              <span class="font-mono text-[11px] sm:text-xs text-cream/30 uppercase tracking-wider text-right">Precio ({{ activeCurrency }})</span>
            </div>
            <div
              v-for="val in explorer.valuations"
              :key="val.id"
              class="grid grid-cols-2 px-4 sm:px-5 py-3 sm:py-3.5 border-b border-cream/4 last:border-0 hover:bg-cream/[0.03] transition-colors"
            >
              <span class="font-mono text-sm sm:text-base text-cream/70">{{ val.year === 0 ? '0 km' : val.year }}</span>
              <span v-if="activeSource === 'api' || val.acara_price == null" class="font-mono text-sm sm:text-base text-gold text-right">{{ activeCurrency === 'USD' ? 'US$' : '$' }}{{ formatPrice(val.price) }}</span>
              <span v-else class="font-mono text-sm sm:text-base text-gold text-right">{{ activeCurrency === 'USD' ? 'US$' : '$' }}{{ formatPrice(val.acara_price) }}</span>
            </div>
          </div>
          <!-- Exchange rate info -->
          <div v-if="explorer.meta?.exchange_rate" class="mt-3 flex items-center gap-1.5 flex-wrap">
            <span class="font-mono text-[10px] sm:text-[11px] text-cream/25">
              Dólar oficial: ${{ formatPrice(explorer.meta.exchange_rate.ars_per_usd) }} ARS/USD
            </span>
            <span class="text-cream/15">&middot;</span>
            <a href="https://bluelytics.com.ar" target="_blank" rel="noopener" class="font-mono text-[10px] sm:text-[11px] text-cream/25 hover:text-gold/60 transition-colors">
              cotización por Bluelytics
            </a>
          </div>
        </div>

        <!-- Empty state -->
        <div v-if="!loading && currentStep === 'valuations' && !explorer.valuations.length" class="text-center py-10 sm:py-12">
          <p class="text-cream/30 text-sm sm:text-base">Sin valuaciones disponibles para esta versión.</p>
        </div>

        <!-- Volver bar -->
        <div v-if="currentStep === 'valuations'" class="mt-6 sm:mt-8">
          <button
            @click="resetTo('brands')"
            class="w-full flex items-center justify-center gap-2.5 px-5 py-3.5 sm:py-4 rounded-lg bg-gold/10 border border-gold/20 hover:bg-gold/15 hover:border-gold/30 active:bg-gold/20 transition-all duration-300 group"
          >
            <svg class="w-4 h-4 text-gold/70 group-hover:text-gold transition-colors duration-300 rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg>
            <span class="text-sm sm:text-base text-gold font-medium transition-colors duration-300">Volver a buscar</span>
          </button>
        </div>
      </section>

      <!-- Footer -->
      <footer class="py-8 sm:py-10 border-t border-cream/6">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2 sm:gap-4">
          <div class="flex items-center gap-2">
            <span class="font-mono text-[10px] sm:text-xs text-cream/20">Desarrollado por <a href="https://www.linkedin.com/in/santiago-evan/" target="_blank" rel="noopener" class="hover:text-gold/40 text-gold transition-colors">Santiago Evangelista</a></span>
          </div>
          <span class="font-mono text-[10px] sm:text-xs text-cream/15">Cotización USD/ARS por <a href="https://bluelytics.com.ar" target="_blank" rel="noopener" style="color: #03a9f4;" class="hover:text-gold/40 transition-colors">Bluelytics</a></span>
        </div>
      </footer>
    </div>
  </div>

  <!-- Info Modal -->
  <Teleport to="body">
    <div
      v-if="modalResult"
      @click.self="closeModal"
      class="fixed inset-0 z-50 flex items-center justify-center px-4 bg-navy-deep/80 backdrop-blur-sm"
    >
      <div class="relative w-full max-w-md bg-navy-deep border border-cream/10 rounded-xl p-6 sm:p-8 shadow-2xl">
        <!-- Close -->
        <button @click="closeModal" class="absolute top-4 right-4 text-cream/30 hover:text-cream/70 transition-colors cursor-pointer">
          <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
          </svg>
        </button>

        <!-- Brand -->
        <span class="font-mono text-[10px] sm:text-xs text-gold/60 tracking-wider uppercase">{{ modalResult.brand }}</span>

        <!-- Model -->
        <h3 class="text-xl sm:text-2xl font-light text-cream mt-1">{{ modalResult.model }}</h3>

        <!-- Version (humanized) -->
        <p class="text-sm sm:text-base text-cream/50 mt-1">{{ modalResult.version }}</p>

        <!-- Divider -->
        <div class="border-t border-cream/8 my-4 sm:my-5"></div>

        <!-- Price -->
        <div v-if="modalResult.price" class="flex items-baseline justify-between">
          <span class="font-mono text-[10px] sm:text-xs text-cream/30 uppercase tracking-wider">Precio referencia</span>
          <div class="text-right">
            <span class="font-mono text-lg sm:text-xl text-gold">
              {{ searchCurrency === 'USD' ? 'US$' : '$' }}{{ formatSearchPrice(modalResult.price) }}
            </span>
            <span class="block font-mono text-[10px] sm:text-[11px] text-cream/25 mt-0.5">
              {{ modalResult.price_year === 0 ? '0 km' : modalResult.price_year }}
            </span>
          </div>
        </div>
        <div v-else class="text-cream/30 text-sm">Precio no disponible</div>

        <!-- Version raw -->
        <div class="mt-4 sm:mt-5">
          <span class="font-mono text-[10px] sm:text-xs text-cream/20 tracking-wider uppercase">Nombre tecnico</span>
          <p class="font-mono text-xs sm:text-sm text-cream/40 mt-0.5">{{ modalResult.version_raw }}</p>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { ref, reactive, computed, watch, onMounted, onUnmounted } from 'vue'
import RankingTable from './components/RankingTable.vue'

const baseUrl = window.location.origin

// GitHub stars
const githubStars = ref(null)
fetch('https://api.github.com/repos/SantiEvangelista/autos-api')
  .then(r => r.ok ? r.json() : null)
  .then(data => { if (data) githubStars.value = data.stargazers_count })
  .catch(() => {})

// Stats
const stats = reactive({ brands: '...', models: '...', versions: '...' })

// Explorer state
const loading = ref(false)
const selected = reactive({ brand: null, model: null, version: null })
const explorer = reactive({ brands: [], models: [], versions: [], valuations: [], meta: null })

const currentStep = computed(() => {
  if (selected.version) return 'valuations'
  if (selected.model) return 'versions'
  if (selected.brand) return 'models'
  return 'brands'
})

const activeCurrency = ref('USD')
const activeSource = ref('api')

const error = ref(null)

async function apiFetch(path) {
  loading.value = true
  error.value = null
  try {
    const res = await fetch(path)
    if (!res.ok) {
      error.value = `Error ${res.status}: No se pudo completar la solicitud.`
      return { data: [] }
    }
    return await res.json()
  } catch (e) {
    error.value = 'Error de conexión. Verificá tu conexión a internet.'
    return { data: [] }
  } finally {
    loading.value = false
  }
}

// Load initial data on mount
apiFetch('/api/v1/brands').then(res => { explorer.brands = res.data })
apiFetch('/api/v1/stats').then(res => {
  stats.brands = Number(res.brands).toLocaleString('es-AR')
  stats.models = Number(res.models).toLocaleString('es-AR')
  stats.versions = Number(res.versions).toLocaleString('es-AR')
})

async function selectBrand(brand) {
  selected.brand = brand
  selected.model = null
  selected.version = null
  const res = await apiFetch(`/api/v1/brands/${brand.id}/models?per_page=100`)
  explorer.models = res.data
}

async function selectModel(model) {
  selected.model = model
  selected.version = null
  const res = await apiFetch(`/api/v1/models/${model.id}/versions?per_page=100`)
  explorer.versions = res.data
}

async function selectVersion(version) {
  selected.version = version
  await fetchValuations()
}

async function fetchValuations() {
  const params = new URLSearchParams()
  if (activeCurrency.value === 'ARS') params.set('currency', 'ars')
  if (activeSource.value === 'acara') params.set('sources', 'acara')
  const qs = params.toString() ? `?${params.toString()}` : ''
  const res = await apiFetch(`/api/v1/versions/${selected.version.id}/valuations${qs}`)
  explorer.valuations = res.data
  explorer.meta = res.meta
}

async function setCurrency(currency) {
  activeCurrency.value = currency
  if (selected.version) {
    await fetchValuations()
  }
}

async function setSource(source) {
  activeSource.value = source
  if (selected.version) {
    await fetchValuations()
  }
}

function resetTo(step) {
  if (step === 'brands') {
    selected.brand = null
    selected.model = null
    selected.version = null
  } else if (step === 'models') {
    selected.model = null
    selected.version = null
  } else if (step === 'versions') {
    selected.version = null
  }
}

function formatPrice(price) {
  return Number(price).toLocaleString('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

// Price Explorer
const priceExplorer = reactive({ results: [], loading: false, hasSearched: false })
const budget = ref(30000)
const budgetYear = ref(null)
const availableYears = [null, 0, 2026, 2025, 2024, 2023, 2022, 2021, 2020]
let priceExplorerTimeout = null

function onBudgetChange() {
  clearTimeout(priceExplorerTimeout)
  priceExplorer.loading = true
  priceExplorerTimeout = setTimeout(() => fetchByPrice(), 400)
}

async function fetchByPrice() {
  const maxPrice = budget.value
  const minPrice = Math.round(maxPrice * 0.85)
  const params = new URLSearchParams({ max_price: maxPrice, min_price: minPrice, per_page: '50' })
  if (budgetYear.value !== null) params.set('year', budgetYear.value)
  priceExplorer.loading = true
  priceExplorer.hasSearched = true
  currentPricePage.value = 1
  try {
    const res = await fetch(`/api/v1/price-explorer?${params}`)
    const data = await res.json()
    const raw = data.data || []
    // Dedup: 1 result per brand+model, keep the one closest to budget
    const seen = new Map()
    for (const r of raw) {
      const key = `${r.brand}|${r.model}`
      if (!seen.has(key) || Math.abs(r.price - maxPrice) < Math.abs(seen.get(key).price - maxPrice)) {
        seen.set(key, r)
      }
    }
    priceExplorer.results = [...seen.values()].sort((a, b) => a.price - b.price).slice(0, 25)
  } catch {
    priceExplorer.results = []
  } finally {
    priceExplorer.loading = false
  }
}

// Price Explorer pagination
const PRICE_ITEMS_PER_PAGE = 6
const currentPricePage = ref(1)

const paginatedPriceResults = computed(() => {
  const start = (currentPricePage.value - 1) * PRICE_ITEMS_PER_PAGE
  return priceExplorer.results.slice(start, start + PRICE_ITEMS_PER_PAGE)
})

const totalPricePages = computed(() =>
  Math.ceil(priceExplorer.results.length / PRICE_ITEMS_PER_PAGE)
)

function goToPricePage(page) {
  if (page >= 1 && page <= totalPricePages.value) {
    currentPricePage.value = page
  }
}

// Price Explorer currency
const priceExplorerCurrency = ref('USD')

async function setPriceExplorerCurrency(currency) {
  priceExplorerCurrency.value = currency
  if (currency === 'ARS' && !searchExchangeRate.value) {
    await fetchSearchExchangeRate()
  }
}

function formatPriceExplorerPrice(priceUsd) {
  if (priceExplorerCurrency.value === 'ARS' && searchExchangeRate.value) {
    return formatPrice(Number(priceUsd) * searchExchangeRate.value)
  }
  return formatPrice(priceUsd)
}

// Search
const searchQuery = ref('')
const searchResults = ref([])
const searching = ref(false)
const searchCurrency = ref('USD')
const searchExchangeRate = ref(null)
let searchTimeout = null

async function fetchSearchExchangeRate() {
  if (searchExchangeRate.value) return
  try {
    const res = await fetch('https://api.bluelytics.com.ar/v2/latest')
    const data = await res.json()
    searchExchangeRate.value = data.oficial.value_sell
  } catch {
    searchExchangeRate.value = null
  }
}

async function setSearchCurrency(currency) {
  searchCurrency.value = currency
  if (currency === 'ARS' && !searchExchangeRate.value) {
    await fetchSearchExchangeRate()
  }
}

function formatSearchPrice(priceUsd) {
  if (searchCurrency.value === 'ARS' && searchExchangeRate.value) {
    return formatPrice(Number(priceUsd) * searchExchangeRate.value)
  }
  return formatPrice(priceUsd)
}

// Search pagination
const ITEMS_PER_PAGE = 6
const currentSearchPage = ref(1)

const paginatedSearchResults = computed(() => {
  const start = (currentSearchPage.value - 1) * ITEMS_PER_PAGE
  return searchResults.value.slice(start, start + ITEMS_PER_PAGE)
})

const totalSearchPages = computed(() =>
  Math.ceil(searchResults.value.length / ITEMS_PER_PAGE)
)

function goToSearchPage(page) {
  if (page >= 1 && page <= totalSearchPages.value) {
    currentSearchPage.value = page
  }
}

function onSearch() {
  clearTimeout(searchTimeout)
  if (searchQuery.value.length < 2) {
    searchResults.value = []
    return
  }
  searching.value = true
  currentSearchPage.value = 1
  searchTimeout = setTimeout(async () => {
    try {
      const res = await apiFetch(`/api/v1/search?q=${encodeURIComponent(searchQuery.value)}&per_page=50`)
      searchResults.value = res.data
    } finally {
      searching.value = false
    }
  }, 300)
}

// Modal
const modalResult = ref(null)
function openModal(result) { modalResult.value = result }
function closeModal() { modalResult.value = null }

function handleKeydown(e) {
  if (e.key === 'Escape' && modalResult.value) closeModal()
}
onMounted(() => document.addEventListener('keydown', handleKeydown))
onUnmounted(() => document.removeEventListener('keydown', handleKeydown))

watch(modalResult, (val) => { document.body.style.overflow = val ? 'hidden' : '' })
</script>

<style>
@keyframes heroFade {
  from { opacity: 0; transform: translateY(12px); }
  to { opacity: 1; transform: translateY(0); }
}

.hero-fade {
  opacity: 0;
  animation: heroFade 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

/* Price range slider */
.price-slider::-webkit-slider-thumb {
  -webkit-appearance: none;
  height: 22px;
  width: 22px;
  border-radius: 50%;
  background: #DDA853;
  cursor: pointer;
  border: 3px solid #0F2A38;
  box-shadow: 0 0 8px rgba(221, 168, 83, 0.3);
}
.price-slider::-moz-range-thumb {
  height: 22px;
  width: 22px;
  border-radius: 50%;
  background: #DDA853;
  cursor: pointer;
  border: 3px solid #0F2A38;
  box-shadow: 0 0 8px rgba(221, 168, 83, 0.3);
}

/* Custom scrollbar */
::-webkit-scrollbar { width: 4px; }
::-webkit-scrollbar-track { background: transparent; }
::-webkit-scrollbar-thumb { background: rgba(245, 238, 220, 0.1); border-radius: 2px; }
::-webkit-scrollbar-thumb:hover { background: rgba(245, 238, 220, 0.2); }
</style>
