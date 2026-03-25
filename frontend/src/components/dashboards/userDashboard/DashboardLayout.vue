<!--
  DashboardLayout.vue
  Composant de coordination qui combine HealthChart et NotificationsWidget.
  Responsable: Orchestrer les composants enfants et gérer le layout global.
-->
<template>
  <div class="mx-auto max-w-[1320px] p-4 sm:p-6 lg:p-8">
    <header class="flex items-start justify-between gap-3">
      <div>
        <h1 class="text-[34px] font-semibold leading-none text-slate-900">Dashboard</h1>
        <p class="mt-2 text-sm text-slate-600">Vue d'ensemble de votre santé</p>
      </div>
    </header>

    <!-- Composant de notifications temporaires (toast) -->
    <NotificationsEnLigne />

    <!-- Widget des notifications de traitements -->
    <NotificationsWidget />

    <section class="mt-5 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
      <div class="flex items-center justify-between gap-3">
        <h2 class="text-[20px] font-semibold text-slate-900">Observation de votre médecin</h2>
        <p v-if="doctorObservation.updatedAtLabel" class="text-xs text-slate-500">Mise à jour : {{ doctorObservation.updatedAtLabel }}</p>
      </div>

      <p v-if="doctorObservation.text" class="mt-2 text-sm leading-6 text-slate-700">{{ doctorObservation.text }}</p>
      <p v-else class="mt-2 text-sm text-slate-500">Aucune observation médecin disponible pour le moment.</p>

      <p v-if="doctorObservation.doctorName" class="mt-2 text-xs text-slate-500">Médecin : {{ doctorObservation.doctorName }}</p>
    </section>

    <!-- Graphique des signes vitaux -->
    <HealthChart />

    <!-- StatsWidget sera ajouté ici plus tard -->
    <!-- <StatsWidget /> -->
  </div>
</template>

<script setup>
import { onMounted, reactive } from 'vue'
import api from '@/services/api'
import HealthChart from './HealthChart.vue'
import NotificationsWidget from './NotificationsWidget.vue'
import NotificationsEnLigne from '@/components/ui/NotificationsEnLigne.vue'

const doctorObservation = reactive({
  text: '',
  updatedAtLabel: '',
  doctorName: '',
})

function formatObservationDate(dateString) {
  if (!dateString) return ''
  const date = new Date(dateString)
  if (Number.isNaN(date.getTime())) return ''
  return date.toLocaleString('fr-FR', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

async function chargerObservationMedecin() {
  try {
    const res = await api.get('/health-data/overview', { params: { days: 7 } })
    const observation = res?.data?.data?.doctor_observation || {}
    doctorObservation.text = String(observation?.text || '')
    doctorObservation.updatedAtLabel = formatObservationDate(observation?.updated_at)
    doctorObservation.doctorName = String(observation?.doctor_name || '')
  } catch {
    doctorObservation.text = ''
    doctorObservation.updatedAtLabel = ''
    doctorObservation.doctorName = ''
  }
}

onMounted(async () => {
  await chargerObservationMedecin()
})
</script>
