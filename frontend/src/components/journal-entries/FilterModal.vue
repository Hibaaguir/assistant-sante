<template>
    <div
        v-if="open"
        class="fixed inset-0 z-50 bg-black/35 backdrop-blur-sm p-4"
        @click.self="$emit('close')"
    >
        <div
            class="mx-auto mt-6 w-full max-w-[540px] rounded-3xl border border-slate-200 bg-gradient-to-br from-white via-[#f9f7ff] to-[#f3f8ff] p-4 shadow-[0_25px_50px_rgba(15,23,42,0.25)] sm:mt-10 sm:p-5"
        >
            <!-- En-tête -->
            <div class="mb-4 flex items-center justify-between">
                <Typography tag="h3" variant="h3-style">
                    Filtrer l'historique
                </Typography>
                <button
                    type="button"
                    class="text-slate-500"
                    @click="$emit('close')"
                >
                    ×
                </button>
            </div>

            <!-- Options -->
            <div class="grid gap-2 sm:grid-cols-2">
                <button
                    v-for="option in OPTIONS"
                    :key="option.id"
                    type="button"
                    class="flex items-center gap-2 rounded-xl border px-3 py-3 text-left text-base font-semibold"
                    :class="model.type === option.id ? SELECTED : UNSELECTED"
                    @click="model.type = option.id"
                >
                    <RadioDot :active="model.type === option.id" />
                    <span aria-hidden="true">{{ option.icon }}</span>
                    <span>{{ option.label }}</span>
                </button>
            </div>

            <!-- Sélecteur de date / mois -->
            <DatePicker
                v-if="model.type === 'month'"
                v-model="model.month"
                type="month"
                label="Sélectionnez un mois"
            />
            <DatePicker
                v-if="model.type === 'date'"
                v-model="model.date"
                type="date"
                label="Sélectionnez une date"
            />

            <!-- Actions -->
            <div class="mt-4 flex gap-2">
                <button
                    type="button"
                    class="flex-1 rounded-xl bg-gradient-to-r from-[#149bd7] to-[#7c3aed] py-2 text-base font-semibold text-white"
                    @click="$emit('apply', model)"
                >
                    Appliquer
                </button>
                <button
                    type="button"
                    class="rounded-xl bg-slate-100 px-4 py-2 text-base font-semibold text-slate-500"
                    @click="$emit('reset')"
                >
                    Réinitialiser
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { reactive, watch } from "vue";
import Typography from "@/components/ui/Typography.vue";
import RadioDot from "@/components/ui/RadioDot.vue";
import DatePicker from "@/components/journal-entries/DatePicker.vue";

const props = defineProps({
    open: { type: Boolean, default: false },
    filter: { type: Object, required: true },
});

defineEmits(["close", "apply", "reset"]);

// --- Constantes -----------------------------------------------

const SELECTED = "border-violet-400 bg-violet-50 text-slate-900";
const UNSELECTED = "border-slate-300 bg-white text-slate-700";

const OPTIONS = [
    { id: "all", label: "Toutes les données", icon: "📊" },
    { id: "date", label: "Par date", icon: "📅" },
    { id: "month", label: "Par mois", icon: "📆" },
    { id: "nutrition", label: "Nutrition", icon: "🍽️" },
    { id: "hydration", label: "Hydratation", icon: "💧" },
    { id: "activity", label: "Activités", icon: "🏃" },
    { id: "sleep", label: "Sommeil", icon: "😴" },
    { id: "stress", label: "Stress", icon: "😰" },
    { id: "energy", label: "Énergie", icon: "⚡" },
];

// --- État local -----------------------------------------------

const model = reactive({ type: "all", month: "", date: "" });

watch(
    () => props.filter,
    (next) => Object.assign(model, next),
    { immediate: true, deep: true },
);
</script>
