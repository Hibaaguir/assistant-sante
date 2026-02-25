<template>
  <div class="mx-auto max-w-[1320px] p-5 sm:p-6">
    <div class="mb-4 flex items-center justify-between">
      <h2 class="text-[44px] font-bold leading-none text-slate-900">Mode édition - {{ entry?.dateLabel }}</h2>
      <button
        type="button"
        class="rounded-xl border border-slate-300 bg-white px-5 py-2 text-base font-medium text-slate-700 hover:bg-slate-50"
        @click="router.push({ name: 'journal-history' })"
      >
        Retour
      </button>
    </div>

    <div v-if="entry" class="rounded-[14px] border border-slate-300 bg-[#f6f8fb] p-5">
      <div class="grid gap-x-3 gap-y-2 md:grid-cols-2">
        <label class="text-[22px] text-slate-700">
          Sommeil
          <input v-model.number="form.sleep" type="number" min="0" max="12" class="mt-1 h-11 w-full rounded-lg border border-slate-300 bg-white px-3 text-[18px] text-slate-700" />
        </label>

        <label class="text-[22px] text-slate-700">
          Stress
          <input v-model.number="form.stress" type="number" min="0" max="10" class="mt-1 h-11 w-full rounded-lg border border-slate-300 bg-white px-3 text-[18px] text-slate-700" />
        </label>

        <label class="text-[22px] text-slate-700">
          Énergie
          <input v-model="form.energy" type="text" class="mt-1 h-11 w-full rounded-lg border border-slate-300 bg-white px-3 text-[18px] text-slate-700" />
        </label>

        <label class="text-[22px] text-slate-700">
          Hydratation
          <input v-model="form.hydration" type="text" class="mt-1 h-11 w-full rounded-lg border border-slate-300 bg-white px-3 text-[18px] text-slate-700" />
        </label>

        <label class="text-[22px] text-slate-700">
          Repas
          <input v-model="form.mealsCount" type="text" class="mt-1 h-11 w-full rounded-lg border border-slate-300 bg-white px-3 text-[18px] text-slate-700" />
        </label>

        <label class="text-[22px] text-slate-700">
          Activité
          <input v-model="form.activity" type="text" class="mt-1 h-11 w-full rounded-lg border border-slate-300 bg-white px-3 text-[18px] text-slate-700" />
        </label>
      </div>

      <div class="mt-5 flex gap-2">
        <button
          type="button"
          class="rounded-lg bg-emerald-600 px-5 py-2 text-[17px] font-semibold text-white hover:bg-emerald-700"
          @click="enregistrer"
        >
          Enregistrer
        </button>
        <button
          type="button"
          class="rounded-lg bg-slate-200 px-5 py-2 text-[17px] font-semibold text-slate-700 hover:bg-slate-300"
          @click="router.push({ name: 'journal-history' })"
        >
          Annuler
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useJournalStore } from '../../stores/journal'

const route = useRoute()
const router = useRouter()
const store = useJournalStore()

const entry = computed(() => store.obtenirParId(String(route.params.id || '')))

onMounted(async () => {
  await store.initialiser()
})

const form = reactive({
  sleep: entry.value?.sleep ?? 7,
  stress: entry.value?.stress ?? 5,
  energy: formaterEnergie(entry.value?.energy ?? 7),
  hydration: formaterHydratation(entry.value?.hydration ?? 1.5),
  mealsCount: `${entry.value?.meals.length ?? 3}`,
  activity: `${entry.value?.activityType ?? 'Marche'} ${entry.value?.activityDuration ?? 30}min`
})

watch(
  entry,
  (value) => {
    if (!value) return
    form.sleep = value.sleep
    form.stress = value.stress
    form.energy = formaterEnergie(value.energy)
    form.hydration = formaterHydratation(value.hydration)
    form.mealsCount = `${value.meals.length}`
    form.activity = `${value.activityType} ${value.activityDuration}min`
  }
)

function formaterEnergie(value) {
  if (value >= 8) return 'Excellente'
  if (value <= 4) return 'Faible'
  return 'Bonne'
}

function formaterHydratation(value) {
  return `${String(value).replace('.', ',')}L`
}

function parserEnergie(value) {
  const normalized = value.trim().toLowerCase()
  if (normalized.includes('excellente')) return 9
  if (normalized.includes('faible')) return 4
  const num = Number(normalized.replace(',', '.'))
  return Number.isFinite(num) ? num : 7
}

function parserHydratation(value) {
  const parsed = Number(value.replace('L', '').replace('l', '').replace(',', '.').trim())
  return Number.isFinite(parsed) ? parsed : 1.5
}

const enregistrer = async () => {
  if (!entry.value) return

  const activityType = form.activity.trim().split(' ')[0] || 'Marche'
  const durationMatch = form.activity.match(/(\d+)/)

  await store.mettreAJourEntree(entry.value.id, {
    sleep: Number(form.sleep),
    stress: Number(form.stress),
    energy: parserEnergie(String(form.energy)),
    hydration: parserHydratation(String(form.hydration)),
    meals: Array.from({ length: Math.max(0, Number(form.mealsCount) || 0) }, () => ({ type: 'lunch', label: 'Repas' })),
    activityType,
    activityDuration: durationMatch ? Number(durationMatch[1]) : 30
  })

  router.push({ name: 'journal-history' })
}
</script>
