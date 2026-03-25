<!--
  DashboardPage.vue
  Page de dashboard unifiée.
  Cette route affiche le dashboard correspondant au rôle connecté.
-->
<template>
  <component :is="dashboardComponent" />
</template>

<script setup>
import { computed } from 'vue'
import { useAuthStore } from '@/stores/auth'
import TableauDeBordMedecin from '@/components/dashboards/doctorDashboard/TableauDeBordMedecin.vue'
import TableauDeBordAdministrateur from '@/components/dashboards/adminDashboard/TableauDeBordAdministrateur.vue'
import DashboardLayout from '@/components/dashboards/userDashboard/DashboardLayout.vue'

const authStore = useAuthStore()

const dashboardComponent = computed(() => {
  if (authStore.estAdministrateur) return TableauDeBordAdministrateur
  if (authStore.estMedecin) return TableauDeBordMedecin
  return DashboardLayout
})
</script>
