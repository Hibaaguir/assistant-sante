<template>
    <div
        v-if="authStore.resolved"
        class="min-h-screen text-slate-900 lg:flex"
        :class="
            authStore.isInDoctorSpace || authStore.isAdmin
                ? 'bg-[#f5f6f8]'
                : 'bg-[#EEF2F7]'
        "
    >
        <BarreLateraleApp
            v-if="authStore.isInUserSpace"
            :active="routeActive"
        />

        <main class="w-full flex-1">
            <!-- Barre d'actions globale -->
            <div
                v-if="route.name"
                class="flex w-full items-center justify-end gap-3 px-4 pb-2 pt-4 sm:px-6 lg:px-8"
            >
                <button
                    type="button"
                    class="inline-flex h-[40px] items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 text-xs font-semibold text-slate-600 shadow-sm transition hover:bg-slate-50"
                    @click="logout"
                >
                    <svg
                        viewBox="0 0 24 24"
                        class="h-4 w-4"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        aria-hidden="true"
                    >
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                        <path d="M16 17l5-5-5-5" />
                        <path d="M21 12H9" />
                    </svg>
                    Déconnexion
                </button>
                <MenuUtilisateur />
            </div>

            <RouterView />
        </main>
    </div>
</template>

<script setup>
import { computed, onMounted } from "vue";
import { useRoute } from "vue-router";
import { useAuthStore } from "@/stores/auth";
import BarreLateraleApp from "@/components/navigation/AppSidebar.vue";
import MenuUtilisateur from "@/components/navigation/UserMenu.vue";

const route = useRoute();
const authStore = useAuthStore();

// ─── Computed ─────────────────────────────────────────────────

const JOURNAL_ROUTES = ["journal", "journal-assistant", "journal-history"];
const MAIN_NAV_ROUTES = [
    "dashboard",
    "health-settings",
    "health-data",
    "ai-recommendations",
];

const routeActive = computed(() => {
    if (JOURNAL_ROUTES.includes(route.name)) return "journal";
    if (MAIN_NAV_ROUTES.includes(route.name)) return route.name;
    return "";
});

// ─── Actions ──────────────────────────────────────────────────

async function logout() {
    await authStore.logout();
    window.location.href = "/";
}

onMounted(() => {
    if (!authStore.resolved) authStore.loadUser();
});
</script>
