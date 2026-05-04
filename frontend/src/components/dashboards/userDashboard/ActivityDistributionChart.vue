<!-- Graphe en anneau : répartition du temps d'activité physique par type -->
<template>
    <section class="mt-5 rounded-2xl border-2 border-blue-300 bg-white p-4 shadow-sm transition-all duration-300 hover:shadow-lg hover:border-blue-400">

        <!-- Titre et boutons de filtre (semaine / mois) -->
        <div class="mb-4 flex items-center justify-between">
            <Typography tag="h2" variant="h2-style">Activité physique par type</Typography>
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

        <!-- Message pendant le chargement -->
        <div v-if="loading" class="flex h-64 items-center justify-center text-slate-700">
            Chargement...
        </div>

        <!-- Message si aucune activité sur la période -->
        <div v-else-if="noData" class="flex h-64 items-center justify-center text-slate-700">
            Aucune activité physique sur cette période.
        </div>

        <!-- Zone du graphe (canvas Chart.js) -->
        <div v-else class="flex flex-col items-center gap-4 sm:flex-row justify-center">
            <canvas ref="canvasRef" class="max-h-64 max-w-[260px]"></canvas>
        </div>
    </section>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick, watch } from "vue";
import Typography from "@/components/ui/Typography.vue";
import { Chart, DoughnutController, ArcElement, Legend, Tooltip } from "chart.js";
import { useDashboardStore } from "@/stores/dashboard";

// Enregistrer les composants Chart.js pour le graphe en anneau
Chart.register(DoughnutController, ArcElement, Legend, Tooltip);

// Couleurs des parts du graphe
const COLORS = ["#6366f1", "#10b981", "#f59e0b", "#f43f5e", "#149bd7", "#8b5cf6", "#14b8a6", "#ec4899"];

// Options de filtre disponibles
const filters = [
    { label: "Par semaine", days: 7 },
    { label: "Par mois",    days: 30 },
];

const dashStore = useDashboardStore();

// Références et variables
const canvasRef = ref(null);
const loading   = computed(() => !dashStore.initialized);
const noData    = ref(false);
const days      = ref(7);

let allEntries    = [];
let chartInstance = null;

// Retourne la date (format YYYY-MM-DD) il y a N jours
function dateNDaysAgo(n) {
    const d = new Date();
    d.setDate(d.getDate() - (n - 1));
    return d.toISOString().slice(0, 10);
}

// Calcule le total des minutes par type d'activité sur la période
function aggregate() {
    const cutoff = dateNDaysAgo(days.value);
    const totals = {};

    for (const entry of allEntries) {
        // Ignorer les entrées trop anciennes ou sans date
        if (!entry.entry_date || entry.entry_date < cutoff) continue;

        // Accepter les deux noms de champ possibles selon la version de l'API
        const activities = entry.physical_activities || entry.physicalActivities || [];

        for (const act of activities) {
            const type    = act.activity_type || "Autre";
            const minutes = act.duration_minutes || 0;

            // Initialiser à 0 si ce type n'a pas encore été rencontré
            if (!totals[type]) totals[type] = 0;

            totals[type] += minutes;
        }
    }

    return totals;
}

// Construit et affiche le graphe en anneau
async function buildChart() {
    // Détruire l'ancien graphe avant d'en créer un nouveau
    if (chartInstance) chartInstance.destroy();

    noData.value = false;

    const totals = aggregate();
    const labels = Object.keys(totals);

    // Si aucune donnée, afficher le message "aucune activité"
    if (labels.length === 0) {
        noData.value = true;
        return;
    }

    // Attendre que le DOM affiche le canvas avant de dessiner
    await nextTick();

    // Créer le graphe en anneau
    chartInstance = new Chart(canvasRef.value, {
        type: "doughnut",
        data: {
            labels,
            datasets: [{
                data: labels.map((l) => totals[l]),
                backgroundColor: COLORS.slice(0, labels.length),
                borderWidth: 2.5,
                borderColor: "#fff",
            }],
        },
        options: {
            responsive: true,
            cutout: "60%",
            plugins: {
                legend: { display: true },
                tooltip: {
                    backgroundColor: "rgba(0,0,0,0.9)",
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

// Charge les données du store et construit le graphe
async function load() {
    allEntries = dashStore.journal;
    await buildChart();
}

async function changeFilter(value) {
    days.value = value;
    await buildChart();
}

onMounted(() => {
    dashStore.initialize();
    if (dashStore.initialized) load();
});
watch(() => dashStore.initialized, async (ready) => { if (ready) await load(); });

// Détruire le graphe quand le composant est retiré (libération mémoire)
onUnmounted(() => {
    if (chartInstance) chartInstance.destroy();
});
</script>
