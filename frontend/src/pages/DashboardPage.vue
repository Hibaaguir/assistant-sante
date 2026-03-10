<!--
  DashboardPage.vue
  Page de routage du dashboard : affiche le dashboard médecin (DoctorDashboard)
  ou le dashboard patient (PatientDashboard) en fonction du rôle de
  l'utilisateur connecté. Aucune logique métier ici, juste l'aiguillage.
-->
<template>
  <DoctorDashboard v-if="authStore.resolved && authStore.isDoctor" />
  <PatientDashboard v-else-if="authStore.resolved" />
</template>

<script setup>
import { useAuthStore } from '@/stores/auth'
import DoctorDashboard from '@/components/dashboards/doctorDashboard/DoctorDashboard.vue'
import PatientDashboard from '@/components/dashboards/userDashboard/PatientDashboard.vue'

const authStore = useAuthStore()
</script>
