<!--
  TableauDeBordPage.vue
  Page de routage du tableau de bord : affiche l'espace administrateur,
  l'espace médecin (TableauDeBordMedecin) ou l'espace patient
  (TableauDeBordPatient) en fonction du rôle de
  l'utilisateur connecté. Aucune logique métier ici, juste l'aiguillage.
-->
<template>
  <TableauDeBordAdministrateur v-if="authStore.resolved && authStore.estAdministrateur" />
  <TableauDeBordMedecin v-else-if="authStore.resolved && authStore.estDansEspaceMedecin" />
  <TableauDeBordPatient v-else-if="authStore.resolved" />
</template>

<script setup>
import { useAuthStore } from '@/stores/auth'
import TableauDeBordAdministrateur from '@/components/dashboards/adminDashboard/TableauDeBordAdministrateur.vue'
import TableauDeBordMedecin from '@/components/dashboards/doctorDashboard/TableauDeBordMedecin.vue'
import TableauDeBordPatient from '@/components/dashboards/userDashboard/TableauDeBordPatient.vue'

const authStore = useAuthStore()
</script>
