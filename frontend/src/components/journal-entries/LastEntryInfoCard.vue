<template>
    <div
        class="rounded-[28px] border border-violet-200 bg-white p-8 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
    >
        <h3 class="text-4xl font-extrabold text-slate-900">
            📋 Autres informations
        </h3>
        <p class="mt-2 text-sm text-slate-600">
            Indicateurs complémentaires associés à votre dernière entrée.
        </p>

        <div
            v-if="!entree"
            class="mt-8 rounded-xl border border-dashed border-slate-300 bg-slate-50 p-4 text-sm text-slate-500"
        >
            Aucune donnée
        </div>

        <div v-else class="mt-6 space-y-4 text-sm text-slate-700">
            <InfoRow v-for="row in lignes" :key="row.label" v-bind="row" />
        </div>
    </div>
</template>

<script setup>
import { computed } from "vue";

const props = defineProps({
    derniereEntree: { type: Object, default: null },
});

const entree = computed(() => props.derniereEntree);

// ─── Formateurs ──────────────────────────────────────────────

const MEAL_TYPES = {
    breakfast: "Petit-déjeuner",
    lunch: "Déjeuner",
    dinner: "Dîner",
    snack: "Snacks",
};

const INTENSITY = {
    high: {
        label: "Intense",
        badge: "border-rose-300 bg-rose-100 text-rose-700",
    },
    light: { label: "Légère", badge: "border-sky-300 bg-sky-100 text-sky-700" },
};
const INTENSITY_DEFAULT = {
    label: "Modérée",
    badge: "border-emerald-300 bg-emerald-100 text-emerald-700",
};

const SUGAR = {
    high: {
        label: "Élevé",
        badge: "border-rose-300 bg-rose-100 text-rose-700",
    },
    low: {
        label: "Faible",
        badge: "border-emerald-300 bg-emerald-100 text-emerald-700",
    },
};
const SUGAR_DEFAULT = {
    label: "Modéré",
    badge: "border-amber-300 bg-amber-100 text-amber-700",
};

const fmt = {
    hydration: (e) => `${e.hydration} L`,

    meals: (e) => {
        if (!e.meals.length) return "Aucun repas";
        const kinds = [
            ...new Set(e.meals.map((m) => MEAL_TYPES[m.type] ?? m.type)),
        ];
        return `${e.meals.length} repas / ${kinds.join("/")}`;
    },

    activity: (e) =>
        e.activityType
            ? `${e.activityType} ${e.activityDuration} min`
            : "Non renseignée",

    tobacco: (e) => {
        if (!e.tobacco) return "Non";
        const parts = [
            e.tobaccoTypes?.cigarette &&
                e.cigarettesPerDay != null &&
                `Cigarette • ${e.cigarettesPerDay}/j`,
            e.tobaccoTypes?.vape &&
                e.vapeFrequency &&
                `Vape • ${e.vapeFrequency} • ${e.vapeLiquidMl} ml`,
        ].filter(Boolean);
        return parts.length ? parts.join(", ") : "Oui";
    },

    alcohol: (e) => {
        if (!e.alcohol) return "Non";
        const n = Number(e.alcoholDrinks ?? 0);
        return n > 0 ? `${n} verres/jour` : "Oui";
    },
};

// ─── Lignes de la carte ───────────────────────────────────────

const lignes = computed(() => {
    const e = entree.value;
    const intensity = INTENSITY[e.intensity] ?? INTENSITY_DEFAULT;
    const sugar = SUGAR[e.sugar] ?? SUGAR_DEFAULT;

    return [
        {
            label: "Hydratation",
            icon: "M10 1.5c-.7 1.8-5 5.5-5 9a5 5 0 0 0 10 0c0-3.5-4.3-7.2-5-9Z",
            color: "text-sky-500",
            texte: fmt.hydration(e),
        },
        {
            label: "Repas",
            icon: "M3 4h14v2H3V4Zm0 4h14v8H3V8Z",
            color: "text-emerald-500",
            texte: fmt.meals(e),
        },
        {
            label: "Activité",
            icon: "M11 3h6v2h-4v4h4v2h-4v6h-2V3ZM3 11l4-8 2 1-2 4h3l-4 8-2-1 2-4H3v-2Z",
            color: "text-orange-500",
            texte: fmt.activity(e),
        },
        {
            label: "Intensité",
            icon: "m9 2 6 8h-4l2 8-6-8h4L9 2Z",
            color: "text-violet-500",
            badge: intensity.badge,
            texte: intensity.label,
        },
        {
            label: "Tabac",
            icon: "M6 2h8v6h-2V4H8v6h4v8H8v-6H6V2Z",
            color: "text-pink-500",
            texte: fmt.tobacco(e),
        },
        {
            label: "Alcool",
            icon: "M4 3h12v14H4V3Zm2 2v2h8V5H6Z",
            color: "text-orange-500",
            texte: fmt.alcohol(e),
        },
        {
            label: "Apport en sucre",
            icon: "M5 5h10v4H5V5Zm-1 6h12v6H4v-6Z",
            color: "text-violet-500",
            badge: sugar.badge,
            texte: sugar.label,
        },
    ];
});
</script>
