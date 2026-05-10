<template>
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
                <path d="M8.5 11.5l-.5 4M15 11l.5 3.5" stroke-linecap="round" />
            </svg>
        </template>
    </MetricSummaryCard>
</template>

<script setup>
import { ref, computed, onMounted, watch } from "vue";
import { useDashboardStore } from "@/stores/dashboard";
import { formatLongDate } from "@/components/doctors/doctorUtilities.js";
import MetricSummaryCard from "./MetricSummaryCard.vue";

const dashStore = useDashboardStore();

const loading = computed(() => !dashStore.initialized);
const activity = ref(null);
const activityDate = ref(null);

const activityBadge = computed(() => {
    if (!activity.value) return null;
    const intensityMap = {
        low:    { text: "Légère",  type: "muted"   },
        medium: { text: "Modérée", type: "info"    },
        high:   { text: "Intense", type: "intense" },
    };
    return (
        intensityMap[activity.value.intensity] ?? {
            text: activity.value.intensity,
            type: "muted",
        }
    );
});
// Met en majuscule la première lettre d'une chaîne
function capitalize(text) {
    if (!text) return text;
    return text.charAt(0).toUpperCase() + text.slice(1);
}

function loadData() {
    const entries = dashStore.activities;
    const entriesWithActivity = [...entries]
        .filter((e) => {
            const activities = e.physical_activities ?? [];
            return activities.length > 0;
        })
        .sort((a, b) => new Date(b.entry_date) - new Date(a.entry_date));
    if (entriesWithActivity.length > 0) {
        const entry      = entriesWithActivity[0];
        const activities = entry.physical_activities ?? [];
        activity.value     = activities[0];
        activityDate.value = formatLongDate(entry.entry_date);
    }
}

onMounted(() => {
    dashStore.initialize();
    if (dashStore.initialized) loadData();
});

watch(
    () => dashStore.initialized,
    (ready) => {
        if (ready) loadData();
    },
);
</script>
