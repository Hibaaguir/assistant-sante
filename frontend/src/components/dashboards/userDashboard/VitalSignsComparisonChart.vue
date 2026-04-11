<!--
  VitalSignsComparisonChart.vue
  Histogramme groupé (Chart.js) comparant les signes vitaux moyens
  du mois actuel vs le mois précédent.
  Données : GET /health-data/vitals?days=60
-->
<template>
    <section class="mt-5 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="mb-4">
            <h2 class="text-2xl font-semibold text-slate-900">Signes vitaux — comparaison</h2>
            <p class="mt-0.5 text-sm text-slate-400">Moyennes mois actuel vs mois précédent</p>
        </div>

        <div v-if="loading" class="flex h-56 items-center justify-center text-slate-400">
            Chargement...
        </div>
        <div v-else-if="noData" class="flex h-56 items-center justify-center text-slate-400">
            Pas assez de données pour comparer.
        </div>

        <template v-else>
            <canvas ref="canvasRef" class="max-h-56"></canvas>
            <div class="mt-3 flex flex-wrap gap-4 text-sm text-slate-600">
                <span class="flex items-center gap-1.5">
                    <span class="inline-block h-3 w-3 rounded-sm bg-[#6366f1]"></span>
                    {{ currentLabel }}
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="inline-block h-3 w-3 rounded-sm bg-[#94a3b8]"></span>
                    {{ prevLabel }}
                </span>
            </div>
        </template>
    </section>
</template>

<script setup>
import { ref, onMounted, onUnmounted, nextTick } from "vue";
import {
    Chart,
    BarController,
    BarElement,
    CategoryScale,
    LinearScale,
    Tooltip,
} from "chart.js";
import api from "@/services/api";

Chart.register(BarController, BarElement, CategoryScale, LinearScale, Tooltip);

const METRICS = [
    { key: "heart_rate",         label: "Fréq. card.\n(bpm)"    },
    { key: "systolic_pressure",  label: "Pression\nsys. (mmHg)" },
    { key: "diastolic_pressure", label: "Pression\ndia. (mmHg)" },
    { key: "oxygen_saturation",  label: "Sat. O2\n(%)"           },
];
const MONTHS_FR = ["Jan","Fév","Mar","Avr","Mai","Jun","Jul","Aoû","Sep","Oct","Nov","Déc"];

const canvasRef    = ref(null);
const loading      = ref(true);
const noData       = ref(false);
const currentLabel = ref("");
const prevLabel    = ref("");
let   chartInstance = null;

function avg(arr) {
    const v = arr.filter(x => x != null);
    return v.length ? +(v.reduce((s, x) => s + parseFloat(x), 0) / v.length).toFixed(1) : null;
}

function monthKey(dateStr) {
    // measured_at may be "2025-04-12T00:00:00.000000Z" or "2025-04-12"
    return (dateStr ?? "").slice(0, 7);
}

async function load() {
    const { data: res } = await api.get("/health-data/vitals", { params: { days: 60 } });
    const vitals = res?.data ?? [];

    const now = new Date();
    const cur  = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, "0")}`;
    const prevDate = new Date(now.getFullYear(), now.getMonth() - 1, 1);
    const prev = `${prevDate.getFullYear()}-${String(prevDate.getMonth() + 1).padStart(2, "0")}`;

    const buckets = {
        [cur]:  Object.fromEntries(METRICS.map(m => [m.key, []])),
        [prev]: Object.fromEntries(METRICS.map(m => [m.key, []])),
    };

    for (const v of vitals) {
        const mk = monthKey(v.measured_at);
        if (!buckets[mk]) continue;
        for (const m of METRICS) {
            if (v[m.key] != null) buckets[mk][m.key].push(v[m.key]);
        }
    }

    loading.value = false;

    const curAvgs  = METRICS.map(m => avg(buckets[cur][m.key]));
    const prevAvgs = METRICS.map(m => avg(buckets[prev][m.key]));

    if (curAvgs.every(v => v === null) && prevAvgs.every(v => v === null)) {
        noData.value = true;
        return;
    }

    const [cy, cm] = cur.split("-");
    const [py, pm] = prev.split("-");
    currentLabel.value = `${MONTHS_FR[+cm - 1]} ${cy}`;
    prevLabel.value    = `${MONTHS_FR[+pm - 1]} ${py}`;

    await nextTick();

    chartInstance = new Chart(canvasRef.value, {
        type: "bar",
        data: {
            labels: METRICS.map(m => m.label),
            datasets: [
                {
                    label: currentLabel.value,
                    data: curAvgs,
                    backgroundColor: "rgba(99,102,241,0.8)",
                    borderColor: "#6366f1",
                    borderWidth: 1.5,
                    borderRadius: 5,
                },
                {
                    label: prevLabel.value,
                    data: prevAvgs,
                    backgroundColor: "rgba(148,163,184,0.7)",
                    borderColor: "#94a3b8",
                    borderWidth: 1.5,
                    borderRadius: 5,
                },
            ],
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    mode: "index",
                    intersect: false,
                    callbacks: {
                        label: ctx =>
                            ctx.parsed.y != null
                                ? ` ${ctx.dataset.label} : ${ctx.parsed.y}`
                                : ` ${ctx.dataset.label} : —`,
                    },
                },
            },
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 11 } } },
                y: { beginAtZero: false, grid: { color: "#f1f5f9" } },
            },
        },
    });
}

onMounted(load);
onUnmounted(() => chartInstance?.destroy());
</script>
