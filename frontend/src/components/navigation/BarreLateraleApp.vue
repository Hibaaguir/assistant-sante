<template>
  <aside class="hidden h-screen w-[266px] shrink-0 self-start border-r border-slate-300 bg-slate-100 lg:sticky lg:top-0 lg:flex lg:flex-col">
    <div class="border-b border-slate-300 px-6 py-7">
      <h1 class="text-[38px] font-medium leading-none tracking-tight text-slate-700">HealthTrack</h1>
      <p class="mt-2 text-[14px] text-slate-600">Votre assistant santé</p>
    </div>

    <nav class="flex-1 space-y-2 overflow-y-auto px-5 py-6">
      <ElementNavLaterale
        v-for="item in navItems"
        :key="item.name"
        :to="item.to"
        :label="item.label"
        :icon="item.icon"
        :active="item.name === active"
      />
    </nav>

    <div class="space-y-4 border-t border-slate-300 px-5 py-6">
      <div class="flex items-center gap-3">
        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-600 text-white">
          <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <circle cx="12" cy="8" r="4" />
            <path d="M6 20a6 6 0 0 1 12 0" />
          </svg>
        </div>
        <div>
          <p class="text-[18px] font-semibold leading-none text-slate-800">{{ authStore.nomUtilisateur }}</p>
          <p class="mt-1 text-[14px] text-slate-500">{{ libelleRoleUtilisateur }}</p>
        </div>
      </div>

      <button
        type="button"
        class="flex items-center gap-2 text-[14px] font-semibold text-slate-600 hover:text-slate-800"
        @click="deconnexion"
      >
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
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import ElementNavLaterale from './ElementNavLaterale.vue'

defineProps({
  active: {
    type: String,
    default: ''
  }
})

const router = useRouter()
const authStore = useAuthStore()

const libelleRoleUtilisateur = computed(() => {
  const role = authStore.roleUtilisateur
  if (role === 'medecin') return 'Médecin'
  if (role === 'user') return 'Patient'
  return ''
})

async function deconnexion() {
  await authStore.deconnexion()
  router.push({ name: 'connexion' })
}

const navItem = (name, label, icon) => ({ name, label, to: { name }, icon })

const navItems = [
  navItem('tableau-de-bord',   'Tableau de bord',   `<svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/></svg>`),
  navItem('mon-profil-sante',  'Profil santé',      `<svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12h4l3-8 4 16 3-8h4"/></svg>`),
  navItem('journal',           'Journal quotidien', `<svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v19H6.5A2.5 2.5 0 0 1 4 18.5z"/><path d="M8 7h8"/></svg>`),
  navItem('donnees-sante',     'Données de santé',  `<svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12h4l2-6 4 12 2-6h4"/><circle cx="12" cy="12" r="9"/></svg>`),
  navItem('recommandations-ia','Recommandations IA',`<svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3l1.4 2.8L16 7.2l-2.6 1.4L12 11l-1.4-2.4L8 7.2l2.6-1.4z"/><path d="M5 14l.9 1.8L8 16.8l-2.1 1L5 20l-.9-2.2L2 16.8l2.1-1z"/><path d="M19 13l.8 1.6L21 15.4l-1.2.6L19 17.6l-.8-1.6-1.2-.6 1.2-.8z"/></svg>`)
]
</script>
