<template>
  <div v-if="roleResolved" class="min-h-screen text-slate-900 lg:flex" :class="isDoctor ? 'bg-[#f5f6f8]' : 'bg-[#EEF2F7]'">
    <BarreLateraleApp v-if="!isDoctor" :active="activeRoute" />

    <main class="w-full flex-1">
      <RouterView />
    </main>
  </div>
</template>

<script setup>
/*
  Layout principal des pages authentifiees.
  Il affiche la barre laterale et la vue active via RouterView.
  `activeRoute` aligne l'etat visuel de la navigation avec la route courante.
*/

import { computed, onMounted, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import api from '@/services/api'
import BarreLateraleApp from '../components/navigation/BarreLateraleApp.vue'

const route = useRoute()
const isDoctor = ref(false)
const roleResolved = ref(false)

const activeRoute = computed(() => {
  if (route.name === 'journal-home' || route.name === 'journal-wizard' || route.name === 'journal-history') {
    return 'journal-home'
  }

  if (route.name === 'dashboard') return 'dashboard'
  if (route.name === 'health') return 'health'
  if (route.name === 'health-data') return 'health-data'
  if (route.name === 'ai') return 'ai'

  return ''
})

async function loadRole() {
  try {
    const res = await api.get('/auth/me')
    const role = String(res?.data?.user?.role || res?.data?.role || '').toLowerCase()
    isDoctor.value = role === 'medecin' || role === 'doctor'
  } catch (_) {
    isDoctor.value = false
  } finally {
    roleResolved.value = true
  }
}

onMounted(async () => {
  await loadRole()
})

watch(
  () => route.fullPath,
  async () => {
    await loadRole()
  }
)
</script>
