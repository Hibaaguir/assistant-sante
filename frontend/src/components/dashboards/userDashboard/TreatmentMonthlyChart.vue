<!-- Graphe en courbe : prises vs oublis de médicaments par jour (7 ou 30 jours) -->
<template>
    <section class="mt-5 rounded-2xl border-2 border-blue-300 bg-white p-4 shadow-sm transition-all duration-300 hover:shadow-lg hover:border-blue-400">

        <!-- Titre et boutons de filtre (semaine / mois) -->
        <div class="mb-4 flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-semibold text-slate-900">Distribution des traitements</h2>
                <p class="mt-0.5 text-sm text-slate-700">Prises quotidiennes par médicament</p>
            </div>
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
        <div v-if="loading" class="flex h-56 items-center justify-center text-slate-700">
            Chargement...
        </div>

        <!-- Message si aucune donnée sur la période -->
        <div v-else-if="noData" class="flex h-56 items-center justify-center text-slate-700">
            Aucune donnée de traitement sur cette période.
        </div>

        <template v-else>
            <!-- Zone du graphe (canvas Chart.js) -->
            <div class="relative h-56">
                <canvas ref="canvasRef"></canvas>
            </div>
        </template>
    </section>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick, watch } from "vue";
import { Chart, LineController, LineElement, PointElement, CategoryScale, LinearScale, Tooltip, Filler } from "chart.js";
import { useDashboardStore } from "@/stores/dashboard";

// Enregistrer les composants Chart.js nécessaires pour le graphe en courbe
Chart.register(LineController, LineElement, PointElement, CategoryScale, LinearScale, Tooltip, Filler);

// Options de filtre disponibles
const filters = [
    { label: "Par semaine", days: 7 },
    { label: "Par mois",    days: 30 },
];

// Noms courts des mois pour les étiquettes de l'axe X
const MONTHS = ["jan", "fév", "mar", "avr", "mai", "jun", "jul", "aoû", "sep", "oct", "nov", "déc"];

const dashStore = useDashboardStore();

// Références et variables
const canvasRef = ref(null);
const loading   = computed(() => !dashStore.initialized);
const noData    = ref(false);
const days      = ref(30);

let allChecks     = [];
let chartInstance = null;
let dates         = [];

// Génère la liste des N derniers jours au format YYYY-MM-DD
function buildDateRange(n) {
    return Array.from({ length: n }, (_, i) => {
        const d = new Date();
        d.setDate(d.getDate() - (n - 1 - i));
        return d.toISOString().slice(0, 10);
    });
}

// Convertit une date ISO en étiquette courte, ex: "14 avr"
function shortLabel(iso) {
    const d = new Date(iso);
    return `${d.getDate()} ${MONTHS[d.getMonth()]}`;
}

// Construit et affiche le graphe pour la période active
async function buildChart() {
    // Détruire l'ancien graphe avant d'en créer un nouveau
    chartInstance?.destroy();
    noData.value = false;

    // Calculer la date limite (début de la période)
    const cutoff = (() => {
        const d = new Date();
        d.setDate(d.getDate() - (days.value - 1));
        return d.toISOString().slice(0, 10);
    })();

    // Garder uniquement les prises dans la période
    const filtered = allChecks.filter((c) => c.check_date >= cutoff);

    if (!filtered.length) {
        noData.value = true;
        return;
    }

    // Regrouper les prises par date : { "2025-04-20": { taken: ["Aspirine"], missed: ["Doliprane"] } }
    const byDate = {};
    for (const c of filtered) {
        const dt = c.check_date;
        if (!byDate[dt]) byDate[dt] = { taken: [], missed: [] };
        const name = c.medication_name || "Inconnu";
        c.taken ? byDate[dt].taken.push(name) : byDate[dt].missed.push(name);
    }

    // Générer les données pour chaque jour de la période
    dates            = buildDateRange(days.value);
    const takenData  = dates.map((d) => byDate[d]?.taken.length ?? 0);
    const missedData = dates.map((d) => byDate[d]?.missed.length ?? 0);
    const labels     = dates.map(shortLabel);

    // Attendre que le DOM affiche le canvas avant de dessiner
    await nextTick();

    // Créer le graphe en courbe avec deux séries (pris / non pris)
    chartInstance = new Chart(canvasRef.value, {
        type: "line",
        data: {
            labels,
            datasets: [
                {
                    label: "Pris",
                    data: takenData,
                    borderColor: "#059669",
                    backgroundColor: "rgba(5,150,105,0.12)",
                    pointBackgroundColor: "#059669",
                    pointBorderColor: "#fff",
                    pointBorderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    borderWidth: 3,
                },
                {
                    label: "Non pris",
                    data: missedData,
                    borderColor: "#dc2626",
                    backgroundColor: "rgba(220,38,38,0.1)",
                    pointBackgroundColor: "#dc2626",
                    pointBorderColor: "#fff",
                    pointBorderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    borderWidth: 3,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: "index", intersect: false },
            plugins: {
                legend: {
                    position: "top",
                    labels: { font: { size: 14, weight: "bold" }, padding: 14, boxWidth: 22, color: "#1f2937" },
                },
                tooltip: {
                    backgroundColor: "rgba(0,0,0,0.9)",
                    titleFont: { size: 15, weight: "bold" },
                    bodyFont: { size: 13 },
                    padding: 14,
                    callbacks: {
                        // Afficher la liste des médicaments pris ou manqués ce jour-là
                        afterLabel(ctx) {
                            const date = dates[ctx.dataIndex];
                            const day  = byDate[date];
                            if (!day) return "";
                            const list = ctx.dataset.label === "Pris" ? day.taken : day.missed;
                            return list.length ? "  · " + list.join("\n  · ") : "";
                        },
                    },
                },
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { maxRotation: 45, font: { size: 12, weight: "500" }, color: "#475569" },
                },
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1, font: { size: 12, weight: "500" }, color: "#475569" },
                    grid: { color: "#e2e8f0" },
                },
            },
        },
    });
}

// Charge les données du store et construit le graphe
async function load() {
    allChecks = dashStore.treatmentChecks90;
    await buildChart();
}

// Changer le filtre et reconstruire le graphe
async function changeFilter(v) {
    days.value = v;
    await buildChart();
}

onMounted(() => {
    dashStore.initialize();
    if (dashStore.initialized) load();
});
watch(() => dashStore.initialized, async (ready) => { if (ready) await load(); });

// Détruire le graphe quand le composant est retiré (libération mémoire)
onUnmounted(() => chartInstance?.destroy());
</script>
