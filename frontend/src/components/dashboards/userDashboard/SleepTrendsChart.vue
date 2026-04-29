<!-- Graphe en courbe : tendances du sommeil par semaine pour un mois choisi -->
<template>
    <section class="mt-5 overflow-hidden rounded-2xl border border-slate-300 bg-white shadow-sm">

        <!-- En-tête avec titre et sélecteur de mois -->
        <div class="flex items-start justify-between gap-3 px-4 pt-4 pb-3 sm:px-5">
            <div>
                <Typography tag="h2" variant="h2-style">
                    <span class="mr-1">🌙</span>Sommeil — {{ selectedMonthName }}
                </Typography>
                <Typography tag="p" variant="paragraph" class="mt-2">
                    Moyenne par semaine · Objectif 8 h / nuit
                </Typography>
            </div>

            <!-- Sélecteur pour choisir le mois à afficher -->
            <div class="relative shrink-0">
                <select
                    v-model="selectedMonth"
                    @change="rebuild"
                    class="appearance-none rounded-xl border border-indigo-100 bg-indigo-50 py-1.5 pl-3 pr-8 text-sm font-semibold text-indigo-600 outline-none transition hover:bg-indigo-100 focus:ring-2 focus:ring-indigo-300"
                >
                    <option v-for="m in months" :key="m.key" :value="m.key">{{ m.label }}</option>
                </select>
                <span class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 text-xs text-indigo-500">▾</span>
            </div>
        </div>

        <div class="border-t border-slate-200 px-2 pt-3 pb-4 sm:px-4">
            <!-- Message pendant le chargement -->
            <div v-if="loading" class="flex h-56 items-center justify-center text-sm text-slate-700">
                Chargement...
            </div>

            <template v-else>
                <!-- Zone du graphe (canvas Chart.js) -->
                <div class="relative h-56">
                    <canvas ref="canvasRef"></canvas>
                </div>

                <!-- Récapitulatif des moyennes par semaine -->
                <div class="mt-2 flex flex-wrap gap-2.5 px-1">
                    <span
                        v-for="(w, i) in weekData"
                        :key="i"
                        class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700"
                    >
                        Sem {{ i + 1 }} · {{ w.avg !== null ? `${w.avg} h` : "—" }}
                    </span>
                </div>
            </template>
        </div>
    </section>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick } from "vue";
import Typography from "@/components/ui/Typography.vue";
import { Chart, registerables } from "chart.js";
import api from "@/services/api";

// Enregistrer tous les composants Chart.js nécessaires
Chart.register(...registerables);

// Noms des mois en français (index 0 = Janvier, index 11 = Décembre)
const MONTHS_FR = ["Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre"];

// Objectif de sommeil en heures par nuit
const GOAL = 8;

// Générer la liste des 12 derniers mois pour le sélecteur déroulant
function buildMonths() {
    const liste = [];
    for (let i = 0; i < 12; i++) {
        // Partir du mois actuel et remonter dans le temps
        const d = new Date();
        d.setDate(1);
        d.setMonth(d.getMonth() - (11 - i)); // 11-i : le plus ancien d'abord

        const annee  = d.getFullYear();
        const mois   = d.getMonth(); // 0 = Janvier
        const moisStr = String(mois + 1).padStart(2, "0"); // "01" à "12"
        const key    = annee + "-" + moisStr; // ex: "2025-04"

        liste.push({ key: key, label: MONTHS_FR[mois] });
    }
    return liste;
}

const months        = buildMonths();
const selectedMonth = ref(months[months.length - 1].key); // mois actuel par défaut

// Nom du mois actuellement sélectionné (pour l'affichage dans le titre)
const selectedMonthName = computed(function () {
    for (const m of months) {
        if (m.key === selectedMonth.value) return m.label;
    }
    return "";
});

// Références et variables
const canvasRef = ref(null);  // référence vers le <canvas>
const loading   = ref(true);  // true pendant le chargement
const weekData  = ref([]);    // moyennes de sommeil par semaine

let chartInstance = null; // instance Chart.js
let allEntries    = [];   // toutes les entrées du journal

// Retourne le numéro de semaine (0 à 4) selon le jour du mois
function weekIdx(dateStr) {
    const day = parseInt((dateStr ?? "").slice(8, 10), 10);
    if (day <= 7)  return 0;
    if (day <= 14) return 1;
    if (day <= 21) return 2;
    if (day <= 28) return 3;
    return 4;
}

// Calcule la moyenne d'une liste de nombres (ignore les null et NaN)
function calculerMoyenne(liste) {
    const valeursValides = [];
    for (const x of liste) {
        if (x != null && !isNaN(x)) {
            valeursValides.push(x);
        }
    }
    if (valeursValides.length === 0) return null;

    let somme = 0;
    for (const v of valeursValides) {
        somme += v;
    }
    return +(somme / valeursValides.length).toFixed(1);
}

// Reconstruit le graphe pour le mois sélectionné
async function rebuild() {
    // Détruire l'ancien graphe avant d'en créer un nouveau
    if (chartInstance) chartInstance.destroy();
    chartInstance = null;

    const moisChoisi = selectedMonth.value;

    // Initialiser 5 buckets (une liste par semaine)
    const buckets = [[], [], [], [], []];

    // Remplir chaque bucket avec les heures de sommeil de la semaine correspondante
    for (const entry of allEntries) {
        if (!entry.entry_date || entry.sleep == null) continue;

        // Ignorer les entrées d'un autre mois
        if (entry.entry_date.slice(0, 7) !== moisChoisi) continue;

        const semaine = weekIdx(entry.entry_date);
        buckets[semaine].push(parseFloat(entry.sleep));
    }

    // Calculer la moyenne de chaque semaine
    const moyennes = [];
    for (const bucket of buckets) {
        moyennes.push(calculerMoyenne(bucket));
    }

    // Pour le graphe : remplacer null par 0 (Chart.js ne gère pas les null en fill)
    const donnéesGraphe = [];
    for (const m of moyennes) {
        donnéesGraphe.push(m == null ? 0 : m);
    }

    // Stocker les moyennes pour le récapitulatif affiché sous le graphe
    weekData.value = [];
    for (const m of moyennes) {
        weekData.value.push({ avg: m });
    }

    // Attendre que le DOM affiche le canvas avant de dessiner
    await nextTick();
    if (!canvasRef.value) return;

    // Créer le graphe en courbe avec la ligne d'objectif
    chartInstance = new Chart(canvasRef.value, {
        type: "line",
        data: {
            labels: ["Sem 1", "Sem 2", "Sem 3", "Sem 4", "Sem 5"],
            datasets: [
                {
                    label: "Sommeil moyen",
                    data: donnéesGraphe,
                    borderColor: "#4338ca",
                    backgroundColor: "rgba(67,56,202,0.15)",
                    borderWidth: 3,
                    pointBackgroundColor: "#ef4444",
                    pointBorderColor: "#4338ca",
                    pointBorderWidth: 2.5,
                    pointRadius: 6,
                    pointHoverRadius: 8,
                    tension: 0.45,
                    fill: true,
                },
                {
                    // Ligne en pointillés représentant l'objectif de 8h
                    label: "Objectif 8 h",
                    data: [GOAL, GOAL, GOAL, GOAL, GOAL],
                    borderColor: "#6366f1",
                    borderDash: [5, 4],
                    borderWidth: 2.5,
                    pointRadius: 0,
                    fill: false,
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
                    backgroundColor: "#111827",
                    titleColor: "#f8fafc",
                    bodyColor: "#f8fafc",
                    titleFont: { size: 13, weight: "bold" },
                    bodyFont: { size: 12 },
                    padding: 12,
                    callbacks: {
                        label: function (ctx) {
                            if (ctx.dataset.label === "Objectif 8 h") {
                                return " Objectif : 8 h / nuit";
                            }
                            const valeur = weekData.value[ctx.dataIndex]?.avg ?? 0;
                            return " Sommeil : " + valeur + " h";
                        },
                    },
                },
            },
            scales: {
                x: {
                    grid: { display: false, drawBorder: false },
                    ticks: { color: "#475569", font: { size: 12 } },
                    border: { display: false },
                },
                y: {
                    min: 0,
                    max: 11,
                    grid: { color: "#e2e8f0", drawBorder: false },
                    ticks: {
                        stepSize: 1,
                        color: "#64748b",
                        font: { size: 12 },
                        callback: function (v) {
                            return (v === 11 || v % 2 === 0) ? v + " h" : "";
                        },
                    },
                    border: { display: false },
                },
            },
        },
    });
}

// Charge toutes les entrées du journal puis construit le graphe
async function load() {
    const { data: res } = await api.get("/dashboard/journal");
    allEntries    = res?.data ?? [];
    loading.value = false;
    await rebuild();
}

// Charger au démarrage du composant
onMounted(load);

// Détruire le graphe quand le composant est retiré (libération mémoire)
onUnmounted(function () { if (chartInstance) chartInstance.destroy(); });
</script>
