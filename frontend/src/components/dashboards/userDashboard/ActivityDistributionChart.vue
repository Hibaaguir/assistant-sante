<!--ActivityDistributionChart.vue-->
<template>
    <section
        class="mt-5 rounded-2xl border-2 border-blue-300 bg-white p-4 shadow-sm transition-all duration-300 hover:shadow-lg hover:border-blue-400"
    >
        <div class="mb-4 flex items-center justify-between">
            <Typography tag="h2" variant="h2-style">
                Activité physique par type
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

        <div
            v-else-if="noData"
            class="flex h-64 items-center justify-center text-slate-700"
        >
            Aucune activité physique sur cette période.
        </div>

        <div
            v-else
            class="flex flex-col items-center gap-4 sm:flex-row justify-center"
        >
            <canvas ref="canvasRef" class="max-h-64 max-w-[260px]"></canvas>
        </div>
    </section>
</template>

<<script setup>
import { ref, onMounted, onUnmounted, nextTick } from "vue";
import Typography from "@/components/ui/Typography.vue";
import {
    Chart,
    DoughnutController,
    ArcElement,
    Legend,
    Tooltip,
} from "chart.js";
import api from "@/services/api";

// Enregistrement des composants Chart.js nécessaires
Chart.register(DoughnutController, ArcElement, Legend, Tooltip);

// Couleurs du graphique
const COLORS = [
    "#6366f1",
    "#10b981",
    "#f59e0b",
    "#f43f5e",
    "#149bd7",
    "#8b5cf6",
    "#14b8a6",
    "#ec4899",
];

// Filtres disponibles (7 jours ou 30 jours)
const filters = [
    { label: "Par semaine", days: 7 },
    { label: "Par mois", days: 30 },
];

// Variables réactives
const canvasRef = ref(null);
const loading = ref(true);
const noData = ref(false);
const days = ref(7);
const summary = ref([]);

// Variables normales
let allEntries = [];
let chartInstance = null;

// Retourne la date (format YYYY-MM-DD) il y a N jours
function dateNDaysAgo(n) {
    const d = new Date();
    d.setDate(d.getDate() - (n - 1));
    return d.toISOString().slice(0, 10);
}

// Calcule le total des minutes par type d'activité
function aggregate() {
    const cutoff = dateNDaysAgo(days.value);
    const totals = {};

    for (const entry of allEntries) {
        // Ignorer si pas de date ou trop ancien
        if (!entry.entry_date || entry.entry_date < cutoff) continue;

        // Récupérer la liste des activités (2 formats possibles)
        const activities =
            entry.physical_activities ||
            entry.physicalActivities ||
            [];

        for (const act of activities) {
            const type = act.activity_type || "Autre";
            const minutes = act.duration_minutes || 0;

            // Initialiser si nécessaire si le type est la première fois rencontré commence par 0
            if (!totals[type]) {
                totals[type] = 0;
            }

            // Ajouter les minutes
            totals[type] += minutes;
        }
    }

    return totals;
}

// Crée et affiche le graphique
async function buildChart() {
    // Supprimer l'ancien graphique s'il existe
    if (chartInstance) {
        chartInstance.destroy();
    }

    noData.value = false;

    const totals = aggregate();
    const labels = Object.keys(totals);

    // Si aucune donnée
    if (labels.length === 0) {
        noData.value = true;
        return;
    }

    // Préparer les données pour affichage ajouter une propriété "label" et "minutes" à summary pour affichage dans la légende 
    summary.value = labels.map((label) => ({
        label: label,
        minutes: totals[label],
    }));

    // Attendre que le DOM soit prêt
    await nextTick();

    // Création du graphique
    chartInstance = new Chart(canvasRef.value, {
        type: "doughnut",
        data: {
            labels: labels,
            datasets: [
                {
                    data: labels.map((l) => totals[l]),
                    backgroundColor: COLORS.slice(0, labels.length),
                    borderWidth: 2.5,
                    borderColor: "#fff",
                },
            ],
        },
        options: {
            responsive: true,
            cutout: "60%",
            plugins: {
                legend: {
                    display: true,
                },
                tooltip: {
                    backgroundColor: "rgba(0, 0, 0, 0.9)",
                    titleFont: { size: 15, weight: "bold" },
                    bodyFont: { size: 13 },
                    padding: 14,
                    callbacks: {
                       label: (ctx) => `${ctx.parsed} min`,
                    },
                },
            },
        },
    });
}

// Charger les données depuis l'API
async function load() {
    loading.value = true;

    const response = await api.get("/dashboard/journal");

    // Récupération des données
    if (response.data && response.data.data) {
        allEntries = response.data.data;
    } else {
        allEntries = [];
    }

    loading.value = false;

    // Construire le graphique
    await buildChart();
}

// Changer le filtre (7 jours / 30 jours)
async function changeFilter(value) {
    days.value = value;
    await buildChart();
}

// Lifecycle
onMounted(load);
onUnmounted(() => {
    if (chartInstance) chartInstance.destroy();
});
</script>