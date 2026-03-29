<!--
  DashboardPage.vue
  Page de dashboard unifiée.
  Cette route affiche le dashboard correspondant au rôle connecté.
-->
<template>
    <div v-if="authStore.user" class="w-full">
        <component :is="dashboardComponent" />
    </div>
    <div v-else class="flex items-center justify-center py-12">
        <p class="text-slate-500">Chargement du dashboard...</p>
    </div>
</template>

<script setup>
import { computed, onMounted } from "vue";
import { useAuthStore } from "@/stores/auth";
import TableauDeBordMedecin from "@/components/dashboards/doctorDashboard/TableauDeBordMedecin.vue";
import TableauDeBordAdministrateur from "@/components/dashboards/adminDashboard/TableauDeBordAdministrateur.vue";
import DashboardLayout from "@/components/dashboards/userDashboard/DashboardLayout.vue";

const authStore = useAuthStore();

onMounted(() => {
    authStore.chargerUtilisateur();
});

const dashboardComponent = computed(() => {
    if (authStore.estAdministrateur) return TableauDeBordAdministrateur;
    if (authStore.estMedecin) return TableauDeBordMedecin;
    return DashboardLayout;
});
</script>
