<!-- Graphe en barres : hydratation quotidienne sur les 7 derniers jours -->
<template>
    <section class="mt-5 overflow-hidden rounded-2xl border-2 border-blue-300 shadow-sm transition-all duration-300 hover:shadow-lg hover:border-blue-400">

        <!-- En-tête dégradé bleu avec titre et objectif -->
        <div class="bg-gradient-to-r from-blue-500 to-cyan-400 px-5 py-4">
            <Typography tag="h2" variant="h2-style" class="text-white">Hydratation hebdomadaire</Typography>
            <p class="mt-0.5 text-sm text-blue-100">Objectif : {{ GOAL }} L / jour</p>
        </div>

        <div class="bg-white p-4">
            <!-- Message pendant le chargement -->
            <div v-if="loading" class="flex h-48 items-center justify-center text-slate-400">
                Chargement...
            </div>

            <!-- Message si aucune donnée cette semaine -->
            <div v-else-if="noData" class="flex h-48 items-center justify-center text-slate-400">
                Aucune donnée d'hydratation cette semaine.
            </div>

            <template v-else>
                <!-- Zone du graphe (canvas Chart.js) -->
                <div class="relative h-52">
                    <canvas ref="canvasRef"></canvas>
                </div>

            </template>
        </div>
    </section>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick, watch } from "vue";
import Typography from "@/components/ui/Typography.vue";
import { Chart, BarController, BarElement, CategoryScale, LinearScale, Tooltip } from "chart.js";
import { useDashboardStore } from "@/stores/dashboard";

// Enregistrer les composants Chart.js pour le graphe en barres
Chart.register(BarController, BarElement, CategoryScale, LinearScale, Tooltip);

// Objectif d'hydratation journalier en litres
const GOAL = 2;

// Noms des jours en français et en anglais (pour la correspondance)
const DAY_LABELS   = ["Lun", "Mar", "Mer", "Jeu", "Ven", "Sam", "Dim"];
const DAYS_OF_WEEK = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"];

const dashStore = useDashboardStore();

// Références et variables
const canvasRef = ref(null);
const loading   = computed(() => !dashStore.initialized);
const noData    = ref(false);
const weekDays  = ref([]);
let chartInstance = null;

// Retourne la liste des 7 derniers jours au format YYYY-MM-DD
function getLast7Days() {
    const liste = [];
    for (let i = 6; i >= 0; i--) {
        const d = new Date();
        d.setDate(d.getDate() - i);
        liste.push(d.toISOString().slice(0, 10));
    }
    return liste;
}

// Traite les données du journal depuis le store et dessine le graphe
async function load() {
    const entries = dashStore.journal;

    // Regrouper les valeurs d'hydratation par date
    const byDate = {};
    for (const entry of entries) {
        if (entry.entry_date && entry.hydration != null) {
            byDate[entry.entry_date] = parseFloat(entry.hydration);
        }
    }

    // Construire les données pour chacun des 7 derniers jours
    const dates  = getLast7Days();
    const values = [];
    for (const d of dates) {
        values.push(byDate[d] ?? 0); // 0 si pas de donnée ce jour
    }

    // Construire la liste des jours avec leur étiquette et leur valeur
    weekDays.value = [];
    for (let i = 0; i < dates.length; i++) {
        // Utiliser T12:00:00 pour éviter les problèmes de fuseau horaire UTC
        const nomJourAnglais = new Date(dates[i] + "T12:00:00").toLocaleDateString("en-US", { weekday: "long" });
        const indexJour = DAYS_OF_WEEK.indexOf(nomJourAnglais);

        weekDays.value.push({
            date:  dates[i],
            label: indexJour >= 0 ? DAY_LABELS[indexJour] : dates[i].slice(5),
            value: values[i],
        });
    }

    // Vérifier si toutes les valeurs sont à zéro (pas de données)
    let toutZero = true;
    for (const v of values) {
        if (v !== 0) { toutZero = false; break; }
    }
    if (toutZero) {
        noData.value = true;
        return;
    }

    // Attendre que le DOM affiche le canvas avant de dessiner
    await nextTick();

    // Préparer les labels et les couleurs des barres
    const labelsGraphe = [];
    const couleursFond = [];
    const couleursBord = [];

    for (let i = 0; i < weekDays.value.length; i++) {
        labelsGraphe.push(weekDays.value[i].label);
        // Barre pleine si l'objectif est atteint, transparente sinon
        couleursFond.push(values[i] >= GOAL ? "rgba(6,182,212,0.75)" : "rgba(6,182,212,0.3)");
        couleursBord.push(values[i] >= GOAL ? "#0891b2" : "#67e8f9");
    }

    // Créer le graphe en barres
    chartInstance = new Chart(canvasRef.value, {
        type: "bar",
        data: {
            labels: labelsGraphe,
            datasets: [{
                label: "Hydratation (L)",
                data: values,
                backgroundColor: couleursFond,
                borderColor: couleursBord,
                borderWidth: 1.5,
                borderRadius: 6,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    max: Math.max(GOAL + 1, ...values) + 0.5,
                    ticks: {
                        callback: function (v) { return v + " L"; },
                        font: { size: 12, weight: "500" },
                        color: "#475569",
                    },
                    grid: { color: "#e2e8f0" },
                },
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 12, weight: "500" }, color: "#475569" },
                },
            },
            plugins: {
                legend: {
                    position: "top",
                    labels: { font: { size: 13, weight: "bold" }, padding: 12, boxWidth: 18, color: "#334155" },
                },
                tooltip: {
                    backgroundColor: "rgba(0,0,0,0.8)",
                    titleFont: { size: 13, weight: "bold" },
                    bodyFont: { size: 12 },
                    padding: 12,
                    callbacks: { label: function (ctx) { return " " + ctx.parsed.y + " L"; } },
                },
            },
        },
        // Plugin maison : tracer une ligne pointillée à l'objectif de 2L
        plugins: [{
            id: "goalLine",
            afterDraw(chart) {
                const { ctx, chartArea, scales } = chart;
                const y = scales.y.getPixelForValue(GOAL);
                ctx.save();
                ctx.beginPath();
                ctx.setLineDash([6, 4]);
                ctx.strokeStyle = "#149bd7";
                ctx.lineWidth   = 1.5;
                ctx.moveTo(chartArea.left, y);
                ctx.lineTo(chartArea.right, y);
                ctx.stroke();
                ctx.restore();
            },
        }],
    });
}

onMounted(() => {
    dashStore.initialize();
    if (dashStore.initialized) load();
});
watch(() => dashStore.initialized, async (ready) => { if (ready) await load(); });

// Détruire le graphe quand le composant est retiré (libération mémoire)
onUnmounted(function () { if (chartInstance) chartInstance.destroy(); });
</script>
