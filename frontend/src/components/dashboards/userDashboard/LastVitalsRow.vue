<!-- Ligne de 5 cartes : dernières valeurs vitales + poids + dernière activité physique -->
<template>
    <div>
        <!-- Carte : fréquence cardiaque -->
        <MetricSummaryCard
            color="red"
            label="Rythme cardiaque"
            :value="vitals?.heart_rate ?? null"
            unit="bpm"
            :badge="heartRateBadge"
            :date="vitalsDate"
            :loading="loading"
        >
            <template #icon>
                <svg
                    class="h-4 w-4 text-red-500"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2.5"
                    viewBox="0 0 24 24"
                >
                    <path
                        d="M22 12h-4l-3 9L9 3l-3 9H2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />
                </svg>
            </template>
        </MetricSummaryCard>

        <!-- Carte : tension artérielle (systolique / diastolique) -->
        <MetricSummaryCard
            color="purple"
            label="Tension artérielle"
            :value="tensionValeur"
            unit="mmHg"
            :badge="tensionBadge"
            :subtext="
                vitals ? `Diastolique : ${vitals.diastolic_pressure}` : null
            "
            :date="vitalsDate"
            :loading="loading"
        >
            <template #icon>
                <svg
                    class="h-4 w-4 text-purple-500"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    viewBox="0 0 24 24"
                >
                    <path
                        d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />
                </svg>
            </template>
        </MetricSummaryCard>

        <!-- Carte : saturation en oxygène -->
        <MetricSummaryCard
            color="sky"
            label="Saturation O₂"
            :value="vitals?.oxygen_saturation ?? null"
            unit="%"
            :badge="saturationBadge"
            :date="vitalsDate"
            :loading="loading"
        >
            <template #icon>
                <svg
                    class="h-4 w-4 text-sky-500"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    viewBox="0 0 24 24"
                >
                    <path
                        d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />
                </svg>
            </template>
        </MetricSummaryCard>

        <!-- Carte : poids actuel vs poids initial -->
        <WeightCard />

        <!-- Carte : dernière activité physique -->
        <LastActivityCard />
    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from "vue";
import { useDashboardStore } from "@/stores/dashboard";
import { formatLongDate } from "@/components/doctors/doctorUtilities.js";
import MetricSummaryCard from "./MetricSummaryCard.vue";
import WeightCard from "./WeightComparisonChart.vue";
import LastActivityCard from "./LastActivityCard.vue";

const dashStore = useDashboardStore();

const loading = computed(() => !dashStore.initialized);
const vitals = ref(null);
const vitalsDate = ref(null);

// Badge pour la fréquence cardiaque (bradycardie / normal / tachycardie)
const heartRateBadge = computed(() => {
    const heartRate = vitals.value?.heart_rate;
    if (heartRate == null) return null;
    if (heartRate < 60)  return { text: "Bradycardie", type: "warning" };
    if (heartRate > 100) return { text: "Tachycardie", type: "danger" };
    return { text: "Normal", type: "normal" };
});

// Valeur affichée pour la tension : "systolique/diastolique"
const tensionValeur = computed(() => {
    if (!vitals.value) return null;
    return `${vitals.value.systolic_pressure}/${vitals.value.diastolic_pressure}`;
});

// Badge pour la tension systolique
const tensionBadge = computed(() => {
    const tensionSystolique = vitals.value?.systolic_pressure;
    if (tensionSystolique == null) return null;
    if (tensionSystolique < 120) return { text: "Optimale", type: "normal" };
    if (tensionSystolique < 130) return { text: "Normale", type: "info" };
    if (tensionSystolique < 140) return { text: "Élevée", type: "warning" };
    return { text: "Haute", type: "danger" };
});

// Badge pour la saturation en oxygène
const saturationBadge = computed(() => {
    const saturationOxygene = vitals.value?.oxygen_saturation;
    if (saturationOxygene == null) return null;
    if (saturationOxygene >= 95) return { text: "Normal", type: "normal" };
    if (saturationOxygene >= 90) return { text: "Faible", type: "warning" };
    return { text: "Critique", type: "danger" };
});

function formatDate(dateStr) {
    return formatLongDate(dateStr);
}
// Récupère les dernières valeurs vitales du store, triées par date de mesure
function mettreAJourDonnees() {
    const vitalsList = dashStore.vitals30;
    if (vitalsList.length > 0) {
        // Trier les entrées par date décroissante pour trouver la plus récente
        const sortedVitals = [...vitalsList].sort(
            (a, b) => new Date(b.measured_at) - new Date(a.measured_at),
        );
        vitals.value = sortedVitals[0];
        vitalsDate.value = formatDate(sortedVitals[0].measured_at);
    }
}

onMounted(() => {
    dashStore.initialize();
    if (dashStore.initialized) mettreAJourDonnees();
});

watch(
    () => dashStore.initialized,
    (ready) => {
        if (ready) mettreAJourDonnees();
    },
);
</script>
