<!--
  Top3ActivitiesChart.vue — Top 5 activités physiques (classement par durée totale)
    Version simplifiée : tableau minimal et filtres semaine/mois.
  Données : GET /journal → physicalActivities[].
-->
<template>
    <section
        class="mt-5 rounded-2xl border-2 border-blue-300 bg-white p-4 shadow-sm transition-all duration-300 hover:shadow-lg hover:border-blue-400"
    >
        <div class="mb-4 flex items-center justify-between">
            <h2 class="text-xl font-semibold text-slate-900">
                Top 5 activités
            </h2>
            <div class="flex gap-2">
                <button
                    v-for="f in filters"
                    :key="f.days"
                    @click="changeFilter(f.days)"
                    class="rounded-lg border px-3 py-1.5 text-sm font-medium transition"
                    :class="
                        days === f.days
                            ? 'border-purple-500 bg-purple-50 text-purple-700'
                            : 'border-slate-200 bg-white text-slate-700 hover:border-slate-300'
                    "
                >
                    {{ f.label }}
                </button>
            </div>
        </div>

        <div
            v-if="loading"
            class="flex h-44 items-center justify-center text-sm text-slate-700"
        >
            Chargement...
        </div>

        <div
            v-else-if="!top5.length"
            class="flex h-44 items-center justify-center text-sm text-slate-700"
        >
            Aucune activité sur cette période.
        </div>

        <table v-else class="w-full text-sm">
            <thead>
                <tr
                    class="border-b border-slate-200 text-xs font-semibold text-slate-700"
                >
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
                    <td class="py-2.5 font-medium text-slate-800">
                        {{ formatActivityLabel(item.label) }}
                    </td>
                    <td class="py-2.5 text-right font-semibold text-slate-700">
                        {{ item.duration }} min
                    </td>
                </tr>
            </tbody>
        </table>
    </section>
</template>

<script setup>
import { ref, onMounted } from "vue";
import api from "@/services/api";

function formatActivityLabel(label) {
    const clean = String(label ?? "Autre")
        .replace(/[_-]+/g, " ")
        .trim()
        .toLowerCase();
    return clean.replace(/\b\w/g, (c) => c.toUpperCase());
}

const filters = [
    { label: "Par semaine", days: 7 },
    { label: "Par mois", days: 30 },
];
const loading = ref(true);
const days = ref(30);
const top5 = ref([]);
let allEntries = [];

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
        for (const act of entry.physical_activities ??
            entry.physicalActivities ??
            []) {
            const t = act.activity_type || "Autre";
            totals[t] = (totals[t] ?? 0) + (act.duration_minutes ?? 0);
        }
    }
    top5.value = Object.entries(totals)
        .sort((a, b) => b[1] - a[1])
        .slice(0, 5)
        .map(([label, duration]) => ({ label, duration: Number(duration) }));
}

async function load() {
    loading.value = true;
    const { data: res } = await api.get("/journal");
    allEntries = res?.data ?? [];
    loading.value = false;
    compute();
}

function changeFilter(v) {
    days.value = v;
    compute();
}

onMounted(load);
</script>
