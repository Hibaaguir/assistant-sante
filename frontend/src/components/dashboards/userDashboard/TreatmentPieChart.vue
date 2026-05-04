<!-- Graphe camembert : répartition des traitements actifs par type -->
<template>
    <section class="mt-5 rounded-2xl border-2 border-blue-300 bg-white p-4 shadow-sm transition-all duration-300 hover:shadow-lg hover:border-blue-400">

        <h2 class="mb-4 text-2xl font-semibold text-slate-900">Traitements par type</h2>

        <!-- Message pendant le chargement -->
        <div v-if="loading" class="flex h-64 items-center justify-center text-slate-700">
            Chargement...
        </div>

        <!-- Message si aucun traitement trouvé -->
        <div v-else-if="noData" class="flex h-64 items-center justify-center text-slate-700">
            Aucun traitement actif trouvé.
        </div>

        <!-- Zone du graphe (canvas Chart.js) -->
        <canvas v-else ref="canvasRef" class="max-h-72"></canvas>
    </section>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick, watch } from "vue";
import { Chart, PieController, ArcElement, Legend, Tooltip } from "chart.js";
import { useDashboardStore } from "@/stores/dashboard";

// Enregistrer uniquement les éléments nécessaires au graphe camembert
Chart.register(PieController, ArcElement, Legend, Tooltip);

// Couleurs des parts du camembert
const COLORS = ["#6366f1", "#f43f5e", "#10b981", "#f59e0b", "#149bd7", "#8b5cf6"];

const dashStore = useDashboardStore();

// Références et variables
const canvasRef   = ref(null);
const loading     = computed(() => !dashStore.initialized);
const noData      = ref(false);
let chartInstance = null;

// Dessine le graphe camembert depuis les données du store
async function loadChart() {
    const treatments = dashStore.treatments;

    const counts = {};
    for (const treatment of treatments) {
        const type = treatment.type || "Autre";
        counts[type] = (counts[type] || 0) + 1;
    }

    const labels = Object.keys(counts);
    const values = Object.values(counts);

    // Si aucune donnée, afficher le message "aucun traitement"
    if (labels.length === 0) {
        noData.value = true;
        return;
    }

    // Attendre que le DOM affiche le canvas avant de dessiner
    await nextTick();

    // Créer le graphe camembert
    chartInstance = new Chart(canvasRef.value, {
        type: "pie",
        data: {
            labels,
            datasets: [{
                data: values,
                backgroundColor: COLORS.slice(0, labels.length),
            }],
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: "bottom",
                    labels: { font: { size: 14, weight: "bold" }, padding: 16, boxWidth: 22, color: "#1f2937" },
                },
                tooltip: {
                    backgroundColor: "rgba(0,0,0,0.9)",
                    titleFont: { size: 15, weight: "bold" },
                    bodyFont: { size: 13 },
                    padding: 14,
                },
            },
        },
    });
}

onMounted(() => {
    dashStore.initialize();
    if (dashStore.initialized) loadChart();
});
watch(() => dashStore.initialized, async (ready) => { if (ready) await loadChart(); });

// Détruire le graphe quand le composant est retiré (libération mémoire)
onUnmounted(() => chartInstance?.destroy());
</script>
