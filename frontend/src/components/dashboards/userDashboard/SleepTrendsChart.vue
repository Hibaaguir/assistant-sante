<!--
  SleepTrendsChart.vue
  Radar chart (Chart.js) — tendances de sommeil par mois (6 derniers mois).
  Chaque axe = un mois, valeur = moyenne des heures de sommeil.
  Second dataset = objectif recommandé 8 h.
  Données : GET /journal → entry.sleep (heures, 0-24).
-->
<template>
    <section class="mt-5 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="mb-4">
            <h2 class="text-2xl font-semibold text-slate-900">Tendances du sommeil</h2>
            <p class="mt-0.5 text-sm text-slate-400">Moyenne mensuelle — 6 derniers mois</p>
        </div>

        <div v-if="loading" class="flex h-64 items-center justify-center text-slate-400">
            Chargement...
        </div>
        <div v-else-if="noData" class="flex h-64 items-center justify-center text-slate-400">
            Aucune donnée de sommeil disponible.
        </div>

        <template v-else>
            <canvas ref="canvasRef" class="max-h-64"></canvas>

            <div class="mt-3 flex flex-wrap gap-4 text-sm text-slate-600">
                <span class="flex items-center gap-1.5">
                    <span class="inline-block h-3 w-3 rounded-full bg-[#8b5cf6]"></span>
                    Sommeil moyen
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="inline-block h-3 w-3 rounded-full border-2 border-dashed border-[#f59e0b] bg-transparent"></span>
                    Objectif 8 h
                </span>
            </div>

            <!-- Monthly chips: green ≥ 7h, red < 7h -->
            <div class="mt-3 flex flex-wrap gap-2">
                <span
                    v-for="item in monthlyData"
                    :key="item.label"
                    class="rounded-full px-3 py-0.5 text-xs font-medium"
                    :class="item.avg !== null && item.avg >= 7
                        ? 'bg-violet-100 text-violet-700'
                        : 'bg-rose-100 text-rose-600'"
                >
                    {{ item.label }} · {{ item.avg !== null ? item.avg + ' h' : '—' }}
                </span>
            </div>
        </template>
    </section>
</template>

<script setup>
import { ref, onMounted, onUnmounted, nextTick } from "vue";
import {
    Chart,
    RadarController,
    RadialLinearScale,
    PointElement,
    LineElement,
    Filler,
    Tooltip,
} from "chart.js";
import api from "@/services/api";

Chart.register(RadarController, RadialLinearScale, PointElement, LineElement, Filler, Tooltip);

const MONTHS_FR = ["Jan","Fév","Mar","Avr","Mai","Jun","Jul","Aoû","Sep","Oct","Nov","Déc"];
const GOAL = 8;

const canvasRef   = ref(null);
const loading     = ref(true);
const noData      = ref(false);
const monthlyData = ref([]);
let   chartInstance = null;

function last6Months() {
    return Array.from({ length: 6 }, (_, i) => {
        const d = new Date();
        d.setDate(1);
        d.setMonth(d.getMonth() - (5 - i));
        return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, "0")}`;
    });
}

function avg(arr) {
    const v = arr.filter(x => x != null);
    return v.length ? +(v.reduce((s, x) => s + x, 0) / v.length).toFixed(1) : null;
}

async function load() {
    const { data: res } = await api.get("/journal");
    const entries = res?.data ?? [];

    const months  = last6Months();
    const buckets = Object.fromEntries(months.map(m => [m, []]));

    for (const e of entries) {
        if (!e.entry_date || e.sleep == null) continue;
        const mk = e.entry_date.slice(0, 7);
        if (buckets[mk] !== undefined) buckets[mk].push(parseFloat(e.sleep));
    }

    loading.value = false;

    const avgs = months.map(m => avg(buckets[m]));
    if (avgs.every(v => v === null)) {
        noData.value = true;
        return;
    }

    monthlyData.value = months.map((m, i) => ({
        label: MONTHS_FR[+m.split("-")[1] - 1],
        avg:   avgs[i],
    }));

    await nextTick();

    chartInstance = new Chart(canvasRef.value, {
        type: "radar",
        data: {
            labels: monthlyData.value.map(d => d.label),
            datasets: [
                {
                    label: "Sommeil moyen (h)",
                    data: avgs.map(v => v ?? 0),
                    borderColor: "#8b5cf6",
                    backgroundColor: "rgba(139,92,246,0.15)",
                    pointBackgroundColor: "#8b5cf6",
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    fill: true,
                },
                {
                    label: "Objectif 8 h",
                    data: Array(months.length).fill(GOAL),
                    borderColor: "#f59e0b",
                    borderDash: [6, 4],
                    backgroundColor: "rgba(245,158,11,0.05)",
                    pointRadius: 0,
                    fill: true,
                },
            ],
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx =>
                            ctx.dataset.label === "Objectif 8 h"
                                ? " Objectif : 8 h"
                                : ` Moyenne : ${ctx.raw} h`,
                    },
                },
            },
            scales: {
                r: {
                    min: 0,
                    max: 12,
                    ticks: {
                        stepSize: 2,
                        callback: v => v + " h",
                        font: { size: 10 },
                        backdropColor: "transparent",
                    },
                    grid:        { color: "#e2e8f0" },
                    angleLines:  { color: "#e2e8f0" },
                    pointLabels: { font: { size: 12 }, color: "#475569" },
                },
            },
        },
    });
}

onMounted(load);
onUnmounted(() => chartInstance?.destroy());
</script>
