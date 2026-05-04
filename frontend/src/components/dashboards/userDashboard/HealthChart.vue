<!-- Graphe en courbe : évolution des signes vitaux sur 7 ou 30 jours -->
<template>
    <section class="mt-5 rounded-2xl border-2 border-blue-300 bg-white p-4 shadow-sm transition-all duration-300 hover:shadow-lg hover:border-blue-400">

        <!-- Titre et boutons de filtre (semaine / mois) -->
        <div class="mb-4 flex items-center justify-between">
            <Typography tag="h2" variant="h2-style">Évolution des signes vitaux</Typography>
            <div class="flex gap-2">
                <button
                    v-for="f in filters"
                    :key="f.days"
                    @click="changeFilter(f.days)"
                    class="rounded-lg border px-3 py-1.5 text-sm font-medium transition"
                    :class="days === f.days
                        ? 'border-purple-500 bg-purple-50 text-purple-700'
                        : 'border-slate-200 bg-white text-slate-700 hover:border-slate-300'"
                >
                    {{ f.label }}
                </button>
            </div>
        </div>

        <!-- Message pendant le chargement des données -->
        <div v-if="loading" class="flex h-72 items-center justify-center text-slate-700">
            Chargement...
        </div>

        <!-- Zone du graphe (canvas Chart.js) -->
        <div v-else class="relative h-72">
            <canvas ref="canvas"></canvas>
        </div>
    </section>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick, watch } from "vue";
import Typography from "@/components/ui/Typography.vue";
import { Chart, registerables } from "chart.js";
import { useDashboardStore } from "@/stores/dashboard";

// Enregistrer tous les composants Chart.js nécessaires
Chart.register(...registerables);

// Options de filtre disponibles
const filters = [
   // exemple d'ajout d'un filtre { label: "3 mois", days: 90 },
    { label: "Par semaine", days: 7 },
    { label: "Par mois",    days: 30 },
];

const dashStore = useDashboardStore();

// Références et variables
const canvas  = ref(null);
const loading = computed(() => !dashStore.initialized);
const days    = ref(7);
let chart     = null;

// Dessine le graphe depuis les données pré-chargées du store (les deux variantes 7/30j sont déjà en cache)
async function loadChart() {
    chart?.destroy();

    const vitals = dashStore.vitalsChart[days.value] ?? {};

    // Attendre que le DOM affiche le canvas avant de dessiner
    await nextTick();

    // Créer le graphe en courbe avec Chart.js
    chart = new Chart(canvas.value, {
        type: "line",
        data: {
            labels: vitals.labels ?? [],// x = dates , y = valeurs
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
                    pointBackgroundColor: "#dc2626",// rouge
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
                    pointBackgroundColor: "#0284c7",// bleu
                },
                {
                    label: "Saturation oxygène (%)",
                    data: vitals.oxygen_saturation ?? [],
                    borderColor: "#7c3aed",// violet
                    fill: false,
                    tension: 0.3,
                    borderWidth: 3,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    pointBackgroundColor: "#7c3aed",
                },
            ],
        },
        options: {//c'est le style du graphe
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: "top",
                    labels: { font: { size: 14, weight: "bold" }, padding: 15, usePointStyle: true, pointStyle: "rect" },
                },
                tooltip: {
                    mode: "index",
                    intersect: false,
                    backgroundColor: "rgba(0,0,0,0.8)",// noir semi-transparent
                    titleFont: { size: 14, weight: "bold" },
                    bodyFont: { size: 13 },
                    padding: 12,
                },
            },
            scales: {
                y: {
                    ticks: { font: { size: 12, weight: "500" }, color: "#475569" },
                    grid:  { color: "#e2e8f0" },// gris clair
                },
                x: {
                    ticks: { font: { size: 12, weight: "500" }, color: "#475569" },// gris foncé
                    grid:  { display: false },
                },
            },
        },
    });
}

function changeFilter(value) {
    days.value = value;
    loadChart();
}

onMounted(() => {
    dashStore.initialize();
    if (dashStore.initialized) loadChart();
});
watch(() => dashStore.initialized, async (ready) => { if (ready) await loadChart(); });

// Détruire le graphe quand le composant est retiré (libération mémoire)
onUnmounted(() => chart?.destroy());
</script>
