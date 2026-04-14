<template>
    <section class="mt-3 rounded-2xl border border-slate-200 bg-white p-3 shadow-sm">

        <!-- Header -->
        <div class="mb-3 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Comparaison mensuelle — signes vitaux</h2>
                <p class="text-xs text-slate-400">
                    <span class="font-medium text-[#f87171]">━</span> {{ currentLabel }}
                    &nbsp;vs&nbsp;
                    <span class="font-medium text-[#60a5fa]">━ ─</span> {{ prevLabel }}
                </p>
            </div>
            <select v-model="selectedKey" @change="rebuild"
                class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-sm font-medium text-slate-700 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                <option v-for="m in METRICS" :key="m.key" :value="m.key">{{ m.label }}</option>
            </select>
        </div>

        <!-- States -->
        <div v-if="loading" class="flex h-48 items-center justify-center text-sm text-slate-400">Chargement…</div>
        <div v-else-if="noData" class="flex h-48 items-center justify-center text-sm text-slate-400">Pas de données pour ce signe vital.</div>

        <!-- Chart -->
        <div v-else class="relative h-48">
            <canvas ref="canvasRef"></canvas>
        </div>

    </section>
</template>

<script setup>
import { ref, onMounted, onUnmounted, nextTick } from "vue";
import { Chart, registerables } from "chart.js";
import api from "@/services/api";

Chart.register(...registerables);

const METRICS = [
    { key: "heart_rate",         label: "Fréquence cardiaque (bpm)"   },
    { key: "systolic_pressure",  label: "Pression systolique (mmHg)"  },
    { key: "diastolic_pressure", label: "Pression diastolique (mmHg)" },
    { key: "oxygen_saturation",  label: "Saturation en oxygène (%)"   },
];
const MONTHS_FR = ["Jan","Fév","Mar","Avr","Mai","Jun","Jul","Aoû","Sep","Oct","Nov","Déc"];

const canvasRef    = ref(null);
const loading      = ref(true);
const noData       = ref(false);
const selectedKey  = ref("heart_rate");
const currentLabel = ref("");
const prevLabel    = ref("");

let chart     = null;
let allVitals = [];
let curKey    = "";
let prevKey   = "";

function monthOf(d) { return (d ?? "").slice(0, 7); }
function dayIdx(d)  { return parseInt((d ?? "").slice(8, 10), 10) - 1; }

function buildSeries(monthK, field) {
    const slots = Array(31).fill(null);
    for (const v of allVitals) {
        if (monthOf(v.measured_at) !== monthK || v[field] == null) continue;
        slots[dayIdx(v.measured_at)] = parseFloat(v[field]);
    }
    let end = 30;
    while (end > 0 && slots[end] === null) end--;
    return slots.slice(0, end + 1);
}

async function rebuild() {
    noData.value = false;
    const field    = selectedKey.value;
    const curData  = buildSeries(curKey,  field);
    const prevData = buildSeries(prevKey, field);
    const len      = Math.max(curData.length, prevData.length);

    if (curData.every(v => v === null) && prevData.every(v => v === null)) {
        noData.value = true;
        chart?.destroy(); chart = null;
        return;
    }

    chart?.destroy();
    await nextTick();

    // ── Progressive line animation (Chart.js official sample) ──────────────
    const totalDuration      = 2000;
    const delayBetweenPoints = totalDuration / len;

    const previousY = (ctx) => {
        if (ctx.index === 0) {
            return ctx.chart.scales.y.getPixelForValue(100);
        }
        const prev = ctx.chart.getDatasetMeta(ctx.datasetIndex).data[ctx.index - 1];
        return prev ? prev.getProps(["y"], true).y : ctx.chart.scales.y.getPixelForValue(100);
    };

    const progressiveAnimation = {
        x: {
            type: "number",
            easing: "linear",
            duration: delayBetweenPoints,
            from: NaN,           // point starts invisible (skipped)
            delay(ctx) {
                if (ctx.type !== "data" || ctx.xStarted) return 0;
                ctx.xStarted = true;
                return ctx.index * delayBetweenPoints;
            },
        },
        y: {
            type: "number",
            easing: "linear",
            duration: delayBetweenPoints,
            from: previousY,     // each point animates FROM the previous point's y
            delay(ctx) {
                if (ctx.type !== "data" || ctx.yStarted) return 0;
                ctx.yStarted = true;
                return ctx.index * delayBetweenPoints;
            },
        },
    };
    // ───────────────────────────────────────────────────────────────────────

    chart = new Chart(canvasRef.value, {
        type: "line",
        data: {
            labels: Array.from({ length: len }, (_, i) => i + 1),
            datasets: [
                {
                    label: currentLabel.value,
                    data: curData,
                    borderColor: "#f87171",
                    backgroundColor: "rgba(248,113,113,0.07)",
                    borderWidth: 2,
                    pointRadius: 0,
                    tension: 0.4,
                    fill: true,
                    spanGaps: true,
                },
                {
                    label: prevLabel.value,
                    data: prevData,
                    borderColor: "#60a5fa",
                    backgroundColor: "rgba(96,165,250,0.07)",
                    borderWidth: 2,
                    borderDash: [5, 3],
                    pointRadius: 0,
                    tension: 0.4,
                    fill: true,
                    spanGaps: true,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: progressiveAnimation,
            interaction: { mode: "index", intersect: false },
            plugins: {
                legend: { position: "top", labels: { boxWidth: 24, font: { size: 11 } } },
                tooltip: { mode: "index", intersect: false },
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 10 }, maxTicksLimit: 16 },
                    title: { display: true, text: "Jour du mois", color: "#94a3b8", font: { size: 10 } },
                },
                y: {
                    beginAtZero: false,
                    grid: { color: "#f1f5f9" },
                    ticks: { font: { size: 10 } },
                },
            },
        },
    });
}

async function load() {
    const { data: res } = await api.get("/health-data/vitals", { params: { days: 60 } });
    allVitals = res?.data ?? [];

    const now  = new Date();
    const prev = new Date(now.getFullYear(), now.getMonth() - 1, 1);
    curKey  = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, "0")}`;
    prevKey = `${prev.getFullYear()}-${String(prev.getMonth() + 1).padStart(2, "0")}`;

    const [cy, cm] = curKey.split("-");
    const [py, pm] = prevKey.split("-");
    currentLabel.value = `${MONTHS_FR[+cm - 1]} ${cy}`;
    prevLabel.value    = `${MONTHS_FR[+pm - 1]} ${py}`;

    loading.value = false;
    await rebuild();
}

onMounted(load);
onUnmounted(() => chart?.destroy());
</script>
