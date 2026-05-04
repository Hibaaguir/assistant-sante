<!-- Tableau classement : top 5 des activités physiques par durée totale -->
<template>
    <section class="mt-5 rounded-2xl border-2 border-blue-300 bg-white p-4 shadow-sm transition-all duration-300 hover:shadow-lg hover:border-blue-400">

        <!-- Titre et boutons de filtre (semaine / mois) -->
        <div class="mb-4 flex items-center justify-between">
            <h2 class="text-xl font-semibold text-slate-900">Top 5 activités</h2>
            <div class="flex gap-2">
                <button
                    v-for="f in filters"
                    :key="f.days"
                    @click="changeFilter(f.days)"
                    class="rounded-lg border px-3 py-1.5 text-sm font-medium transition"
                    :class="days === f.days
                        ? 'border-purple-500 bg-purple-50 text-purple-700'
                        : 'border-slate-200 bg-white text-slate-700 hover:border-slate-300'"
                >
                    {{ f.label }}
                </button>
            </div>
        </div>

        <!-- Message pendant le chargement -->
        <div v-if="loading" class="flex h-44 items-center justify-center text-sm text-slate-700">
            Chargement...
        </div>

        <!-- Message si aucune activité sur la période -->
        <div v-else-if="!top5.length" class="flex h-44 items-center justify-center text-sm text-slate-700">
            Aucune activité sur cette période.
        </div>

        <!-- Tableau du classement -->
        <table v-else class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-200 text-xs font-semibold text-slate-700">
                    <th class="w-16 py-2 text-left">Rang</th>
                    <th class="py-2 text-left">Activité</th>
                    <th class="w-24 py-2 text-right">Durée</th>
                </tr>
            </thead>
            <tbody>
                <tr
                    v-for="(item, i) in top5"
                    :key="item.label"
                    class="border-b border-slate-100 last:border-0"
                >
                    <td class="py-2.5 text-slate-700">{{ i + 1 }}</td>
                    <td class="py-2.5 font-medium text-slate-800">{{ formatActivityLabel(item.label) }}</td>
                    <td class="py-2.5 text-right font-semibold text-slate-700">{{ item.duration }} min</td>
                </tr>
            </tbody>
        </table>
    </section>
</template>

<script setup>
import { ref, computed, onMounted, watch } from "vue";
import { useDashboardStore } from "@/stores/dashboard";

// Formate le nom d'une activité : remplace _ et - par un espace, met en capitales
function formatActivityLabel(label) {
    const clean = String(label ?? "Autre").replace(/[_-]+/g, " ").trim().toLowerCase();
    return clean.replace(/\b\w/g, (c) => c.toUpperCase());
}

// Options de filtre disponibles
const filters = [
    { label: "Par semaine", days: 7  },
    { label: "Par mois",    days: 30 },
];

const dashStore = useDashboardStore();

// Références et variables
const loading = computed(() => !dashStore.initialized);
const days    = ref(30);
const top5    = ref([]);

let allEntries = [];

// Retourne la date limite (format YYYY-MM-DD) il y a N jours
function cutoffDate(n) {
    const d = new Date();
    d.setDate(d.getDate() - (n - 1));
    return d.toISOString().slice(0, 10);
}

// Calcule le top 5 des activités par durée totale sur la période
function compute() {
    const cutoff = cutoffDate(days.value);
    const totals = {};

    for (const entry of allEntries) {
        // Ignorer les entrées hors de la période
        if (!entry.entry_date || entry.entry_date < cutoff) continue;

        // Accepter les deux noms de champ possibles selon la version de l'API
        const activities = entry.physical_activities ?? entry.physicalActivities ?? [];

        for (const act of activities) {
            const t     = act.activity_type || "Autre";
            totals[t]   = (totals[t] ?? 0) + (act.duration_minutes ?? 0);
        }
    }

    // Trier par durée décroissante et garder les 5 premiers
    top5.value = Object.entries(totals)
        .sort((a, b) => b[1] - a[1])
        .slice(0, 5)
        .map(([label, duration]) => ({ label, duration: Number(duration) }));
}

function load() {
    allEntries = dashStore.journal;
    compute();
}

function changeFilter(v) {
    days.value = v;
    compute();
}

onMounted(() => {
    dashStore.initialize();
    if (dashStore.initialized) load();
});
watch(() => dashStore.initialized, (ready) => { if (ready) load(); });
</script>
