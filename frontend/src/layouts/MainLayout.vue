<template>
  <div v-if="authStore.resolved" class="min-h-screen text-slate-900 lg:flex" :class="authStore.isDoctor ? 'bg-[#f5f6f8]' : 'bg-[#EEF2F7]'">
    <BarreLateraleApp v-if="!authStore.isDoctor" :active="activeRoute" />

    <main class="w-full flex-1">
      <RouterView />
    </main>
  </div>
</template>

<script setup>
/*
  Layout principal des pages authentifiées.
  Il affiche la barre latérale (patients uniquement) et la vue active via RouterView.
  L'état auth est lu depuis le store centralisé — pas d'appel /auth/me ici.
*/

import { computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import BarreLateraleApp from '@/components/navigation/BarreLateraleApp.vue'

const route = useRoute()
const authStore = useAuthStore()

const activeRoute = computed(() => {
  if (['journal-home', 'journal-wizard', 'journal-history'].includes(route.name)) return 'journal-home'
  return ['dashboard', 'health', 'health-data', 'ai'].includes(route.name) ? route.name : ''
})

onMounted(() => {
  // Le store est déjà chargé par le guard router.
  // Ce onMounted garantit resolved=true même si MainLayout est monté directement.
  if (!authStore.resolved) {
    authStore.fetchUser()
  }
})
</script>
