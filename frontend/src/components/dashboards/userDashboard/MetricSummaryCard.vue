<!-- Carte générique réutilisable pour afficher une métrique de santé avec badge et icône -->
<template>
    <div
        class="rounded-2xl border-2 p-4 shadow-sm transition-all duration-300 hover:shadow-lg w-full min-h-full"
        :class="[theme.border, theme.bg]"
    >
        <!-- En-tête : icône à gauche + badge d'état à droite -->
        <div class="flex items-center justify-between">
            <div class="rounded-xl p-2" :class="theme.iconBg">
                <!-- Slot pour l'icône personnalisée (fournie par le composant parent) -->
                <slot name="icon">
                    <svg class="h-4 w-4" :class="theme.iconColor" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="8" />
                    </svg>
                </slot>
            </div>
            <!-- Badge coloré affichant le statut (ex: "Normal", "Élevée") -->
            <span v-if="badge?.text" class="rounded-full px-2 py-0.5 text-xs font-semibold" :class="badgeClass">
                {{ badge.text }}
            </span>
        </div>

        <!-- Libellé de la métrique (ex: "Rythme cardiaque") -->
        <p class="mt-3 text-sm font-bold text-slate-700">{{ label }}</p>

        <!-- Valeur principale avec animation de chargement -->
        <div class="mt-1.5 min-h-[2rem]">
            <!-- Skeleton animé pendant le chargement -->
            <template v-if="loading">
                <div class="h-8 w-20 animate-pulse rounded-md bg-slate-200" />
            </template>
            <!-- Slot optionnel pour un contenu personnalisé à la place de la valeur -->
            <slot v-else name="content">
                <p v-if="value != null" class="font-bold leading-tight" :class="[theme.value, valueFontClass]">
                    {{ value }}<span v-if="unit" class="ml-0.5 text-xs font-semibold text-slate-500">{{ unit }}</span>
                </p>
                <!-- Tiret si aucune valeur disponible -->
                <p v-else class="text-2xl font-bold text-slate-200">—</p>
                <!-- Sous-texte optionnel (ex: "Diastolique : 80") -->
                <p v-if="subtext" class="mt-1 truncate text-xs text-slate-500">{{ subtext }}</p>
            </slot>
        </div>

        <!-- Date de la dernière mesure -->
        <p v-if="date && !loading" class="mt-2 text-[11px] text-slate-400">{{ date }}</p>
    </div>
</template>

<script setup>
import { computed } from "vue";

// Propriétés acceptées par la carte
const props = defineProps({
    color:   { type: String, default: "blue" },       // couleur du thème : red, purple, sky, emerald, blue, orange
    label:   { type: String, required: true },         // libellé affiché sous l'icône
    value:   { type: [String, Number], default: null },// valeur principale à afficher
    unit:    { type: String, default: "" },            // unité de la valeur (ex: "bpm", "%")
    badge:   { type: Object, default: null },          // { text, type } — type: normal | warning | danger | info | muted | intense
    subtext: { type: String, default: null },          // texte secondaire sous la valeur
    date:    { type: String, default: null },          // date de la mesure
    loading: { type: Boolean, default: false },        // true pendant le chargement
});

// Thèmes de couleur disponibles (une entrée par couleur)
const themes = {
    red:     { border: "border-red-300",     bg: "bg-white", iconBg: "bg-red-100",     iconColor: "text-red-500",     value: "text-red-600"     },
    purple:  { border: "border-purple-300",  bg: "bg-white", iconBg: "bg-purple-100",  iconColor: "text-purple-500",  value: "text-purple-600"  },
    sky:     { border: "border-sky-300",     bg: "bg-white", iconBg: "bg-sky-100",     iconColor: "text-sky-500",     value: "text-sky-600"     },
    emerald: { border: "border-emerald-300", bg: "bg-white", iconBg: "bg-emerald-100", iconColor: "text-emerald-500", value: "text-emerald-600" },
    blue:    { border: "border-blue-300",    bg: "bg-white", iconBg: "bg-blue-100",    iconColor: "text-blue-500",    value: "text-indigo-600"  },
    orange:  { border: "border-orange-300",  bg: "bg-white", iconBg: "bg-orange-100",  iconColor: "text-orange-500",  value: "text-orange-600"  },
};

// Classes CSS selon le type de badge
const badgeTypes = {
    normal:  "bg-green-100 text-green-600",
    warning: "bg-yellow-100 text-yellow-700",
    danger:  "bg-red-100 text-red-600",
    info:    "bg-blue-100 text-blue-600",
    muted:   "bg-slate-100 text-slate-500",
    intense: "bg-orange-100 text-orange-600",
};

// Appliquer le thème de couleur choisi (blue par défaut)
const theme      = computed(() => themes[props.color] ?? themes.blue);

// Appliquer le style du badge selon son type
const badgeClass = computed(() => badgeTypes[props.badge?.type ?? "muted"]);

// Réduire la taille de la police si la valeur est longue
const valueFontClass = computed(() => {
    const len = props.value != null ? String(props.value).length : 0;
    if (len > 15) return "text-base";
    if (len > 8)  return "text-xl";
    return "text-2xl";
});
</script>
