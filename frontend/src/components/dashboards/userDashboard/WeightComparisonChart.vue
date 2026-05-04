<!-- Carte métrique : poids actuel avec comparaison au poids initial -->
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
            <!-- Icône de balance -->
            <template #icon>
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M7 7a5 5 0 0 1 10 0" />
                    <path d="M4 9h16l2 11H2L4 9z" />
                </svg>
            </template>
        </MetricSummaryCard>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from "vue";
import MetricSummaryCard from "./MetricSummaryCard.vue";
import { useDashboardStore } from "@/stores/dashboard";

const dashStore = useDashboardStore();

const initialWeight = ref(null);
const currentWeight = ref(null);
const loading       = computed(() => !dashStore.initialized);

// Calcule l'écart entre le poids actuel et le poids initial
const diff = computed(() => {
    if (initialWeight.value === null || currentWeight.value === null) return 0;
    return +(currentWeight.value - initialWeight.value).toFixed(1);
});

// Badge de statut : stable, perte de poids ou prise de poids
const badge = computed(() => {
    if (diff.value === 0)  return { text: "Stable",         type: "muted"   };
    if (diff.value < 0)    return { text: `${diff.value} kg`, type: "normal" };
    return                        { text: `+${diff.value} kg`, type: "warning" };
});

// Sous-texte affichant le poids initial
const subtextLabel = computed(() => {
    if (initialWeight.value === null) return null;
    return `Initial: ${initialWeight.value} kg`;
});

function load() {
    const profile = dashStore.weight;
    if (profile?.initial_weight == null || profile?.current_weight == null) return;
    initialWeight.value = +parseFloat(profile.initial_weight).toFixed(1);
    currentWeight.value = +parseFloat(profile.current_weight).toFixed(1);
}

onMounted(() => {
    dashStore.initialize();
    if (dashStore.initialized) load();
});
watch(() => dashStore.initialized, (ready) => { if (ready) load(); });
</script>
