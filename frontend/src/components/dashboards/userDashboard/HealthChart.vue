<!--
  HealthChart.vue
  Line chart showing vital signs filtered by week or month.
-->
<template>
    <section
        class="mt-5 rounded-2xl border-2 border-blue-300 bg-white p-4 shadow-sm transition-all duration-300 hover:shadow-lg hover:border-blue-400"
    >
        <div class="mb-4 flex items-center justify-between">
            <Typography tag="h2" variant="h2-style">
                Évolution des signes vitaux
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
        <canvas v-else ref="canvas"></canvas>
    </section>
</template>

<script setup>
import { ref, onMounted, onUnmounted, nextTick } from "vue";
import Typography from "@/components/ui/Typography.vue";
import { Chart, registerables } from "chart.js";
import api from "@/services/api";

Chart.register(...registerables);

const filters = [
    { label: "Par semaine", days: 7 },
    { label: "Par mois", days: 30 },
];

const canvas = ref(null);
const loading = ref(true);
const days = ref(7);
let chart = null;

async function loadChart() {
    loading.value = true;
    chart?.destroy();

    const { data: res } = await api.get("/dashboard/vitals-chart", {
        params: { days: days.value },
    });

    const vitals = res?.data ?? {};

    loading.value = false;
    await nextTick();

    chart = new Chart(canvas.value, {
        type: "line",
        data: {
            labels: vitals.labels ?? [],
            datasets: [
                {
                    label: "Fréquence cardiaque (bpm)",
                    data: vitals.heart_rate ?? [],
                    borderColor: "#dc2626",
                    fill: false,
                    tension: 0.3,
                    borderWidth: 3,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    pointBackgroundColor: "#dc2626",
                },
                {
                    label: "Pression systolique (mmHg)",
                    data: vitals.systolic_pressure ?? [],
                    borderColor: "#0284c7",
                    fill: false,
                    tension: 0.3,
                    borderWidth: 3,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    pointBackgroundColor: "#0284c7",
                },
                {
                    label: "Saturation oxygène (%)",
                    data: vitals.oxygen_saturation ?? [],
                    borderColor: "#7c3aed",
                    fill: false,
                    tension: 0.3,
                    borderWidth: 3,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    pointBackgroundColor: "#7c3aed",
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: "top",
                    labels: {
                        font: { size: 14, weight: "bold" },
                        padding: 15,
                        usePointStyle: true,
                        pointStyle: "rect",
                    },
                },
                tooltip: {
                    mode: "index",
                    intersect: false,
                    backgroundColor: "rgba(0, 0, 0, 0.8)",
                    titleFont: { size: 14, weight: "bold" },
                    bodyFont: { size: 13 },
                    padding: 12,
                    displayColors: true,
                },
            },
            scales: {
                y: {
                    ticks: {
                        font: { size: 12, weight: "500" },
                        color: "#475569",
                    },
                    grid: { color: "#e2e8f0", drawBorder: true },
                },
                x: {
                    ticks: {
                        font: { size: 12, weight: "500" },
                        color: "#475569",
                    },
                    grid: { display: false },
                },
            },
        },
    });
}

function changeFilter(value) {
    days.value = value;
    loadChart();
}

onMounted(loadChart);
onUnmounted(() => chart?.destroy());
</script>
