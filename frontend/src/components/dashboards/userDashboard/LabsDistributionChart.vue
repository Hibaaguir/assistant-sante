<!-- Histogramme empilé : nombre d'analyses biologiques par mois et par type -->
<template>
    <section class="mt-3 rounded-2xl border-2 border-blue-300 bg-white p-3 shadow-sm transition-all duration-300 hover:shadow-lg hover:border-blue-400">

        <!-- Titre et sous-titre -->
        <div class="mb-3 flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Répartition des analyses par mois</h2>
                <p class="mt-0.5 text-xs text-slate-400">Types d'analyses réalisées chaque mois</p>
            </div>
        </div>

        <!-- Message pendant le chargement -->
        <div v-if="loading" class="flex h-48 items-center justify-center text-sm text-slate-400">
            Chargement…
        </div>

        <!-- Message si aucune analyse enregistrée -->
        <div v-else-if="noData" class="flex h-48 items-center justify-center text-sm text-slate-400">
            Aucune analyse enregistrée.
        </div>

        <template v-else>
            <!-- Zone du graphe (canvas Chart.js) -->
            <div class="relative h-52">
                <canvas ref="canvasRef"></canvas>
            </div>

            <!-- Légende des types d'analyses -->
            <div class="mt-3 flex flex-wrap gap-x-4 gap-y-1.5">
                <span
                    v-for="(color, type) in typeColors"
                    :key="type"
                    class="flex items-center gap-1.5 text-xs text-slate-600"
                >
                    <span class="inline-block h-2.5 w-2.5 rounded-sm flex-shrink-0" :style="{ background: color }"></span>
                    {{ type }}
                </span>
            </div>
        </template>
    </section>
</template>

<script setup>
import { ref, onMounted, onUnmounted, nextTick } from "vue";
import { Chart, registerables } from "chart.js";
import api from "@/services/api";

// Enregistrer tous les composants Chart.js nécessaires
Chart.register(...registerables);

// Noms courts des mois en français
const MONTHS_FR = ["Jan", "Fév", "Mar", "Avr", "Mai", "Jun", "Jul", "Aoû", "Sep", "Oct", "Nov", "Déc"];

// Palette de couleurs pour les types d'analyses
const PALETTE = ["#6366f1", "#f87171", "#34d399", "#149bd7", "#fbbf24", "#a78bfa", "#f472b6", "#2dd4bf", "#fb923c", "#94a3b8"];

// Références et variables
const canvasRef  = ref(null);  // référence vers le <canvas>
const loading    = ref(true);  // true pendant le chargement
const noData     = ref(false); // true si aucune analyse
const typeColors = ref({});    // { "Biologie sanguine": "#6366f1", ... }
let chart        = null;       // instance Chart.js

// Charge les analyses et construit le graphe
async function load() {
    const { data: res } = await api.get("/dashboard/labs");
    const labs = res?.data ?? [];

    // Si aucune donnée, afficher le message vide
    if (!labs.length) {
        loading.value = false;
        noData.value  = true;
        return;
    }

    // Étape 1 : Collecter les types d'analyses uniques (sans doublons)
    const allTypes = [];
    for (const lab of labs) {
        const type = lab.analysis_type;
        if (type && !allTypes.includes(type)) {
            allTypes.push(type);
        }
    }

    // Étape 2 : Associer une couleur à chaque type
    const colors = {};
    for (let i = 0; i < allTypes.length; i++) {
        colors[allTypes[i]] = PALETTE[i % PALETTE.length];
    }
    typeColors.value = colors;

    // Étape 3 : Compter les analyses par mois et par type
    // Résultat : { "2026-03": { "Biologie": 3, "Hématologie": 1 }, "2026-04": { ... }, ... }
    const monthMap = {};
    for (const lab of labs) {
        const moisAnnee = (lab.analysis_date ?? "").slice(0, 7); // ex: "2026-03"
        if (!moisAnnee) continue;

        // Créer l'entrée du mois si elle n'existe pas encore
        if (!monthMap[moisAnnee]) {
            monthMap[moisAnnee] = {};
        }

        const type = lab.analysis_type ?? "Autre";

        // Initialiser à 0 si ce type n'a pas encore été vu ce mois
        if (!monthMap[moisAnnee][type]) {
            monthMap[moisAnnee][type] = 0;
        }

        monthMap[moisAnnee][type] += 1;
    }

    // Étape 4 : Trier les mois dans l'ordre chronologique
    const tousMoisCles = Object.keys(monthMap);
    tousMoisCles.sort(); // tri alphabétique = tri chronologique pour "YYYY-MM"

    // Convertir les clés "YYYY-MM" en étiquettes lisibles "Avr 2026"
    const labels = [];
    for (const cle of tousMoisCles) {
        const annee = cle.slice(0, 4);
        const mois  = parseInt(cle.slice(5, 7), 10) - 1; // 0 = Jan, 11 = Déc
        labels.push(MONTHS_FR[mois] + " " + annee);
    }

    // Étape 5 : Créer une série (dataset) par type d'analyse
    const datasets = [];
    for (const type of allTypes) {
        // Pour chaque mois, récupérer le nombre d'analyses de ce type (0 si aucune)
        const valeurs = [];
        for (const cle of tousMoisCles) {
            valeurs.push(monthMap[cle][type] ?? 0);
        }

        datasets.push({
            label: type,
            data: valeurs,
            backgroundColor: colors[type],
            borderRadius: 5,
            borderSkipped: false,
            borderWidth: 1.5,
            borderColor: "rgba(0,0,0,0.05)",
        });
    }

    loading.value = false;

    // Attendre que le DOM affiche le canvas avant de dessiner
    await nextTick();

    // Créer l'histogramme empilé
    chart = new Chart(canvasRef.value, {
        type: "bar",
        data: { labels, datasets },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: "index", intersect: false },
            datasets: {
                bar: { categoryPercentage: 0.4, barPercentage: 0.9 },
            },
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
                        // Afficher uniquement les types avec au moins 1 analyse
                        beforeBody(items) {
                            const lignes = [];
                            for (const item of items) {
                                if (item.parsed.y > 0) {
                                    lignes.push(item.dataset.label + " : " + item.parsed.y);
                                }
                            }
                            return lignes.join("\n");
                        },
                        label: () => null, // masquer les étiquettes individuelles
                    },
                },
            },
            scales: {
                x: {
                    stacked: true,
                    grid: { display: false },
                    ticks: { font: { size: 12, weight: "500" }, color: "#475569" },
                },
                y: {
                    stacked: true,
                    beginAtZero: true,
                    grid: { color: "#e2e8f0" },
                    ticks: { font: { size: 12, weight: "500" }, color: "#475569", stepSize: 1 },
                    title: { display: true, text: "Nb d'analyses", color: "#64748b", font: { size: 12, weight: "600" } },
                },
            },
        },
    });
}

// Charger au démarrage du composant
onMounted(load);

// Détruire le graphe quand le composant est retiré (libération mémoire)
onUnmounted(() => chart?.destroy());
</script>
