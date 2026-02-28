<template>
  <div class="mx-auto max-w-[1320px] p-4 sm:p-6 lg:p-8">
    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
      <div>
        <h2 class="text-4xl font-bold tracking-tight text-slate-900">Journal quotidien</h2>
        <p class="mt-2 text-lg text-slate-600">Suivez votre bien-etre au quotidien</p>
      </div>
      <button
        type="button"
        class="rounded-2xl border border-slate-200 bg-white px-6 py-3 text-lg font-semibold text-slate-900 shadow-sm transition hover:-translate-y-0.5 hover:bg-slate-50 hover:shadow-md"
        @click="router.push({ name: 'journal-history' })"
      >
        Voir l'historique
      </button>
    </div>

    <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
      <button
        type="button"
        class="rounded-[28px] border border-blue-200 bg-white p-8 text-left shadow-sm transition hover:-translate-y-0.5 hover:border-blue-300 hover:shadow-md"
        @click="router.push({ name: 'journal-wizard' })"
      >
        <div class="mb-8 flex h-20 w-20 items-center justify-center rounded-3xl bg-gradient-to-b from-blue-600 to-indigo-600 text-white shadow-md">
          <svg viewBox="0 0 24 24" class="h-10 w-10" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M12 5v14" />
            <path d="M5 12h14" />
          </svg>
        </div>
        <h3 class="text-4xl font-bold text-slate-900">Nouvelle entree</h3>
        <p class="mt-3 text-xl text-slate-700">Enregistrez les donnees d'aujourd'hui</p>
      </button>

      <div class="rounded-[28px] border border-slate-200 bg-white p-8 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
        <h3 class="text-4xl font-bold text-slate-900">Derniere entree</h3>
        <div v-if="latest" class="mt-8 space-y-4 text-xl text-slate-700">
          <div class="flex items-center justify-between border-b border-slate-200 pb-4">
            <span>Date</span>
            <span class="font-semibold text-slate-900">{{ latest.dateLabel }}</span>
          </div>
          <div class="flex items-center justify-between border-b border-slate-200 pb-4">
            <span>Sommeil</span>
            <span class="font-semibold text-slate-900">{{ sleepLabel(latest.sleep) }}</span>
          </div>
          <div class="flex items-center justify-between border-b border-slate-200 pb-4">
            <span>Stress</span>
            <span class="font-semibold text-slate-900">{{ stressLabel(latest.stress) }}</span>
          </div>
          <div class="flex items-center justify-between">
            <span>Energie</span>
            <span class="font-semibold text-slate-900">{{ energyLabel(latest.energy) }}</span>
          </div>
        </div>
        <div v-else class="mt-8 rounded-xl border border-dashed border-slate-300 bg-slate-50 p-4 text-sm text-slate-500">
          Aucune donnee
        </div>
      </div>

      <CarteInfosDerniereEntree :latest-entry="latest ?? null" />
    </div>
  </div>
</template>

<script setup>
/*
  Page d'accueil du module Journal.
  Elle propose la creation d'une nouvelle entree et resume la derniere.
  Les informations affichees proviennent du store journal.
*/

import { onMounted } from "vue";
import { storeToRefs } from "pinia";
import { useRouter } from "vue-router";
import CarteInfosDerniereEntree from '../../components/journal/CarteInfosDerniereEntree.vue'
import { useJournalStore } from '../../stores/journal'

const router = useRouter()
const store = useJournalStore()
const { latestEntry: latest } = storeToRefs(store)

onMounted(async () => {
  await store.initialiser();
});

const sleepLabel = (hours) => {
  const h = Math.floor(hours)
  const m = Math.round((hours - h) * 60)
  return m ? `${h}h ${m}min` : `${h}h`
}

const stressLabel = (value) => {
  if (value >= 8) return 'Eleve'
  if (value <= 3) return 'Faible'
  return 'Modere'
}

const energyLabel = (value) => {
  if (value >= 8) return 'Excellente'
  if (value <= 4) return 'Faible'
  return 'Bonne'
}
</script>


