<!-- Graphe en courbe animé : comparaison du signe vital sélectionné entre 2 mois -->
<template>
    <section class="mt-3 rounded-2xl border-2 border-blue-300 bg-white p-3 shadow-sm transition-all duration-300 hover:shadow-lg hover:border-blue-400">

        <!-- Titre, légende des deux mois et sélecteur de l'indicateur -->
        <div class="mb-3 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Comparaison mensuelle — signes vitaux</h2>
                <!-- Légende : mois actuel (rouge plein) et mois précédent (bleu pointillé) -->
                <p class="mt-0.5 flex items-center gap-3 text-xs text-slate-600">
                    <span class="flex items-center gap-1.5">
                        <span class="inline-block h-2.5 w-5 rounded-full bg-[#ef4444]"></span>
                        {{ currentLabel }}
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span class="inline-block h-0 w-5 border-t-2 border-dashed border-[#0284c7]"></span>
                        {{ prevLabel }}
                    </span>
                </p>
            </div>

            <!-- Sélecteur de l'indicateur à afficher -->
            <select
                v-model="selectedKey"
                @change="rebuild"
                class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-sm font-medium text-slate-700 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400"
            >
                <option v-for="m in METRICS" :key="m.key" :value="m.key">{{ m.label }}</option>
            </select>
        </div>

        <!-- Message pendant le chargement -->
        <div v-if="loading" class="flex h-52 items-center justify-center text-sm text-slate-500">
            Chargement…
        </div>

        <!-- Message si aucune donnée pour cet indicateur -->
        <div v-else-if="noData" class="flex h-52 items-center justify-center text-sm text-slate-500">
            Pas de données pour ce signe vital.
        </div>

        <!-- Zone du graphe (canvas Chart.js) -->
        <div v-else class="relative h-52">
            <canvas ref="canvasRef"></canvas>
        </div>
    </section>
</template>

<script setup>
import { ref, onMounted, onUnmounted, nextTick } from "vue";
import { Chart, LineController, LineElement, PointElement, CategoryScale, LinearScale, Tooltip, Legend, Filler } from "chart.js";
import api from "@/services/api";

// Enregistrer les composants Chart.js nécessaires
Chart.register(LineController, LineElement, PointElement, CategoryScale, LinearScale, Tooltip, Legend, Filler);

// Les 4 indicateurs disponibles dans le sélecteur
const METRICS = [
    { key: "heart_rate",         label: "Fréquence cardiaque (bpm)"   },
    { key: "systolic_pressure",  label: "Pression systolique (mmHg)"  },
    { key: "diastolic_pressure", label: "Pression diastolique (mmHg)" },
    { key: "oxygen_saturation",  label: "Saturation en oxygène (%)"   },
];

// Noms courts des mois en français
const MONTHS_FR = ["Jan", "Fév", "Mar", "Avr", "Mai", "Jun", "Jul", "Aoû", "Sep", "Oct", "Nov", "Déc"];

// Références et variables
const canvasRef    = ref(null);          // référence vers le <canvas>
const loading      = ref(true);          // true pendant le chargement
const noData       = ref(false);         // true si aucune donnée
const selectedKey  = ref("heart_rate");  // indicateur sélectionné
const currentLabel = ref("");            // étiquette du mois actuel ex: "Avr 2025"
const prevLabel    = ref("");            // étiquette du mois précédent

let chart     = null;  // instance Chart.js
let allVitals = [];    // toutes les mesures chargées
let curKey    = "";    // clé du mois actuel ex: "2025-04"
let prevKey   = "";    // clé du mois précédent

// Retourne les 7 premiers caractères d'une date ISO pour obtenir "YYYY-MM"
function monthOf(dateStr) {
    return (dateStr ?? "").slice(0, 7);
}

// Retourne la liste des mesures d'un mois donné pour un indicateur donné
// Chaque élément : { day: 14, value: 72.5 }
function buildPoints(monthK, field) {
    const points = [];

    for (const vital of allVitals) {
        // Ignorer si pas du bon mois ou si la valeur est manquante
        if (monthOf(vital.measured_at) !== monthK) continue;
        if (vital[field] == null) continue;

        // Extraire le numéro du jour depuis la date (ex: "2025-04-14" → 14)
        const day   = parseInt(vital.measured_at.slice(8, 10), 10);
        const value = parseFloat(vital[field]);
        points.push({ day, value });
    }

    // Trier les points par jour croissant (1, 2, 3 ... 30)
    points.sort(function (a, b) { return a.day - b.day; });

    return points;
}

// Reconstruit le graphe pour l'indicateur sélectionné
async function rebuild() {
    noData.value = false;
    const field  = selectedKey.value;

    // Récupérer les points du mois actuel et du mois précédent
    const curPts  = buildPoints(curKey, field);
    const prevPts = buildPoints(prevKey, field);

    // Si aucune donnée dans les deux mois, afficher le message vide
    if (curPts.length === 0 && prevPts.length === 0) {
        noData.value = true;
        if (chart) { chart.destroy(); chart = null; }
        return;
    }

    if (chart) { chart.destroy(); }

    // Attendre que le DOM affiche le canvas avant de dessiner
    await nextTick();

    // Trouver la longueur maximale des deux séries pour aligner l'axe X
    const longueur = Math.max(curPts.length, prevPts.length);

    // Créer les étiquettes de l'axe X : 1, 2, 3 ... longueur
    const labels = [];
    for (let i = 0; i < longueur; i++) {
        labels.push(i + 1);
    }

    // Créer les tableaux de données (null si pas de point à cette position)
    const curData  = [];
    const prevData = [];
    const curDays  = []; // numéros de jour réels pour les tooltips
    const prevDays = [];

    for (let i = 0; i < longueur; i++) {
        curData.push(i < curPts.length  ? curPts[i].value  : null);
        prevData.push(i < prevPts.length ? prevPts[i].value : null);
        curDays.push(i < curPts.length  ? curPts[i].day  : null);
        prevDays.push(i < prevPts.length ? prevPts[i].day : null);
    }

    // Créer le graphe en courbe avec les deux séries (mois actuel + mois précédent)
    chart = new Chart(canvasRef.value, {
        type: "line",
        data: {
            labels,
            datasets: [
                {
                    label: currentLabel.value,
                    data: curData,
                    borderColor: "#ef4444",
                    backgroundColor: "rgba(239,68,68,0.08)",
                    borderWidth: 2.5,
                    pointRadius: 3.5,
                    pointHoverRadius: 6,
                    pointBackgroundColor: "#ef4444",
                    pointBorderColor: "#fff",
                    pointBorderWidth: 1.5,
                    tension: 0.4,
                    fill: true,
                    spanGaps: false,
                },
                {
                    label: prevLabel.value,
                    data: prevData,
                    borderColor: "#0284c7",
                    backgroundColor: "rgba(2,132,199,0.07)",
                    borderWidth: 2.5,
                    borderDash: [6, 4],
                    pointRadius: 3.5,
                    pointHoverRadius: 6,
                    pointBackgroundColor: "#0284c7",
                    pointBorderColor: "#fff",
                    pointBorderWidth: 1.5,
                    tension: 0.4,
                    fill: true,
                    spanGaps: false,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: { duration: 1000 }, // animation simple d'apparition
            interaction: { mode: "index", intersect: false },
            plugins: {
                legend: { display: false },
                tooltip: {
                    mode: "index",
                    intersect: false,
                    backgroundColor: "rgba(15,23,42,0.92)",
                    titleColor: "#f8fafc",
                    bodyColor: "#cbd5e1",
                    titleFont: { size: 13, weight: "bold" },
                    bodyFont: { size: 12 },
                    padding: 12,
                    callbacks: {
                        // Afficher le numéro du jour réel dans le titre du tooltip
                        title: function (items) {
                            const idx   = items[0].dataIndex;
                            const parts = [];
                            if (curDays[idx]  != null) parts.push(currentLabel.value + " — Jour " + curDays[idx]);
                            if (prevDays[idx] != null) parts.push(prevLabel.value + " — Jour " + prevDays[idx]);
                            return parts;
                        },
                        label: function (ctx) {
                            if (ctx.parsed.y != null) {
                                return " " + ctx.dataset.label + " : " + ctx.parsed.y;
                            }
                            return " " + ctx.dataset.label + " : —";
                        },
                    },
                },
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 10, weight: "500" }, color: "#64748b", maxTicksLimit: 16, autoSkip: true },
                    title: { display: true, text: "Mesure n°", color: "#94a3b8", font: { size: 10, weight: "600" } },
                },
                y: {
                    beginAtZero: false,
                    grid: { color: "#f1f5f9", drawBorder: false },
                    border: { display: false },
                    ticks: { font: { size: 11, weight: "500" }, color: "#64748b" },
                },
            },
        },
    });
}

// Charge les mesures vitales depuis l'API et initialise les clés de mois
async function load() {
    const { data: res } = await api.get("/dashboard/vitals", { params: { days: 62 } });
    allVitals = res?.data ?? [];

    // Calculer la clé du mois actuel (ex: "2025-04")
    const now  = new Date();
    const annee    = now.getFullYear();
    const moisNum  = now.getMonth() + 1; // 1 = Janvier, 12 = Décembre
    curKey = annee + "-" + String(moisNum).padStart(2, "0");

    // Calculer la clé du mois précédent
    const moisPrecedent = new Date(annee, now.getMonth() - 1, 1);
    const anneePrev     = moisPrecedent.getFullYear();
    const moisPrevNum   = moisPrecedent.getMonth() + 1;
    prevKey = anneePrev + "-" + String(moisPrevNum).padStart(2, "0");

    // Préparer les étiquettes lisibles pour la légende
    currentLabel.value = MONTHS_FR[moisNum - 1]    + " " + annee;
    prevLabel.value    = MONTHS_FR[moisPrevNum - 1] + " " + anneePrev;

    loading.value = false;
    await rebuild();
}

// Charger au démarrage du composant
onMounted(load);

// Détruire le graphe quand le composant est retiré (libération mémoire)
onUnmounted(function () { if (chart) chart.destroy(); });
</script>
