<!--
  HistogrammeAnalysesChart.vue
  Histogramme interactif des analyses biologiques d'un patient. Affiche
  les résultats numériques récents sous forme de barres colorées (vert,
  orange ou rouge selon le statut), avec un filtre semaine/mois, un panneau
  de détail de l'analyse sélectionnée et un label de période. Reçoit
  l'objet patient complet en prop.
-->
<template>
  <article class="rounded-[20px] border border-[#d4d9e1] bg-white p-6 shadow-[0_1px_4px_rgba(15,23,42,0.05)]">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
      <div>
        <h3 class="text-[18px] font-bold text-[#041c49]">Histogramme des analyses biologiques</h3>
        <p class="mt-1 text-[14px] text-[#5b6b84]">Vue rapide des resultats numeriques recents, avec filtre semaine ou mois.</p>
      </div>

      <div class="inline-flex rounded-[14px] bg-[#f2f5fb] p-1">
        <button
          type="button"
          class="h-[36px] rounded-[10px] px-4 text-[14px] font-semibold transition"
          :class="analysisOverviewPeriod === 'week' ? 'bg-white text-[#1b2d57] shadow-[0_6px_14px_rgba(15,23,42,0.10)]' : 'text-[#62718a]'"
          @click="analysisOverviewPeriod = 'week'"
        >
          Semaine
        </button>
        <button
          type="button"
          class="h-[36px] rounded-[10px] px-4 text-[14px] font-semibold transition"
          :class="analysisOverviewPeriod === 'month' ? 'bg-white text-[#1b2d57] shadow-[0_6px_14px_rgba(15,23,42,0.10)]' : 'text-[#62718a]'"
          @click="analysisOverviewPeriod = 'month'"
        >
          Mois
        </button>
      </div>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-[minmax(0,1fr)_240px]">
      <div class="rounded-[18px] border border-[#e2e7f0] bg-[linear-gradient(180deg,#f9fbff_0%,#ffffff_100%)] p-5">
        <div class="flex items-center justify-between gap-3">
          <p class="text-[14px] font-semibold text-[#23375f]">{{ analysisOverviewTitle }}</p>
          <span class="rounded-full bg-[#edf2ff] px-3 py-1 text-[12px] font-semibold text-[#4a55f5]">{{ analysisOverviewCountLabel }}</span>
        </div>

        <div v-if="analysisOverviewBars.length" class="mt-6 flex h-[260px] items-end gap-3 overflow-x-auto pb-2">
          <button
            v-for="item in analysisOverviewBars"
            :key="`${item.name}-${item.isoDate}`"
            type="button"
            class="group flex min-w-[84px] flex-1 flex-col items-center justify-end rounded-[18px] px-2 pb-2 pt-3 text-left transition"
            :class="selectedAnalysisOverview?.key === item.key ? 'bg-[#f4f7ff]' : 'hover:bg-[#f8faff]'"
            @click="selectedAnalysisOverviewKey = item.key"
          >
            <span class="mb-3 text-[12px] font-semibold text-[#20345d]">{{ item.value }}</span>
            <div class="flex h-[188px] w-full items-end justify-center">
              <div
                class="w-full rounded-t-[18px] shadow-[0_10px_20px_rgba(59,91,219,0.14)] transition duration-200 group-hover:-translate-y-1"
                :class="selectedAnalysisOverview?.key === item.key ? 'ring-2 ring-[#d7e1ff] ring-offset-2 ring-offset-white' : ''"
                :style="{ height: `${item.barHeight}%`, background: item.barGradient }"
              />
            </div>
            <p class="mt-4 line-clamp-2 text-center text-[12px] font-semibold leading-5 text-[#1f3158]">{{ item.shortLabel }}</p>
            <p class="mt-1 text-[11px] text-[#7a87a0]">{{ item.date }}</p>
          </button>
        </div>

        <div v-else class="mt-6 rounded-[16px] border border-dashed border-[#d5dce8] bg-[#fbfcff] px-5 py-10 text-center">
          <p class="text-[15px] font-semibold text-[#10254f]">Aucune analyse numerique sur cette periode.</p>
          <p class="mt-2 text-[13px] text-[#697892]">Passez en vue mois pour afficher une plage plus large.</p>
        </div>
      </div>

      <div class="space-y-4">
        <article class="rounded-[18px] border border-[#dbe3ef] bg-[#f7faff] p-5">
          <p class="text-[13px] font-semibold uppercase tracking-[0.08em] text-[#687896]">Periode</p>
          <p class="mt-3 text-[16px] font-bold text-[#10254f]">{{ analysisOverviewRangeLabel }}</p>
        </article>

        <article class="rounded-[18px] border border-[#dbe3ef] bg-white p-5">
          <p class="text-[13px] font-semibold uppercase tracking-[0.08em] text-[#687896]">Analyse selectionnee</p>
          <div v-if="selectedAnalysisOverview" class="mt-4 rounded-[14px] border border-[#e4e8f0] bg-[#fbfcfe] px-4 py-4">
            <div class="flex items-start justify-between gap-3">
              <p class="text-[14px] font-semibold text-[#112652]">{{ selectedAnalysisOverview.name }}</p>
              <span class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-semibold" :class="selectedAnalysisOverview.badgeClass">
                {{ selectedAnalysisOverview.status }}
              </span>
            </div>
            <div class="mt-4 space-y-3 text-[13px] text-[#60708b]">
              <div class="flex items-center justify-between gap-3">
                <span>Valeur</span>
                <span class="font-semibold text-[#112652]">{{ selectedAnalysisOverview.value }}</span>
              </div>
              <div class="flex items-center justify-between gap-3">
                <span>Type</span>
                <span class="font-semibold text-[#112652]">{{ selectedAnalysisOverview.type }}</span>
              </div>
              <div class="flex items-center justify-between gap-3">
                <span>Resultat</span>
                <span class="font-semibold text-[#112652]">{{ selectedAnalysisOverview.result || '-' }}</span>
              </div>
              <div class="flex items-center justify-between gap-3">
                <span>Date</span>
                <span class="font-semibold text-[#112652]">{{ selectedAnalysisOverview.date }}</span>
              </div>
            </div>
          </div>
          <p v-else class="mt-4 text-[13px] text-[#60708b]">Cliquez sur une barre pour afficher les details de l'analyse.</p>
        </article>
      </div>
    </div>
  </article>
</template>

<script setup>
import { computed, ref } from 'vue'

const props = defineProps({
  patient: { type: Object, required: true }
})

const analysisOverviewPeriod = ref('week')
const selectedAnalysisOverviewKey = ref('')

// ---------------------------------------------------------------------------
// Computed
// ---------------------------------------------------------------------------

const analysisOverviewReferenceDate = computed(() => {
  const analyses = Array.isArray(props.patient?.analyses) ? props.patient.analyses : []
  const isoDates = analyses.map((analysis) => analysis.isoDate).filter(Boolean).sort()
  const referenceIso = isoDates.at(-1)
  return referenceIso ? new Date(`${referenceIso}T00:00:00`) : new Date()
})

const analysisOverviewWindow = computed(() => {
  const end = new Date(analysisOverviewReferenceDate.value)
  const start = new Date(end)
  const days = analysisOverviewPeriod.value === 'month' ? 30 : 7
  start.setDate(end.getDate() - (days - 1))

  return {
    start,
    end,
    days,
  }
})

const analysisOverviewItems = computed(() => {
  const analyses = Array.isArray(props.patient?.analyses) ? props.patient.analyses : []
  const { start, end } = analysisOverviewWindow.value

  return analyses
    .filter((analysis) => analysis.isoDate && analysis.numericValue !== null)
    .filter((analysis) => {
      const date = new Date(`${analysis.isoDate}T00:00:00`)
      return date >= start && date <= end
    })
    .sort((a, b) => String(a.isoDate).localeCompare(String(b.isoDate)))
})

const analysisOverviewBars = computed(() => {
  const items = analysisOverviewItems.value.slice(-6)
  const maxValue = Math.max(...items.map((item) => item.numericValue || 0), 0)

  return items.map((item, index) => ({
    ...item,
    key: `${item.name}-${item.isoDate}-${index}`,
    shortLabel: buildAnalysisShortLabel(item),
    barHeight: maxValue > 0 ? Math.max(18, Math.round(((item.numericValue || 0) / maxValue) * 100)) : 18,
    barGradient: resolveAnalysisBarGradient(item.status),
  }))
})

const selectedAnalysisOverview = computed(() => {
  const items = analysisOverviewBars.value
  return items.find((item) => item.key === selectedAnalysisOverviewKey.value) || items.at(-1) || null
})

const analysisOverviewTitle = computed(() =>
  analysisOverviewPeriod.value === 'month' ? 'Mois en cours glissant' : '7 derniers jours'
)

const analysisOverviewCountLabel = computed(() => `${analysisOverviewItems.value.length} resultat${analysisOverviewItems.value.length > 1 ? 's' : ''}`)

const analysisOverviewRangeLabel = computed(() => {
  const { start, end } = analysisOverviewWindow.value
  return `${formatOverviewDate(start)} - ${formatOverviewDate(end)}`
})

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

function buildAnalysisShortLabel(item) {
  const source = String(item?.result || item?.type || 'Analyse').trim()
  return source.length > 18 ? `${source.slice(0, 16)}...` : source
}

function resolveAnalysisBarGradient(status) {
  if (status === 'Critique') return 'linear-gradient(180deg, #ff9a9f 0%, #ff4d6d 100%)'
  if (status === 'Attention') return 'linear-gradient(180deg, #ffd78a 0%, #ff9f43 100%)'
  return 'linear-gradient(180deg, #87e0a1 0%, #42c6ac 100%)'
}

function formatOverviewDate(date) {
  if (!(date instanceof Date) || Number.isNaN(date.getTime())) return '-'
  return date.toLocaleDateString('fr-FR', { day: 'numeric', month: 'short' }).replace('.', '')
}
</script>
