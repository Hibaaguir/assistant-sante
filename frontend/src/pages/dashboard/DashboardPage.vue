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

const DoctorDashboard = defineAsyncComponent(() => import("@/components/dashboards/doctorDashboard/DoctorDashboard.vue"));
const AdminDashboard = defineAsyncComponent(() => import("@/components/dashboards/adminDashboard/AdminDashboard.vue"));
const UserDashboard = defineAsyncComponent(() => import("@/components/dashboards/userDashboard/UserDashboard.vue"));

const authStore = useAuthStore();

const dashboardComponent = computed(() => {
    if (authStore.isAdmin) return AdminDashboard;
    if (authStore.isDoctor) return DoctorDashboard;
    return UserDashboard;
});
</script>
