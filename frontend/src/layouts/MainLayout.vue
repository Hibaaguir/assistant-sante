<template>
  <div class="min-h-screen bg-[#EEF2F7] text-slate-900 lg:flex">
    <BarreLateraleApp :active="activeRoute" />

    <main class="w-full flex-1">
      <RouterView />
    </main>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import BarreLateraleApp from '../components/navigation/BarreLateraleApp.vue'

const route = useRoute()

const activeRoute = computed(() => {
  if (route.name === 'journal-home' || route.name === 'journal-wizard' || route.name === 'journal-history') {
    return 'journal-home'
  }

  if (route.name === 'dashboard') return 'dashboard'
  if (route.name === 'health') return 'health'
  if (route.name === 'health-data') return 'health-data'
  if (route.name === 'ai') return 'ai'
  if (route.name === 'doctor') return 'doctor'

  return ''
})
</script>
