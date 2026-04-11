<!--
  TreatmentMonthlyChart.vue
  Deux courbes (Chart.js) : traitements pris vs non pris par jour.
  Tooltip : noms des médicaments pris/manqués ce jour-là.
  Filtre : 7 jours (semaine) ou 30 jours (mois).
  Données : GET /health-data/treatment-checks?days=N.
-->
<template>
    <section class="mt-5 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="mb-4 flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-semibold text-slate-900">Distribution des traitements</h2>
                <p class="mt-0.5 text-sm text-slate-400">Prises quotidiennes par médicament</p>
            </div>
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

        <div v-if="loading" class="flex h-56 items-center justify-center text-slate-400">
            Chargement...
        </div>

        <div v-else-if="noData" class="flex h-56 items-center justify-center text-slate-400">
            Aucune donnée de traitement sur cette période.
        </div>

        <template v-else>
            <canvas ref="canvasRef" class="max-h-56"></canvas>

            <!-- Legend -->
            <div class="mt-3 flex items-center gap-5 text-sm text-slate-600">
                <span class="flex items-center gap-1.5">
                    <span class="inline-block h-3 w-3 rounded-full bg-emerald-500"></span> Pris
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="inline-block h-3 w-3 rounded-full bg-rose-400"></span> Non pris
                </span>
            </div>
        </template>
    </section>
</template>

<script setup>
import { ref, onMounted, onUnmounted, nextTick } from "vue";
import {
    Chart,
    LineController,
    LineElement,
    PointElement,
    CategoryScale,
    LinearScale,
    Tooltip,
    Filler,
} from "chart.js";
import api from "@/services/api";

Chart.register(LineController, LineElement, PointElement, CategoryScale, LinearScale, Tooltip, Filler);

const filters = [
    { label: "Par semaine", days: 7  },
    { label: "Par mois",    days: 30 },
];

const canvasRef = ref(null);
const loading   = ref(true);
const noData    = ref(false);
const days      = ref(30);
let   allChecks = [];
let   chartInstance = null;

// Build an array of ISO date strings for the last N days
function buildDateRange(n) {
    return Array.from({ length: n }, (_, i) => {
        const d = new Date();
        d.setDate(d.getDate() - (n - 1 - i));
        return d.toISOString().slice(0, 10);
    });
}

// Short label: "14 avr"
const MONTHS = ["jan","fév","mar","avr","mai","jun","jul","aoû","sep","oct","nov","déc"];
function shortLabel(iso) {
    const d = new Date(iso);
    return `${d.getDate()} ${MONTHS[d.getMonth()]}`;
}

async function buildChart() {
    chartInstance?.destroy();
    noData.value = false;

    const cutoff = (() => {
        const d = new Date();
        d.setDate(d.getDate() - (days.value - 1));
        return d.toISOString().slice(0, 10);
    })();

    const filtered = allChecks.filter(c => c.check_date >= cutoff);

    if (!filtered.length) {
        noData.value = true;
        return;
    }

    // Per day: list of taken names and missed names
    const byDate = {};
    for (const c of filtered) {
        const dt = c.check_date;
        if (!byDate[dt]) byDate[dt] = { taken: [], missed: [] };
        const name = c.medication_name || "Inconnu";
        c.taken ? byDate[dt].taken.push(name) : byDate[dt].missed.push(name);
    }

    const dates      = buildDateRange(days.value);
    const takenData  = dates.map(d => byDate[d]?.taken.length  ?? 0);
    const missedData = dates.map(d => byDate[d]?.missed.length ?? 0);
    const labels     = dates.map(shortLabel);

    await nextTick();

    chartInstance = new Chart(canvasRef.value, {
        type: "line",
        data: {
            labels,
            datasets: [
                {
                    label: "Pris",
                    data: takenData,
                    borderColor: "#10b981",
                    backgroundColor: "rgba(16,185,129,0.08)",
                    pointBackgroundColor: "#10b981",
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                },
                {
                    label: "Non pris",
                    data: missedData,
                    borderColor: "#f43f5e",
                    backgroundColor: "rgba(244,63,94,0.06)",
                    pointBackgroundColor: "#f43f5e",
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                },
            ],
        },
        options: {
            responsive: true,
            interaction: { mode: "index", intersect: false },
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        // Show medication names for each status on that day
                        afterLabel(ctx) {
                            const date = dates[ctx.dataIndex];
                            const day  = byDate[date];
                            if (!day) return "";
                            const list = ctx.dataset.label === "Pris"
                                ? day.taken
                                : day.missed;
                            return list.length
                                ? "  · " + list.join("\n  · ")
                                : "";
                        },
                    },
                },
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { maxRotation: 45, font: { size: 11 } },
                },
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1 },
                    grid: { color: "#f1f5f9" },
                },
            },
        },
    });
}

async function load() {
    loading.value = true;
    const { data: res } = await api.get("/health-data/treatment-checks", {
        params: { days: 90 },   // fetch 90 days once, slice locally on filter change
    });
    allChecks     = res?.data ?? [];
    loading.value = false;
    await buildChart();
}

async function changeFilter(v) {
    days.value = v;
    await buildChart();
}

onMounted(load);
onUnmounted(() => chartInstance?.destroy());
</script>
