<template>
    <section
        class="mt-5 overflow-hidden rounded-2xl border border-slate-300 bg-white shadow-sm"
    >
        <div
            class="flex items-start justify-between gap-3 px-4 pt-4 pb-3 sm:px-5"
        >
            <div>
                <Typography tag="h2" variant="h2-style">
                    <span class="mr-1">🌙</span>Sommeil —
                    {{ selectedMonthName }}
                </Typography>
                <Typography tag="p" variant="paragraph" class="mt-2">
                    Moyenne par semaine · Objectif 8 h / nuit
                </Typography>
            </div>

            <div class="relative shrink-0">
                <select
                    v-model="selectedMonth"
                    @change="rebuild"
                    class="appearance-none rounded-xl border border-indigo-100 bg-indigo-50 py-1.5 pl-3 pr-8 text-sm font-semibold text-indigo-600 outline-none transition hover:bg-indigo-100 focus:ring-2 focus:ring-indigo-300"
                >
                    <option v-for="m in months" :key="m.key" :value="m.key">
                        {{ m.label }}
                    </option>
                </select>
                <span
                    class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 text-xs text-indigo-500"
                >
                    ▾
                </span>
            </div>
        </div>

        <div class="border-t border-slate-200 px-2 pt-3 pb-4 sm:px-4">
            <div
                v-if="loading"
                class="flex h-56 items-center justify-center text-sm text-slate-700"
            >
                Chargement...
            </div>

            <template v-else>
                <div class="relative h-56">
                    <canvas ref="canvasRef"></canvas>
                </div>

                <div class="mt-2 flex flex-wrap gap-2.5 px-1">
                    <span
                        v-for="(w, i) in weekData"
                        :key="i"
                        class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700"
                    >
                        Sem {{ i + 1 }} ·
                        {{ w.avg !== null ? `${w.avg} h` : "—" }}
                    </span>
                </div>
            </template>
        </div>
    </section>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick } from "vue";
import Typography from "@/components/ui/Typography.vue";
import { Chart, registerables } from "chart.js";
import api from "@/services/api";

Chart.register(...registerables);

const MONTHS_FR = [
    "Janvier",
    "Février",
    "Mars",
    "Avril",
    "Mai",
    "Juin",
    "Juillet",
    "Août",
    "Septembre",
    "Octobre",
    "Novembre",
    "Décembre",
];
const GOAL = 8;

// Build a month picker for the last 12 months.
function buildMonths() {
    return Array.from({ length: 12 }, (_, i) => {
        const d = new Date();
        d.setDate(1);
        d.setMonth(d.getMonth() - (11 - i));
        const key = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, "0")}`;
        return { key, label: MONTHS_FR[d.getMonth()] };
    });
}

const months = buildMonths();
const selectedMonth = ref(months[months.length - 1].key); // current month by default
const selectedMonthName = computed(
    () => months.find((m) => m.key === selectedMonth.value)?.label ?? "",
);

const canvasRef = ref(null);
const loading = ref(true);
const weekData = ref([]); // [{ avg: 6.8 }, { avg: null }, ...]

let chartInstance = null;
let allEntries = [];

// Week index (0-4) from day-of-month.
function weekIdx(dayStr) {
    const day = parseInt((dayStr ?? "").slice(8, 10), 10);
    if (day <= 7) return 0;
    if (day <= 14) return 1;
    if (day <= 21) return 2;
    if (day <= 28) return 3;
    return 4;
}

function avg(arr) {
    const v = arr.filter((x) => x != null && !isNaN(x));
    return v.length
        ? +(v.reduce((s, x) => s + x, 0) / v.length).toFixed(1)
        : null;
}

// (Re)build chart for the selected month.
async function rebuild() {
    chartInstance?.destroy();
    chartInstance = null;

    const mk = selectedMonth.value;
    const buckets = [[], [], [], [], []];

    for (const e of allEntries) {
        if (!e.entry_date || e.sleep == null) continue;
        if (e.entry_date.slice(0, 7) !== mk) continue;
        buckets[weekIdx(e.entry_date)].push(parseFloat(e.sleep));
    }

    const avgs = buckets.map((b) => avg(b));
    const chartSeries = avgs.map((v) => (v == null ? 0 : v));
    weekData.value = avgs.map((a) => ({ avg: a }));

    await nextTick();

    chartInstance = new Chart(canvasRef.value, {
        type: "line",
        data: {
            labels: ["Sem 1", "Sem 2", "Sem 3", "Sem 4", "Sem 5"],
            datasets: [
                {
                    label: "Sommeil moyen",
                    data: chartSeries,
                    borderColor: "#4338ca",
                    backgroundColor: "rgba(67, 56, 202, 0.15)",
                    borderWidth: 3,
                    pointBackgroundColor: "#ef4444",
                    pointBorderColor: "#4338ca",
                    pointBorderWidth: 2.5,
                    pointRadius: 6,
                    pointHoverRadius: 8,
                    tension: 0.45,
                    fill: true,
                },
                {
                    label: "Objectif 8 h",
                    data: [GOAL, GOAL, GOAL, GOAL, GOAL],
                    borderColor: "#6366f1",
                    borderDash: [5, 4],
                    borderWidth: 2.5,
                    pointRadius: 0,
                    fill: false,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: "index", intersect: false },
            plugins: {
                legend: {
                    position: "top",
                    labels: {
                        font: { size: 14, weight: "bold" },
                        padding: 14,
                        boxWidth: 22,
                        color: "#1f2937",
                    },
                },
                tooltip: {
                    backgroundColor: "#111827",
                    titleColor: "#f8fafc",
                    bodyColor: "#f8fafc",
                    titleFont: { size: 15, weight: "bold" },
                    bodyFont: { size: 13 },
                    padding: 14,
                    titleFont: { size: 13, weight: "bold" },
                    bodyFont: { size: 12 },
                    padding: 12,
                    callbacks: {
                        label: (ctx) =>
                            ctx.dataset.label === "Objectif 8 h"
                                ? " Objectif : 8 h / nuit"
                                : ` Sommeil : ${weekData.value[ctx.dataIndex]?.avg ?? 0} h`,
                    },
                },
            },
            scales: {
                x: {
                    grid: { display: false, drawBorder: false },
                    ticks: { color: "#475569", font: { size: 12 } },
                    border: { display: false },
                },
                y: {
                    min: 0,
                    max: 11,
                    grid: { color: "#e2e8f0", drawBorder: false },
                    ticks: {
                        stepSize: 1,
                        color: "#64748b",
                        font: { size: 12 },
                        callback: (v) =>
                            v === 11 || v % 2 === 0 ? `${v} h` : "",
                    },
                    border: { display: false },
                },
            },
        },
    });
}

async function load() {
    const { data: res } = await api.get("/journal");
    allEntries = res?.data ?? [];
    loading.value = false;
    await rebuild();
}

onMounted(load);
onUnmounted(() => chartInstance?.destroy());
</script>
