<!-- Histogramme groupé : moyennes des signes vitaux du mois actuel vs mois précédent -->
<template>
    <section class="mt-5 flex flex-col rounded-2xl border-2 border-blue-300 bg-white p-4 shadow-sm transition-all duration-300 hover:shadow-lg hover:border-blue-400" style="height: calc(100% - 1.25rem);">

        <!-- Titre et sous-titre -->
        <div class="mb-4 shrink-0">
            <h2 class="text-2xl font-semibold text-slate-900">Signes vitaux — comparaison</h2>
            <p class="mt-0.5 text-sm text-slate-700">Moyennes mois actuel vs mois précédent</p>
        </div>

        <!-- Message pendant le chargement -->
        <div v-if="loading" class="flex flex-1 items-center justify-center text-slate-700">
            Chargement...
        </div>

        <!-- Message si pas assez de données -->
        <div v-else-if="noData" class="flex flex-1 items-center justify-center text-slate-700">
            Pas assez de données pour comparer.
        </div>

        <div v-else class="relative flex-1">
            <canvas ref="canvasRef"></canvas>
        </div>
    </section>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick, watch } from "vue";
import { Chart, BarController, BarElement, CategoryScale, LinearScale, Tooltip } from "chart.js";
import { useDashboardStore } from "@/stores/dashboard";

// Enregistrer les composants Chart.js pour l'histogramme
Chart.register(BarController, BarElement, CategoryScale, LinearScale, Tooltip);

// Les 4 indicateurs à comparer
const VITAL_SIGNS = [
    { key: "heart_rate",          label: "Fréq. card.\n(bpm)"      },
    { key: "systolic_pressure",   label: "Pression\nsys. (mmHg)"   },
    { key: "diastolic_pressure",  label: "Pression\ndia. (mmHg)"   },
    { key: "oxygen_saturation",   label: "Sat. O2\n(%)"            },
];

// Noms courts des mois en français
const MONTHS_FR = ["Jan", "Fév", "Mar", "Avr", "Mai", "Jun", "Jul", "Aoû", "Sep", "Oct", "Nov", "Déc"];

const dashStore = useDashboardStore();

// Références et variables
const canvasRef    = ref(null);
const loading      = computed(() => !dashStore.initialized);
const noData       = ref(false);
const currentLabel = ref("");
const prevLabel    = ref("");
let chartInstance  = null;

// Calcule la moyenne d'un tableau de valeurs (ignore les nulls)
function avg(values) {
    const filtered = values.filter((x) => x != null);
    if (filtered.length === 0) return null;
    const sum = filtered.reduce((total, x) => total + parseFloat(x), 0);
    return +( sum / filtered.length).toFixed(1);
}

// Extrait la clé "YYYY-MM" depuis une date ISO ou une chaîne de date
function monthKey(dateStr) {
    return (dateStr ?? "").slice(0, 7);
}

// Dessine l'histogramme comparatif depuis les données du store
async function buildChart() {
    const allVitals = dashStore.vitals60;

    // Calculer les clés du mois actuel et du mois précédent (format "YYYY-MM")
    const now              = new Date();
    const currentMonth     = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, "0")}`;
    const previousDate     = new Date(now.getFullYear(), now.getMonth() - 1, 1);
    const previousMonth    = `${previousDate.getFullYear()}-${String(previousDate.getMonth() + 1).padStart(2, "0")}`;

    // Initialiser les tableaux de valeurs par indicateur, pour chaque mois
    const monthlyData = {
        [currentMonth]:  Object.fromEntries(VITAL_SIGNS.map((sign) => [sign.key, []])),
        [previousMonth]: Object.fromEntries(VITAL_SIGNS.map((sign) => [sign.key, []])),
    };

    // Remplir les tableaux avec les valeurs de chaque mesure
    for (const vital of allVitals) {
        const monthStr = monthKey(vital.measured_at);
        if (!monthlyData[monthStr]) continue;
        for (const sign of VITAL_SIGNS) {
            if (vital[sign.key] != null) monthlyData[monthStr][sign.key].push(vital[sign.key]);
        }
    }

    // Calculer la moyenne de chaque indicateur pour chaque mois
    const currentAverages  = VITAL_SIGNS.map((sign) => avg(monthlyData[currentMonth][sign.key]));
    const previousAverages = VITAL_SIGNS.map((sign) => avg(monthlyData[previousMonth][sign.key]));

    // Si aucune donnée du tout, afficher le message vide
    if (currentAverages.every((v) => v === null) && previousAverages.every((v) => v === null)) {
        noData.value = true;
        return;
    }

    // Préparer les étiquettes des mois pour la légende
    const [currentYear, currentMonthNum]   = currentMonth.split("-");
    const [previousYear, previousMonthNum] = previousMonth.split("-");
    currentLabel.value = `${MONTHS_FR[+currentMonthNum - 1]} ${currentYear}`;
    prevLabel.value    = `${MONTHS_FR[+previousMonthNum - 1]} ${previousYear}`;

    // Attendre que le DOM affiche le canvas avant de dessiner
    await nextTick();

    // Créer l'histogramme groupé (mois actuel vs mois précédent)
    chartInstance = new Chart(canvasRef.value, {
        type: "bar",
        data: {
            labels: VITAL_SIGNS.map((m) => m.label),
            datasets: [
                {
                    label: currentLabel.value,
                    data: currentAverages,
                    backgroundColor: "rgba(99,102,241,1)",
                    borderColor: "#4f46e5",
                    borderWidth: 2.5,
                    borderRadius: 6,
                },
                {
                    label: prevLabel.value,
                    data: previousAverages,
                    backgroundColor: "rgba(100,116,139,0.9)",
                    borderColor: "#64748b",
                    borderWidth: 2.5,
                    borderRadius: 6,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: "top",
                    labels: { font: { size: 14, weight: "bold" }, padding: 14, boxWidth: 22 },
                },
                tooltip: {
                    mode: "index",
                    intersect: false,
                    backgroundColor: "rgba(0,0,0,0.9)",
                    titleFont: { size: 15, weight: "bold" },
                    bodyFont: { size: 13 },
                    padding: 14,
                    callbacks: {
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
                    ticks: { font: { size: 13, weight: "600" }, color: "#1f2937" },
                },
                y: {
                    beginAtZero: false,
                    grid: { color: "#d1d5db", lineWidth: 1.5 },
                    ticks: { font: { size: 13, weight: "600" }, color: "#1f2937" },
                },
            },
        },
    });
}

onMounted(() => {
    dashStore.initialize();
    if (dashStore.initialized) buildChart();
});
watch(() => dashStore.initialized, async (ready) => { if (ready) await buildChart(); });

// Détruire le graphe quand le composant est retiré (libération mémoire)
onUnmounted(() => chartInstance?.destroy());
</script>
