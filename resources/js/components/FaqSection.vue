<template>
  <section class="py-12 sm:py-16 lg:py-20">
    <p class="font-mono text-[10px] sm:text-xs text-cream/30 tracking-[0.2em] sm:tracking-[0.3em] uppercase mb-2 sm:mb-3"></p>
    <h2 class="text-2xl sm:text-3xl lg:text-4xl font-light text-cream mb-1.5 sm:mb-2">Preguntas Frecuentes</h2>
    <p class="text-xs sm:text-sm text-cream/30 mb-8 sm:mb-12">Todo lo que necesitás saber sobre los precios y datos del mercado automotor</p>

    <div class="space-y-2.5 sm:space-y-3">
      <div
        v-for="(item, index) in items"
        :key="index"
        class="rounded-lg bg-cream/[0.03] border transition-all duration-300"
        :class="openIndex === index ? 'border-gold/25 bg-cream/[0.06]' : 'border-cream/6 hover:border-gold/25 hover:bg-cream/[0.06]'"
      >
        <button
          @click="toggle(index)"
          class="w-full flex items-center justify-between gap-4 px-4 sm:px-5 py-3.5 sm:py-4 text-left cursor-pointer"
        >
          <span class="text-sm sm:text-base text-cream/80 font-medium">{{ item.question }}</span>
          <svg
            class="w-4 h-4 sm:w-5 sm:h-5 text-gold/60 shrink-0 transition-transform duration-300"
            :class="openIndex === index ? 'rotate-180' : ''"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
          >
            <path d="M6 9l6 6 6-6"/>
          </svg>
        </button>
        <div
          class="grid overflow-hidden transition-all duration-300 ease-out"
          :class="openIndex === index ? 'grid-rows-[1fr]' : 'grid-rows-[0fr]'"
        >
          <div class="min-h-0 overflow-hidden">
            <p class="px-4 sm:px-5 pt-1 pb-4 sm:pb-5 text-xs sm:text-sm text-cream/50 leading-relaxed">{{ item.answer }}</p>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref } from 'vue'

const openIndex = ref(null)
function toggle(index) {
  openIndex.value = openIndex.value === index ? null : index
}

const items = [
  {
    question: '¿De dónde salen los datos de marcas, modelos y versiones?',
    answer: 'Los datos provienen de tres fuentes oficiales del mercado automotor argentino: CCA (Cámara del Comercio Automotor), ACARA (Asociación de Concesionarios de Automotores de la República Argentina) e InfoAuto (referencia clásica de la industria para valores 0km y usados). Cada fuente se consulta por separado y podés comparar sus precios usando el selector al buscar una versión.'
  },
  {
    question: '¿Qué significa el precio que se muestra?',
    answer: 'Es el precio de referencia del mercado automotor argentino. No es un precio de venta ni una oferta comercial, sino una valuación de referencia utilizada por concesionarias, aseguradoras y registros automotores para estimar el valor de un vehículo según su marca, modelo, versión y año-modelo.'
  },
  {
    question: '¿Qué cotización de dólar se usa y cada cuánto se actualiza?',
    answer: 'Las cotizaciones se obtienen en tiempo real a través de Bluelytics. Están disponibles tanto el dólar oficial (venta) como el dólar blue. Cada vez que consultás un precio en pesos argentinos (ARS), podés elegir con cuál cotización convertir. La cotización se actualiza automáticamente con cada consulta, reflejando el valor más reciente del mercado.'
  },
  {
    question: '¿Cada cuánto se actualizan los precios de los autos?',
    answer: 'Los precios de referencia de la CCA, ACARA e InfoAuto se actualizan mensualmente. Cada mes se procesan las nuevas valuaciones publicadas por estas entidades para reflejar los cambios del mercado automotor.'
  },
  {
    question: '¿Es gratis consultar los precios?',
    answer: 'Sí, Arg Autos es 100% gratuito. Podés consultar precios de más de 60 marcas, 600 modelos y 5.800 versiones sin costo, tanto a través del sitio web como de la API REST pública.'
  },
  {
    question: '¿Qué diferencia hay entre el precio CCA, ACARA e InfoAuto?',
    answer: 'CCA (Cámara del Comercio Automotor), ACARA (Asociación de Concesionarios) e InfoAuto son tres entidades distintas que publican sus propias valuaciones. Los precios pueden diferir porque cada una usa metodologías y fuentes de mercado diferentes. En Arg Autos podés comparar todas las fuentes usando el selector de fuente de precio al consultar una versión.'
  },
  {
    question: '¿Cómo obtenemos los precios similares a InfoAuto cuando no hay dato oficial?',
    answer: 'En base a la información publica de la Camara del Comercio Automotor (CCA), se entreno al sistema para estimar de forma precisa los precios de los autos usados.Siguiendo los alineamientos de la revista InfoAuto, Se logro obtener una precisión del 98% en la estimación de los precios de los autos usados para esta fuente.'
  },
  {
    question: '¿Puedo usar la API en mi aplicación o proyecto?',
    answer: 'Sí, la API REST es pública y gratuita. Podés integrarla en tu aplicación, sitio web o proyecto sin necesidad de registro ni API key. Consultá la documentación para ver los endpoints disponibles, ejemplos de uso y formatos de respuesta.'
  },
  {
    question: '¿Qué significa "0 km" en la valuación?',
    answer: 'La categoría "0 km" se refiere al año-modelo más reciente disponible para esa versión. Es el precio de referencia para un vehículo nuevo sin uso. Los demás años representan valuaciones de vehículos usados según su antigüedad.'
  },
]
</script>
