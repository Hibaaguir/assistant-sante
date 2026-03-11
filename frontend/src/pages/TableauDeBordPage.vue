<!--
  TableauDeBordPage.vue
  Page de routage du tableau de bord : affiche l'espace médecin (TableauDeBordMedecin)
  ou l'espace patient (TableauDeBordPatient) en fonction du rôle de
  l'utilisateur connecté. Aucune logique métier ici, juste l'aiguillage.
-->
<template>
  <TableauDeBordMedecin v-if="authStore.resolved && authStore.estMedecin" />
  <TableauDeBordPatient v-else-if="authStore.resolved" />
</template>

<script setup>
import { useAuthStore } from '@/stores/auth'
import TableauDeBordMedecin from '@/components/dashboards/doctorDashboard/TableauDeBordMedecin.vue'
import TableauDeBordPatient from '@/components/dashboards/userDashboard/TableauDeBordPatient.vue'

const authStore = useAuthStore()
</script>
