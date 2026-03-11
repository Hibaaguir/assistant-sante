<template>
  <!-- Structure principale du layout authentifié avec affichage conditionnel de la barre latérale et du contenu courant -->
  <div v-if="authStore.resolved" class="min-h-screen text-slate-900 lg:flex" :class="authStore.estMedecin ? 'bg-[#f5f6f8]' : 'bg-[#EEF2F7]'">
    <BarreLateraleApp v-if="!authStore.estMedecin" :active="routeActive" />

    <main class="w-full flex-1">
      <RouterView />
    </main>
  </div>
</template>

<script setup>
// Import des dépendances Vue, du routeur, du store d'authentification et du composant de navigation latérale
import { computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import BarreLateraleApp from '@/components/navigation/BarreLateraleApp.vue'

// Initialisation de la route courante et du store centralisé d'authentification
const route = useRoute()
const authStore = useAuthStore()

// Calcul de la route active pour synchroniser l'état visuel de la barre latérale avec la page affichée
const routeActive = computed(() => {
  if (['journal', 'assistant-journal', 'historique-journal'].includes(route.name)) return 'journal'
  return ['tableau-de-bord', 'mon-profil-sante', 'donnees-sante', 'recommandations-ia'].includes(route.name) ? route.name : ''
})

// Vérification au montage que l'état d'authentification est bien résolu avant l'affichage complet du layout
onMounted(() => {
  if (!authStore.resolved) {
    authStore.chargerUtilisateur()
  }
})
</script>
