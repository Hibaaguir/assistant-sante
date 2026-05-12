<template>
    <div v-if="authStore.user" class="w-full">
        <component :is="dashboardComponent" />
    </div>
    <div v-else class="flex items-center justify-center py-12">
        <p class="text-slate-500">Chargement du dashboard...</p>
    </div>
</template>

<script setup>
import { computed, defineAsyncComponent } from "vue";
import { useAuthStore } from "@/stores/auth";

const DoctorDashboard = defineAsyncComponent(() => import("@/pages/dashboard/DoctorDashboardPage.vue"));
const AdminDashboard = defineAsyncComponent(() => import("@/pages/dashboard/AdminDashboardPage.vue"));
const UserDashboard = defineAsyncComponent(() => import("@/pages/dashboard/UserDashboardPage.vue"));

const authStore = useAuthStore();

const dashboardComponent = computed(() => {
    if (authStore.isAdmin) return AdminDashboard;
    if (authStore.isDoctor) return DoctorDashboard;
    return UserDashboard;
});
</script>
