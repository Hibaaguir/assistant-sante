<template>
  <div class="mx-auto max-w-[1320px] p-4 sm:p-6 lg:p-8">
    <div class="relative mb-4 overflow-hidden rounded-3xl border border-[#d6e2ff] bg-gradient-to-br from-[#edf4ff] via-[#f8f4ff] to-[#eefaf4] p-5 shadow-sm sm:p-6">
      <div class="pointer-events-none absolute -right-10 -top-10 h-28 w-28 rounded-full bg-[#7c3aed]/15 blur-2xl"></div>
      <div class="pointer-events-none absolute -bottom-10 left-8 h-24 w-24 rounded-full bg-[#2563eb]/15 blur-2xl"></div>
      <div class="flex flex-wrap items-start justify-between gap-3">
      <div>
        <p class="inline-flex items-center rounded-full border border-[#d2ddff] bg-white/85 px-3 py-1 text-xs font-semibold text-[#3b5ac8]">HealthFlow Journal</p>
        <h2 class="mt-3 text-4xl font-bold tracking-tight text-slate-900">🗂️ Historique du journal</h2>
        <p class="mt-1 text-sm text-slate-600">Consultez, filtrez et mettez a jour vos entrees precedentes en toute clarte.</p>
      </div>
      <div class="flex gap-2">
        <button type="button" class="inline-flex items-center gap-1.5 rounded-xl bg-gradient-to-r from-[#2563eb] to-[#7c3aed] px-4 py-2 text-xs font-semibold text-white shadow-md shadow-indigo-500/20" @click="showFilter = true">
          <svg viewBox="0 0 24 24" class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M3 5h18l-7 8v5l-4 2v-7z" />
          </svg>
          Filtrer
        </button>
        <button type="button" class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-xs font-semibold text-slate-700 transition-colors hover:bg-slate-50" @click="router.push({ name: 'journal' })">
          Retour
        </button>
      </div>
      </div>
    </div>
    <NotificationsEnLigne />

    <p
      v-if="messageAvis"
      class="mb-4 rounded-xl border px-4 py-3 text-[15px] font-semibold"
      :class="tonAvis === 'success' ? 'border-emerald-300 bg-emerald-50 text-emerald-700' : 'border-amber-300 bg-amber-50 text-amber-700'"
    >
      {{ messageAvis }}
    </p>

    <div v-if="store.filter.type !== 'all'" class="mb-4 flex items-center gap-2 text-sm">
      <span class="text-slate-500">Filtre actif :</span>
      <span class="rounded-full bg-gradient-to-r from-[#2563eb] to-[#7c3aed] px-3 py-1 font-semibold text-white">{{ libelleFiltreActif }}</span>
      <button type="button" class="font-semibold text-slate-500 underline" @click="store.reinitialiserFiltre()">Réinitialiser filtre</button>
    </div>

    <div class="space-y-3">
      <CarteEntreeHistorique
        v-for="entree in store.entreesFiltrees"
        :key="entree.id"
        :entree="entree"
        :editing="false"
        :filter-type="store.filter.type"
        @edit="router.push({ name: 'assistant-journal', query: { edit: entree.id } })"
        @request-delete="demanderSuppression(entree.id)"
      />
    </div>

    <div v-if="showNoResults" class="mt-4 rounded-2xl border border-slate-200 bg-white p-6 text-center shadow-sm">
      <p class="text-sm font-semibold text-slate-800">Aucune entrée trouvée avec ce filtre.</p>
      <p class="mt-1 text-sm text-slate-500">Réinitialise le filtre pour afficher tout l’historique.</p>
      <button
        type="button"
        class="mt-4 rounded-xl bg-gradient-to-r from-[#2563eb] to-[#7c3aed] px-4 py-2 text-sm font-semibold text-white"
        @click="reinitialiserFiltre"
      >
        Réinitialiser le filtre
      </button>
    </div>

    <div v-else-if="!hasEntries" class="mt-4 rounded-2xl border border-slate-200 bg-white p-6 text-center shadow-sm">
      <p class="text-sm font-semibold text-slate-800">Aucune entrée enregistrée pour le moment.</p>
      <button
        type="button"
        class="mt-4 rounded-xl bg-gradient-to-r from-[#2563eb] to-[#7c3aed] px-4 py-2 text-sm font-semibold text-white"
        @click="router.push({ name: 'assistant-journal' })"
      >
        Ajouter une entrée
      </button>
    </div>

    <ModalFiltre
      :open="showFilter"
      :filter="store.filter"
      @close="showFilter = false"
      @apply="appliquerFiltre"
      @reset="reinitialiserFiltre"
    />

    <DialogueConfirmation
      :open="showDeleteConfirm"
      title="Supprimer l'entree"
      message="Cette action est definitive. Voulez-vous continuer ?"
      confirm-label="Supprimer"
      cancel-label="Annuler"
      @cancel="annulerSuppression"
      @confirm="confirmerSuppression"
    />
  </div>
</template>

<script setup>
/*
  Page historique du journal.
  Elle affiche les entrees avec filtres et actions (editer/supprimer).
  Les donnees sont centralisees dans le store journal.
*/

import { computed, onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import ModalFiltre from '@/components/journal/ModalFiltre.vue'
import CarteEntreeHistorique from '@/components/journal/CarteEntreeHistorique.vue'
import DialogueConfirmation from '@/components/ui/DialogueConfirmation.vue'
import NotificationsEnLigne from '@/components/ui/NotificationsEnLigne.vue'
import { useJournalStore } from '@/stores/journal'
import { useNotificationsStore } from '@/stores/notifications'

const route = useRoute()
const router = useRouter()
const store = useJournalStore()
const notifications = useNotificationsStore()
const showFilter = ref(false)
const showDeleteConfirm = ref(false)
const pendingDeleteId = ref(null)

onMounted(async () => {
  await store.initialiser()
})

const libelleFiltreActif = computed(() => {
  const map = {
    all: 'Toutes les données',
    date: 'Par date',
    month: 'Par mois',
    nutrition: 'Nutrition',
    hydration: 'Hydratation',
    sleep: 'Sommeil',
    stress: 'Stress',
    energy: 'Énergie',
    activity: 'Activités'
  }
  return map[store.filter.type] ?? 'Toutes les données'
})

const hasEntries = computed(() => store.entries.length > 0)
const hasFilteredEntries = computed(() => store.entreesFiltrees.length > 0)
const showNoResults = computed(() => hasEntries.value && !hasFilteredEntries.value)
const tonAvis = computed(() => (route.query.notice === 'saved' ? 'success' : route.query.notice === 'canceled' ? 'info' : ''))
const messageAvis = computed(() => {
  if (route.query.notice === 'saved') return 'Modifications enregistrées avec succès.'
  if (route.query.notice === 'canceled') return 'Modifications annulées.'
  return ''
})


const appliquerFiltre = (nextFilter) => {
  store.definirFiltre(nextFilter)
  showFilter.value = false
}

const reinitialiserFiltre = () => {
  store.reinitialiserFiltre()
  showFilter.value = false
}

const demanderSuppression = (id) => {
  pendingDeleteId.value = id
  showDeleteConfirm.value = true
}

const annulerSuppression = () => {
  pendingDeleteId.value = null
  showDeleteConfirm.value = false
  notifications.actionAnnulee()
}

const confirmerSuppression = async () => {
  if (!pendingDeleteId.value) return
  try {
    await store.supprimerEntree(pendingDeleteId.value);
    notifications.actionSupprimee();
  } catch (error) {
    const message = error?.response?.data?.message || "Erreur lors de la suppression.";
    notifications.erreur(message);
  } finally {
    pendingDeleteId.value = null
    showDeleteConfirm.value = false
  }
}
</script>
