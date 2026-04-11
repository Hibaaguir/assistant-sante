<!--
  HealthChart.vue
  Line chart showing vital signs filtered by week or month.
-->
<template>
    <section class="mt-5 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="mb-4 flex items-center justify-between">
            <h2 class="text-2xl font-semibold text-slate-900">
                Évolution des signes vitaux
            </h2>
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

        <div v-if="loading" class="flex h-64 items-center justify-center text-slate-400">
            Chargement...
        </div>
        <canvas v-else ref="canvas"></canvas>
    </section>
</template>

<script setup>
import { ref, onMounted, onUnmounted, nextTick } from "vue";
import { Chart, registerables } from "chart.js";
import api from "@/services/api";

Chart.register(...registerables);

const filters = [
    { label: "Par semaine", days: 7 },
    { label: "Par mois",    days: 30 },
];

const canvas  = ref(null);
const loading = ref(true);
const days    = ref(7);
let chart     = null;

async function loadChart() {
    loading.value = true;
    chart?.destroy();

    const { data: res } = await api.get("/health-data/overview", {
        params: { days: days.value },
    });

    const vitals = res?.data?.vitals_chart ?? {};

    loading.value = false;
    await nextTick();

    chart = new Chart(canvas.value, {
        type: "line",
        data: {
            labels: vitals.labels ?? [],
            datasets: [
                { label: "Fréquence cardiaque (bpm)",  data: vitals.heart_rate         ?? [], borderColor: "#f43f5e", fill: false, tension: 0.3 },
                { label: "Pression systolique (mmHg)", data: vitals.systolic_pressure  ?? [], borderColor: "#3b82f6", fill: false, tension: 0.3 },
                { label: "Saturation oxygène (%)",     data: vitals.oxygen_saturation  ?? [], borderColor: "#8b5cf6", fill: false, tension: 0.3 },
            ],
        },
        options: {
            responsive: true,
            plugins: {
                legend:  { position: "top" },
                tooltip: { mode: "index", intersect: false },
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
