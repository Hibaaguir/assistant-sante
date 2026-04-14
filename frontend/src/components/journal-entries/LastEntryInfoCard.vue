<template>
    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-xs">
        <!-- Header -->
        <h3 class="text-base font-bold text-slate-900 mb-1">📋 Autres informations</h3>
        <p class="text-sm text-slate-500 mb-3 leading-tight">Indicateurs complémentaires associés à votre dernière entrée.</p>

        <!-- Contenu -->
        <div v-if="!entree" class="flex items-center justify-center rounded-lg border border-dashed border-slate-300 bg-slate-50 px-4 py-6 text-sm text-slate-500">
            Aucune donnée
        </div>

        <div v-else class="space-y-2">
            <!-- Hydratation -->
            <div class="flex items-center justify-between py-0.5">
                <div class="flex items-center gap-2.5">
                    <span class="text-base">💧</span>
                    <span class="text-sm text-slate-600">Hydratation</span>
                </div>
                <span class="text-sm font-semibold text-slate-900">{{ entree.hydration }} L</span>
            </div>

            <!-- Repas -->
            <div class="flex items-center justify-between py-0.5">
                <div class="flex items-center gap-2.5">
                    <span class="text-base">🍽️</span>
                    <span class="text-sm text-slate-600">Repas</span>
                </div>
                <span class="text-sm font-semibold text-slate-900">{{ formatMeals(entree) }}</span>
            </div>

            <!-- Activité -->
            <div class="flex items-center justify-between py-0.5">
                <div class="flex items-center gap-2.5">
                    <span class="text-base">🏃</span>
                    <span class="text-sm text-slate-600">Activité</span>
                </div>
                <span class="text-sm font-semibold text-slate-900">{{ formatActivity(entree) }}</span>
            </div>

            <!-- Intensité -->
            <div class="flex items-center justify-between py-0.5">
                <div class="flex items-center gap-2.5">
                    <span class="text-base">💪</span>
                    <span class="text-sm text-slate-600">Intensité</span>
                </div>
                <span 
                    class="text-sm font-semibold px-2 py-0.5 rounded-full border"
                    :class="intensityBadgeClass(entree.intensity)"
                >
                    {{ formatIntensity(entree.intensity) }}
                </span>
            </div>

            <!-- Tabac -->
            <div class="flex items-center justify-between py-0.5">
                <div class="flex items-center gap-2.5">
                    <span class="text-base">🚬</span>
                    <span class="text-sm text-slate-600">Tabac</span>
                </div>
                <span class="text-sm font-semibold text-slate-900">{{ formatTobacco(entree) }}</span>
            </div>

            <!-- Alcool -->
            <div class="flex items-center justify-between py-0.5">
                <div class="flex items-center gap-2.5">
                    <span class="text-base">🍷</span>
                    <span class="text-sm text-slate-600">Alcool</span>
                </div>
                <span class="text-sm font-semibold text-slate-900">{{ formatAlcohol(entree) }}</span>
            </div>

            <!-- Apport en sucre -->
            <div class="flex items-center justify-between py-0.5">
                <div class="flex items-center gap-2.5">
                    <span class="text-base">🍬</span>
                    <span class="text-sm text-slate-600">Apport en sucre</span>
                </div>
                <span 
                    class="text-sm font-semibold px-2 py-0.5 rounded-full border"
                    :class="sugarBadgeClass(entree.sugar)"
                >
                    {{ formatSugar(entree.sugar) }}
                </span>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from "vue";

const props = defineProps({
    derniereEntree: { type: Object, default: null },
});

const entree = computed(() => props.derniereEntree);

// Formatters
const formatMeals = (e) => {
    if (!e.meals || e.meals.length === 0) return "Aucun repas";
    return `${e.meals.length} repas`;
};

const formatActivity = (e) => {
    if (!e.activityType) return "Non renseignée";
    return `${e.activityType} ${e.activityDuration} min`;
};

const formatIntensity = (intensity) => {
    const map = { high: "Intense", medium: "Modérée", light: "Légère" };
    return map[intensity] || "Modérée";
};

const intensityBadgeClass = (intensity) => {
    const map = {
        high: "bg-rose-100 text-rose-700 border-rose-300",
        medium: "bg-emerald-100 text-emerald-700 border-emerald-300",
        light: "bg-sky-100 text-sky-700 border-sky-300",
    };
    return map[intensity] || map.medium;
};

const formatTobacco = (e) => {
    if (!e.tobacco) return "Non";
    const parts = [];
    if (e.tobaccoTypes?.cigarette && e.cigarettesPerDay != null) {
        parts.push(`Cigarette • ${e.cigarettesPerDay}/j`);
    }
    if (e.tobaccoTypes?.vape && e.vapeFrequency) {
        parts.push(`Vape • ${e.vapeFrequency}`);
    }
    return parts.length ? parts.join(" + ") : "Oui";
};

const formatAlcohol = (e) => {
    if (!e.alcohol) return "Non";
    const n = Number(e.alcoholDrinks ?? 0);
    return n > 0 ? `${n} verres/jour` : "Oui";
};

const formatSugar = (sugar) => {
    const map = { high: "Élevé", medium: "Modéré", low: "Faible" };
    return map[sugar] || "Modéré";
};

const sugarBadgeClass = (sugar) => {
    const map = {
        high: "bg-rose-100 text-rose-700 border-rose-300",
        medium: "bg-amber-100 text-amber-700 border-amber-300",
        low: "bg-emerald-100 text-emerald-700 border-emerald-300",
    };
    return map[sugar] || map.medium;
};
</script>
