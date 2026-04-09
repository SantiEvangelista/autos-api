import { ref, reactive, computed } from 'vue'

const config = window.__BODY_TYPES__ || { prefixes: {}, labels: {}, fuel_keywords: {}, fuel_labels: {} }
const prefixEntries = Object.entries(config.prefixes)
const fuelEntries = Object.entries(config.fuel_keywords)

function classifyBodyType(versionRaw) {
  const found = prefixEntries.find(([prefix]) => versionRaw.startsWith(prefix))
  return found ? found[1] : null
}

function classifyFuel(versionRaw) {
  const upper = versionRaw.toUpperCase()
  const found = fuelEntries.find(([keyword]) => upper.includes(keyword.toUpperCase()))
  return found ? found[1] : 'nafta'
}

function enrichResult(result) {
  return {
    ...result,
    bodyType: classifyBodyType(result.version_raw),
    fuel: classifyFuel(result.version_raw),
  }
}

export function usePriceExplorer() {
  const baseUrl = window.location.origin

  // Core state
  const results = ref([])
  const loading = ref(false)
  const hasSearched = ref(false)
  const budget = ref(30000)
  const budgetYear = ref(null)
  const availableYears = [null, 0, 2025, 2024, 2023, 2022, 2021, 2020]

  // Filter state
  const selectedBodyType = ref(null)
  const selectedBrand = ref(null)
  const selectedFuel = ref(null)

  // Pagination
  const ITEMS_PER_PAGE = 6
  const currentPage = ref(1)

  // Currency
  const currency = ref('USD')
  const exchangeRate = ref(null)

  // Debounce
  let timeout = null

  // --- Fetch ---

  function onBudgetChange() {
    clearTimeout(timeout)
    loading.value = true
    timeout = setTimeout(() => fetchByPrice(), 400)
  }

  async function fetchByPrice() {
    const maxPrice = budget.value
    const minPrice = Math.round(maxPrice * 0.85)
    const params = new URLSearchParams({ max_price: maxPrice, min_price: minPrice, per_page: '50' })
    if (budgetYear.value !== null) params.set('year', budgetYear.value)

    loading.value = true
    hasSearched.value = true
    resetFilters()

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

      results.value = [...seen.values()]
        .sort((a, b) => a.price - b.price)
        .map(enrichResult)
    } catch {
      results.value = []
    } finally {
      loading.value = false
    }
  }

  // --- Filters ---

  function resetFilters() {
    selectedBodyType.value = null
    selectedBrand.value = null
    selectedFuel.value = null
    currentPage.value = 1
  }

  function setBodyType(slug) {
    selectedBodyType.value = selectedBodyType.value === slug ? null : slug
    currentPage.value = 1
  }

  function setBrand(slug) {
    selectedBrand.value = selectedBrand.value === slug ? null : slug
    currentPage.value = 1
  }

  function setFuel(slug) {
    selectedFuel.value = selectedFuel.value === slug ? null : slug
    currentPage.value = 1
  }

  // --- Computeds ---

  const filteredResults = computed(() => {
    let filtered = results.value

    if (selectedBodyType.value) {
      filtered = filtered.filter(r => r.bodyType === selectedBodyType.value)
    }
    if (selectedBrand.value) {
      filtered = filtered.filter(r => r.brand_slug === selectedBrand.value)
    }
    if (selectedFuel.value) {
      filtered = filtered.filter(r => r.fuel === selectedFuel.value)
    }

    return filtered
  })

  const paginatedResults = computed(() => {
    const start = (currentPage.value - 1) * ITEMS_PER_PAGE
    return filteredResults.value.slice(start, start + ITEMS_PER_PAGE)
  })

  const totalPages = computed(() =>
    Math.ceil(filteredResults.value.length / ITEMS_PER_PAGE)
  )

  const availableBrands = computed(() => {
    const brands = new Map()
    for (const r of results.value) {
      if (!brands.has(r.brand_slug)) {
        brands.set(r.brand_slug, r.brand)
      }
    }
    return [...brands.entries()]
      .map(([slug, name]) => ({ value: slug, label: name }))
      .sort((a, b) => a.label.localeCompare(b.label))
  })

  const availableBodyTypes = computed(() => {
    const types = new Set()
    for (const r of results.value) {
      if (r.bodyType) types.add(r.bodyType)
    }
    return [...types]
      .map(slug => ({ value: slug, label: config.labels[slug] || slug }))
      .sort((a, b) => a.label.localeCompare(b.label))
  })

  const availableFuels = computed(() => {
    const fuels = new Set()
    for (const r of results.value) {
      fuels.add(r.fuel)
    }
    return [...fuels]
      .map(slug => ({ value: slug, label: config.fuel_labels[slug] || slug }))
      .sort((a, b) => a.label.localeCompare(b.label))
  })

  const resultCount = computed(() => filteredResults.value.length)

  // --- Pagination ---

  function goToPage(page) {
    if (page >= 1 && page <= totalPages.value) {
      currentPage.value = page
    }
  }

  // --- Currency ---

  async function fetchExchangeRate() {
    if (exchangeRate.value) return
    try {
      const res = await fetch('https://api.bluelytics.com.ar/v2/latest')
      const data = await res.json()
      exchangeRate.value = data.oficial.value_sell
    } catch {
      exchangeRate.value = null
    }
  }

  async function setCurrency(c) {
    currency.value = c
    if (c === 'ARS' && !exchangeRate.value) {
      await fetchExchangeRate()
    }
  }

  function formatPrice(price) {
    return Number(price).toLocaleString('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
  }

  function formatDisplayPrice(priceUsd) {
    if (currency.value === 'ARS' && exchangeRate.value) {
      return formatPrice(Number(priceUsd) * exchangeRate.value)
    }
    return formatPrice(priceUsd)
  }

  return {
    // State
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

    // Computeds
    filteredResults,
    paginatedResults,
    totalPages,
    availableBrands,
    availableBodyTypes,
    availableFuels,
    resultCount,

    // Actions
    onBudgetChange,
    fetchByPrice,
    setBodyType,
    setBrand,
    setFuel,
    goToPage,
    setCurrency,
    formatPrice,
    formatDisplayPrice,
  }
}
