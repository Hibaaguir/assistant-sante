<!--
  HealthChart.vue
  Graphique interactif des signes vitaux (rythme cardiaque, tension, saturation O₂) sur 7 jours.
-->
<template>
  <section class="mt-5 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">

    <!-- En-tête + toggles -->
    <div class="mb-3 flex items-center justify-between">
      <h2 class="text-[34px] font-semibold leading-none text-slate-900">Évolution - 7 derniers jours</h2>
      <div class="flex items-center gap-2 text-[12px] font-medium">
        <button
          v-for="s in SERIES" :key="s.key" type="button"
          class="rounded-xl border px-3 py-1.5 transition"
          :class="active[s.key] ? `border-${s.shade}-500 bg-white text-${s.shade}-600` : 'border-slate-200 bg-slate-100 text-slate-500'"
          @click="toggle(s.key)"
        >
          {{ s.label }}
        </button>
      </div>
    </div>

    <!-- SVG -->
    <div ref="chartRef" class="relative overflow-x-auto" @mousemove="onMove" @mouseleave="hoverIndex = null">
      <svg :viewBox="`0 0 ${C.width} ${C.height}`" class="h-[300px] w-full min-w-[980px]">

        <!-- Grille -->
        <g stroke="#e2e8f0" stroke-dasharray="4 4">
          <line v-for="t in Y_TICKS" :key="`h-${t}`"  :x1="C.left" :y1="toY(t)" :x2="C.width - C.right" :y2="toY(t)" />
          <line v-for="(_, i) in labels" :key="`v-${i}`" :x1="toX(i)" :y1="C.top" :x2="toX(i)" :y2="C.height - C.bottom" />
        </g>

        <!-- Ligne de survol -->
        <line v-if="hoverIndex !== null" :x1="toX(hoverIndex)" :y1="C.top" :x2="toX(hoverIndex)" :y2="C.height - C.bottom" stroke="#cbd5e1" stroke-width="1.5" />

        <!-- Courbes + points -->
        <template v-for="s in visibleSeries" :key="s.key">
          <polyline fill="none" :stroke="s.color" stroke-width="3" :points="s.points" />
          <g v-for="(v, i) in s.values" :key="i">
            <circle :cx="toX(i)" :cy="toY(v)" :r="hoverIndex === i ? 6 : 5" :fill="s.color" />
            <circle v-if="hoverIndex === i" :cx="toX(i)" :cy="toY(v)" r="3.2" fill="white" />
          </g>
        </template>

        <!-- Labels Y -->
        <g fill="#94a3b8" font-size="13">
          <text v-for="t in Y_TICKS" :key="`y-${t}`" :x="C.left - 22" :y="toY(t) + 4">{{ t }}</text>
        </g>

        <!-- Labels X -->
        <g fill="#94a3b8" font-size="14">
          <text v-for="(l, i) in labels" :key="`x-${i}`" :x="toX(i) - 24" :y="C.height - 8">{{ l }}</text>
        </g>
      </svg>

      <!-- Tooltip -->
      <div
        v-if="hoverIndex !== null"
        class="pointer-events-none absolute rounded-2xl border border-slate-200 bg-white/95 px-4 py-3 text-[12px] shadow-lg"
        :style="{ left: `${tooltipLeft}px`, top: `${C.top + 10}px` }"
      >
        <p class="text-slate-900">{{ labels[hoverIndex] }}</p>
        <template v-for="s in visibleSeries" :key="s.key">
          <p class="mt-1" :style="{ color: s.color }">{{ s.tooltipLabel }} : {{ s.values[hoverIndex] }}</p>
        </template>
      </div>
    </div>

    <!-- Légende -->
    <div class="mt-1.5 flex items-center justify-center gap-3 text-[12px] font-medium">
      <span v-for="s in visibleSeries" :key="s.key" class="inline-flex items-center gap-1" :style="{ color: s.color }">
        <span class="h-1.5 w-1.5 rounded-full" :style="{ background: s.color }" />
        {{ s.tooltipLabel }}
      </span>
    </div>

  </section>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import api from '@/services/api'

// ─── Config graphique ─────────────────────────────────────────────────────────
const C       = { width: 980, height: 300, left: 70, right: 28, top: 24, bottom: 38, minY: 0, maxY: 140 }
const Y_TICKS = [0, 35, 70, 105, 140]

const SERIES = [
  { key: 'rhythm',     label: 'Rythme',    tooltipLabel: 'Rythme cardiaque (bpm)',    color: '#f43f5e', shade: 'rose',   field: 'heart_rate',         fallback: 70  },
  { key: 'tension',    label: 'Tension',   tooltipLabel: 'Tension systolique (mmHg)', color: '#3b82f6', shade: 'blue',   field: 'systolic_pressure',  fallback: 120 },
  { key: 'saturation', label: 'Saturation',tooltipLabel: 'Saturation O₂ (%)',         color: '#8b5cf6', shade: 'violet', field: 'oxygen_saturation',  fallback: 98  },
]

// ─── État ─────────────────────────────────────────────────────────────────────
const chartRef   = ref(null)
const hoverIndex = ref(null)
const labels     = ref([])
const data       = reactive({ rhythm: [], tension: [], saturation: [] })
const active     = reactive({ rhythm: true, tension: true, saturation: true })

// ─── Séries visibles ──────────────────────────────────────────────────────────
const visibleSeries = computed(() =>
  SERIES.filter(s => active[s.key]).map(s => ({
    ...s,
    values: data[s.key],
    points: data[s.key].map((v, i) => `${toX(i)},${toY(v)}`).join(' '),
  }))
)

const tooltipLeft = computed(() =>
  Math.max(8, Math.min(toX(hoverIndex.value) + 12, C.width - 230))
)

// ─── Coordonnées ─────────────────────────────────────────────────────────────
const toX = i => {
  if (labels.value.length <= 1) return C.left
  return C.left + i * (C.width - C.left - C.right) / (labels.value.length - 1)
}

const toY = v => {
  const n = Number(v)
  if (!Number.isFinite(n)) return C.height - C.bottom
  return C.height - C.bottom - ((n - C.minY) / (C.maxY - C.minY)) * (C.height - C.top - C.bottom)
}

// ─── Données ──────────────────────────────────────────────────────────────────
function normaliser(values, fallback) {
  let last = fallback
  return (Array.isArray(values) ? values : []).map(v => {
    const n = Number(v)
    if (Number.isFinite(n)) { last = n; return n }
    return last
  })
}

function formatLabel(str) {
  if (!str) return ''
  const d = new Date(`${str}T00:00:00`)
  return isNaN(d) ? String(str) : d.toLocaleDateString('fr-FR', { day: '2-digit', month: 'short' }).replace('.', '')
}

async function chargerDonnees() {
  const { data: res } = await api.get('/health-data/overview', { params: { days: 7 } })
  const chart = res?.data?.vitals_chart ?? {}
  labels.value = (chart.labels ?? []).map(formatLabel)
  SERIES.forEach(s => { data[s.key] = normaliser(chart[s.field], s.fallback) })
}

// ─── Interactions ─────────────────────────────────────────────────────────────
function onMove(e) {
  if (!chartRef.value || !labels.value.length) return
  const rect  = chartRef.value.getBoundingClientRect()
  const localX = ((e.clientX - rect.left) / rect.width) * C.width
  const step   = labels.value.length > 1 ? (C.width - C.left - C.right) / (labels.value.length - 1) : 1
  hoverIndex.value = Math.min(Math.max(Math.round((localX - C.left) / step), 0), labels.value.length - 1)
}

function toggle(key) {
  if (active[key] && Object.values(active).filter(Boolean).length === 1) return
  active[key] = !active[key]
}

onMounted(chargerDonnees)
</script>