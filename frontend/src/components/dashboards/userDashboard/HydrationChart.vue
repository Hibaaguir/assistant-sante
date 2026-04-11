<!--
  HydrationChart.vue
  Weekly hydration rate per day (in liters).
  Different template: blue gradient header, goal line at 2 L, bar chart.
  Data comes from GET /journal → entry.hydration (numeric, liters).
-->
<template>
    <section class="mt-5 overflow-hidden rounded-2xl border border-blue-100 shadow-sm">
        <!-- Blue gradient header -->
        <div class="bg-gradient-to-r from-blue-500 to-cyan-400 px-5 py-4">
            <h2 class="text-xl font-bold text-white">Hydratation hebdomadaire</h2>
            <p class="mt-0.5 text-sm text-blue-100">Objectif : {{ GOAL }} L / jour</p>
        </div>

        <div class="bg-white p-4">
            <div v-if="loading" class="flex h-48 items-center justify-center text-slate-400">
                Chargement...
            </div>

            <div v-else-if="noData" class="flex h-48 items-center justify-center text-slate-400">
                Aucune donnée d'hydratation cette semaine.
            </div>

            <template v-else>
                <!-- Bar chart canvas -->
                <canvas ref="canvasRef" class="max-h-52"></canvas>

                <!-- Daily recap chips -->
                <div class="mt-4 flex flex-wrap gap-2">
                    <span
                        v-for="day in weekDays"
                        :key="day.date"
                        class="rounded-full px-3 py-1 text-xs font-medium"
                        :class="day.value >= GOAL
                            ? 'bg-cyan-100 text-cyan-700'
                            : 'bg-slate-100 text-slate-500'"
                    >
                        {{ day.label }} · {{ day.value > 0 ? day.value + ' L' : '—' }}
                    </span>
                </div>
            </template>
        </div>
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

const GOAL         = 2;   // liters per day
const DAY_LABELS   = ["Lun", "Mar", "Mer", "Jeu", "Ven", "Sam", "Dim"];
const DAYS_OF_WEEK = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"];

const canvasRef = ref(null);
const loading   = ref(true);
const noData    = ref(false);
const weekDays  = ref([]);
let chartInstance = null;

function getLast7Days() {
    return Array.from({ length: 7 }, (_, i) => {
        const d = new Date();
        d.setDate(d.getDate() - (6 - i));
        return d.toISOString().slice(0, 10);
    });
}

async function load() {
    const { data: res } = await api.get("/journal");
    const entries = res?.data ?? [];

    const byDate = {};
    for (const e of entries) {
        if (e.entry_date && e.hydration != null) {
            byDate[e.entry_date] = parseFloat(e.hydration);
        }
    }

    const dates  = getLast7Days();
    const values = dates.map(d => byDate[d] ?? 0);

    weekDays.value = dates.map((d, i) => {
        // Use T12:00:00 to avoid UTC midnight being interpreted as the previous day in local timezone
        const jsDay = new Date(d + "T12:00:00").toLocaleDateString("en-US", { weekday: "long" });
        const idx   = DAYS_OF_WEEK.indexOf(jsDay);
        return {
            date:  d,
            label: idx >= 0 ? DAY_LABELS[idx] : d.slice(5),
            value: values[i],
        };
    });

    loading.value = false;

    if (values.every(v => v === 0)) {
        noData.value = true;
        return;
    }

    await nextTick();

    chartInstance = new Chart(canvasRef.value, {
        type: "bar",
        data: {
            labels: weekDays.value.map(d => d.label),
            datasets: [
                {
                    label: "Hydratation (L)",
                    data: values,
                    backgroundColor: values.map(v =>
                        v >= GOAL ? "rgba(6,182,212,0.75)" : "rgba(6,182,212,0.3)"
                    ),
                    borderColor: values.map(v =>
                        v >= GOAL ? "#0891b2" : "#67e8f9"
                    ),
                    borderWidth: 1.5,
                    borderRadius: 6,
                },
            ],
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    max: Math.max(GOAL + 1, ...values) + 0.5,
                    ticks: { callback: v => v + " L" },
                    grid: { color: "#f1f5f9" },
                },
                x: { grid: { display: false } },
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => ` ${ctx.parsed.y} L`,
                    },
                },
                // Inline goal line annotation via afterDraw plugin
            },
        },
        plugins: [{
            id: "goalLine",
            afterDraw(chart) {
                const { ctx, chartArea, scales } = chart;
                const y = scales.y.getPixelForValue(GOAL);
                ctx.save();
                ctx.beginPath();
                ctx.setLineDash([6, 4]);
                ctx.strokeStyle = "#0ea5e9";
                ctx.lineWidth   = 1.5;
                ctx.moveTo(chartArea.left,  y);
                ctx.lineTo(chartArea.right, y);
                ctx.stroke();
                ctx.restore();
            },
        }],
    });
}

onMounted(load);
onUnmounted(() => chartInstance?.destroy());
</script>
