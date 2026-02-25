<template>
  <aside class="hidden w-[286px] shrink-0 border-r border-slate-300 bg-slate-100 lg:flex lg:flex-col">
    <div class="border-b border-slate-300 px-8 py-8">
      <h1 class="text-[40px] font-medium leading-none tracking-tight text-slate-700">HealthTrack</h1>
      <p class="mt-2 text-[28px] text-slate-600">Votre assistant santé</p>
    </div>

    <nav class="flex-1 space-y-2 px-5 py-8">
      <ElementNavLaterale
        v-for="item in navItems"
        :key="item.name"
        :to="item.to"
        :label="item.label"
        :icon="item.icon"
        :active="item.name === active"
      />
    </nav>

    <div class="space-y-6 border-t border-slate-300 px-6 py-7">
      <button
        type="button"
        class="flex w-full items-center gap-3 rounded-xl p-2 text-left transition-colors hover:bg-slate-200"
        @click="isAccountPanelOpen = !isAccountPanelOpen"
      >
        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-600 text-white">
          <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <circle cx="12" cy="8" r="4" />
            <path d="M6 20a6 6 0 0 1 12 0" />
          </svg>
        </div>
        <div class="flex-1">
          <p class="text-base font-semibold leading-none tracking-tight text-slate-800">Compte utilisateur</p>
          <p class="mt-1 text-sm leading-none text-slate-500">Plan Premium</p>
        </div>
        <svg
          viewBox="0 0 24 24"
          class="h-4 w-4 text-slate-500 transition-transform"
          :class="isAccountPanelOpen ? 'rotate-180' : ''"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
          stroke-linecap="round"
          stroke-linejoin="round"
          aria-hidden="true"
        >
          <path d="m6 9 6 6 6-6" />
        </svg>
      </button>

      <div v-if="isAccountPanelOpen" class="rounded-xl border border-slate-300 bg-white p-3 text-sm text-slate-600">
        <p class="font-semibold text-slate-800">Interface compte</p>
        <p class="mt-1">Email: utilisateur@healthtrack.app</p>
        <p class="mt-1">Statut: Premium actif</p>
        <div class="mt-3 flex gap-2">
          <button type="button" class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-100">Modifier profil</button>
          <button type="button" class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-100">Paramètres</button>
        </div>
      </div>

      <button type="button" class="flex items-center gap-2 text-[28px] font-semibold text-slate-600 hover:text-slate-800">
        <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
          <path d="M16 17l5-5-5-5" />
          <path d="M21 12H9" />
        </svg>
        Déconnexion
      </button>
    </div>
  </aside>
</template>

<script setup>
import { ref } from 'vue'
import ElementNavLaterale from './ElementNavLaterale.vue'

defineProps({
  active: {
    type: String,
    default: ''
  }
})

const isAccountPanelOpen = ref(false)

const navItems = [
  {
    name: 'dashboard',
    label: 'Dashboard',
    to: { name: 'dashboard' },
    icon: `<svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/></svg>`
  },
  {
    name: 'health',
    label: 'Profil santé',
    to: { name: 'health' },
    icon: `<svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12h4l3-8 4 16 3-8h4"/></svg>`
  },
  {
    name: 'journal-home',
    label: 'Journal quotidien',
    to: { name: 'journal-home' },
    icon: `<svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v19H6.5A2.5 2.5 0 0 1 4 18.5z"/><path d="M8 7h8"/></svg>`
  },
  {
    name: 'ai',
    label: 'Recommandations IA',
    to: { name: 'ai' },
    icon: `<svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3l1.4 2.8L16 7.2l-2.6 1.4L12 11l-1.4-2.4L8 7.2l2.6-1.4z"/><path d="M5 14l.9 1.8L8 16.8l-2.1 1L5 20l-.9-2.2L2 16.8l2.1-1z"/><path d="M19 13l.8 1.6L21 15.4l-1.2.6L19 17.6l-.8-1.6-1.2-.6 1.2-.8z"/></svg>`
  },
  {
    name: 'doctor',
    label: 'Vue médecin',
    to: { name: 'doctor' },
    icon: `<svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M19 14v5a2 2 0 0 1-2 2h-2"/><path d="M5 14v5a2 2 0 0 0 2 2h2"/><path d="M12 2v7"/><path d="M9 6h6"/><path d="M8 14a4 4 0 1 1 8 0v2H8z"/></svg>`
  }
]
</script>
