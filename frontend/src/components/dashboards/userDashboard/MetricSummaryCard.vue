<!--
  MetricSummaryCard.vue
  Carte générique de métrique de santé (rythme cardiaque, tension, O₂, activité…).
  Usage : fournir le slot #icon + les props label/value/unit/badge/date.
-->
<template>
    <div
        class="rounded-2xl border-2 p-4 shadow-sm transition-all duration-300 hover:shadow-lg w-full min-h-full"
        :class="[theme.border, theme.bg]"
    >
        <!-- En-tête : icône + badge état -->
        <div class="flex items-center justify-between">
            <div class="rounded-xl p-2" :class="theme.iconBg">
                <slot name="icon">
                    <svg
                        class="h-4 w-4"
                        :class="theme.iconColor"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        viewBox="0 0 24 24"
                    >
                        <circle cx="12" cy="12" r="8" />
                    </svg>
                </slot>
            </div>
            <span
                v-if="badge?.text"
                class="rounded-full px-2 py-0.5 text-xs font-semibold"
                :class="badgeClass"
            >
                {{ badge.text }}
            </span>
        </div>

        <!-- Libellé -->
        <p class="mt-3 text-sm font-bold text-slate-700">{{ label }}</p>

        <!-- Valeur principale ou contenu custom via slot #content -->
        <div class="mt-1.5 min-h-[2rem]">
            <template v-if="loading">
                <div class="h-8 w-20 animate-pulse rounded-md bg-slate-200" />
            </template>
            <slot v-else name="content">
                <p
                    v-if="value != null"
                    class="font-bold leading-tight"
                    :class="[theme.value, valueFontClass]"
                >
                    {{ value
                    }}<span
                        v-if="unit"
                        class="ml-0.5 text-xs font-semibold text-slate-500"
                        >{{ unit }}</span
                    >
                </p>
                <p v-else class="text-2xl font-bold text-slate-200">—</p>
                <p v-if="subtext" class="mt-1 truncate text-xs text-slate-500">
                    {{ subtext }}
                </p>
            </slot>
        </div>

        <!-- Date de mesure -->
        <p v-if="date && !loading" class="mt-2 text-[11px] text-slate-400">
            {{ date }}
        </p>
    </div>
</template>

<script setup>
import { computed } from "vue";

const props = defineProps({
    /** Thème couleur : 'red' | 'purple' | 'sky' | 'emerald' | 'blue' | 'orange' */
    color:   { type: String, default: "blue" },
    label:   { type: String, required: true },
    value:   { type: [String, Number], default: null },
    unit:    { type: String, default: "" },
    /** { text: string, type: 'normal'|'warning'|'danger'|'info'|'muted'|'intense' } */
    badge:   { type: Object, default: null },
    subtext: { type: String, default: null },
    date:    { type: String, default: null },
    loading: { type: Boolean, default: false },
});

// ── Thèmes couleurs ────────────────────────────────────────────────────────────
const themes = {
    red:     { border: "border-red-200",     bg: "bg-red-50",     iconBg: "bg-red-100",     iconColor: "text-red-500",     value: "text-red-600"     },
    purple:  { border: "border-purple-200",  bg: "bg-purple-50",  iconBg: "bg-purple-100",  iconColor: "text-purple-500",  value: "text-purple-600"  },
    sky:     { border: "border-sky-200",     bg: "bg-sky-50",     iconBg: "bg-sky-100",     iconColor: "text-sky-500",     value: "text-sky-600"     },
    emerald: { border: "border-emerald-200", bg: "bg-emerald-50", iconBg: "bg-emerald-100", iconColor: "text-emerald-500", value: "text-emerald-600" },
    blue:    { border: "border-blue-300",    bg: "bg-blue-50",    iconBg: "bg-blue-100",    iconColor: "text-blue-500",    value: "text-indigo-600"  },
    orange:  { border: "border-orange-200",  bg: "bg-orange-50",  iconBg: "bg-orange-100",  iconColor: "text-orange-500",  value: "text-orange-600"  },
};

const badgeTypes = {
    normal:  "bg-green-100 text-green-600",
    warning: "bg-yellow-100 text-yellow-700",
    danger:  "bg-red-100 text-red-600",
    info:    "bg-blue-100 text-blue-600",
    muted:   "bg-slate-100 text-slate-500",
    intense: "bg-orange-100 text-orange-600",
};

const theme      = computed(() => themes[props.color] ?? themes.blue);
const badgeClass = computed(() => badgeTypes[props.badge?.type ?? "muted"]);

// Taille de fonte adaptée à la longueur de la valeur
const valueFontClass = computed(() => {
    const len = props.value != null ? String(props.value).length : 0;
    if (len > 15) return "text-base";
    if (len > 8)  return "text-xl";
    return "text-2xl";
});
</script>
