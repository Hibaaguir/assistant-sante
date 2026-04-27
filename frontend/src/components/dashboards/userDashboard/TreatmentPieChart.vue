<!--
  TreatmentPieChart.vue
  Pie chart showing the distribution of active treatments by type.
  Uses Chart.js. Data comes from /health-data/overview (treatment_medicines).
-->
<template>
    <section
        class="mt-5 rounded-2xl border-2 border-blue-300 bg-white p-4 shadow-sm transition-all duration-300 hover:shadow-lg hover:border-blue-400"
    >
        <h2 class="mb-4 text-2xl font-semibold text-slate-900">
            Traitements par type
        </h2>

        <div
            v-if="loading"
            class="flex h-64 items-center justify-center text-slate-700"
        >
            Chargement...
        </div>

        <div
            v-else-if="noData"
            class="flex h-64 items-center justify-center text-slate-700"
        >
            Aucun traitement actif trouvé.
        </div>

        <canvas v-else ref="canvasRef" class="max-h-72"></canvas>
    </section>
</template>

<script setup>
import { ref, onMounted, onUnmounted, nextTick } from "vue";
import { Chart, PieController, ArcElement, Legend, Tooltip } from "chart.js";
import api from "@/services/api";

// Register only what we need
Chart.register(PieController, ArcElement, Legend, Tooltip);

const canvasRef = ref(null);
const loading = ref(true);
const noData = ref(false);
let chartInstance = null;

// Colors for the pie slices
const COLORS = [
    "#6366f1",
    "#f43f5e",
    "#10b981",
    "#f59e0b",
    "#149bd7",
    "#8b5cf6",
];

async function loadChart() {
    const { data: res } = await api.get("/dashboard/treatments");

    const medicines = res?.data ?? [];

    // Count treatments per type (the "note" field holds medication_type)
    const counts = {};
    for (const med of medicines) {
        const type = med.type || "Autre";
        counts[type] = (counts[type] || 0) + 1;
    }

    const labels = Object.keys(counts);
    const values = Object.values(counts);

    loading.value = false;

    if (labels.length === 0) {
        noData.value = true;
        return;
    }

    // Wait for the canvas to appear in the DOM
    await nextTick();

    chartInstance = new Chart(canvasRef.value, {
        type: "pie",
        data: {
            labels,
            datasets: [
                {
                    data: values,
                    backgroundColor: COLORS.slice(0, labels.length),
                },
            ],
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: "bottom",
                    labels: {
                        font: { size: 14, weight: "bold" },
                        padding: 16,
                        boxWidth: 22,
                        color: "#1f2937",
                    },
                },
                tooltip: {
                    backgroundColor: "rgba(0, 0, 0, 0.9)",
                    titleFont: { size: 15, weight: "bold" },
                    bodyFont: { size: 13 },
                    padding: 14,
                    enabled: true,
                },
            },
        },
    });
}

onMounted(loadChart);

// Destroy chart when component is removed to avoid memory leaks
onUnmounted(() => chartInstance?.destroy());
</script>
