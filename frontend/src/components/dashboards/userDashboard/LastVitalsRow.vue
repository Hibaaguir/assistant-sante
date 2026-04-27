<!--
  LastVitalsRow.vue
  Ligne de 4 cartes de métriques vitales :
  rythme cardiaque · tension artérielle · saturation O₂ · dernière activité physique
-->
<template>
    <div>
        <!-- Rythme cardiaque -->
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

        <!-- Tension artérielle -->
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

        <!-- Saturation en oxygène -->
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

        <!-- Dernière activité physique -->
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
                <svg
                    class="h-4 w-4 text-emerald-500"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    viewBox="0 0 24 24"
                >
                    <circle cx="12" cy="5" r="2" />
                    <path
                        d="M5 16.5l3.5-5 3.5 2.5 2.5-3 4 5"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />
                    <path
                        d="M8.5 11.5l-.5 4M15 11l.5 3.5"
                        stroke-linecap="round"
                    />
                </svg>
            </template>
        </MetricSummaryCard>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import api from "@/services/api";
import MetricSummaryCard from "./MetricSummaryCard.vue";

const loading     = ref(true);
const vitals      = ref(null);
const vitalsDate  = ref(null);
const activity    = ref(null);
const activityDate = ref(null);

// ── Badges calculés ────────────────────────────────────────────────────────────

const heartRateBadge = computed(() => {
    const hr = vitals.value?.heart_rate;
    if (hr == null) return null;
    if (hr < 60)   return { text: "Bradycardie", type: "warning" };
    if (hr > 100)  return { text: "Tachycardie", type: "danger"  };
    return { text: "Normal", type: "normal" };
});

const bpValue = computed(() => {
    if (!vitals.value) return null;
    return `${vitals.value.systolic_pressure}/${vitals.value.diastolic_pressure}`;
});

const bpBadge = computed(() => {
    const sys = vitals.value?.systolic_pressure;
    if (sys == null) return null;
    if (sys < 120)  return { text: "Optimale", type: "normal"  };
    if (sys < 130)  return { text: "Normale",  type: "info"    };
    if (sys < 140)  return { text: "Élevée",   type: "warning" };
    return { text: "Haute", type: "danger" };
});

const spo2Badge = computed(() => {
    const spo2 = vitals.value?.oxygen_saturation;
    if (spo2 == null) return null;
    if (spo2 >= 95)  return { text: "Normal",   type: "normal"  };
    if (spo2 >= 90)  return { text: "Faible",   type: "warning" };
    return { text: "Critique", type: "danger" };
});

const activityBadge = computed(() => {
    if (!activity.value) return null;
    const map = {
        low:    { text: "Légère",  type: "muted"  },
        medium: { text: "Modérée", type: "info"   },
        high:   { text: "Intense", type: "intense" },
    };
    return map[activity.value.intensity] ?? { text: activity.value.intensity, type: "muted" };
});

// ── Helpers ────────────────────────────────────────────────────────────────────

function capitalize(str) {
    if (!str) return str;
    return str.charAt(0).toUpperCase() + str.slice(1);
}

function formatDate(dateStr) {
    if (!dateStr) return null;
    return new Date(dateStr).toLocaleDateString("fr-FR", {
        day: "numeric",
        month: "short",
        year: "numeric",
    });
}

// ── Chargement des données ─────────────────────────────────────────────────────

async function load() {
    loading.value = true;
    try {
        const [vitalsRes, journalRes] = await Promise.all([
            api.get("/dashboard/vitals", { params: { days: 30 } }),
            api.get("/dashboard/journal"),
        ]);

        // Dernière mesure vitale (tri par measured_at desc)
        const vitalsList = vitalsRes.data?.data ?? [];
        if (vitalsList.length > 0) {
            const sorted = [...vitalsList].sort(
                (a, b) => new Date(b.measured_at) - new Date(a.measured_at),
            );
            vitals.value     = sorted[0];
            vitalsDate.value = formatDate(sorted[0].measured_at);
        }

        // Dernière entrée de journal avec activité physique
        const entries = journalRes.data?.data ?? journalRes.data ?? [];
        const withActivity = [...entries]
            .filter((e) => {
                const acts = e.physical_activities ?? e.physicalActivities ?? [];
                return acts.length > 0;
            })
            .sort((a, b) => new Date(b.entry_date) - new Date(a.entry_date));

        if (withActivity.length > 0) {
            const entry = withActivity[0];
            const acts  = entry.physical_activities ?? entry.physicalActivities ?? [];
            activity.value      = acts[0];
            activityDate.value  = formatDate(entry.entry_date);
        }
    } catch (e) {
        console.error("LastVitalsRow load error:", e);
    } finally {
        loading.value = false;
    }
}

onMounted(load);
</script>
