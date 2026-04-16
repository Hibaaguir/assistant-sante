<!--
  WeightComparisonChart.vue
  Carte compacte : poids initial vs poids actuel
-->
<template>
    <div
        class="mt-5 inline-block rounded-2xl border-2 border-blue-300 bg-blue-50 p-4 shadow-sm transition-all duration-300 hover:shadow-lg hover:border-blue-400"
        style="width: 280px"
    >
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div class="rounded-xl bg-blue-100 p-2">
                <svg
                    class="h-4 w-4 text-blue-500"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    viewBox="0 0 24 24"
                >
                    <path d="M7 7a5 5 0 0 1 10 0" />
                    <path d="M4 9h16l2 11H2L4 9z" />
                </svg>
            </div>
            <span
                class="rounded-full px-2 py-0.5 text-xs font-semibold"
                :class="diffClass"
            >
                {{ diffLabel }}
            </span>
        </div>

        <p class="mt-3 text-s font-bold text-black-700">Évolution du poids</p>

        <!-- Valeurs -->
        <div class="mt-2 flex items-end justify-between">
            <div>
                <Typography tag="h3" variant="h2-style">Initial </Typography>
                <p class="text-xll font-bold text-indigo-600">
                    {{ initialWeight ?? "—" }}
                    <span class="text-xs font-bold text-slate-700">kg</span>
                </p>
            </div>
            <div class="text-right">
                <Typography tag="h3" variant="h2-style">Actuel</Typography>
                <p class="text-xll font-bold text-indigo-600">
                    {{ currentWeight ?? "—" }}
                    <span class="text-xs font-bold text-slate-700">kg</span>
                </p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import Typography from "@/components/ui/Typography.vue";
import api from "@/services/api";

const initialWeight = ref(null);
const currentWeight = ref(null);

const diff = computed(() => {
    if (initialWeight.value === null || currentWeight.value === null) return 0;
    return +(currentWeight.value - initialWeight.value).toFixed(1);
});

const diffLabel = computed(() => {
    if (diff.value === 0) return "Stable";
    return diff.value > 0 ? `+${diff.value} kg` : `${diff.value} kg`;
});

const diffClass = computed(() => {
    if (diff.value < 0) return "bg-green-100 text-green-600";
    if (diff.value > 0) return "bg-red-100 text-red-500";
    return "bg-blue-100 text-blue-500";
});

async function load() {
    try {
        const { data: res } = await api.get("/health-profile");
        const profile = res?.data;

        if (profile?.initial_weight == null || profile?.current_weight == null)
            return;

        initialWeight.value = +parseFloat(profile.initial_weight).toFixed(1);
        currentWeight.value = +parseFloat(profile.current_weight).toFixed(1);
    } catch (error) {
        console.error("Weight comparison load failed:", error);
    }
}

onMounted(load);
</script>
