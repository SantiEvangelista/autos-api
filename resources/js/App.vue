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
        <div class="flex items-center gap-2 mb-5 sm:mb-6 hero-fade" style="animation-delay: 0ms">
          <span class="font-mono text-[10px] sm:text-xs text-gold/80 tracking-[0.2em] sm:tracking-[0.3em] uppercase">API Pública</span>
          
        </div>
        <h1 class="text-[2.5rem] leading-[1] sm:text-6xl lg:text-7xl xl:text-8xl font-light text-cream mb-5 sm:mb-7 hero-fade" style="animation-delay: 80ms">
          Arg <br>
          <span class="font-semibold text-gold">Autos API</span>
        </h1>
        <p class="text-base sm:text-lg text-cream/45 max-w-md sm:max-w-xl leading-relaxed font-light hero-fade" style="animation-delay: 160ms">
          Consultá precios del mercado automotor argentino por marca, modelo, versión y año.
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

      <!-- Endpoints -->
      <section class="py-12 sm:py-16 lg:py-20">
        <p class="font-mono text-[10px] sm:text-xs text-cream/30 tracking-[0.2em] sm:tracking-[0.3em] uppercase mb-6 sm:mb-10">Endpoints</p>

        <div class="space-y-0">
          <div
            v-for="(ep, i) in endpoints"
            :key="i"
            class="group flex flex-col gap-1.5 sm:flex-row sm:items-center sm:gap-6 py-3.5 sm:py-4 border-b border-cream/6 active:border-gold/20 hover:border-gold/20 transition-colors duration-500"
          >
            <div class="flex items-center gap-2.5 sm:gap-3 shrink-0 min-w-0">
              <span class="font-mono text-[10px] sm:text-[11px] font-medium text-navy-deep bg-gold/90 px-1.5 sm:px-2 py-0.5 rounded shrink-0">GET</span>
              <code class="font-mono text-xs sm:text-sm text-cream/80 group-hover:text-gold transition-colors duration-300 truncate">{{ ep.path }}</code>
            </div>
            <span class="text-xs sm:text-sm text-cream/30 sm:ml-auto pl-7 sm:pl-0">{{ ep.desc }}</span>
          </div>
        </div>

        <!-- Base URL hint -->
        <div class="mt-6 sm:mt-10 flex items-center gap-2.5 sm:gap-3">
          <span class="font-mono text-[10px] sm:text-xs text-cream/20">Base URL</span>
          <code class="font-mono text-[10px] sm:text-xs text-cream/40 bg-cream/5 px-2.5 sm:px-3 py-1 sm:py-1.5 rounded truncate">{{ baseUrl }}/api/v1</code>
        </div>
      </section>

      <!-- Divider -->
      <div class="border-t border-cream/8"></div>

      <!-- Live Explorer -->
      <section id="explorador" class="py-12 sm:py-16 lg:py-20">
        <p class="font-mono text-[10px] sm:text-xs text-cream/30 tracking-[0.2em] sm:tracking-[0.3em] uppercase mb-2 sm:mb-3">Explorador</p>
        <h2 class="text-2xl sm:text-3xl lg:text-4xl font-light text-cream mb-1.5 sm:mb-2">Probá la API en vivo</h2>
        <p class="text-xs sm:text-sm text-cream/30 mb-8 sm:mb-12">Seleccioná una marca para comenzar a navegar.</p>

        <!-- Breadcrumb -->
        <div class="flex items-center gap-1.5 sm:gap-2 mb-6 sm:mb-8 flex-wrap" v-if="selected.brand">
          <button
            @click="resetTo('brands')"
            class="font-mono text-[11px] sm:text-xs text-gold/70 active:text-gold hover:text-gold transition-colors"
          >
            Marcas
          </button>
          <template v-if="selected.brand">
            <span class="text-cream/20 text-xs">/</span>
            <button
              @click="resetTo('models')"
              class="font-mono text-[11px] sm:text-xs transition-colors"
              :class="selected.model ? 'text-gold/70 active:text-gold hover:text-gold' : 'text-cream/70'"
            >
              {{ selected.brand.name }}
            </button>
          </template>
          <template v-if="selected.model">
            <span class="text-cream/20 text-xs">/</span>
            <button
              @click="resetTo('versions')"
              class="font-mono text-[11px] sm:text-xs transition-colors"
              :class="selected.version ? 'text-gold/70 active:text-gold hover:text-gold' : 'text-cream/70'"
            >
              {{ selected.model.name }}
            </button>
          </template>
          <template v-if="selected.version">
            <span class="text-cream/20 text-xs">/</span>
            <span class="font-mono text-[11px] sm:text-xs text-cream/70 break-all">{{ selected.version.name }}</span>
          </template>
        </div>

        <!-- Currency toggle -->
        <div v-if="currentStep === 'valuations'" class="flex items-center gap-2 mb-4 sm:mb-6">
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

        <!-- Request preview -->
        <div class="bg-navy/60 border border-cream/6 rounded-lg p-3 sm:p-4 mb-6 sm:mb-8 backdrop-blur-sm overflow-hidden">
          <div class="flex items-center gap-2">
            <span class="font-mono text-[10px] sm:text-[11px] font-medium text-navy-deep bg-gold/90 px-1.5 sm:px-2 py-0.5 rounded shrink-0">GET</span>
            <code class="font-mono text-[11px] sm:text-sm text-cream/60 truncate">{{ currentRequestUrl }}</code>
          </div>
          <div v-if="loading" class="flex items-center gap-2 mt-2.5 sm:mt-3">
            <div class="w-3 h-3 border border-gold/50 border-t-gold rounded-full animate-spin"></div>
            <span class="font-mono text-[10px] sm:text-xs text-cream/30">Cargando...</span>
          </div>
        </div>

        <!-- Error banner -->
        <div v-if="error" class="border border-red-500/20 rounded-lg px-3.5 sm:px-4 py-2.5 sm:py-3 mb-4 sm:mb-6 bg-red-500/5">
          <p class="font-mono text-[11px] sm:text-xs text-red-400/80">{{ error }}</p>
        </div>

        <!-- Results grid -->
        <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 gap-1.5 sm:gap-2">
          <!-- Brands -->
          <template v-if="currentStep === 'brands'">
            <button
              v-for="brand in explorer.brands"
              :key="brand.id"
              @click="selectBrand(brand)"
              class="text-left px-3 sm:px-4 py-2.5 sm:py-3 rounded-lg bg-cream/[0.03] border border-cream/6 active:border-gold/30 active:bg-cream/[0.06] hover:border-gold/30 hover:bg-cream/[0.06] transition-all duration-300 group"
            >
              <span class="text-xs sm:text-sm text-cream/80 group-hover:text-cream transition-colors block truncate">{{ brand.name }}</span>
              <span class="block font-mono text-[10px] sm:text-[11px] text-cream/20 mt-0.5 truncate">/{{ brand.slug }}</span>
            </button>
          </template>

          <!-- Models -->
          <template v-if="currentStep === 'models'">
            <button
              v-for="model in explorer.models"
              :key="model.id"
              @click="selectModel(model)"
              class="text-left px-3 sm:px-4 py-2.5 sm:py-3 rounded-lg bg-cream/[0.03] border border-cream/6 active:border-gold/30 active:bg-cream/[0.06] hover:border-gold/30 hover:bg-cream/[0.06] transition-all duration-300 group"
            >
              <span class="text-xs sm:text-sm text-cream/80 group-hover:text-cream transition-colors block truncate">{{ model.name }}</span>
              <span class="block font-mono text-[10px] sm:text-[11px] text-cream/20 mt-0.5">id: {{ model.id }}</span>
            </button>
          </template>

          <!-- Versions -->
          <template v-if="currentStep === 'versions'">
            <button
              v-for="version in explorer.versions"
              :key="version.id"
              @click="selectVersion(version)"
              class="text-left px-3 sm:px-4 py-2.5 sm:py-3 rounded-lg bg-cream/[0.03] border border-cream/6 active:border-gold/30 active:bg-cream/[0.06] hover:border-gold/30 hover:bg-cream/[0.06] transition-all duration-300 group col-span-2 sm:col-span-1"
            >
              <span class="text-xs sm:text-sm text-cream/80 group-hover:text-cream transition-colors block">{{ version.name }}</span>
              <span class="block font-mono text-[10px] sm:text-[11px] text-cream/20 mt-0.5">id: {{ version.id }}</span>
            </button>
          </template>
        </div>

        <!-- Valuations table -->
        <div v-if="currentStep === 'valuations' && explorer.valuations.length" class="mt-2">
          <div class="bg-cream/[0.03] border border-cream/6 rounded-lg overflow-hidden">
            <div class="grid grid-cols-2 px-3 sm:px-4 py-2 sm:py-2.5 border-b border-cream/6">
              <span class="font-mono text-[10px] sm:text-[11px] text-cream/30 uppercase tracking-wider">Año</span>
              <span class="font-mono text-[10px] sm:text-[11px] text-cream/30 uppercase tracking-wider text-right">Precio ({{ activeCurrency }})</span>
            </div>
            <div
              v-for="val in explorer.valuations"
              :key="val.id"
              class="grid grid-cols-2 px-3 sm:px-4 py-2.5 sm:py-3 border-b border-cream/4 last:border-0 hover:bg-cream/[0.03] transition-colors"
            >
              <span class="font-mono text-xs sm:text-sm text-cream/70">{{ val.year === 0 ? '0 km' : val.year }}</span>
              <span class="font-mono text-xs sm:text-sm text-gold text-right">{{ activeCurrency === 'USD' ? 'US$' : '$' }}{{ formatPrice(val.price) }}</span>
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
          <p class="text-cream/30 text-xs sm:text-sm">Sin valuaciones disponibles para esta versión.</p>
        </div>
      </section>

      <!-- Divider -->
      <div class="border-t border-cream/8"></div>

      <!-- Search -->
      <section class="py-12 sm:py-16 lg:py-20">
        <p class="font-mono text-[10px] sm:text-xs text-cream/30 tracking-[0.2em] sm:tracking-[0.3em] uppercase mb-2 sm:mb-3">Búsqueda</p>
        <h2 class="text-2xl sm:text-3xl lg:text-4xl font-light text-cream mb-6 sm:mb-10">Buscar por texto</h2>

        <div class="relative">
          <input
            v-model="searchQuery"
            @input="onSearch"
            type="text"
            placeholder="ej: corolla, hilux 4x4, bmw serie 3..."
            class="w-full bg-cream/[0.04] border border-cream/10 rounded-lg px-3.5 sm:px-4 py-3 sm:py-3.5 font-mono text-xs sm:text-sm text-cream placeholder-cream/20 outline-none focus:border-gold/40 transition-colors duration-300"
          />
          <div v-if="searching" class="absolute right-3 sm:right-4 top-1/2 -translate-y-1/2">
            <div class="w-3.5 h-3.5 sm:w-4 sm:h-4 border border-gold/50 border-t-gold rounded-full animate-spin"></div>
          </div>
        </div>

        <!-- Search results -->
        <div v-if="searchResults.length" class="mt-4 sm:mt-6 space-y-1.5">
          <div
            v-for="r in searchResults"
            :key="r.version_id"
            class="flex flex-col px-3.5 sm:px-4 py-2.5 sm:py-3 rounded-lg bg-cream/[0.03] border border-cream/6"
          >
            <div class="flex items-baseline gap-1.5 sm:gap-2 min-w-0">
              <span class="text-xs sm:text-sm text-gold/80 shrink-0">{{ r.brand }}</span>
              <span class="text-cream/15">&middot;</span>
              <span class="text-xs sm:text-sm text-cream/70 truncate">{{ r.model }}</span>
            </div>
            <span class="text-[11px] sm:text-xs text-cream/40 mt-0.5 truncate">{{ r.version }}</span>
          </div>
        </div>
        <div v-if="searchQuery.length >= 2 && !searching && !searchResults.length" class="mt-4 sm:mt-6">
          <p class="text-cream/30 text-xs sm:text-sm">Sin resultados para "{{ searchQuery }}"</p>
        </div>
      </section>

      <!-- Footer -->
      <footer class="py-8 sm:py-10 border-t border-cream/6">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2 sm:gap-4">
          <div class="flex items-center gap-2">
            <span class="font-mono text-[10px] sm:text-xs text-cream/20">Desarrollado por <a href="https://github.com/SantiEvangelista" target="_blank" rel="noopener" class="hover:text-gold/40 text-gold transition-colors">Santiago Evangelista</a></span>
          </div>
          
          <span class="font-mono text-[10px] sm:text-xs text-cream/15">Cotización USD/ARS por <a href="https://bluelytics.com.ar" target="_blank" rel="noopener" style="color: #03a9f4;" class="hover:text-gold/40 transition-colors">Bluelytics</a></span>
        </div>
      </footer>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'

const baseUrl = window.location.origin

// GitHub stars
const githubStars = ref(null)
fetch('https://api.github.com/repos/SantiEvangelista/autos-api')
  .then(r => r.ok ? r.json() : null)
  .then(data => { if (data) githubStars.value = data.stargazers_count })
  .catch(() => {})

const endpoints = [
  { path: '/api/v1/brands', desc: 'Lista de marcas' },
  { path: '/api/v1/brands/{id}/models', desc: 'Modelos de una marca' },
  { path: '/api/v1/models/{id}/versions', desc: 'Versiones de un modelo' },
  { path: '/api/v1/versions/{id}/valuations', desc: 'Precios por año (USD por defecto)' },
  { path: '/api/v1/versions/{id}/valuations?currency=ars', desc: 'Precios convertidos a ARS (dólar oficial)' },
  { path: '/api/v1/search?q={term}', desc: 'Búsqueda general' },
]

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

const currentRequestUrl = computed(() => {
  if (selected.version) {
    const base = `/api/v1/versions/${selected.version.id}/valuations`
    return activeCurrency.value === 'ARS' ? `${base}?currency=ars` : base
  }
  if (selected.model) return `/api/v1/models/${selected.model.id}/versions`
  if (selected.brand) return `/api/v1/brands/${selected.brand.id}/models`
  return '/api/v1/brands'
})

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
  const res = await apiFetch(`/api/v1/brands/${brand.id}/models`)
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
  const currParam = activeCurrency.value === 'ARS' ? '?currency=ars' : ''
  const res = await apiFetch(`/api/v1/versions/${selected.version.id}/valuations${currParam}`)
  explorer.valuations = res.data
  explorer.meta = res.meta
}

async function setCurrency(currency) {
  activeCurrency.value = currency
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

// Search
const searchQuery = ref('')
const searchResults = ref([])
const searching = ref(false)
let searchTimeout = null

function onSearch() {
  clearTimeout(searchTimeout)
  if (searchQuery.value.length < 2) {
    searchResults.value = []
    return
  }
  searching.value = true
  searchTimeout = setTimeout(async () => {
    try {
      const res = await apiFetch(`/api/v1/search?q=${encodeURIComponent(searchQuery.value)}&per_page=50`)
      searchResults.value = res.data
    } finally {
      searching.value = false
    }
  }, 300)
}
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

/* Custom scrollbar */
::-webkit-scrollbar { width: 4px; }
::-webkit-scrollbar-track { background: transparent; }
::-webkit-scrollbar-thumb { background: rgba(245, 238, 220, 0.1); border-radius: 2px; }
::-webkit-scrollbar-thumb:hover { background: rgba(245, 238, 220, 0.2); }
</style>
