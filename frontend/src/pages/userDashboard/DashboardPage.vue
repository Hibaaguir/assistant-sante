<!--
  DashboardPage.vue
  Unified dashboard page — shows the correct dashboard based on the authenticated user's role.
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
import DoctorDashboard from "@/components/dashboards/doctorDashboard/DoctorDashboard.vue";
import AdminDashboard from "@/components/dashboards/adminDashboard/AdminDashboard.vue";
import DashboardLayout from "@/components/dashboards/userDashboard/DashboardLayout.vue";

const authStore = useAuthStore();

onMounted(() => {
    authStore.loadUser();
});

const dashboardComponent = computed(() => {
    if (authStore.isAdmin) return AdminDashboard;
    if (authStore.isDoctor) return DoctorDashboard;
    return DashboardLayout;
});
</script>
