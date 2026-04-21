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
          <button @click="showContact = true" class="font-mono text-[11px] sm:text-xs text-gold active:text-gold/70 hover:text-gold-light transition-colors duration-300 cursor-pointer">
            Contacto
          </button>
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

      <p class="font-mono text-sm sm:text-base text-gold/80 py-6 sm:py-8 hero-fade" style="animation-delay: 200ms">Precios tomados de la Camara de Comercio Automotor (CCA), Acara e InfoAuto</p>
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
        <div v-if="searchResults.length" class="flex items-center gap-2 mt-4 sm:mt-6 mb-2 sm:mb-3 flex-wrap">
          <span class="font-mono text-[10px] sm:text-xs text-cream/30">Moneda:</span>
          <button
            @click="setSearchCurrency('USD')"
            class="font-mono text-[10px] sm:text-xs px-2 py-0.5 rounded transition-colors"
            :class="searchCurrency === 'USD' ? 'bg-gold/90 text-navy-deep' : 'text-cream/40 hover:text-cream/70'"
          >USD</button>
          <button
            @click="setSearchCurrency('ARS_OFICIAL')"
            class="font-mono text-[10px] sm:text-xs px-2 py-0.5 rounded transition-colors"
            :class="searchCurrency === 'ARS_OFICIAL' ? 'bg-gold/90 text-navy-deep' : 'text-cream/40 hover:text-cream/70'"
          >ARS</button>
          <button
            @click="setSearchCurrency('ARS_BLUE')"
            class="font-mono text-[10px] sm:text-xs px-2 py-0.5 rounded transition-colors"
            :class="searchCurrency === 'ARS_BLUE' ? 'bg-gold/90 text-navy-deep' : 'text-cream/40 hover:text-cream/70'"
          >ARS Blue</button>
          <span v-if="searchCurrency === 'ARS_OFICIAL' && searchExchangeRates.oficial" class="font-mono text-[10px] sm:text-[11px] text-cream/25 ml-2">
            Dólar oficial: ${{ formatPrice(searchExchangeRates.oficial) }} ARS/USD
          </span>
          <span v-if="searchCurrency === 'ARS_BLUE' && searchExchangeRates.blue" class="font-mono text-[10px] sm:text-[11px] text-cream/25 ml-2">
            Dólar blue: ${{ formatPrice(searchExchangeRates.blue) }} ARS/USD
          </span>
        </div>

        <!-- Search source toggle -->
        <div v-if="searchResults.length" class="flex items-center gap-2 mb-3 sm:mb-4 flex-wrap" data-testid="search-source-toggle">
          <span class="font-mono text-[10px] sm:text-xs text-cream/30">Fuente:</span>
          <button
            @click="setSearchSource('cca')"
            data-testid="search-source-cca"
            class="font-mono text-[10px] sm:text-xs px-2 py-0.5 rounded transition-colors"
            :class="searchSource === 'cca' ? 'bg-gold/90 text-navy-deep' : 'text-cream/40 hover:text-cream/70'"
          >API</button>
          <button
            @click="setSearchSource('acara')"
            data-testid="search-source-acara"
            class="font-mono text-[10px] sm:text-xs px-2 py-0.5 rounded transition-colors"
            :class="searchSource === 'acara' ? 'bg-gold/90 text-navy-deep' : 'text-cream/40 hover:text-cream/70'"
          >ACARA</button>
          <button
            @click="setSearchSource('infoauto')"
            data-testid="search-source-infoauto"
            class="font-mono text-[10px] sm:text-xs px-2 py-0.5 rounded transition-colors"
            :class="searchSource === 'infoauto' ? 'bg-gold/90 text-navy-deep' : 'text-cream/40 hover:text-cream/70'"
          >InfoAuto</button>
        </div>

        <!-- Search year filter -->
        <div v-if="searchResults.length && availableSearchYears.length > 1" class="flex items-center gap-2 mb-3 sm:mb-4">
          <span class="font-mono text-[10px] sm:text-xs text-cream/30">Año:</span>
          <select
            :value="searchYearFilter"
            @change="searchYearFilter = $event.target.value === '' ? null : Number($event.target.value); currentSearchPage = 1"
            class="font-mono text-[10px] sm:text-xs bg-cream/[0.04] border border-cream/10 rounded px-2 py-1 text-cream/70 outline-none focus:border-gold/40 transition-colors duration-300 cursor-pointer appearance-none pr-6"
            style="background-image: url('data:image/svg+xml;charset=UTF-8,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%2212%22 height=%2212%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22rgba(255,255,255,0.3)%22 stroke-width=%222%22%3E%3Cpath d=%22M6 9l6 6 6-6%22/%3E%3C/svg%3E'); background-repeat: no-repeat; background-position: right 6px center;"
          >
            <option value="" class="bg-[#0a1628] text-cream/70">Todos</option>
            <option v-for="year in availableSearchYears" :key="year" :value="year" class="bg-[#0a1628] text-cream/70">
              {{ year === 0 ? '0 km' : year }}
            </option>
          </select>
          <span v-if="searchYearFilter !== null" class="font-mono text-[10px] sm:text-[11px] text-cream/25">
            {{ filteredSearchResults.length }} resultado{{ filteredSearchResults.length !== 1 ? 's' : '' }}
          </span>
        </div>

        <!-- Search results (with reference price) -->
        <div v-if="filteredSearchResults.length" class="space-y-2.5 sm:space-y-3">
          <div
            v-for="r in paginatedSearchResults"
            :key="r.version_id ?? r.external_id"
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
              <span class="font-mono text-sm sm:text-base text-gold">
                {{ searchCurrency === 'USD' ? 'US$' : '$' }}{{ searchRowPrice(r) }}
                <span v-if="r.price_origin === 'predicted'" class="text-cream/40 text-[10px] sm:text-[11px] ml-0.5 font-mono">(estimado)</span>
              </span>
              <span class="block font-mono text-xs sm:text-sm text-cream mt-1">{{ r.price_year === 0 ? '0 km' : r.price_year }}</span>
            </div>
            <div v-else-if="searchSource === 'infoauto' || searchSource === 'acara'" class="shrink-0 text-right">
              <span class="font-mono text-sm sm:text-base text-cream/30">—</span>
              <span class="block font-mono text-xs sm:text-sm text-cream/40 mt-1">sin dato</span>
            </div>
            <svg class="w-4 h-4 shrink-0 text-cream/15 group-hover:text-gold/60 transition-colors duration-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg>
          </div>
        </div>

        <!-- Search pagination -->
        <div v-if="filteredSearchResults.length && totalSearchPages > 1" class="flex items-center justify-center gap-1.5 mt-6">
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
        <div v-if="searchResults.length && !filteredSearchResults.length" class="mt-4 sm:mt-6">
          <p class="text-cream/30 text-sm sm:text-base">Sin resultados para el año seleccionado</p>
        </div>
      </section>

      <!-- Divider -->
      <div class="border-t border-cream/8"></div>

      <!-- Price Explorer -->
      <PriceExplorer />

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
        <div v-if="currentStep === 'valuations'" class="flex items-center gap-2 mb-4 sm:mb-6 flex-wrap">
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
          <button
            @click="setSource('infoauto')"
            class="font-mono text-[10px] sm:text-xs px-2 py-0.5 rounded transition-colors"
            :class="activeSource === 'infoauto' ? 'bg-gold/90 text-navy-deep' : 'text-cream/40 hover:text-cream/70'"
          >InfoAuto</button>
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
              <span v-if="activeSource === 'infoauto' && val.infoauto_price != null" class="font-mono text-sm sm:text-base text-gold text-right">
                {{ activeCurrency === 'USD' ? 'US$' : '$' }}{{ formatPrice(infoautoDisplay(val)) }}
                <span v-if="val.infoauto_price_origin === 'predicted'" class="text-cream/40 text-[10px] sm:text-xs ml-1">(estimado)</span>
              </span>
              <span v-else-if="activeSource === 'infoauto'" class="font-mono text-sm sm:text-base text-cream/30 text-right">—</span>
              <span v-else-if="activeSource === 'acara' && val.acara_price != null" class="font-mono text-sm sm:text-base text-gold text-right">{{ activeCurrency === 'USD' ? 'US$' : '$' }}{{ formatPrice(val.acara_price) }}</span>
              <span v-else class="font-mono text-sm sm:text-base text-gold text-right">{{ activeCurrency === 'USD' ? 'US$' : '$' }}{{ formatPrice(val.price) }}</span>
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

      <!-- Divider -->
      <div class="border-t border-cream/8"></div>

      <!-- FAQ -->
      <FaqSection />

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
      <div class="relative w-full max-w-md bg-navy-deep border border-cream/10 rounded-xl p-6 sm:p-8 shadow-2xl max-h-[90vh] overflow-y-auto">
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

        <!-- Loading -->
        <div v-if="modalLoading" class="text-cream/40 text-sm text-center py-6">Cargando precios...</div>

        <!-- Valuations table -->
        <div v-else-if="modalValuations.length" class="bg-cream/[0.03] border border-cream/6 rounded-lg overflow-hidden">
          <div class="grid grid-cols-2 px-4 py-2.5 border-b border-cream/6">
            <span class="font-mono text-[10px] sm:text-xs text-cream/30 uppercase tracking-wider">Año</span>
            <span class="font-mono text-[10px] sm:text-xs text-cream/30 uppercase tracking-wider text-right">
              Precio ({{ searchCurrency === 'USD' ? 'USD' : 'ARS' }})
            </span>
          </div>
          <div
            v-for="val in modalValuations"
            :key="`${val.version_id ?? val.external_id}-${val.year}`"
            class="grid grid-cols-2 px-4 py-2.5 border-b border-cream/4 last:border-0"
          >
            <span class="font-mono text-sm text-cream/70">{{ val.year === 0 ? '0 km' : val.year }}</span>
            <template v-if="modalRowPrice(val).price != null">
              <span class="font-mono text-sm text-gold text-right">
                {{ searchCurrency === 'USD' ? 'US$' : '$' }}{{ formatPrice(modalRowPrice(val).price) }}
                <span v-if="modalRowPrice(val).origin === 'predicted'" class="text-cream/40 text-[10px] ml-1">(estimado)</span>
              </span>
            </template>
            <span v-else class="font-mono text-sm text-cream/30 text-right">—</span>
          </div>
        </div>

        <!-- Empty -->
        <div v-else class="text-cream/30 text-sm text-center py-6">Sin valuaciones disponibles.</div>

        <!-- Exchange rate note -->
        <div v-if="searchCurrency !== 'USD' && (searchExchangeRates.oficial || searchExchangeRates.blue)" class="mt-3 font-mono text-[10px] sm:text-[11px] text-cream/25">
          Dólar {{ searchCurrency === 'ARS_BLUE' ? 'blue' : 'oficial' }}:
          ${{ formatPrice(searchCurrency === 'ARS_BLUE' ? searchExchangeRates.blue : searchExchangeRates.oficial) }} ARS/USD
        </div>

        <!-- Version raw -->
        <div class="mt-4 sm:mt-5">
          <span class="font-mono text-[10px] sm:text-xs text-cream/20 tracking-wider uppercase">Nombre tecnico</span>
          <p class="font-mono text-xs sm:text-sm text-cream/40 mt-0.5">{{ modalResult.version_raw }}</p>
        </div>
      </div>
    </div>
  </Teleport>

  <!-- Contact Modal -->
  <Teleport to="body">
    <Transition name="contact-modal">
      <div
        v-if="showContact"
        @click.self="closeContact"
        class="fixed inset-0 z-50 flex items-center justify-center px-4 bg-black/70 backdrop-blur-md"
      >
        <div class="relative w-full max-w-lg bg-navy-deep border border-gold/15 rounded-xl p-6 sm:p-8 shadow-[0_0_60px_rgba(221,168,83,0.08)]">
          <!-- Close -->
          <button @click="closeContact" class="absolute top-4 right-4 text-cream/30 hover:text-cream/70 transition-colors cursor-pointer">
            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
          </button>

          <!-- Header -->
          <div class="mb-6">
            <span class="font-mono text-[10px] sm:text-xs text-gold/60 tracking-[0.2em] uppercase">Contacto</span>
            <h3 class="text-xl sm:text-2xl font-light text-cream mt-1">Enviame un mensaje</h3>
            <p class="text-xs sm:text-sm text-cream/30 mt-1">Sugerencias, bugs, ideas — todo suma.</p>
          </div>

          <!-- Success state -->
          <div v-if="contactSuccess" class="text-center py-8">
            <div class="w-12 h-12 rounded-full bg-gold/15 border border-gold/30 flex items-center justify-center mx-auto mb-4">
              <svg class="w-6 h-6 text-gold" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            <p class="text-cream text-base font-light">Mensaje enviado</p>
            <p class="text-cream/30 text-sm mt-1">Gracias por escribir, te respondo pronto.</p>
          </div>

          <!-- Form -->
          <form v-else @submit.prevent="submitContact" class="space-y-4">
            <!-- Honeypot — invisible to humans -->
            <input type="text" name="website" v-model="contactForm.website" class="absolute opacity-0 pointer-events-none h-0 w-0 -z-10" tabindex="-1" autocomplete="off" />

            <div>
              <label class="font-mono text-[10px] sm:text-xs text-cream/40 tracking-wider uppercase block mb-1.5">Nombre</label>
              <input
                v-model="contactForm.name"
                type="text"
                required
                maxlength="100"
                class="w-full bg-cream/[0.04] border border-cream/10 rounded-lg px-3.5 py-2.5 text-sm text-cream placeholder-cream/20 outline-none focus:border-gold/40 transition-colors duration-300"
                placeholder="Tu nombre"
              />
            </div>

            <div>
              <label class="font-mono text-[10px] sm:text-xs text-cream/40 tracking-wider uppercase block mb-1.5">Email</label>
              <input
                v-model="contactForm.email"
                type="email"
                required
                maxlength="255"
                class="w-full bg-cream/[0.04] border border-cream/10 rounded-lg px-3.5 py-2.5 text-sm text-cream placeholder-cream/20 outline-none focus:border-gold/40 transition-colors duration-300"
                placeholder="tu@email.com"
              />
            </div>

            <div>
              <label class="font-mono text-[10px] sm:text-xs text-cream/40 tracking-wider uppercase block mb-1.5">Mensaje</label>
              <textarea
                v-model="contactForm.message"
                required
                maxlength="2000"
                rows="4"
                style="resize: none;"
                class="w-full bg-cream/[0.04] border border-cream/10 rounded-lg px-3.5 py-2.5 text-sm text-cream placeholder-cream/20 outline-none focus:border-gold/40 transition-colors duration-300"
                placeholder="Escribí tu mensaje..."
              ></textarea>
            </div>

            <!-- Turnstile widget -->
            <div ref="turnstileRef"></div>

            <!-- Error -->
            <p v-if="contactError" class="font-mono text-[11px] text-red-400/80">{{ contactError }}</p>

            <!-- Submit -->
            <button
              type="submit"
              :disabled="contactSending"
              class="w-full flex items-center justify-center gap-2 px-5 py-3 rounded-lg bg-gold/15 border border-gold/25 text-gold text-sm font-medium hover:bg-gold/20 hover:border-gold/35 active:bg-gold/25 disabled:opacity-40 disabled:cursor-not-allowed transition-all duration-300 cursor-pointer"
            >
              <div v-if="contactSending" class="w-3.5 h-3.5 border border-gold/50 border-t-gold rounded-full animate-spin"></div>
              <span>{{ contactSending ? 'Enviando...' : 'Enviar mensaje' }}</span>
            </button>
          </form>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, reactive, computed, watch, nextTick, onMounted, onUnmounted } from 'vue'
import RankingTable from './components/RankingTable.vue'
import PriceExplorer from './components/PriceExplorer.vue'
import FaqSection from './components/FaqSection.vue'

const baseUrl = window.location.origin

// GitHub stars
const githubStars = ref(null)
fetch('https://api.github.com/repos/SantiEvangelista/autos-api')
  .then(r => r.ok ? r.json() : null)
  .then(data => { if (data) githubStars.value = data.stargazers_count })
  .catch(() => {})

// Stats (server-side injected to avoid NaN on SSR/crawl)
const serverStats = window.__STATS__
const stats = reactive({
  brands: serverStats ? Number(serverStats.brands).toLocaleString('es-AR') : '...',
  models: serverStats ? Number(serverStats.models).toLocaleString('es-AR') : '...',
  versions: serverStats ? Number(serverStats.versions).toLocaleString('es-AR') : '...',
})

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
  if (res.brands != null && !isNaN(Number(res.brands))) {
    stats.brands = Number(res.brands).toLocaleString('es-AR')
  }
  if (res.models != null && !isNaN(Number(res.models))) {
    stats.models = Number(res.models).toLocaleString('es-AR')
  }
  if (res.versions != null && !isNaN(Number(res.versions))) {
    stats.versions = Number(res.versions).toLocaleString('es-AR')
  }
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
  if (activeSource.value === 'infoauto') params.set('sources', 'infoauto')
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

function infoautoDisplay(val) {
  if (activeCurrency.value === 'ARS' && val.infoauto_price_raw_ars != null) {
    return val.infoauto_price_raw_ars
  }
  return val.infoauto_price
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

// Search
const searchQuery = ref('')
const searchResults = ref([])
const searching = ref(false)
const searchCurrency = ref('USD')
const searchSource = ref('cca')
const searchExchangeRates = ref({ oficial: null, blue: null })
let searchTimeout = null

async function fetchSearchExchangeRates() {
  if (searchExchangeRates.value.oficial) return
  try {
    const res = await fetch('/api/v1/exchange-rates')
    const data = await res.json()
    searchExchangeRates.value = {
      oficial: data.oficial,
      blue: data.blue,
    }
  } catch {
    searchExchangeRates.value = { oficial: null, blue: null }
  }
}

async function setSearchCurrency(currency) {
  searchCurrency.value = currency
  if (currency !== 'USD' && !searchExchangeRates.value.oficial) {
    await fetchSearchExchangeRates()
  }
}

function formatSearchPrice(priceUsd) {
  const rate = searchCurrency.value === 'ARS_BLUE' ? searchExchangeRates.value.blue : searchExchangeRates.value.oficial
  if (searchCurrency.value !== 'USD' && rate) {
    return formatPrice(Number(priceUsd) * rate)
  }
  return formatPrice(priceUsd)
}

// Para ARS_OFICIAL + source con raw_ars: usar el valor absoluto ARS directo
// Fallback al USD-reconvertido cuando no hay raw.
function searchRowPrice(r) {
  if (searchCurrency.value === 'ARS_OFICIAL' && r.price_raw_ars != null) {
    return formatPrice(r.price_raw_ars)
  }
  return formatSearchPrice(r.price)
}

// Search year filter
const searchYearFilter = ref(null)

const availableSearchYears = computed(() => {
  const years = [...new Set(searchResults.value.map(r => r.price_year).filter(y => y !== null))]
  years.sort((a, b) => {
    if (a === 0) return -1
    if (b === 0) return 1
    return b - a
  })
  return years
})

const filteredSearchResults = computed(() => {
  if (searchYearFilter.value === null) return searchResults.value
  return searchResults.value.filter(r => r.price_year === searchYearFilter.value)
})

// Search pagination
const ITEMS_PER_PAGE = 6
const currentSearchPage = ref(1)

const paginatedSearchResults = computed(() => {
  const start = (currentSearchPage.value - 1) * ITEMS_PER_PAGE
  return filteredSearchResults.value.slice(start, start + ITEMS_PER_PAGE)
})

const totalSearchPages = computed(() =>
  Math.ceil(filteredSearchResults.value.length / ITEMS_PER_PAGE)
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
    searchYearFilter.value = null
    return
  }
  searching.value = true
  currentSearchPage.value = 1
  searchYearFilter.value = null
  searchTimeout = setTimeout(() => { runSearch() }, 300)
}

async function runSearch() {
  searching.value = true
  try {
    const sourceParam = searchSource.value !== 'cca' ? `&source=${searchSource.value}` : ''
    const res = await apiFetch(`/api/v1/search?q=${encodeURIComponent(searchQuery.value)}&per_page=50${sourceParam}`)
    searchResults.value = res.data
  } finally {
    searching.value = false
  }
}

async function setSearchSource(source) {
  if (searchSource.value === source) return
  searchSource.value = source
  currentSearchPage.value = 1
  searchYearFilter.value = null
  if (searchQuery.value.length >= 2) {
    await runSearch()
  }
}

// Modal
const modalResult = ref(null)
const modalValuations = ref([])
const modalMeta = ref(null)
const modalLoading = ref(false)

async function openModal(result) {
  modalResult.value = result
  modalValuations.value = []
  modalMeta.value = null
  modalLoading.value = true
  try {
    // source=infoauto: el read model usa external_id (ia_<id>), no version_id.
    // Ir al endpoint dedicado y mapear la grilla al shape que el modal espera
    // (las keys que consume modalRowPrice: infoauto_price, infoauto_price_raw_ars,
    // infoauto_price_origin).
    if (result.external_id) {
      const params = new URLSearchParams()
      if (searchCurrency.value === 'ARS_OFICIAL') params.set('currency', 'ars')
      const qs = params.toString() ? `?${params.toString()}` : ''
      const res = await apiFetch(`/api/v1/infoauto/catalog/${result.external_id}/prices${qs}`)
      modalValuations.value = (res.data || []).map((r) => ({
        external_id: result.external_id,
        year: r.year,
        // price del shape legacy = USD; mantenemos para compatibilidad con el render genérico.
        price: r.price,
        // Los tres campos que `modalRowPrice(val)` consume para source=infoauto.
        infoauto_price: r.price,
        infoauto_price_raw_ars:
          r.price_ars_thousands != null ? Number(r.price_ars_thousands) * 1000 : null,
        infoauto_price_origin: r.origin,
      }))
      modalMeta.value = res.meta || null
      return
    }

    // source=cca | source=acara: endpoint legacy /versions/{id}/valuations.
    const params = new URLSearchParams()
    if (searchCurrency.value === 'ARS_OFICIAL') params.set('currency', 'ars')
    if (searchSource.value === 'acara') params.set('sources', 'acara')
    const qs = params.toString() ? `?${params.toString()}` : ''
    const res = await apiFetch(`/api/v1/versions/${result.version_id}/valuations${qs}`)
    modalValuations.value = res.data || []
    modalMeta.value = res.meta || null
  } finally {
    modalLoading.value = false
  }
}

function closeModal() {
  modalResult.value = null
  modalValuations.value = []
  modalMeta.value = null
}

// Precio a mostrar en una fila del modal segun source/currency activo.
// Retorna { price: number|null, origin: 'real'|'predicted'|null }.
function modalRowPrice(val) {
  if (searchSource.value === 'infoauto') {
    const real = val.infoauto_price
    if (real == null) return { price: null, origin: null }
    if (searchCurrency.value === 'ARS_OFICIAL' && val.infoauto_price_raw_ars != null) {
      return { price: Number(val.infoauto_price_raw_ars), origin: val.infoauto_price_origin }
    }
    if (searchCurrency.value === 'ARS_BLUE' && searchExchangeRates.value.blue) {
      // infoauto_price viene en USD cuando no pedimos currency=ars. Pero con sources=infoauto
      // y currency=ars_oficial, viene convertido a ARS oficial. Para ARS_BLUE reconstruimos
      // desde raw (absoluto) o desde USD pidiendo sin currency. Simple: en ARS_BLUE, el
      // fetch no envia currency=ars, asi que infoauto_price esta en USD. Multiplicamos por blue.
      return { price: Number(real) * searchExchangeRates.value.blue, origin: val.infoauto_price_origin }
    }
    return { price: Number(real), origin: val.infoauto_price_origin }
  }
  if (searchSource.value === 'acara') {
    if (val.acara_price == null) return { price: null, origin: null }
    if (searchCurrency.value === 'ARS_BLUE' && searchExchangeRates.value.blue) {
      return { price: Number(val.acara_price) * searchExchangeRates.value.blue, origin: null }
    }
    return { price: Number(val.acara_price), origin: null }
  }
  // cca: val.price esta en USD (o ARS oficial si pedimos currency=ars).
  if (searchCurrency.value === 'ARS_BLUE' && searchExchangeRates.value.blue) {
    return { price: Number(val.price) * searchExchangeRates.value.blue, origin: null }
  }
  return { price: Number(val.price), origin: null }
}

// Re-fetch modal valuations cuando el usuario cambia moneda/fuente con el modal abierto.
watch([searchCurrency, searchSource], () => {
  if (modalResult.value) {
    openModal(modalResult.value)
  }
})

// Contact modal
const showContact = ref(false)
const contactSending = ref(false)
const contactSuccess = ref(false)
const contactError = ref(null)
const contactForm = reactive({ name: '', email: '', message: '', website: '' })
const turnstileRef = ref(null)
let turnstileWidgetId = null
let turnstileToken = null

function renderTurnstile() {
  if (!turnstileRef.value || !window.turnstile) return
  if (turnstileWidgetId !== null) {
    window.turnstile.reset(turnstileWidgetId)
    return
  }
  turnstileWidgetId = window.turnstile.render(turnstileRef.value, {
    sitekey: window.__TURNSTILE_SITE_KEY__,
    theme: 'dark',
    callback: (token) => { turnstileToken = token },
    'expired-callback': () => { turnstileToken = null },
  })
}

function closeContact() {
  showContact.value = false
  contactSuccess.value = false
  contactError.value = null
  if (turnstileWidgetId !== null && window.turnstile) {
    window.turnstile.remove(turnstileWidgetId)
    turnstileWidgetId = null
  }
  turnstileToken = null
}

watch(showContact, (val) => {
  if (val) {
    nextTick(() => {
      const tryRender = () => {
        if (window.turnstile) renderTurnstile()
        else setTimeout(tryRender, 200)
      }
      tryRender()
    })
  }
})

async function submitContact() {
  contactError.value = null

  if (!turnstileToken) {
    contactError.value = 'Esperá a que se complete la verificación de seguridad.'
    return
  }

  contactSending.value = true
  try {
    const res = await fetch('/contact', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify({
        name: contactForm.name,
        email: contactForm.email,
        message: contactForm.message,
        website: contactForm.website,
        cf_turnstile_response: turnstileToken,
      }),
    })
    const data = await res.json()
    if (!res.ok) {
      if (data.errors) {
        const firstError = Object.values(data.errors).flat()[0]
        contactError.value = firstError || 'Error al enviar. Intentá de nuevo.'
      } else {
        contactError.value = data.message || 'Error al enviar. Intentá de nuevo.'
      }
      return
    }
    contactSuccess.value = true
    contactForm.name = ''
    contactForm.email = ''
    contactForm.message = ''
  } catch {
    contactError.value = 'Error de conexión. Intentá de nuevo.'
  } finally {
    contactSending.value = false
  }
}

function handleKeydown(e) {
  if (e.key === 'Escape') {
    if (showContact.value) closeContact()
    else if (modalResult.value) closeModal()
  }
}
onMounted(() => document.addEventListener('keydown', handleKeydown))
onUnmounted(() => document.removeEventListener('keydown', handleKeydown))

watch(modalResult, (val) => { document.body.style.overflow = val ? 'hidden' : '' })
watch(showContact, (val) => { document.body.style.overflow = val ? 'hidden' : '' })
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

/* Contact modal transition */
.contact-modal-enter-active { transition: opacity 0.25s ease; }
.contact-modal-enter-active > div { transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.25s ease; }
.contact-modal-leave-active { transition: opacity 0.15s ease; }
.contact-modal-leave-active > div { transition: transform 0.15s ease, opacity 0.15s ease; }
.contact-modal-enter-from { opacity: 0; }
.contact-modal-enter-from > div { transform: translateY(16px) scale(0.97); opacity: 0; }
.contact-modal-leave-to { opacity: 0; }
.contact-modal-leave-to > div { transform: translateY(8px) scale(0.98); opacity: 0; }

/* Custom scrollbar */
::-webkit-scrollbar { width: 4px; }
::-webkit-scrollbar-track { background: transparent; }
::-webkit-scrollbar-thumb { background: rgba(245, 238, 220, 0.1); border-radius: 2px; }
::-webkit-scrollbar-thumb:hover { background: rgba(245, 238, 220, 0.2); }
</style>
