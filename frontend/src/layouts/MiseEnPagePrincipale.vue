<template>
  <!-- Structure principale du layout authentifié avec affichage conditionnel de la barre latérale et du contenu courant -->
  <div v-if="authStore.resolved" class="min-h-screen text-slate-900 lg:flex" :class="authStore.estAdministrateur ? 'bg-[#f5f6f8]' : 'bg-[#EEF2F7]'">
    <BarreLateraleApp v-if="authStore.estDansEspacePersonnel" :active="routeActive" />

    <main class="w-full flex-1">
      <div
        v-if="afficherBarreActionsGlobale"
        class="mx-auto flex w-full max-w-[1160px] items-center justify-end gap-3 px-4 pb-1 pt-2 sm:px-4 lg:px-6"
      >
        <button
          type="button"
          class="inline-flex h-[40px] items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 text-xs font-semibold text-slate-600 shadow-sm transition hover:bg-slate-50"
          @click="deconnexion"
        >
          <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
            <path d="M16 17l5-5-5-5" />
            <path d="M21 12H9" />
          </svg>
          Deconnexion
        </button>
        <MenuUtilisateur />
      </div>

      <RouterView />
    </main>
  </div>
</template>

<script setup>
// Import des dépendances Vue, du routeur, du store d'authentification et du composant de navigation latérale
import { computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import BarreLateraleApp from '@/components/navigation/BarreLateraleApp.vue'
import MenuUtilisateur from '@/components/navigation/MenuUtilisateur.vue'

// Initialisation de la route courante et du store centralisé d'authentification
const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()

// Calcul de la route active pour synchroniser l'état visuel de la barre latérale avec la page affichée
const routeActive = computed(() => {
  if (['journal', 'assistant-journal', 'historique-journal'].includes(route.name)) return 'journal'
  return ['tableau-de-bord', 'mon-profil-sante', 'donnees-sante', 'recommandations-ia', 'mes-taches'].includes(route.name) ? route.name : ''
})

const afficherBarreActionsGlobale = computed(() => Boolean(route.name))

async function deconnexion() {
  await authStore.deconnexion()
  router.push({ name: 'accueil-publique' })
}

// Vérification au montage que l'état d'authentification est bien résolu avant l'affichage complet du layout
onMounted(() => {
  if (!authStore.resolved) {
    authStore.chargerUtilisateur()
  }
})
</script>
