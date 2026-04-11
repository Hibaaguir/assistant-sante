<!--
  Top3ActivitiesChart.vue — Top 5 activités physiques (classement par durée totale)
  Template : cartes de classement, sans affichage du temps.
  Données : GET /journal → physicalActivities[].
-->
<template>
    <section class="mt-5 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="mb-4 flex items-center justify-between">
            <h2 class="text-2xl font-semibold text-slate-900">Top 5 activités</h2>
            <div class="flex gap-2">
                <button
                    v-for="f in filters"
                    :key="f.days"
                    @click="changeFilter(f.days)"
                    class="rounded-lg border px-3 py-1.5 text-sm font-medium transition"
                    :class="days === f.days
                        ? 'border-purple-500 bg-purple-50 text-purple-700'
                        : 'border-slate-200 bg-white text-slate-500 hover:border-slate-300'"
                >
                    {{ f.label }}
                </button>
            </div>
        </div>

        <div v-if="loading" class="flex h-48 items-center justify-center text-slate-400">
            Chargement...
        </div>

        <div v-else-if="!top5.length" class="flex h-48 items-center justify-center text-slate-400">
            Aucune activité sur cette période.
        </div>

        <ol v-else class="space-y-2">
            <li
                v-for="(item, i) in top5"
                :key="item.label"
                class="flex items-center gap-3 rounded-xl border px-4 py-3"
                :class="CARD[i] ?? 'border-slate-100 bg-slate-50'"
            >
                <span
                    class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full text-sm font-bold"
                    :class="BADGE[i] ?? 'bg-slate-200 text-slate-600'"
                >
                    {{ i + 1 }}
                </span>
                <span class="text-xl">{{ icon(item.label) }}</span>
                <span class="flex-1 truncate font-semibold text-slate-800 capitalize">
                    {{ item.label }}
                </span>
            </li>
        </ol>
    </section>
</template>

<script setup>
import { ref, onMounted } from "vue";
import api from "@/services/api";

const CARD  = [
    "border-amber-200  bg-amber-50",
    "border-slate-200  bg-slate-50",
    "border-orange-200 bg-orange-50",
    "border-purple-100 bg-purple-50",
    "border-blue-100   bg-blue-50",
];
const BADGE = [
    "bg-amber-400  text-white",
    "bg-slate-400  text-white",
    "bg-orange-400 text-white",
    "bg-purple-400 text-white",
    "bg-blue-400   text-white",
];
const ICON_MAP = {
    course: "🏃", running: "🏃", natation: "🏊", swimming: "🏊",
    vélo: "🚴", velo: "🚴", cycling: "🚴", yoga: "🧘", pilates: "🧘",
    musculation: "💪", gym: "💪", fitness: "💪", marche: "🚶",
    football: "⚽", tennis: "🎾", basket: "🏀", boxe: "🥊", danse: "💃",
};

function icon(name) {
    const k = name.toLowerCase();
    for (const [key, emoji] of Object.entries(ICON_MAP)) {
        if (k.includes(key)) return emoji;
    }
    return "🏅";
}

const filters    = [{ label: "Par semaine", days: 7 }, { label: "Par mois", days: 30 }];
const loading    = ref(true);
const days       = ref(30);
const top5       = ref([]);
let   allEntries = [];

function cutoffDate(n) {
    const d = new Date();
    d.setDate(d.getDate() - (n - 1));
    return d.toISOString().slice(0, 10);
}

function compute() {
    const cutoff = cutoffDate(days.value);
    const totals = {};
    for (const entry of allEntries) {
        if (!entry.entry_date || entry.entry_date < cutoff) continue;
        for (const act of entry.physical_activities ?? entry.physicalActivities ?? []) {
            const t = act.activity_type || "Autre";
            totals[t] = (totals[t] ?? 0) + (act.duration_minutes ?? 0);
        }
    }
    top5.value = Object.entries(totals)
        .sort((a, b) => b[1] - a[1])
        .slice(0, 5)
        .map(([label]) => ({ label }));
}

async function load() {
    loading.value = true;
    const { data: res } = await api.get("/journal");
    allEntries    = res?.data ?? [];
    loading.value = false;
    compute();
}

function changeFilter(v) { days.value = v; compute(); }

onMounted(load);
</script>
