<template>
    <div class="space-y-10">
        <!-- Titre -->
        <div class="mx-auto max-w-2xl text-center">
            <h2 class="mb-4 text-4xl font-extrabold text-slate-900">
                Informations de base
            </h2>
            <p class="text-gray-500">
                Commençons par quelques informations essentielles pour
                personnaliser ton expérience
            </p>
        </div>

        <div class="space-y-8">
            <!-- Genre -->
            <div class="space-y-4">
                <FieldLabel required>Genre</FieldLabel>
                <div class="grid max-w-xl grid-cols-2 gap-4">
                    <GenderCard
                        v-for="gender in GENDERS"
                        :key="gender.value"
                        v-bind="gender"
                        :selected="form.sexe === gender.value"
                        :has-error="showErrors && !!errors.sexe"
                        @select="form.sexe = gender.value"
                    />
                </div>
                <FieldError
                    v-if="showErrors && errors.sexe"
                    :message="errors.sexe"
                />
            </div>

            <!-- Âge calculé -->
            <div class="max-w-md space-y-2">
                <FieldLabel>Âge</FieldLabel>
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

            <!-- Taille / Poids -->
            <div class="space-y-4">
                <FieldLabel required>Taille et poids</FieldLabel>
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
                                showErrors && errors[field.key]
                                    ? 'border-red-300'
                                    : 'border-gray-200'
                            "
                        />
                        <FieldError
                            v-if="showErrors && errors[field.key]"
                            :message="errors[field.key]"
                        />
                        <p class="px-1 text-xs text-gray-500">
                            {{ field.hint }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Habitudes de vie -->
            <div class="space-y-4">
                <FieldLabel>Habitudes de vie</FieldLabel>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <ToggleCard
                        v-for="habit in HABITS"
                        :key="habit.key"
                        v-model="form[habit.key]"
                        :label="habit.label"
                    />
                </div>
            </div>

            <!-- Objectifs -->
            <div class="space-y-4">
                <FieldLabel required>Objectifs</FieldLabel>
                <p class="text-xs text-gray-500">
                    Vous pouvez sélectionner plusieurs objectifs.
                </p>
                <div
                    class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3"
                >
                    <GoalCard
                        v-for="goal in GOALS"
                        :key="goal.value"
                        v-bind="goal"
                        :selected="form.objectifs.includes(goal.value)"
                        @click="toggleGoal(goal.value)"
                    />
                </div>
                <FieldError
                    v-if="showErrors && errors.objectifs"
                    :message="errors.objectifs"
                />
            </div>
        </div>
    </div>
</template>

<script setup>
import { defineComponent, h, toRef } from "vue";

// ─── Mini-composants locaux ───────────────────────────────────────────────────

const FieldLabel = defineComponent({
    props: { required: Boolean },
    setup:
        (p, { slots }) =>
        () =>
            h("p", { class: "text-base font-medium text-gray-900" }, [
                slots.default?.(),
                p.required
                    ? h("span", { class: "ml-0.5 text-red-600" }, " *")
                    : null,
            ]),
});

const FieldError = defineComponent({
    props: { message: String },
    setup: (p) => () => h("p", { class: "text-sm text-red-600" }, p.message),
});

const GenderCard = defineComponent({
    props: {
        value: String,
        label: String,
        iconPaths: Array,
        selected: Boolean,
        hasError: Boolean,
    },
    emits: ["select"],
    setup:
        (p, { emit }) =>
        () => {
            const borderClass = p.selected
                ? "border-purple-400 bg-purple-50/50 shadow-sm"
                : p.hasError
                  ? "border-red-300 bg-white"
                  : "border-gray-200 bg-white hover:border-gray-300";

            return h(
                "label",
                {
                    class: `relative cursor-pointer rounded-2xl border-2 p-6 transition-all hover:shadow-md ${borderClass}`,
                },
                [
                    h("input", {
                        class: "sr-only",
                        type: "radio",
                        name: "sex",
                        checked: p.selected,
                        onChange: () => emit("select"),
                    }),
                    h("div", { class: "flex flex-col items-center gap-3" }, [
                        h(
                            "svg",
                            {
                                class: `h-8 w-8 ${p.selected ? "text-purple-500" : "text-gray-400"}`,
                                fill: "none",
                                viewBox: "0 0 24 24",
                                stroke: "currentColor",
                            },
                            p.iconPaths.map((d) =>
                                h("path", { d, "stroke-width": "2" }),
                            ),
                        ),
                        h(
                            "span",
                            {
                                class: `font-medium ${p.selected ? "text-purple-700" : "text-gray-700"}`,
                            },
                            p.label,
                        ),
                    ]),
                ],
            );
        },
});

const ToggleCard = defineComponent({
    props: { modelValue: Boolean, label: String },
    emits: ["update:modelValue"],
    setup:
        (p, { emit }) =>
        () =>
            h(
                "div",
                {
                    class: "flex items-center justify-between rounded-xl border-2 border-gray-200 bg-white px-4 py-4",
                },
                [
                    h(
                        "span",
                        { class: "text-sm font-medium text-gray-800" },
                        p.label,
                    ),
                    h(
                        "label",
                        {
                            class: "relative inline-flex cursor-pointer items-center",
                        },
                        [
                            h("input", {
                                type: "checkbox",
                                class: "peer sr-only",
                                checked: p.modelValue,
                                onChange: (e) =>
                                    emit("update:modelValue", e.target.checked),
                            }),
                            h("div", {
                                class: "h-8 w-14 rounded-full bg-gray-300 transition-colors peer-checked:bg-purple-400",
                            }),
                            h("div", {
                                class: "absolute left-1 h-6 w-6 rounded-full bg-white shadow transition-transform peer-checked:translate-x-6",
                            }),
                        ],
                    ),
                ],
            ),
});

const GoalCard = defineComponent({
    props: {
        value: String,
        label: String,
        color: String,
        icon: String,
        selected: Boolean,
    },
    emits: ["click"],
    setup:
        (p, { emit }) =>
        () =>
            h(
                "div",
                {
                    class: `cursor-pointer rounded-xl border-2 p-6 transition-all hover:shadow-md ${p.selected ? "border-purple-400 bg-purple-50/50 shadow-sm" : "border-gray-200 bg-white hover:border-gray-300"}`,
                    onClick: () => emit("click"),
                },
                [
                    h("div", { class: "flex items-center gap-4" }, [
                        h(
                            "div",
                            {
                                class: `flex h-12 w-12 items-center justify-center rounded-2xl border ${p.color}`,
                            },
                            h(
                                "svg",
                                {
                                    class: "h-6 w-6",
                                    fill: "none",
                                    viewBox: "0 0 24 24",
                                    stroke: "currentColor",
                                },
                                h("path", { d: p.icon, "stroke-width": "2" }),
                            ),
                        ),
                        h(
                            "p",
                            { class: "flex-1 font-medium text-gray-900" },
                            p.label,
                        ),
                    ]),
                ],
            ),
});

// ─── Données statiques ────────────────────────────────────────────────────────
const GENDERS = [
    {
        value: "homme",
        label: "Homme",
        iconPaths: [
            "M14 10l6-6m0 0h-4m4 0v4",
            "M10 19a7 7 0 1 0 0-14 7 7 0 0 0 0 14z",
        ],
    },
    {
        value: "femme",
        label: "Femme",
        iconPaths: ["M12 13v7m-3-3h6", "M12 13a5 5 0 1 0 0-10 5 5 0 0 0 0 10z"],
    },
];

const HABITS = [
    { key: "fumeur", label: "Fumeur" },
    { key: "alcool", label: "Consommation d'alcool" },
];

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

// ─── Props ────────────────────────────────────────────────────────────────────
const props = defineProps({
    form: { type: Object, required: true },
    computedAge: { type: [Number, String], default: "" },
    errors: {
        type: Object,
        default: () => ({ sexe: "", taille: "", poids: "", objectifs: "" }),
    },
    showErrors: { type: Boolean, default: false },
});

const { form, errors } = props;
const showErrors = toRef(props, "showErrors");

if (!Array.isArray(form.objectifs)) form.objectifs = [];

// ─── Logique ──────────────────────────────────────────────────────────────────
function toggleGoal(value) {
    form.objectifs = form.objectifs.includes(value)
        ? form.objectifs.filter((v) => v !== value)
        : [...form.objectifs, value];
}
</script>
