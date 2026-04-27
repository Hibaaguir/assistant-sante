<template>
    <section
        class="mt-3 rounded-2xl border-2 border-blue-300 bg-white p-3 shadow-sm transition-all duration-300 hover:shadow-lg hover:border-blue-400"
    >
        <!-- Header -->
        <div class="mb-3 flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">
                    Répartition des analyses par mois
                </h2>
                <p class="mt-0.5 text-xs text-slate-400">
                    Types d'analyses réalisées chaque mois
                </p>
            </div>
        </div>

        <!-- Loading / No data -->
        <div
            v-if="loading"
            class="flex h-48 items-center justify-center text-sm text-slate-400"
        >
            Chargement…
        </div>
        <div
            v-else-if="noData"
            class="flex h-48 items-center justify-center text-sm text-slate-400"
        >
            Aucune analyse enregistrée.
        </div>

        <template v-else>
            <!-- Chart -->
            <div class="relative h-52">
                <canvas ref="canvasRef"></canvas>
            </div>

            <!-- Legend -->
            <div class="mt-3 flex flex-wrap gap-x-4 gap-y-1.5">
                <span
                    v-for="(color, type) in typeColors"
                    :key="type"
                    class="flex items-center gap-1.5 text-xs text-slate-600"
                >
                    <span
                        class="inline-block h-2.5 w-2.5 rounded-sm flex-shrink-0"
                        :style="{ background: color }"
                    ></span>
                    {{ type }}
                </span>
            </div>
        </template>
    </section>
</template>

<script setup>
import { ref, onMounted, onUnmounted, nextTick } from "vue";
import { Chart, registerables } from "chart.js";
import api from "@/services/api";

Chart.register(...registerables);

const MONTHS_FR = [
    "Jan",
    "Fév",
    "Mar",
    "Avr",
    "Mai",
    "Jun",
    "Jul",
    "Aoû",
    "Sep",
    "Oct",
    "Nov",
    "Déc",
];

// One distinct color per analysis type
const PALETTE = [
    "#6366f1",
    "#f87171",
    "#34d399",
    "#149bd7",
    "#fbbf24",
    "#a78bfa",
    "#f472b6",
    "#2dd4bf",
    "#fb923c",
    "#94a3b8",
    "#4ade80",
    "#e879f9",
];

const canvasRef = ref(null);
const loading = ref(true);
const noData = ref(false);
const typeColors = ref({}); // { "Biologie sanguine": "#6366f1", ... }

let chart = null;

async function load() {
    const { data: res } = await api.get("/dashboard/labs");
    const labs = res?.data ?? [];

    if (!labs.length) {
        loading.value = false;
        noData.value = true;
        return;
    }

    // ── 1. Collect all unique analysis types ─────────────────────────────
    const allTypes = [
        ...new Set(labs.map((l) => l.analysis_type).filter(Boolean)),
    ];

    // Assign one color per type
    const colors = {};
    allTypes.forEach((t, i) => {
        colors[t] = PALETTE[i % PALETTE.length];
    });
    typeColors.value = colors;

    // ── 2. Group by month-year → count per type ───────────────────────────
    // monthMap: { "2026-03": { "Biologie sanguine": 3, "Hématologie": 1 }, ... }
    const monthMap = {};
    for (const l of labs) {
        const mk = (l.analysis_date ?? "").slice(0, 7);
        if (!mk) continue;
        if (!monthMap[mk]) monthMap[mk] = {};
        const t = l.analysis_type ?? "Autre";
        monthMap[mk][t] = (monthMap[mk][t] ?? 0) + 1;
    }

    // ── 3. Sort months chronologically ───────────────────────────────────
    const sortedMonths = Object.keys(monthMap).sort();
    const labels = sortedMonths.map((mk) => {
        const [y, m] = mk.split("-");
        return `${MONTHS_FR[+m - 1]} ${y}`;
    });

    // ── 4. Build one dataset per analysis type ────────────────────────────
    const datasets = allTypes.map((type) => ({
        label: type,
        data: sortedMonths.map((mk) => monthMap[mk][type] ?? 0),
        backgroundColor: colors[type],
        borderRadius: 5,
        borderSkipped: false,
        borderWidth: 1.5,
        borderColor: "rgba(0, 0, 0, 0.05)",
    }));

    loading.value = false;
    await nextTick();

    chart = new Chart(canvasRef.value, {
        type: "bar",
        data: { labels, datasets },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: "index", intersect: false },
            // Default width uses categoryPercentage 0.8 and barPercentage 0.9.
            // Keeping barPercentage and halving categoryPercentage gives exactly 50% width.
            datasets: {
                bar: {
                    categoryPercentage: 0.4,
                    barPercentage: 0.9,
                },
            },
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
                    backgroundColor: "rgba(0, 0, 0, 0.9)",
                    titleFont: { size: 15, weight: "bold" },
                    bodyFont: { size: 13 },
                    padding: 14,
                    callbacks: {
                        // Show only types with count > 0 in the tooltip
                        beforeBody(items) {
                            return items
                                .filter((i) => i.parsed.y > 0)
                                .map(
                                    (i) => `${i.dataset.label} : ${i.parsed.y}`,
                                )
                                .join("\n");
                        },
                        label: () => null, // suppress individual labels
                    },
                },
            },
            scales: {
                x: {
                    stacked: true,
                    grid: { display: false },
                    ticks: {
                        font: { size: 12, weight: "500" },
                        color: "#475569",
                    },
                },
                y: {
                    stacked: true,
                    beginAtZero: true,
                    grid: { color: "#e2e8f0", drawBorder: true },
                    ticks: {
                        font: { size: 12, weight: "500" },
                        color: "#475569",
                        stepSize: 1,
                    },
                    title: {
                        display: true,
                        text: "Nb d'analyses",
                        color: "#64748b",
                        font: { size: 12, weight: "600" },
                    },
                },
            },
        },
    });
}

onMounted(load);
onUnmounted(() => chart?.destroy());
</script>
