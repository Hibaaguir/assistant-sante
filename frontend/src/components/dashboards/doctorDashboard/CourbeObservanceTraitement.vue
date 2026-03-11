<!--
  CourbeObservanceTraitement.vue
  Graphique avancé en aire (gradient) montrant l'évolution de l'observance
  du traitement d'un patient jour par jour. Affiche le pourcentage de prises
  effectuées, avec des points cliquables pour voir la valeur exacte, des
  statistiques résumées (dernier suivi, moyenne, prises suivies) et une
  légende dynamique. Reçoit l'objet patient complet en prop.
-->
<template>
  <article class="overflow-hidden rounded-[24px] border border-[#d3e4da] bg-[linear-gradient(135deg,#f4fbf6_0%,#ffffff_46%,#eef8f2_100%)] p-6 shadow-[0_18px_40px_rgba(24,77,48,0.08)]">
    <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
      <div>
        <span class="inline-flex items-center rounded-full border border-[#d9ebdf] bg-white/80 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.12em] text-[#2a7b4b]">
          Traitements
        </span>
        <h3 class="mt-3 text-[20px] font-bold text-[#123a2b]">Evolution de l'observance du traitement</h3>
        <p class="mt-1 text-[14px] text-[#5a7167]">Visualisation avancee des prises reelles enregistrees sur les derniers jours suivis.</p>
      </div>

      <div class="flex flex-wrap gap-3">
        <span class="inline-flex min-h-[42px] min-w-[120px] flex-col justify-center rounded-[16px] border border-[#d8eadf] bg-white/85 px-4 py-2">
          <span class="text-[10px] font-semibold uppercase tracking-[0.12em] text-[#789184]">Dernier suivi</span>
          <span class="mt-1 text-[16px] font-bold text-[#123a2b]">{{ treatmentTrendSummary.latestValue }}</span>
        </span>
        <span class="inline-flex min-h-[42px] min-w-[120px] flex-col justify-center rounded-[16px] border border-[#d8eadf] bg-white/85 px-4 py-2">
          <span class="text-[10px] font-semibold uppercase tracking-[0.12em] text-[#789184]">Moyenne</span>
          <span class="mt-1 text-[16px] font-bold text-[#123a2b]">{{ treatmentTrendSummary.averageValue }}</span>
        </span>
        <span class="inline-flex min-h-[42px] min-w-[132px] flex-col justify-center rounded-[16px] border border-[#d8eadf] bg-white/85 px-4 py-2">
          <span class="text-[10px] font-semibold uppercase tracking-[0.12em] text-[#789184]">Prises suivies</span>
          <span class="mt-1 text-[16px] font-bold text-[#123a2b]">{{ treatmentTrendSummary.takenTotal }}/{{ treatmentTrendSummary.expectedTotal }}</span>
        </span>
      </div>
    </div>

    <div ref="treatmentTrendChartRef" class="mt-6 rounded-[22px] border border-[#d7e8de] bg-[linear-gradient(180deg,rgba(255,255,255,0.86)_0%,rgba(244,251,247,0.98)_100%)] p-5 shadow-[inset_0_1px_0_rgba(255,255,255,0.8)]">
      <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex flex-wrap items-center gap-3">
          <span class="inline-flex items-center gap-2 rounded-full bg-[#eaf7ef] px-3 py-1 text-[12px] font-semibold text-[#1d7a46]">
            <span class="h-2.5 w-2.5 rounded-full bg-[#18a45b]" />
            Observance quotidienne
          </span>
          <span class="inline-flex items-center rounded-full border border-[#dbe8e0] bg-white px-3 py-1 text-[12px] font-medium text-[#5b7166]">
            {{ treatmentTrendSummary.periodLabel }}
          </span>
        </div>

        <p class="text-[12px] font-medium text-[#688073]">Cliquez sur un point pour afficher le pourcentage exact.</p>
      </div>

      <svg viewBox="0 0 520 220" class="mt-5 h-[280px] w-full" @click="selectedTreatmentTrendDateKey = ''">
        <defs>
          <linearGradient id="treatmentAreaGradient" x1="0" y1="0" x2="0" y2="1">
            <stop offset="0%" stop-color="#23b26b" stop-opacity="0.30" />
            <stop offset="100%" stop-color="#23b26b" stop-opacity="0.02" />
          </linearGradient>
          <linearGradient id="treatmentStrokeGradient" x1="0" y1="0" x2="1" y2="0">
            <stop offset="0%" stop-color="#17995a" />
            <stop offset="50%" stop-color="#1fb86c" />
            <stop offset="100%" stop-color="#0f7f49" />
          </linearGradient>
          <filter id="treatmentLineGlow" x="-20%" y="-20%" width="140%" height="140%">
            <feDropShadow dx="0" dy="10" stdDeviation="10" flood-color="#22a862" flood-opacity="0.18" />
          </filter>
        </defs>

        <rect x="36" y="18" width="456" height="162" rx="22" fill="#fbfefc" />

        <g stroke="#dfe9e3" stroke-dasharray="4 4">
            <line v-for="tick in treatmentChart.ticks" :key="`treatment-y-${tick.value}`" :x1="chartFrame.left" :y1="tick.y" :x2="chartFrame.width - chartFrame.right" :y2="tick.y" />
            <line v-for="(x, index) in treatmentChart.xPositions" :key="`treatment-x-${index}`" :x1="x" :y1="chartFrame.top" :x2="x" :y2="chartFrame.height - chartFrame.bottom" />
        </g>

        <polygon
          v-if="treatmentAreaPoints"
          :points="treatmentAreaPoints"
          fill="url(#treatmentAreaGradient)"
        />

        <line
          v-if="selectedTreatmentTrendTooltip"
          :x1="selectedTreatmentTrendTooltip.x"
          :x2="selectedTreatmentTrendTooltip.x"
          :y1="chartFrame.top"
          :y2="chartFrame.height - chartFrame.bottom"
          stroke="#b9d9c5"
          stroke-dasharray="5 5"
        />

        <polyline
          v-if="treatmentChart.series[0]?.points"
          fill="none"
          stroke="url(#treatmentStrokeGradient)"
          stroke-width="4"
          stroke-linecap="round"
          stroke-linejoin="round"
          filter="url(#treatmentLineGlow)"
          :points="treatmentChart.series[0].points"
        />

        <g v-if="treatmentChart.series[0]">
          <g
            v-for="dot in treatmentChart.series[0].dots"
            :key="`treatment-dot-${dot.index}`"
            class="cursor-pointer"
            @click.stop="selectedTreatmentTrendDateKey = treatmentTrendRows[dot.index]?.dateKey || ''"
          >
            <circle
              :cx="dot.x"
              :cy="dot.y"
              :r="selectedTreatmentTrendPoint?.dateKey === treatmentTrendRows[dot.index]?.dateKey ? 12 : 9"
              fill="#ffffff"
              :fill-opacity="selectedTreatmentTrendPoint?.dateKey === treatmentTrendRows[dot.index]?.dateKey ? 0.92 : 0.76"
            />
            <circle
              :cx="dot.x"
              :cy="dot.y"
              :r="selectedTreatmentTrendPoint?.dateKey === treatmentTrendRows[dot.index]?.dateKey ? 6.5 : 5.5"
              fill="#18a45b"
              stroke="#ffffff"
              stroke-width="2"
            />
            <circle
              :cx="dot.x"
              :cy="dot.y"
              r="14"
              fill="transparent"
            />
          </g>
        </g>

        <g v-if="selectedTreatmentTrendTooltip">
          <rect
            :x="selectedTreatmentTrendTooltip.x - 30"
            :y="selectedTreatmentTrendTooltip.y - 26"
            width="60"
            height="26"
            rx="9"
            fill="#123a2b"
          />
          <text
            :x="selectedTreatmentTrendTooltip.x"
            :y="selectedTreatmentTrendTooltip.y - 9"
            text-anchor="middle"
            fill="#ffffff"
            font-size="12"
            font-weight="700"
          >
            {{ selectedTreatmentTrendTooltip.label }}
          </text>
        </g>

        <g fill="#7a8f84" font-size="13">
            <text
              v-for="tick in treatmentChart.ticks"
              :key="`treatment-label-${tick.value}`"
              :x="chartFrame.left - 18"
              :y="tick.y + 4"
              text-anchor="end"
            >
              {{ formatPercentageTick(tick.value) }}
            </text>
        </g>
        <g fill="#7a8f84" font-size="13">
            <text
              v-for="(label, index) in treatmentChart.labels"
              :key="`treatment-date-${label}-${index}`"
              :x="treatmentChart.xPositions[index]"
              :y="chartFrame.height - 12"
              text-anchor="middle"
            >
              {{ label }}
            </text>
        </g>
      </svg>

      </div>

      <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-[#e0ece4] pt-4">
        <p class="text-[13px] text-[#5f766a]">{{ treatmentTrendSummary.latestLabel }}</p>
        <p v-if="!treatmentChart.hasData" class="text-[13px] font-medium text-[#6a7891]">Aucune prise de traitement exploitable pour afficher la courbe.</p>
      </div>
  </article>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'

const props = defineProps({
  patient: { type: Object, required: true }
})

const selectedTreatmentTrendDateKey = ref('')
const treatmentTrendChartRef = ref(null)

const chartFrame = {
  width: 520,
  height: 220,
  left: 60,
  right: 20,
  top: 30,
  bottom: 40,
}

// ---------------------------------------------------------------------------
// Computed
// ---------------------------------------------------------------------------

const treatmentTrendRows = computed(() => {
  const rows = Array.isArray(props.patient?.treatmentHistoryRows) ? props.patient.treatmentHistoryRows : []

  return rows
    .slice(0, 7)
    .reverse()
    .map((day) => {
      const adherence = day.total > 0 ? Number(((day.taken / day.total) * 100).toFixed(1)) : null

      return {
        ...day,
        adherence,
        chartLabel: formatShortDateFromIso(day.dateKey),
      }
    })
})

const treatmentChart = computed(() =>
  buildTrendChart(
    treatmentTrendRows.value.map((day) => day.chartLabel),
    [
      {
        key: 'treatment-adherence',
        color: '#18a45b',
        values: treatmentTrendRows.value.map((day) => day.adherence),
      },
    ],
    { fallbackMin: 0, fallbackMax: 100, minSpread: 25, padding: 6 }
  )
)

const selectedTreatmentTrendPoint = computed(() => {
  const rows = treatmentTrendRows.value
  return rows.find((day) => day.dateKey === selectedTreatmentTrendDateKey.value) || null
})

const selectedTreatmentTrendTooltip = computed(() => {
  const point = selectedTreatmentTrendPoint.value
  const dots = treatmentChart.value?.series?.[0]?.dots || []
  const rowIndex = treatmentTrendRows.value.findIndex((day) => day.dateKey === point?.dateKey)
  const dot = rowIndex >= 0 ? dots[rowIndex] : null

  if (!point || !dot || !Number.isFinite(point.adherence)) return null

  return {
    x: dot.x,
    y: Math.max(chartFrame.top + 28, dot.y - 12),
    label: formatPercentValue(point.adherence),
  }
})

const treatmentAreaPoints = computed(() => {
  const dots = treatmentChart.value?.series?.[0]?.dots || []
  if (!dots.length) return ''

  const baseline = chartFrame.height - chartFrame.bottom
  return [
    `${dots[0].x},${baseline}`,
    ...dots.map((dot) => `${dot.x},${dot.y}`),
    `${dots[dots.length - 1].x},${baseline}`,
  ].join(' ')
})

const treatmentTrendSummary = computed(() => {
  const rows = treatmentTrendRows.value
  const latest = rows.at(-1) || null
  const adherenceValues = rows.map((day) => day.adherence).filter((value) => Number.isFinite(value))
  const average = adherenceValues.length
    ? formatPercentValue(adherenceValues.reduce((sum, value) => sum + value, 0) / adherenceValues.length)
    : '--'
  const takenTotal = rows.reduce((sum, day) => sum + Number(day.taken || 0), 0)
  const expectedTotal = rows.reduce((sum, day) => sum + Number(day.total || 0), 0)

  return {
    periodLabel: rows.length ? `${rows.length} dernier${rows.length > 1 ? 's' : ''} jour${rows.length > 1 ? 's' : ''} suivis` : 'Aucune prise suivie',
    latestValue: latest && Number.isFinite(latest.adherence) ? formatPercentValue(latest.adherence) : '--',
    latestLabel: latest ? `${formatTreatmentHistoryDate(latest.dateKey)} · ${latest.taken}/${latest.total} prises` : 'Aucune prise recente',
    averageValue: average,
    daysCount: `${rows.length} jour${rows.length > 1 ? 's' : ''}`,
    takenTotal,
    expectedTotal,
  }
})

// ---------------------------------------------------------------------------
// Chart builders
// ---------------------------------------------------------------------------

function buildTrendChart(labelsInput, seriesInput, options = {}) {
  const labels = Array.isArray(labelsInput) ? labelsInput : []
  const xPositions = buildXPositions(labels.length)
  const series = Array.isArray(seriesInput) ? seriesInput : []
  const numericValues = series.flatMap((serie) => (Array.isArray(serie.values) ? serie.values : [])).filter((value) => Number.isFinite(value))
  const hasData = numericValues.length > 0

  let minValue = options.fallbackMin ?? 0
  let maxValue = options.fallbackMax ?? 100

  if (hasData) {
    const rawMin = Math.min(...numericValues)
    const rawMax = Math.max(...numericValues)
    const padding = Math.max(options.padding ?? 5, (rawMax - rawMin) * 0.15)
    minValue = Math.floor(rawMin - padding)
    maxValue = Math.ceil(rawMax + padding)
  }

  if (maxValue - minValue < (options.minSpread ?? 10)) {
    const diff = options.minSpread ?? 10
    const center = (maxValue + minValue) / 2
    minValue = Math.floor(center - diff / 2)
    maxValue = Math.ceil(center + diff / 2)
  }

  const ticks = buildTickValues(minValue, maxValue, 4).map((value) => ({
    value,
    y: convertValueToY(value, minValue, maxValue),
  }))

  return {
    labels,
    xPositions,
    ticks,
    hasData,
    series: series.map((serie) => buildSeriesGeometry(serie, xPositions, minValue, maxValue)),
  }
}

function buildSeriesGeometry(serie, xPositions, minValue, maxValue) {
  const source = Array.isArray(serie?.values) ? serie.values : []
  const dots = source
    .map((value, index) => {
      if (!Number.isFinite(value) || xPositions[index] === undefined) return null
      return {
        index,
        x: xPositions[index],
        y: convertValueToY(value, minValue, maxValue),
      }
    })
    .filter(Boolean)

  return {
    key: serie?.key || 'series',
    color: serie?.color || '#031a46',
    dots,
    points: dots.map((dot) => `${dot.x},${dot.y}`).join(' '),
  }
}

function buildXPositions(length) {
  if (length <= 0) return []
  if (length === 1) return [chartFrame.left]

  const usableWidth = chartFrame.width - chartFrame.left - chartFrame.right
  const step = usableWidth / (length - 1)
  return Array.from({ length }, (_, index) => chartFrame.left + step * index)
}

function convertValueToY(value, minValue, maxValue) {
  if (!Number.isFinite(value)) return chartFrame.height - chartFrame.bottom

  const usableHeight = chartFrame.height - chartFrame.top - chartFrame.bottom
  const ratio = (value - minValue) / Math.max(maxValue - minValue, 1)
  return chartFrame.height - chartFrame.bottom - ratio * usableHeight
}

function buildTickValues(minValue, maxValue, tickCount) {
  if (tickCount <= 1) return [minValue, maxValue]
  const step = (maxValue - minValue) / (tickCount - 1)
  return Array.from({ length: tickCount }, (_, index) => Number((minValue + step * index).toFixed(1)))
}

// ---------------------------------------------------------------------------
// Formatters
// ---------------------------------------------------------------------------

function formatPercentValue(value) {
  if (!Number.isFinite(value)) return '--'
  return Number.isInteger(value) ? `${value}%` : `${value.toFixed(1)}%`
}

function formatPercentageTick(value) {
  return formatPercentValue(value)
}

function formatShortDateFromIso(dateIso) {
  if (!dateIso) return '-'
  const date = new Date(`${dateIso}T00:00:00`)
  if (Number.isNaN(date.getTime())) return dateIso
  return date.toLocaleDateString('fr-FR', { day: 'numeric', month: 'short' }).replace('.', '')
}

function formatTreatmentHistoryDate(dateIso) {
  if (!dateIso) return '-'
  const date = new Date(`${dateIso}T00:00:00`)
  if (Number.isNaN(date.getTime())) return dateIso
  return date.toLocaleDateString('fr-FR', {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  })
}

// ---------------------------------------------------------------------------
// Document click handler (deselect point when clicking outside)
// ---------------------------------------------------------------------------

function handleDocumentClick(event) {
  const chartElement = treatmentTrendChartRef.value
  const target = event?.target

  if (!chartElement || !(target instanceof Node)) return
  if (!chartElement.contains(target)) {
    selectedTreatmentTrendDateKey.value = ''
  }
}

onMounted(() => {
  if (typeof document !== 'undefined') {
    document.addEventListener('click', handleDocumentClick)
  }
})

onBeforeUnmount(() => {
  if (typeof document !== 'undefined') {
    document.removeEventListener('click', handleDocumentClick)
  }
})
</script>
