<template>
  <div class="rounded-[28px] border border-slate-200 bg-white p-8 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
    <h3 class="text-3xl font-bold text-slate-900">Autres informations</h3>
    <p class="mt-2 text-sm text-slate-500">Details supplementaires de la derniere entree</p>

    <div v-if="!entry" class="mt-8 rounded-xl border border-dashed border-slate-300 bg-slate-50 p-4 text-sm text-slate-500">
      Aucune donnee
    </div>

    <div v-else class="mt-6 space-y-4 text-sm text-slate-700">
      <div class="flex items-center justify-between gap-3 border-b border-slate-200 pb-3">
        <span class="inline-flex items-center gap-2">
          <svg viewBox="0 0 20 20" class="h-4 w-4 text-slate-400" fill="currentColor" aria-hidden="true"><path d="M10 1.5c-.7 1.8-5 5.5-5 9a5 5 0 0 0 10 0c0-3.5-4.3-7.2-5-9Z" /></svg>
          Hydratation
        </span>
        <span class="font-semibold text-slate-900">{{ hydrationText }}</span>
      </div>

      <div class="flex items-center justify-between gap-3 border-b border-slate-200 pb-3">
        <span class="inline-flex items-center gap-2">
          <svg viewBox="0 0 20 20" class="h-4 w-4 text-slate-400" fill="currentColor" aria-hidden="true"><path d="M3 4h14v2H3V4Zm0 4h14v8H3V8Z" /></svg>
          Repas
        </span>
        <span class="text-right font-semibold text-slate-900">{{ mealsText }}</span>
      </div>

      <div class="flex items-center justify-between gap-3 border-b border-slate-200 pb-3">
        <span class="inline-flex items-center gap-2">
          <svg viewBox="0 0 20 20" class="h-4 w-4 text-slate-400" fill="currentColor" aria-hidden="true"><path d="M11 3h6v2h-4v4h4v2h-4v6h-2V3ZM3 11l4-8 2 1-2 4h3l-4 8-2-1 2-4H3v-2Z" /></svg>
          Activite
        </span>
        <span class="font-semibold text-slate-900">{{ activityText }}</span>
      </div>

      <div class="flex items-center justify-between gap-3 border-b border-slate-200 pb-3">
        <span class="inline-flex items-center gap-2">
          <svg viewBox="0 0 20 20" class="h-4 w-4 text-slate-400" fill="currentColor" aria-hidden="true"><path d="m9 2 6 8h-4l2 8-6-8h4L9 2Z" /></svg>
          Intensite
        </span>
        <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold" :class="intensityBadgeClass">
          {{ intensityLabel }}
        </span>
      </div>

      <div class="flex items-center justify-between gap-3 border-b border-slate-200 pb-3">
        <span class="inline-flex items-center gap-2">
          <svg viewBox="0 0 20 20" class="h-4 w-4 text-slate-400" fill="currentColor" aria-hidden="true"><path d="M6 2h8v6h-2V4H8v6h4v8H8v-6H6V2Z" /></svg>
          Tabac
        </span>
        <span class="font-semibold text-slate-900">{{ tobaccoText }}</span>
      </div>

      <div class="flex items-center justify-between gap-3 border-b border-slate-200 pb-3">
        <span class="inline-flex items-center gap-2">
          <svg viewBox="0 0 20 20" class="h-4 w-4 text-slate-400" fill="currentColor" aria-hidden="true"><path d="M4 3h12v14H4V3Zm2 2v2h8V5H6Z" /></svg>
          Alcool
        </span>
        <span class="font-semibold text-slate-900">{{ alcoholText }}</span>
      </div>

      <div class="flex items-center justify-between gap-3">
        <span class="inline-flex items-center gap-2">
          <svg viewBox="0 0 20 20" class="h-4 w-4 text-slate-400" fill="currentColor" aria-hidden="true"><path d="M5 5h10v4H5V5Zm-1 6h12v6H4v-6Z" /></svg>
          Apport en sucre
        </span>
        <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold" :class="sugarBadgeClass">
          {{ sugarLabel }}
        </span>
      </div>
    </div>
  </div>
</template>

<script setup>
/*
  Carte "Autres informations" de la derniere entree.
  Les textes affiches sont derives de l'objet `latestEntry`.
  Le composant reste purement visuel pour etre reutilisable.
*/

import { computed } from 'vue'

const props = defineProps({
  latestEntry: {
    type: Object,
    default: null
  }
})

const entry = computed(() => props.latestEntry ?? null)

const hydrationText = computed(() => {
  if (!entry.value) return 'Aucune donnee'
  return `${entry.value.hydration} L`
})

const mealTypeMap = {
  breakfast: 'Petit dejeuner',
  lunch: 'Dejeuner',
  dinner: 'Diner',
  snack: 'Snacks'
}

const mealsText = computed(() => {
  if (!entry.value || !entry.value.meals.length) return 'Aucun repas'
  const kinds = [...new Set(entry.value.meals.map((meal) => mealTypeMap[meal.type] ?? meal.type))]
  return `${entry.value.meals.length} repas / ${kinds.join('/')}`
})

const activityText = computed(() => {
  if (!entry.value?.activityType) return 'Non renseignee'
  return `${entry.value.activityType} ${entry.value.activityDuration} min`
})

const intensityLabel = computed(() => {
  if (!entry.value) return 'Non'
  if (entry.value.intensity === 'high') return 'Intense'
  if (entry.value.intensity === 'light') return 'Legere'
  return 'Moderee'
})

const intensityBadgeClass = computed(() => {
  if (!entry.value) return 'border-slate-300 bg-slate-100 text-slate-700'
  if (entry.value.intensity === 'high') return 'border-rose-300 bg-rose-100 text-rose-700'
  if (entry.value.intensity === 'light') return 'border-sky-300 bg-sky-100 text-sky-700'
  return 'border-emerald-300 bg-emerald-100 text-emerald-700'
})

const tobaccoText = computed(() => {
  if (!entry.value?.tobacco) return 'Non'
  const parts = []
  const hasCigarette = Boolean(entry.value.tobaccoTypes?.cigarette)
  const hasVape = Boolean(entry.value.tobaccoTypes?.vape)
  if (hasCigarette && typeof entry.value.cigarettesPerDay === 'number') {
    parts.push(`Cigarette • ${entry.value.cigarettesPerDay}/j`)
  }
  if (hasVape && entry.value.vapeFrequency && typeof entry.value.vapeLiquidMl === 'number') {
    parts.push(`Vape • ${entry.value.vapeFrequency} • ${entry.value.vapeLiquidMl} liquide`)
  }
  if (!parts.length) return 'Oui'
  return parts.join(', ')
})

const alcoholText = computed(() => {
  if (!entry.value?.alcohol) return 'Non'
  const drinks = Number(entry.value.alcoholDrinks ?? 0)
  if (drinks > 0) return `${drinks} verres/jour`
  return 'Oui'
})

const sugarLabel = computed(() => {
  if (!entry.value) return 'Non'
  if (entry.value.sugar === 'high') return 'Eleve'
  if (entry.value.sugar === 'low') return 'Faible'
  return 'Modere'
})

const sugarBadgeClass = computed(() => {
  if (!entry.value) return 'border-slate-300 bg-slate-100 text-slate-700'
  if (entry.value.sugar === 'high') return 'border-rose-300 bg-rose-100 text-rose-700'
  if (entry.value.sugar === 'low') return 'border-emerald-300 bg-emerald-100 text-emerald-700'
  return 'border-amber-300 bg-amber-100 text-amber-700'
})
</script>

