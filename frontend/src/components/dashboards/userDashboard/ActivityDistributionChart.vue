<!--
  ActivityDistributionChart.vue
  Doughnut chart showing physical activity distribution by type (total minutes per type).
  Filterable by last 7 days (week) or last 30 days (month).
  Data comes from GET /journal → physicalActivities[].
-->
<template>
    <section
        class="mt-5 rounded-2xl border-2 border-blue-300 bg-white p-4 shadow-sm transition-all duration-300 hover:shadow-lg hover:border-blue-400"
    >
        <div class="mb-4 flex items-center justify-between">
            <Typography tag="h2" variant="h2-style">
                Activité physique par type
            </Typography>
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
            class="flex h-64 items-center justify-center text-slate-700"
        >
            Chargement...
        </div>

        <div
            v-else-if="noData"
            class="flex h-64 items-center justify-center text-slate-700"
        >
            Aucune activité physique sur cette période.
        </div>

        <div
            v-else
            class="flex flex-col items-center gap-4 sm:flex-row justify-center"
        >
            <canvas ref="canvasRef" class="max-h-64 max-w-[260px]"></canvas>
        </div>
    </section>
</template>

<script setup>
import { ref, onMounted, onUnmounted, nextTick } from "vue";
import Typography from "@/components/ui/Typography.vue";
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
    "#6366f1",
    "#10b981",
    "#f59e0b",
    "#f43f5e",
    "#149bd7",
    "#8b5cf6",
    "#14b8a6",
    "#ec4899",
];

const filters = [
    { label: "Par semaine", days: 7 },
    { label: "Par mois", days: 30 },
];

const canvasRef = ref(null);
const loading = ref(true);
const noData = ref(false);
const days = ref(7);
const summary = ref([]);
let allEntries = [];
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
        for (const act of entry.physical_activities ??
            entry.physicalActivities ??
            []) {
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

    summary.value = labels.map((l) => ({ label: l, minutes: totals[l] }));

    await nextTick();

    chartInstance = new Chart(canvasRef.value, {
        type: "doughnut",
        data: {
            labels,
            datasets: [
                {
                    data: labels.map((l) => totals[l]),
                    backgroundColor: COLORS.slice(0, labels.length),
                    borderWidth: 2.5,
                    borderColor: "#fff",
                },
            ],
        },
        options: {
            responsive: true,
            cutout: "60%",
            plugins: {
                legend: {
                    display: false,
                },
                tooltip: {
                    backgroundColor: "rgba(0, 0, 0, 0.9)",
                    titleFont: { size: 15, weight: "bold" },
                    bodyFont: { size: 13 },
                    padding: 14,
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
    allEntries = res?.data ?? [];
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
