<!--
  ActivityDistributionChart.vue
  Doughnut chart showing physical activity distribution by type (total minutes per type).
  Filterable by last 7 days (week) or last 30 days (month).
  Data comes from GET /journal → physicalActivities[].
-->
<template>
    <section class="mt-5 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="mb-4 flex items-center justify-between">
            <h2 class="text-2xl font-semibold text-slate-900">
                Activité physique par type
            </h2>
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

        <div v-if="loading" class="flex h-64 items-center justify-center text-slate-400">
            Chargement...
        </div>

        <div v-else-if="noData" class="flex h-64 items-center justify-center text-slate-400">
            Aucune activité physique sur cette période.
        </div>

        <div v-else class="flex flex-col items-center gap-4 sm:flex-row">
            <canvas ref="canvasRef" class="max-h-64 max-w-[260px]"></canvas>

            <ul class="w-full space-y-2 text-sm sm:w-auto sm:min-w-[160px]">
                <li
                    v-for="(item, i) in summary"
                    :key="item.label"
                    class="flex items-center gap-2"
                >
                    <span
                        class="inline-block h-3 w-3 flex-shrink-0 rounded-full"
                        :style="{ backgroundColor: COLORS[i % COLORS.length] }"
                    ></span>
                    <span class="text-slate-700 font-medium">{{ item.label }}</span>
                    <span class="ml-auto text-slate-500">{{ item.minutes }} min</span>
                </li>
            </ul>
        </div>
    </section>
</template>

<script setup>
import { ref, onMounted, onUnmounted, nextTick } from "vue";
import {
    Chart,
    DoughnutController,
    ArcElement,
    Legend,
    Tooltip,
} from "chart.js";
import api from "@/services/api";

Chart.register(DoughnutController, ArcElement, Legend, Tooltip);

const COLORS = [
    "#6366f1", "#10b981", "#f59e0b",
    "#f43f5e", "#149bd7", "#8b5cf6",
    "#14b8a6", "#ec4899",
];

const filters = [
    { label: "Par semaine", days: 7  },
    { label: "Par mois",    days: 30 },
];

const canvasRef = ref(null);
const loading   = ref(true);
const noData    = ref(false);
const days      = ref(7);
const summary   = ref([]);
let allEntries    = [];
let chartInstance = null;

// Compute the ISO date string for N days ago
function dateNDaysAgo(n) {
    const d = new Date();
    d.setDate(d.getDate() - (n - 1));
    return d.toISOString().slice(0, 10);
}

function aggregate() {
    const cutoff = dateNDaysAgo(days.value);
    const totals = {};

    for (const entry of allEntries) {
        if (!entry.entry_date || entry.entry_date < cutoff) continue;
        for (const act of entry.physical_activities ?? entry.physicalActivities ?? []) {
            const type = act.activity_type || "Autre";
            totals[type] = (totals[type] ?? 0) + (act.duration_minutes ?? 0);
        }
    }

    return totals;
}

async function buildChart() {
    chartInstance?.destroy();
    noData.value = false;

    const totals = aggregate();
    const labels = Object.keys(totals);

    if (labels.length === 0) {
        noData.value = true;
        return;
    }

    summary.value = labels.map(l => ({ label: l, minutes: totals[l] }));

    await nextTick();

    chartInstance = new Chart(canvasRef.value, {
        type: "doughnut",
        data: {
            labels,
            datasets: [{
                data: labels.map(l => totals[l]),
                backgroundColor: COLORS.slice(0, labels.length),
                borderWidth: 2,
                borderColor: "#fff",
            }],
        },
        options: {
            responsive: true,
            cutout: "60%",
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: (ctx) => ` ${ctx.parsed} min`,
                    },
                },
            },
        },
    });
}

async function load() {
    loading.value = true;
    const { data: res } = await api.get("/journal");
    allEntries    = res?.data ?? [];
    loading.value = false;
    await buildChart();
}

async function changeFilter(value) {
    days.value = value;
    await buildChart();
}

onMounted(load);
onUnmounted(() => chartInstance?.destroy());
</script>
