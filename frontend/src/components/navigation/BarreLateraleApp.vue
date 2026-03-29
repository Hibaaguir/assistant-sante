<template>
    <aside
        class="hidden h-screen w-[300px] shrink-0 self-start border-r border-purple-200 bg-gradient-to-b from-purple-100 via-purple-100 to-purple-200 lg:sticky lg:top-0 lg:flex lg:flex-col"
    >
        <!-- Logo -->
        <div class="border-b border-purple-200 px-8 py-9">
            <h1 class="text-[44px] font-semibold leading-none tracking-tight">
                <span class="text-purple-600">HealthFlow</span>
            </h1>
            <p class="mt-3 text-[15px] text-purple-500">
                Votre assistant santé
            </p>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 space-y-3 overflow-y-auto px-6 py-8">
            <ElementNavLaterale
                v-for="item in NAV_ITEMS"
                :key="item.name"
                :to="item.to"
                :label="item.label"
                :icon="item.icon"
                :active="item.name === active"
            />
        </nav>
    </aside>
</template>

<script setup>
import { computed } from "vue";
import { useAuthStore } from "@/stores/auth";
import ElementNavLaterale from "./ElementNavLaterale.vue";

defineProps({
    active: { type: String, default: "" },
});

const authStore = useAuthStore();

// ─── Helpers ──────────────────────────────────────────────────

const ROLES = { medecin: "Médecin", user: "Patient" };
const roleLabel = computed(() => ROLES[authStore.roleUtilisateur] ?? "");

const nav = (name, label, icon) => ({ name, label, icon, to: { name } });

// ─── Items de navigation ──────────────────────────────────────

const NAV_ITEMS = [
    nav(
        "tableau-de-bord",
        "Tableau de bord",
        `<svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
      <rect x="3"  y="3"  width="7" height="7" rx="1"/>
      <rect x="14" y="3"  width="7" height="7" rx="1"/>
      <rect x="14" y="14" width="7" height="7" rx="1"/>
      <rect x="3"  y="14" width="7" height="7" rx="1"/>
    </svg>`,
    ),

    nav(
        "mon-profil-sante",
        "Profil santé",
        `<svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
      <path d="M3 12h4l3-8 4 16 3-8h4"/>
    </svg>`,
    ),

    nav(
        "journal",
        "Journal quotidien",
        `<svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
      <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
      <path d="M6.5 2H20v19H6.5A2.5 2.5 0 0 1 4 18.5z"/>
      <path d="M8 7h8"/>
    </svg>`,
    ),

    nav(
        "donnees-sante",
        "Données de santé",
        `<svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
      <path d="M3 12h4l2-6 4 12 2-6h4"/>
      <circle cx="12" cy="12" r="9"/>
    </svg>`,
    ),

    nav(
        "recommandations-ia",
        "Recommandations IA",
        `<svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
      <path d="M12 3l1.4 2.8L16 7.2l-2.6 1.4L12 11l-1.4-2.4L8 7.2l2.6-1.4z"/>
      <path d="M5 14l.9 1.8L8 16.8l-2.1 1L5 20l-.9-2.2L2 16.8l2.1-1z"/>
      <path d="M19 13l.8 1.6L21 15.4l-1.2.6L19 17.6l-.8-1.6-1.2-.6 1.2-.8z"/>
    </svg>`,
    ),
];
</script>
