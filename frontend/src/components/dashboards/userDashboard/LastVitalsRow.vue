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
                <svg class="h-4 w-4 text-red-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path d="M22 12h-4l-3 9L9 3l-3 9H2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </template>
        </MetricSummaryCard>

        <!-- Carte : tension artérielle (systolique / diastolique) -->
        <MetricSummaryCard
            color="purple"
            label="Tension artérielle"
            :value="bpValue"
            unit="mmHg"
            :badge="bpBadge"
            :subtext="vitals ? `Diastolique : ${vitals.diastolic_pressure}` : null"
            :date="vitalsDate"
            :loading="loading"
        >
            <template #icon>
                <svg class="h-4 w-4 text-purple-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </template>
        </MetricSummaryCard>

        <!-- Carte : saturation en oxygène -->
        <MetricSummaryCard
            color="sky"
            label="Saturation O₂"
            :value="vitals?.oxygen_saturation ?? null"
            unit="%"
            :badge="spo2Badge"
            :date="vitalsDate"
            :loading="loading"
        >
            <template #icon>
                <svg class="h-4 w-4 text-sky-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </template>
        </MetricSummaryCard>

        <!-- Carte : poids actuel vs poids initial -->
        <WeightCard />

        <!-- Carte : dernière activité physique -->
        <MetricSummaryCard
            color="emerald"
            label="Dernière activité"
            :value="activity ? capitalize(activity.activity_type) : null"
            :badge="activityBadge"
            :subtext="activity ? `${activity.duration_minutes} min` : null"
            :date="activityDate"
            :loading="loading"
        >
            <template #icon>
                <svg class="h-4 w-4 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="12" cy="5" r="2" />
                    <path d="M5 16.5l3.5-5 3.5 2.5 2.5-3 4 5" stroke-linecap="round" stroke-linejoin="round" />
                    <path d="M8.5 11.5l-.5 4M15 11l.5 3.5" stroke-linecap="round" />
                </svg>
            </template>
        </MetricSummaryCard>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import api from "@/services/api";
import { formatLongDate } from "@/components/doctors/doctorUtilities.js";
import MetricSummaryCard from "./MetricSummaryCard.vue";
import WeightCard from "./WeightComparisonChart.vue";

// Données chargées depuis l'API
const loading      = ref(true);   // true pendant le chargement
const vitals       = ref(null);   // dernière mesure vitale
const vitalsDate   = ref(null);   // date formatée de la dernière mesure
const activity     = ref(null);   // dernière activité physique
const activityDate = ref(null);   // date formatée de la dernière activité

// Badge pour la fréquence cardiaque (bradycardie / normal / tachycardie)
const heartRateBadge = computed(() => {
    const hr = vitals.value?.heart_rate;
    if (hr == null) return null;
    if (hr < 60)   return { text: "Bradycardie", type: "warning" };
    if (hr > 100)  return { text: "Tachycardie", type: "danger"  };
    return { text: "Normal", type: "normal" };
});

// Valeur affichée pour la tension : "systolique/diastolique"
const bpValue = computed(() => {
    if (!vitals.value) return null;
    return `${vitals.value.systolic_pressure}/${vitals.value.diastolic_pressure}`;
});

// Badge pour la tension systolique
const bpBadge = computed(() => {
    const sys = vitals.value?.systolic_pressure;
    if (sys == null) return null;
    if (sys < 120)   return { text: "Optimale", type: "normal"  };
    if (sys < 130)   return { text: "Normale",  type: "info"    };
    if (sys < 140)   return { text: "Élevée",   type: "warning" };
    return { text: "Haute", type: "danger" };
});

// Badge pour la saturation en oxygène
const spo2Badge = computed(() => {
    const spo2 = vitals.value?.oxygen_saturation;
    if (spo2 == null) return null;
    if (spo2 >= 95)   return { text: "Normal",   type: "normal"  };
    if (spo2 >= 90)   return { text: "Faible",   type: "warning" };
    return { text: "Critique", type: "danger" };
});

// Badge pour l'intensité de la dernière activité physique
const activityBadge = computed(() => {
    if (!activity.value) return null;
    const map = {
        low:    { text: "Légère",  type: "muted"   },
        medium: { text: "Modérée", type: "info"    },
        high:   { text: "Intense", type: "intense" },
    };
    return map[activity.value.intensity] ?? { text: activity.value.intensity, type: "muted" };
});

// Met la première lettre en majuscule
function capitalize(str) {
    if (!str) return str;
    return str.charAt(0).toUpperCase() + str.slice(1);
}

function formatDate(dateStr) {
    return formatLongDate(dateStr);
}

// Charge les signes vitaux et les activités depuis l'API
async function load() {
    loading.value = true;
    try {
        // Lancer les deux requêtes en parallèle pour gagner du temps
        const [vitalsRes, journalRes] = await Promise.all([
            api.get("/dashboard/vitals", { params: { days: 30 } }),
            api.get("/dashboard/journal"),
        ]);

        // Trouver la mesure vitale la plus récente
        const vitalsList = vitalsRes.data?.data ?? [];
        if (vitalsList.length > 0) {
            const sorted     = [...vitalsList].sort((a, b) => new Date(b.measured_at) - new Date(a.measured_at));
            vitals.value     = sorted[0];
            vitalsDate.value = formatDate(sorted[0].measured_at);
        }

        // Trouver la dernière entrée du journal contenant une activité physique
        const entries = journalRes.data?.data ?? journalRes.data ?? [];
        const withActivity = [...entries]
            .filter((e) => {
                const acts = e.physical_activities ?? e.physicalActivities ?? [];
                return acts.length > 0;
            })
            .sort((a, b) => new Date(b.entry_date) - new Date(a.entry_date));

        if (withActivity.length > 0) {
            const entry        = withActivity[0];
            const acts         = entry.physical_activities ?? entry.physicalActivities ?? [];
            activity.value     = acts[0];
            activityDate.value = formatDate(entry.entry_date);
        }
    } catch (e) {
        console.error("Erreur chargement LastVitalsRow :", e);
    } finally {
        loading.value = false;
    }
}

// Charger au démarrage du composant
onMounted(load);
</script>
