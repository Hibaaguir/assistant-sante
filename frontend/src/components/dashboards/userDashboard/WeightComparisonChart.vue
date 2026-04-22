<!--
  WeightComparisonChart.vue
  Carte de poids actuel vs initial — utilise MetricSummaryCard.
-->
<template>
    <div class="w-full">
        <MetricSummaryCard
            color="sky"
            label="Poids"
            :value="currentWeight"
            unit="kg"
            :badge="badge"
            :subtext="subtextLabel"
            :loading="loading"
        >
            <template #icon>
                <svg
                    class="h-4 w-4"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    viewBox="0 0 24 24"
                >
                    <path d="M7 7a5 5 0 0 1 10 0" />
                    <path d="M4 9h16l2 11H2L4 9z" />
                </svg>
            </template>
        </MetricSummaryCard>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import MetricSummaryCard from "./MetricSummaryCard.vue";
import api from "@/services/api";

const initialWeight = ref(null);
const currentWeight = ref(null);
const loading = ref(false);

const diff = computed(() => {
    if (initialWeight.value === null || currentWeight.value === null) return 0;
    return +(currentWeight.value - initialWeight.value).toFixed(1);
});

const badge = computed(() => {
    if (diff.value === 0) {
        return { text: "Stable", type: "muted" };
    }
    if (diff.value < 0) {
        return { text: `${diff.value} kg`, type: "normal" };
    }
    return { text: `+${diff.value} kg`, type: "warning" };
});

const subtextLabel = computed(() => {
    if (initialWeight.value === null) return null;
    return `Initial: ${initialWeight.value} kg`;
});

async function load() {
    loading.value = true;
    try {
        const { data: res } = await api.get("/health-profile");
        const profile = res?.data;

        if (profile?.initial_weight == null || profile?.current_weight == null)
            return;

        initialWeight.value = +parseFloat(profile.initial_weight).toFixed(1);
        currentWeight.value = +parseFloat(profile.current_weight).toFixed(1);
    } catch (error) {
        console.error("Weight comparison load failed:", error);
    } finally {
        loading.value = false;
    }
}

onMounted(load);
</script>
