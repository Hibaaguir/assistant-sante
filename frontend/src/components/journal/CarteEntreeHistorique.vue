<template>
  <article class="rounded-2xl border border-slate-200 bg-white px-5 py-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
    <div class="flex flex-wrap items-center justify-between gap-3">
      <div class="flex flex-wrap items-center gap-x-4 gap-y-2 text-xs text-slate-600 min-w-0 flex-1">

        <!-- Date -->
        <span class="inline-flex items-center gap-1.5 font-semibold text-slate-700">
          <svg
            viewBox="0 0 24 24"
            class="h-3.5 w-3.5 text-slate-500"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
            aria-hidden="true"
          >
            <path d="M8 2v4M16 2v4M4 9h16M5 5h14v15H5z" />
          </svg>
          {{ entree.dateLabel }}
        </span>

        <!-- Champs filtrés -->
        <span v-for="champ in champsVisibles" :key="champ.label">
          {{ champ.label }}: <b class="text-slate-900">{{ champ.valeur }}</b>
        </span>
      </div>

      <!-- Actions -->
      <div class="flex items-center gap-2">
        <button
          type="button"
          class="rounded-lg border border-indigo-200 bg-indigo-50 p-1.5 text-indigo-600 transition-colors hover:bg-indigo-100"
          @click="$emit('edit')"
        >
          <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M12 20h9" />
            <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z" />
          </svg>
        </button>
        <button
          type="button"
          class="rounded-lg border border-pink-200 bg-pink-50 p-1.5 text-pink-600 transition-colors hover:bg-pink-100"
          @click="$emit('request-delete')"
        >
          <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M3 6h18" />
            <path d="M8 6V4h8v2" />
            <path d="M19 6l-1 14H6L5 6" />
          </svg>
        </button>
      </div>
    </div>
  </article>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  entree:     { type: Object, required: true },
  filterType: { type: String, default: 'all' }
})

defineEmits(['edit', 'request-delete'])

// Formateurs
const fmtSommeil = (h) => {
  const hh = Math.floor(h), mm = Math.round((h - hh) * 60)
  return mm ? `${hh}h ${mm}min` : `${hh}h`
}
const fmtStress  = (v) => v >= 8 ? 'Élevé'      : v <= 3 ? 'Faible' : 'Modéré'
const fmtEnergie = (v) => v >= 8 ? 'Excellente'  : v <= 4 ? 'Faible' : 'Bonne'
const fmtMois    = (iso) =>
  new Date(`${iso}T00:00:00`).toLocaleDateString('fr-FR', { month: 'long', year: 'numeric' })

const fmtTabac = ({ tobacco, tobaccoTypes, cigarettesPerDay, vapeFrequency, vapeLiquidMl }) => {
  if (!tobacco) return 'Non'
  const parts = [
    tobaccoTypes?.cigarette && cigarettesPerDay != null && `Cigarette • ${cigarettesPerDay}/j`,
    tobaccoTypes?.vape      && vapeFrequency            && `Vape • ${vapeFrequency} • ${vapeLiquidMl} ml`,
  ].filter(Boolean)
  return parts.length ? parts.join(', ') : 'Oui'
}

// Table de correspondance filtre → champ
const CHAMPS = {
  sleep:     (e) => ({ label: 'Sommeil',     valeur: fmtSommeil(e.sleep) }),
  stress:    (e) => ({ label: 'Stress',      valeur: fmtStress(e.stress) }),
  energy:    (e) => ({ label: 'Énergie',     valeur: fmtEnergie(e.energy) }),
  nutrition: (e) => ({ label: 'Repas',       valeur: `${e.meals.length} repas` }),
  hydration: (e) => ({ label: 'Hydratation', valeur: `${e.hydration} L` }),
  activity:  (e) => ({ label: 'Activité',    valeur: `${e.activityType} ${e.activityDuration}min` }),
  tobacco:   (e) => ({ label: 'Tabac',       valeur: fmtTabac(e) }),
  date:      (e) => ({ label: 'Date',        valeur: e.dateIso }),
  month:     (e) => ({ label: 'Mois',        valeur: fmtMois(e.dateIso) }),
}

const champsVisibles = computed(() => {
  const e = props.entree
  if (props.filterType === 'all') return Object.values(CHAMPS).map(fn => fn(e))
  return [CHAMPS[props.filterType]?.call(null, e)].filter(Boolean)
})
</script>