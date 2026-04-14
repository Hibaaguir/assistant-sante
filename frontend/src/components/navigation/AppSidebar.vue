<template>
    <aside
        class="hidden h-screen w-[300px] shrink-0 self-start border-r border-slate-200 bg-white lg:sticky lg:top-0 lg:flex lg:flex-col shadow-sm"
    >
        <!-- Logo -->
        <div class="border-b border-slate-200 px-6 py-8">
            <div class="flex items-center gap-3 mb-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-blue-600">
                    <svg viewBox="0 0 24 24" class="h-7 w-7 text-white" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 21s-6.5-4.5-9-8.5C.7 8.4 3 4 7.3 4c2 0 3.6 1 4.7 2.6C13.1 5 14.7 4 16.7 4 21 4 23.3 8.4 21 12.5 18.5 16.5 12 21 12 21z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-[26px] font-bold leading-none text-slate-900">
                        HealthFlow
                    </h1>
                </div>
            </div>
            <p class="text-[14px] font-medium text-slate-500 pl-15">
                Votre assistant santé
            </p>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 space-y-2 overflow-y-auto px-4 py-6">
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
import ElementNavLaterale from "./SidebarLink.vue";

defineProps({
    active: { type: String, default: "" },
});

const nav = (name, label, icon) => ({ name, label, icon, to: { name } });

// ─── Items de navigation ──────────────────────────────────────

const NAV_ITEMS = [
    nav(
        "dashboard",
        "Tableau de bord",
        `<svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
      <rect x="3"  y="3"  width="7" height="7" rx="1"/>
      <rect x="14" y="3"  width="7" height="7" rx="1"/>
      <rect x="14" y="14" width="7" height="7" rx="1"/>
      <rect x="3"  y="14" width="7" height="7" rx="1"/>
    </svg>`,
    ),

    nav(
        "health-settings",
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
        "health-data",
        "Données de santé",
        `<svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
      <path d="M3 12h4l2-6 4 12 2-6h4"/>
      <circle cx="12" cy="12" r="9"/>
    </svg>`,
    ),

    nav(
        "notifications",
        "Notifications",
        `<svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
      <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
      <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
    </svg>`,
    ),

    nav(
        "ai-recommendations",
        "Recommandations IA",
        `<svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
      <path d="M12 3l1.4 2.8L16 7.2l-2.6 1.4L12 11l-1.4-2.4L8 7.2l2.6-1.4z"/>
      <path d="M5 14l.9 1.8L8 16.8l-2.1 1L5 20l-.9-2.2L2 16.8l2.1-1z"/>
      <path d="M19 13l.8 1.6L21 15.4l-1.2.6L19 17.6l-.8-1.6-1.2-.6 1.2-.8z"/>
    </svg>`,
    ),
];
</script>
