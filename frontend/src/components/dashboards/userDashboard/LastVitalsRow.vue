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
        <MetricSummaryCard
            color="amber"
            label="Caféine"
            :value="caffeine?.caffeine ?? null"
            unit="mg"
            :badge="null"
            :date="formatDate(caffeine?.entry_date)"
            :loading="loading"
        >
            <template #icon>
                <svg
                    class="h-4 w-4 text-amber-500"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    viewBox="0 0 24 24"
                >
                    <path
                        d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />
                </svg>
            </template>
        </MetricSummaryCard>
        <MetricSummaryCard
            color="amber"
            label="Hydratation"
            :value="hydration?.hydration ?? null"
            unit="L"
            :badge="null"
            :date="formatDate(hydration?.entry_date)"
            :loading="loading"
        >
            <template #icon>
                <svg
                    class="h-4 w-4 text-amber-500"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    viewBox="0 0 24 24"
                >
                    <path
                        d="M12 2s6 6.5 6 11a6 6 0 0 1-12 0c0-4.5 6-11 6-11z"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />
                </svg>
            </template>
        </MetricSummaryCard>

        <!-- Carte : dernier repas -->
        <MetricSummaryCard
            color="emerald"
            label="Dernier repas"
            :value="lastMeal?.calories ?? null"
            unit="kcal"
            :badge="null"
            :subtext="
                lastMeal
                    ? `${lastMeal.meal_type} — ${lastMeal.description}`
                    : null
            "
            :date="formatDate(lastMeal?.entry_date)"
            :loading="loading"
        >
            <template #icon>
                <svg
                    class="h-4 w-4 text-emerald-500"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    viewBox="0 0 24 24"
                >
                    <path
                        d="M12 2v7"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />
                    <path
                        d="M8 11v7a4 4 0 0 0 8 0v-7"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />
                </svg>
            </template>
        </MetricSummaryCard>
        <MetricSummaryCard
            color="amber"
            label="sleep"
            :value="sleep?.sleep ?? null"
            unit="H"
            :badge="null"
            :date="formatDate(sleep?.entry_date)"
            :loading="loading"
        >
        </MetricSummaryCard>
        <MetricSummaryCard
            color="amber"
            label="Traitement actif"
            :value="treatement?.type ?? null"
            :badge="null"
            subtext="Traitement en cours aujourd'hui"
            :loading="loading"
        >
        </MetricSummaryCard>
        <MetricSummaryCard
            color="amber"
            label="Dernière analyse"
            :value="lab?.analysis_type ?? null"
            :badge="null"
            :subtext="
                lab?.analysis_date
                    ? 'Analyse du ' + formatDate(lab.analysis_date)
                    : null
            "
            :loading="loading"
        >
        </MetricSummaryCard>
        <MetricSummaryCard
            color="amber"
            label="Dernière treatement checks"
            :value="treatementchek?.medication_name ?? null"
            :badge="null"
            :subtext="
                treatementchek?.check_date
                    ? 'Vérification du ' +
                      formatDate(treatementchek.check_date) +
                      (treatementchek.taken ? ' · prise' : ' · non prise')
                    : null
            "
            :loading="loading"
        >
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
const caffeine = ref(null);
const hydration = ref(null);
const lastMeal = ref(null);
const sleep = ref(null);
const treatement = ref(null);
const lab = ref(null);
const treatementchek = ref(null);
// Badge pour la fréquence cardiaque (bradycardie / normal / tachycardie)
const heartRateBadge = computed(() => {
    const heartRate = vitals.value?.heart_rate;
    if (heartRate == null) return null;
    if (heartRate < 60) return { text: "Bradycardie", type: "warning" };
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
    if (vitalsList && vitalsList.length > 0) {
        // Trier les entrées par date décroissante pour trouver la plus récente
        const sortedVitals = [...vitalsList].sort(
            (a, b) => new Date(b.measured_at) - new Date(a.measured_at),
        );
        vitals.value = sortedVitals[0];
        vitalsDate.value = formatDate(sortedVitals[0].measured_at);
    }
    const caffeineList = dashStore.caffeine;
    if (caffeineList && caffeineList.length > 0) {
        // Trier les entrées par date décroissante pour trouver la plus récente
        const sortedCaffeine = [...caffeineList].sort(
            (a, b) => new Date(b.entry_date) - new Date(a.entry_date),
        );
        caffeine.value = sortedCaffeine[0];
    }

    const hydrationEntries = dashStore.hydration;
    if (hydrationEntries && hydrationEntries.length > 0) {
        // Trier les entrées par date décroissante pour trouver la plus récente
        const sortedHydration = [...hydrationEntries].sort(
            (a, b) => new Date(b.entry_date) - new Date(a.entry_date),
        );
        hydration.value = sortedHydration[0];
    }

    const entries = dashStore.meals ?? [];
    const entriesWithMeals = [...entries]
        .filter((e) => {
            const meals = e.meals ?? [];
            return meals.length > 0;
        })
        .sort((a, b) => new Date(b.entry_date) - new Date(a.entry_date));
    if (entriesWithMeals.length > 0) {
        const entry = entriesWithMeals[0];
        const meals = entry.meals ?? [];
        lastMeal.value = meals[0]
            ? { ...meals[0], entry_date: entry.entry_date }
            : null;
    }
    const sleepentrie = dashStore.sleep;
    if (sleepentrie && sleepentrie.length > 0) {
        // Trier les entrées par date décroissante pour trouver la plus récente
        const sortedsleep = [...sleepentrie].sort(
            (a, b) => new Date(b.entry_date) - new Date(a.entry_date),
        );
        sleep.value = sortedsleep[0];
    }
    const treatmentsList = dashStore.treatments;
    treatement.value = treatmentsList?.length > 0 ? treatmentsList[0] : null;

    const labsList = dashStore.labs;
    if (labsList && labsList.length > 0) {
        // Trier les entrées par date décroissante pour trouver la plus récente
        const sortedlab = [...labsList].sort(
            (a, b) => new Date(b.analysis_date) - new Date(a.analysis_date),
        );
        lab.value = sortedlab[0];
    } else {
        lab.value = null;
    }
    const treatmentChecksList = dashStore.treatmentChecks90;
    if (treatmentChecksList && treatmentChecksList.length > 0) {
        const sortedTreatmentChecks = [...treatmentChecksList].sort(
            (a, b) => new Date(b.check_date) - new Date(a.check_date),
        );
        treatementchek.value = sortedTreatmentChecks[0];
    } else {
        treatementchek.value = null;
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
