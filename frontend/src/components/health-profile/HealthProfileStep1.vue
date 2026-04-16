<template>
    <div class="space-y-10">
        <!-- Step title -->
        <div class="mx-auto max-w-2xl text-center">
            <Typography tag="h2" variant="h1-style">
                Informations de base
            </Typography>
            <Typography tag="p" variant="paragraph">
                Commençons par quelques informations essentielles pour
                personnaliser ton expérience
            </Typography>
        </div>

        <div class="space-y-8">
            <!-- ── Genre ─────────────────────────────────── -->
            <div class="space-y-4">
                <p class="text-base font-medium text-gray-900">
                    Genre <span class="ml-0.5 text-red-600">*</span>
                </p>

                <!-- Two gender cards side by side -->
                <div class="grid max-w-xl grid-cols-2 gap-4">
                    <GenderCard
                        v-for="gender in GENDERS"
                        :key="gender.value"
                        v-bind="gender"
                        :selected="form.sexe === gender.value"
                        :has-error="props.showErrors && !!errors.sexe"
                        @select="form.sexe = gender.value"
                    />
                </div>

                <!-- Error message shown after the user tries to advance -->
                <p
                    v-if="props.showErrors && errors.sexe"
                    class="text-sm text-red-600"
                >
                    {{ errors.sexe }}
                </p>
            </div>

            <!-- ── Âge (read-only, calculated from date of birth) ─────── -->
            <div class="max-w-md space-y-2">
                <p class="text-base font-medium text-gray-900">Âge</p>
                <div
                    class="flex h-14 items-center rounded-xl border-2 border-gray-200 bg-gray-50 px-5 text-lg text-gray-900"
                >
                    {{
                        computedAge !== ""
                            ? `${computedAge} ans`
                            : "Âge indisponible"
                    }}
                </div>
                <p class="px-1 text-xs text-gray-500">
                    L'âge est calculé automatiquement depuis la date de
                    naissance
                </p>
            </div>

            <!-- ── Taille / Poids ─────────────────────────────────────── -->
            <div class="space-y-4">
                <p class="text-base font-medium text-gray-900">
                    Taille et poids <span class="ml-0.5 text-red-600">*</span>
                </p>

                <div class="grid max-w-2xl grid-cols-1 gap-4 sm:grid-cols-2">
                    <div
                        v-for="field in MEASURE_FIELDS"
                        :key="field.key"
                        class="space-y-2"
                    >
                        <input
                            v-model="form[field.key]"
                            type="number"
                            :min="field.min"
                            :max="field.max"
                            :placeholder="field.placeholder"
                            class="h-14 w-full rounded-xl border-2 px-4 text-lg outline-none focus:border-purple-400"
                            :class="
                                props.showErrors && errors[field.key]
                                    ? 'border-red-300'
                                    : 'border-gray-200'
                            "
                        />

                        <!-- Inline error for height or weight -->
                        <p
                            v-if="props.showErrors && errors[field.key]"
                            class="text-sm text-red-600"
                        >
                            {{ errors[field.key] }}
                        </p>

                        <p class="px-1 text-xs text-gray-500">
                            {{ field.hint }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- ── Habitudes de vie ───────────────────────────────────── -->
            <div class="space-y-4">
                <p class="text-base font-medium text-gray-900">
                    Habitudes de vie
                </p>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <!-- ToggleCard supports v-model to set true/false -->
                    <ToggleCard
                        v-for="habit in HABITS"
                        :key="habit.key"
                        v-model="form[habit.key]"
                        :label="habit.label"
                    />
                </div>
            </div>

            <!-- ── Objectifs ──────────────────────────────────────────── -->
            <div class="space-y-4">
                <p class="text-base font-medium text-gray-900">
                    Objectifs <span class="ml-0.5 text-red-600">*</span>
                </p>
                <p class="text-xs text-gray-500">
                    Vous pouvez sélectionner plusieurs objectifs.
                </p>

                <div
                    class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3"
                >
                    <!-- GoalCard emits "click" to toggle the goal in the list -->
                    <GoalCard
                        v-for="goal in GOALS"
                        :key="goal.value"
                        v-bind="goal"
                        :selected="form.objectifs.includes(goal.value)"
                        @click="toggleGoal(goal.value)"
                    />
                </div>

                <!-- Error shown if no goal was selected -->
                <p
                    v-if="props.showErrors && errors.objectifs"
                    class="text-sm text-red-600"
                >
                    {{ errors.objectifs }}
                </p>
            </div>
        </div>
    </div>
</template>

<script setup>
// Import the extracted sub-components (no more render functions!)
import GenderCard from "./GenderCard.vue";
import ToggleCard from "./ToggleCard.vue";
import GoalCard from "./GoalCard.vue";
import Typography from "@/components/ui/Typography.vue";


// ─── Static data ───────────────────────────────────────────────────────────────

// Gender options — each has a value, a label, and SVG icon paths
const GENDERS = [
    {
        value: "male",
        label: "Homme",
        iconPaths: [
            "M14 10l6-6m0 0h-4m4 0v4",
            "M10 19a7 7 0 1 0 0-14 7 7 0 0 0 0 14z",
        ],
    },
    {
        value: "female",
        label: "Femme",
        iconPaths: ["M12 13v7m-3-3h6", "M12 13a5 5 0 1 0 0-10 5 5 0 0 0 0 10z"],
    },
];

// Life habits to show as toggle switches
const HABITS = [
    { key: "fumeur", label: "Fumeur" },
    { key: "alcool", label: "Consommation d'alcool" },
];

// Health goals the user can choose (multiple allowed)
const GOALS = [
    {
        value: "Maintenir mon poids",
        label: "Bien-être général",
        color: "bg-purple-50 text-purple-600 border-purple-200",
        icon: "M5 13a7 7 0 0 0 14 0M8 8h.01M16 8h.01",
    },
    {
        value: "Perdre du poids",
        label: "Suivi et évolution du poids",
        color: "bg-purple-50 text-purple-600 border-purple-200",
        icon: "M4 7h16M7 12h10M10 17h4",
    },
    {
        value: "Avoir plus d'energie",
        label: "Augmenter l'énergie",
        color: "bg-orange-50 text-orange-600 border-orange-200",
        icon: "M13 2L3 14h7l-1 8 10-12h-7z",
    },
    {
        value: "Mieux dormir",
        label: "Améliorer le sommeil",
        color: "bg-indigo-50 text-indigo-600 border-indigo-200",
        icon: "M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8z",
    },
    {
        value: "Reduire mon stress",
        label: "Réduire le stress",
        color: "bg-purple-50 text-purple-500 border-purple-200",
        icon: "M12 2a7 7 0 0 0-7 7c0 2.4 1.4 4.4 3.5 5.6V17a1 1 0 0 0 1 1h5a1 1 0 0 0 1-1v-2.4A6.5 6.5 0 0 0 19 9a7 7 0 0 0-7-7z",
    },
    {
        value: "Suivre ma sante regulierement",
        label: "Suivi de l'état de santé",
        color: "bg-red-50 text-red-600 border-red-200",
        icon: "M9 3h6v4H9zM5 7h14v14H5zM9 12h6M9 16h4",
    },
];

// Height and weight input configuration (min, max, placeholder, hint)
const MEASURE_FIELDS = [
    {
        key: "taille",
        min: 80,
        max: 250,
        placeholder: "Taille en cm",
        hint: "Ex: 175",
    },
    {
        key: "poids",
        min: 35,
        max: 250,
        placeholder: "Poids en kg",
        hint: "Ex: 70",
    },
];

// ─── Props ─────────────────────────────────────────────────────────────────────
// Note: we keep a reference to `props` so we can use props.showErrors in template
const props = defineProps({
    form: { type: Object, required: true },
    computedAge: { type: [Number, String], default: "" },
    errors: {
        type: Object,
        default: () => ({ sexe: "", taille: "", poids: "", objectifs: "" }),
    },
    showErrors: { type: Boolean, default: false },
});

// Objects keep reactivity when destructured (they are passed by reference)
const { form, errors } = props;

// Make sure objectifs is always an array before rendering
if (!Array.isArray(form.objectifs)) form.objectifs = [];

// ─── Functions ─────────────────────────────────────────────────────────────────

// Add or remove a goal from the selected list when the user clicks a GoalCard
function toggleGoal(value) {
    if (form.objectifs.includes(value)) {
        // Already selected → remove it
        form.objectifs = form.objectifs.filter((v) => v !== value);
    } else {
        // Not yet selected → add it
        form.objectifs = [...form.objectifs, value];
    }
}
</script>
