<template>
    <section
        class="mt-3 rounded-2xl border-2 border-blue-300 bg-white p-3 shadow-sm transition-all duration-300 hover:shadow-lg hover:border-blue-400"
    >
        <div
            class="mb-3 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <h2 class="text-lg font-semibold text-slate-900">
                    Comparaison mensuelle — signes vitaux
                </h2>
                <p class="mt-0.5 flex items-center gap-3 text-xs text-slate-600">
                    <span class="flex items-center gap-1.5">
                        <span class="inline-block h-2.5 w-5 rounded-full bg-[#ef4444]"></span>
                        {{ currentLabel }}
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span
                            class="inline-block h-0 w-5 border-t-2 border-dashed border-[#0284c7]"
                        ></span>
                        {{ prevLabel }}
                    </span>
                </p>
            </div>
            <select
                v-model="selectedKey"
                @change="rebuild"
                class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-sm font-medium text-slate-700 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400"
            >
                <option v-for="m in METRICS" :key="m.key" :value="m.key">
                    {{ m.label }}
                </option>
            </select>
        </div>

        <div
            v-if="loading"
            class="flex h-52 items-center justify-center text-sm text-slate-500"
        >
            Chargement…
        </div>
        <div
            v-else-if="noData"
            class="flex h-52 items-center justify-center text-sm text-slate-500"
        >
            Pas de données pour ce signe vital.
        </div>

        <div v-else class="relative h-52">
            <canvas ref="canvasRef"></canvas>
        </div>
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
    Legend,
    Filler,
} from "chart.js";
import api from "@/services/api";

Chart.register(
    LineController,
    LineElement,
    PointElement,
    CategoryScale,
    LinearScale,
    Tooltip,
    Legend,
    Filler
);

const METRICS = [
    { key: "heart_rate", label: "Fréquence cardiaque (bpm)" },
    { key: "systolic_pressure", label: "Pression systolique (mmHg)" },
    { key: "diastolic_pressure", label: "Pression diastolique (mmHg)" },
    { key: "oxygen_saturation", label: "Saturation en oxygène (%)" },
];
const MONTHS_FR = [
    "Jan", "Fév", "Mar", "Avr", "Mai", "Jun",
    "Jul", "Aoû", "Sep", "Oct", "Nov", "Déc",
];

const canvasRef = ref(null);
const loading = ref(true);
const noData = ref(false);
const selectedKey = ref("heart_rate");
const currentLabel = ref("");
const prevLabel = ref("");

let chart = null;
let allVitals = [];
let curKey = "";
let prevKey = "";

function monthOf(d) {
    return (d ?? "").slice(0, 7);
}

// Returns sorted [{day, value}] for a month+field — sequential, no gaps
function buildPoints(monthK, field) {
    const pts = [];
    for (const v of allVitals) {
        if (monthOf(v.measured_at) !== monthK || v[field] == null) continue;
        pts.push({
            day: parseInt(v.measured_at.slice(8, 10), 10),
            value: parseFloat(v[field]),
        });
    }
    pts.sort((a, b) => a.day - b.day);
    return pts;
}

async function rebuild() {
    noData.value = false;
    const field = selectedKey.value;

    const curPts = buildPoints(curKey, field);
    const prevPts = buildPoints(prevKey, field);

    if (!curPts.length && !prevPts.length) {
        noData.value = true;
        chart?.destroy();
        chart = null;
        return;
    }

    chart?.destroy();
    await nextTick();

    const len = Math.max(curPts.length, prevPts.length);

    // X-axis: sequential index 1..N
    const labels = Array.from({ length: len }, (_, i) => i + 1);

    // Pad shorter series with null at the end so both share the same x-axis
    const curData = Array.from({ length: len }, (_, i) =>
        i < curPts.length ? curPts[i].value : null
    );
    const prevData = Array.from({ length: len }, (_, i) =>
        i < prevPts.length ? prevPts[i].value : null
    );

    // Day labels for tooltips
    const curDays = Array.from({ length: len }, (_, i) =>
        i < curPts.length ? curPts[i].day : null
    );
    const prevDays = Array.from({ length: len }, (_, i) =>
        i < prevPts.length ? prevPts[i].day : null
    );

    // Progressive draw animation
    const totalDuration = 1800;
    const delay = totalDuration / len;
    const previousY = (ctx) => {
        if (ctx.index === 0) return ctx.chart.scales.y.getPixelForValue(100);
        const prev = ctx.chart.getDatasetMeta(ctx.datasetIndex).data[ctx.index - 1];
        return prev ? prev.getProps(["y"], true).y : ctx.chart.scales.y.getPixelForValue(100);
    };
    const animation = {
        x: {
            type: "number", easing: "linear", duration: delay, from: NaN,
            delay(ctx) {
                if (ctx.type !== "data" || ctx.xStarted) return 0;
                ctx.xStarted = true;
                return ctx.index * delay;
            },
        },
        y: {
            type: "number", easing: "linear", duration: delay, from: previousY,
            delay(ctx) {
                if (ctx.type !== "data" || ctx.yStarted) return 0;
                ctx.yStarted = true;
                return ctx.index * delay;
            },
        },
    };

    chart = new Chart(canvasRef.value, {
        type: "line",
        data: {
            labels,
            datasets: [
                {
                    label: currentLabel.value,
                    data: curData,
                    borderColor: "#ef4444",
                    backgroundColor: "rgba(239,68,68,0.08)",
                    borderWidth: 2.5,
                    pointRadius: 3.5,
                    pointHoverRadius: 6,
                    pointBackgroundColor: "#ef4444",
                    pointBorderColor: "#fff",
                    pointBorderWidth: 1.5,
                    tension: 0.4,
                    fill: true,
                    spanGaps: false,
                },
                {
                    label: prevLabel.value,
                    data: prevData,
                    borderColor: "#0284c7",
                    backgroundColor: "rgba(2,132,199,0.07)",
                    borderWidth: 2.5,
                    borderDash: [6, 4],
                    pointRadius: 3.5,
                    pointHoverRadius: 6,
                    pointBackgroundColor: "#0284c7",
                    pointBorderColor: "#fff",
                    pointBorderWidth: 1.5,
                    tension: 0.4,
                    fill: true,
                    spanGaps: false,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation,
            interaction: { mode: "index", intersect: false },
            plugins: {
                legend: { display: false },
                tooltip: {
                    mode: "index",
                    intersect: false,
                    backgroundColor: "rgba(15,23,42,0.92)",
                    titleColor: "#f8fafc",
                    bodyColor: "#cbd5e1",
                    titleFont: { size: 13, weight: "bold" },
                    bodyFont: { size: 12 },
                    padding: 12,
                    displayColors: true,
                    callbacks: {
                        title: (items) => {
                            const idx = items[0].dataIndex;
                            const cd = curDays[idx];
                            const pd = prevDays[idx];
                            const parts = [];
                            if (cd != null) parts.push(`${currentLabel.value} — Jour ${cd}`);
                            if (pd != null) parts.push(`${prevLabel.value} — Jour ${pd}`);
                            return parts;
                        },
                        label: (ctx) =>
                            ctx.parsed.y != null
                                ? ` ${ctx.dataset.label} : ${ctx.parsed.y}`
                                : ` ${ctx.dataset.label} : —`,
                    },
                },
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: {
                        font: { size: 10, weight: "500" },
                        color: "#64748b",
                        maxTicksLimit: 16,
                        autoSkip: true,
                    },
                    title: {
                        display: true,
                        text: "Mesure n°",
                        color: "#94a3b8",
                        font: { size: 10, weight: "600" },
                    },
                },
                y: {
                    beginAtZero: false,
                    grid: { color: "#f1f5f9", drawBorder: false },
                    border: { display: false },
                    ticks: {
                        font: { size: 11, weight: "500" },
                        color: "#64748b",
                    },
                },
            },
        },
    });
}

async function load() {
    const { data: res } = await api.get("/health-data/vitals", {
        params: { days: 62 },
    });
    allVitals = res?.data ?? [];

    const now = new Date();
    const prev = new Date(now.getFullYear(), now.getMonth() - 1, 1);
    curKey = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, "0")}`;
    prevKey = `${prev.getFullYear()}-${String(prev.getMonth() + 1).padStart(2, "0")}`;

    const [cy, cm] = curKey.split("-");
    const [py, pm] = prevKey.split("-");
    currentLabel.value = `${MONTHS_FR[+cm - 1]} ${cy}`;
    prevLabel.value = `${MONTHS_FR[+pm - 1]} ${py}`;

    loading.value = false;
    await rebuild();
}

onMounted(load);
onUnmounted(() => chart?.destroy());
</script>
