<!--
  DashboardLayout.vue
  Layout principal du patient : notifications, observation médecin, graphique.
-->
<template>
  <div class="mx-auto max-w-[1320px] p-4 sm:p-6 lg:p-8">

    <header>
      <h1 class="text-[34px] font-semibold leading-none text-slate-900">Dashboard</h1>
      <p class="mt-2 text-sm text-slate-600">Vue d'ensemble de votre santé</p>
    </header>

    <NotificationsEnLigne />
    <NotificationsWidget />

    <!-- Observation médecin -->
    <section class="mt-5 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
      <div class="flex items-center justify-between gap-3">
        <h2 class="text-[20px] font-semibold text-slate-900">Observation de votre médecin</h2>
        <p v-if="obs.updatedAtLabel" class="text-xs text-slate-500">Mise à jour : {{ obs.updatedAtLabel }}</p>
      </div>

      <p class="mt-2 text-sm leading-6" :class="obs.text ? 'text-slate-700' : 'text-slate-500'">
        {{ obs.text || 'Aucune observation médecin disponible pour le moment.' }}
      </p>

      <p v-if="obs.doctorName" class="mt-2 text-xs text-slate-500">Médecin : {{ obs.doctorName }}</p>
    </section>

    <HealthChart />

  </div>
</template>

<script setup>
import { onMounted, reactive } from 'vue'
import api from '@/services/api'
import HealthChart          from './HealthChart.vue'
import NotificationsWidget  from './NotificationsWidget.vue'
import NotificationsEnLigne from '@/components/ui/NotificationsEnLigne.vue'

const obs = reactive({ text: '', updatedAtLabel: '', doctorName: '' })

function formatDate(str) {
  if (!str) return ''
  const d = new Date(str)
  return isNaN(d) ? '' : d.toLocaleString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' })
}

async function chargerObservation() {
  try {
    const { data } = await api.get('/health-data/overview', { params: { days: 7 } })
    const o = data?.data?.doctor_observation ?? {}
    obs.text           = o.text        ?? ''
    obs.updatedAtLabel = formatDate(o.updated_at)
    obs.doctorName     = o.doctor_name ?? ''
  } catch {
    obs.text = obs.updatedAtLabel = obs.doctorName = ''
  }
}

onMounted(chargerObservation)
</script>