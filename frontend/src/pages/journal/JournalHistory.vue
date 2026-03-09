<template>
  <div class="mx-auto max-w-[1320px] p-4 sm:p-6 lg:p-8">
    <div class="mb-4 flex flex-wrap items-start justify-between gap-3">
      <div>
        <h2 class="text-4xl font-bold tracking-tight text-slate-900">Historique du journal</h2>
        <p class="mt-1 text-xs text-slate-600">Consultez et modifiez vos entrées passées</p>
      </div>
      <div class="flex gap-2">
        <button type="button" class="inline-flex items-center gap-1.5 rounded-xl border border-slate-300 bg-white px-4 py-2 text-xs font-semibold text-slate-700 transition-colors hover:bg-slate-50" @click="showFilter = true">
          <svg viewBox="0 0 24 24" class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M3 5h18l-7 8v5l-4 2v-7z" />
          </svg>
          Filtrer
        </button>
        <button type="button" class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-xs font-semibold text-slate-700 transition-colors hover:bg-slate-50" @click="router.push({ name: 'journal-home' })">
          Retour
        </button>
      </div>
    </div>

    <p
      v-if="noticeMessage"
      class="mb-4 rounded-xl border px-4 py-3 text-[15px] font-semibold"
      :class="noticeTone === 'success' ? 'border-emerald-300 bg-emerald-50 text-emerald-700' : 'border-amber-300 bg-amber-50 text-amber-700'"
    >
      {{ noticeMessage }}
    </p>

    <div v-if="store.filter.type !== 'all'" class="mb-4 flex items-center gap-2 text-sm">
      <span class="text-slate-500">Filtre actif :</span>
      <span class="rounded-full bg-blue-600 px-3 py-1 font-semibold text-white">{{ activeFilterLabel }}</span>
      <button type="button" class="font-semibold text-slate-500 underline" @click="store.reinitialiserFiltre()">Réinitialiser filtre</button>
    </div>

    <div class="space-y-3">
      <CarteEntreeHistorique
        v-for="entry in store.filteredEntries"
        :key="entry.id"
        :entry="entry"
        :editing="false"
        :filter-type="store.filter.type"
        @edit="router.push({ name: 'journal-wizard', query: { edit: entry.id } })"
        @delete="handleDelete(entry.id)"
      />
    </div>

    <div v-if="showNoResults" class="mt-4 rounded-2xl border border-slate-300 bg-white p-6 text-center">
      <p class="text-sm font-semibold text-slate-800">Aucune entrée trouvée avec ce filtre.</p>
      <p class="mt-1 text-sm text-slate-500">Réinitialise le filtre pour afficher tout l’historique.</p>
      <button
        type="button"
        class="mt-4 rounded-xl bg-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-300"
        @click="reinitialiserFiltre"
      >
        Réinitialiser le filtre
      </button>
    </div>

    <div v-else-if="!hasEntries" class="mt-4 rounded-2xl border border-slate-300 bg-white p-6 text-center">
      <p class="text-sm font-semibold text-slate-800">Aucune entrée enregistrée pour le moment.</p>
      <button
        type="button"
        class="mt-4 rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700"
        @click="router.push({ name: 'journal-wizard' })"
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

    <ConfirmDialog
      :open="showDeleteConfirm"
      title="Supprimer l'entree"
      message="Cette action est definitive. Voulez-vous continuer ?"
      confirm-label="Supprimer"
      cancel-label="Annuler"
      @cancel="cancelDelete"
      @confirm="confirmDelete"
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
import ConfirmDialog from '@/components/ui/ConfirmDialog.vue'
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

const activeFilterLabel = computed(() => {
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
const hasFilteredEntries = computed(() => store.filteredEntries.length > 0)
const showNoResults = computed(() => hasEntries.value && !hasFilteredEntries.value)
const noticeTone = computed(() => (route.query.notice === 'saved' ? 'success' : route.query.notice === 'canceled' ? 'info' : ''))
const noticeMessage = computed(() => {
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

const handleDelete = async (id) => {
  pendingDeleteId.value = id
  showDeleteConfirm.value = true
}

const cancelDelete = () => {
  pendingDeleteId.value = null
  showDeleteConfirm.value = false
  notifications.actionCanceled()
}

const confirmDelete = async () => {
  if (!pendingDeleteId.value) return
  try {
    await store.supprimerEntree(pendingDeleteId.value);
    notifications.actionDeleted();
  } catch (error) {
    const message = error?.response?.data?.message || "Erreur lors de la suppression.";
    notifications.error(message);
  } finally {
    pendingDeleteId.value = null
    showDeleteConfirm.value = false
  }
}
</script>
