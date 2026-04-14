<template>
    <section class="mt-5 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">

        <!-- Header -->
        <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">
                    Sommeil — {{ selectedLabel }}
                </h2>
                <p class="mt-0.5 text-xs text-slate-400">Objectif 8 h</p>
            </div>
            <select v-model="selectedMonth" @change="rebuild"
                class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-sm font-medium text-slate-700 shadow-sm focus:outline-none focus:ring-2 focus:ring-violet-400">
                <option v-for="m in months" :key="m.key" :value="m.key">{{ m.label }}</option>
            </select>
        </div>

        <div v-if="loading" class="flex h-52 items-center justify-center text-sm text-slate-400">Chargement…</div>
        <div v-else-if="noData"  class="flex h-52 items-center justify-center text-sm text-slate-400">Aucune donnée de sommeil pour ce mois.</div>

        <template v-else>
            <!-- Chart -->
            <div class="relative h-52">
                <canvas ref="canvasRef"></canvas>
            </div>

            <!-- Legend -->
            <div class="mt-3 flex flex-wrap gap-4 text-xs text-slate-600">
                <span class="flex items-center gap-1.5">
                    <span class="inline-block h-2.5 w-2.5 rounded-full bg-[#8b5cf6]"></span>
                    Sommeil moyen
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="inline-block h-2.5 w-2.5 rounded-full border-2 border-dashed border-[#f59e0b] bg-transparent"></span>
                    Objectif 8 h
                </span>
            </div>

            <!-- Week chips -->
            <div class="mt-3 flex flex-wrap gap-2">
                <span
                    v-for="(w, i) in weekData"
                    :key="i"
                    class="rounded-full px-3 py-0.5 text-xs font-medium"
                    :class="w.avg !== null && w.avg >= 7
                        ? 'bg-violet-100 text-violet-700'
                        : 'bg-rose-100 text-rose-600'"
                >
                    Sem {{ i + 1 }} · {{ w.avg !== null ? w.avg + ' h' : '—' }}
                </span>
            </div>
        </template>

    </section>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick } from "vue";
import { Chart, registerables } from "chart.js";
import api from "@/services/api";

Chart.register(...registerables);

const MONTHS_FR = ["Janvier","Février","Mars","Avril","Mai","Juin","Juillet","Août","Septembre","Octobre","Novembre","Décembre"];
const GOAL = 8;

// ── Build list of last 6 months ───────────────────────────────────────────────
function buildMonths() {
    return Array.from({ length: 6 }, (_, i) => {
        const d = new Date();
        d.setDate(1);
        d.setMonth(d.getMonth() - (5 - i));
        const key = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, "0")}`;
        return { key, label: `${MONTHS_FR[d.getMonth()]} ${d.getFullYear()}` };
    });
}

const months       = buildMonths();
const selectedMonth = ref(months[months.length - 1].key);   // current month by default
const selectedLabel = computed(() => months.find(m => m.key === selectedMonth.value)?.label ?? "");

const canvasRef = ref(null);
const loading   = ref(true);
const noData    = ref(false);
const weekData  = ref([]);   // [{ avg: 6.8 }, { avg: null }, ...]

let chartInstance = null;
let allEntries    = [];

// ── Week index (0-3) from day-of-month ───────────────────────────────────────
function weekIdx(dayStr) {
    const day = parseInt((dayStr ?? "").slice(8, 10), 10);
    if (day <=  7) return 0;
    if (day <= 14) return 1;
    if (day <= 21) return 2;
    return 3;
}

function avg(arr) {
    const v = arr.filter(x => x != null && !isNaN(x));
    return v.length ? +(v.reduce((s, x) => s + x, 0) / v.length).toFixed(1) : null;
}

// ── (Re)build chart for the selected month ────────────────────────────────────
async function rebuild() {
    noData.value = false;
    chartInstance?.destroy();
    chartInstance = null;

    const mk      = selectedMonth.value;
    const buckets = [[], [], [], []];

    for (const e of allEntries) {
        if (!e.entry_date || e.sleep == null) continue;
        if (e.entry_date.slice(0, 7) !== mk)  continue;
        buckets[weekIdx(e.entry_date)].push(parseFloat(e.sleep));
    }

    const avgs = buckets.map(b => avg(b));
    weekData.value = avgs.map(a => ({ avg: a }));

    if (avgs.every(v => v === null)) {
        noData.value = true;
        return;
    }

    await nextTick();

    chartInstance = new Chart(canvasRef.value, {
        type: "line",
        data: {
            labels: ["Sem 1", "Sem 2", "Sem 3", "Sem 4"],
            datasets: [
                {
                    label: "Sommeil moyen (h)",
                    data: avgs.map(v => v ?? null),
                    borderColor: "#8b5cf6",
                    backgroundColor: "rgba(139,92,246,0.10)",
                    borderWidth: 2.5,
                    pointBackgroundColor: "#8b5cf6",
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    tension: 0.4,
                    fill: true,
                    spanGaps: true,
                },
                {
                    label: "Objectif 8 h",
                    data: [GOAL, GOAL, GOAL, GOAL],
                    borderColor: "#f59e0b",
                    borderDash: [6, 4],
                    borderWidth: 2,
                    backgroundColor: "rgba(245,158,11,0.04)",
                    pointRadius: 0,
                    fill: true,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: "index", intersect: false },
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx =>
                            ctx.dataset.label === "Objectif 8 h"
                                ? " Objectif : 8 h"
                                : ` Moyenne : ${ctx.raw ?? "—"} h`,
                    },
                },
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 11 } },
                },
                y: {
                    min: 0,
                    max: 12,
                    grid: { color: "#f1f5f9" },
                    ticks: {
                        stepSize: 2,
                        font: { size: 10 },
                        callback: v => v + " h",
                    },
                },
            },
        },
    });
}

async function load() {
    const { data: res } = await api.get("/journal");
    allEntries    = res?.data ?? [];
    loading.value = false;
    await rebuild();
}

onMounted(load);
onUnmounted(() => chartInstance?.destroy());
</script>
