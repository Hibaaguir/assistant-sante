<template>
    <div class="w-full bg-white">
        <!-- Main centered container -->
        <!-- max-w-5.3xl largeur taille -->
        <div
            class="mx-auto max-w-5.2xl px-4 sm:px-6 lg:px-8 py-6 sm:py-8 lg:py-10"
        >
            <!-- Header Section -->
            <div class="mb-12">
                <Typography tag="h3" variant="h3-style" class="mt-2">
                    Renseignez vos indicateurs quotidiens pour un suivi simple,
                    clair et régulier.
                </Typography>
            </div>

            <!-- Main Content Container -->

            <IndicateurEtapes :current="step" :steps="steps" />

            <div class="mt-6 space-y-6">
                <!-- Step 1: Body & energy -->
                <div v-if="step === 1" class="space-y-5">
                    <div
                        class="mx-auto px-6 rounded-2xl border-2 border-blue-300 bg-white p-4 sm:p-5"
                    >
                        <div class="space-y-3">
                            <div
                                class="flex items-center justify-between text-base font-bold text-slate-800"
                            >
                                <span>Sommeil</span>
                                <span class="text-blue-700"
                                    >{{ form.sleep }}h</span
                                >
                            </div>
                            <input
                                v-model.number="form.sleep"
                                type="range"
                                min="0"
                                max="12"
                                class="range-base"
                            />
                            <div
                                class="flex justify-between text-sm text-slate-500"
                            >
                                <span>0h</span><span>12h</span>
                            </div>
                        </div>
                    </div>

                    <div
                        class="mx-auto px-6 rounded-2xl border-2 border-blue-300 bg-white p-4 sm:p-5"
                    >
                        <div class="space-y-3">
                            <div
                                class="flex items-center justify-between text-base font-bold text-slate-800"
                            >
                                <span>Niveau de stress</span>
                                <span class="text-blue-700"
                                    >{{ form.stress }}/10</span
                                >
                            </div>
                            <input
                                v-model.number="form.stress"
                                type="range"
                                min="0"
                                max="10"
                                class="range-base"
                            />
                            <div
                                class="flex justify-between text-sm text-slate-500"
                            >
                                <span>Faible</span><span>Élevé</span>
                            </div>
                        </div>
                    </div>

                    <div
                        class="mx-auto px-6 rounded-2xl border-2 border-blue-300 bg-white p-4 sm:p-5"
                    >
                        <div class="space-y-3">
                            <div
                                class="flex items-center justify-between text-base font-bold text-slate-800"
                            >
                                <span>Niveau d'énergie</span>
                                <span class="text-blue-700"
                                    >{{ STATIC_ENERGY }}/10</span
                                >
                            </div>
                            <div
                                class="rounded-xl border border-blue-300 bg-white px-4 py-3 text-sm text-slate-600"
                            >
                                Valeur statique temporaire (prediction IA a
                                venir)
                            </div>
                        </div>
                    </div>
                </div>
                <div v-else-if="step === 2" class="space-y-4">
                    <!-- Bloc 1 : Repas -->
                    <div class="rounded-xl border border-blue-200 bg-white p-4">
                        <p class="text-sm font-semibold text-slate-900">Repas d'aujourd'hui</p>
                        <p class="mt-0.5 text-xs text-slate-400">Les repas ajoutés seront enregistrés dans votre journal du jour.</p>

                        <div class="mt-3 grid gap-2 sm:grid-cols-2 xl:grid-cols-4">
                            <button
                                v-for="item in meals"
                                :key="item.id"
                                type="button"
                                class="rounded-lg border px-3 py-2 text-sm font-semibold transition-colors"
                                :class="form.selectedMeal === item.id ? 'border-blue-500 bg-blue-50 text-blue-700' : 'border-slate-200 bg-white text-slate-600 hover:border-blue-300'"
                                @click="form.selectedMeal = item.id"
                            >
                                <div v-html="item.icon" />
                                <div>{{ item.label }}</div>
                            </button>
                        </div>

                        <div v-if="form.selectedMeal" class="mt-3 border-t border-slate-100 pt-3">
                            <div class="mb-2 flex items-center justify-between text-sm font-semibold text-slate-700">
                                <span>{{ selectedMealLabel }}</span>
                                <button type="button" class="flex h-6 w-6 items-center justify-center rounded-full bg-red-100 text-sm font-bold text-red-500 hover:bg-red-200 transition-colors" @click="form.selectedMeal = ''">×</button>
                            </div>
                            <input v-model="mealDraft.label" type="text" class="w-full rounded-lg border border-slate-200 px-3 py-1.5 text-sm outline-none focus:border-blue-400" placeholder="Ex: Oeufs + pain complet" />
                            <input v-model.number="mealDraft.calories" type="number" min="0" class="mt-1.5 w-full rounded-lg border border-slate-200 px-3 py-1.5 text-sm outline-none focus:border-blue-400" placeholder="Calories (optionnel)" />
                            <button type="button" class="mt-2 w-full rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 disabled:opacity-50 transition-colors" :disabled="!mealDraft.label.trim()" @click="addMeal">Ajouter</button>
                        </div>

                        <div v-if="form.meals.length" class="mt-3 border-t border-slate-100 pt-3">
                            <ul class="space-y-1.5">
                                <li v-for="(meal, index) in form.meals" :key="`${meal.type}-${meal.label}-${index}`" class="flex items-center justify-between rounded-lg bg-slate-50 px-3 py-1.5 text-sm">
                                    <span class="text-slate-700"><strong>{{ getMealTypeLabel(meal.type) }}</strong> — {{ meal.label }}<span v-if="meal.calories != null"> ({{ meal.calories }} kcal)</span></span>
                                    <BaseButton type="button" variant="delete" size="sm" @click="deleteMeal(index)">Supprimer</BaseButton>
                                </li>
                            </ul>
                            <p class="mt-2 text-xs font-semibold text-green-700">Total calories : {{ mealsCaloriesTotal }} kcal</p>
                        </div>
                        <p v-else class="mt-3 text-xs text-slate-400">Aucun repas ajouté pour le moment.</p>
                    </div>

                    <!-- Bloc 2 : Caféine + Hydratation + Sucre -->
                    <div class="rounded-xl border border-blue-200 bg-white p-3 sm:p-4 space-y-4">
                        <!-- Caféine -->
                        <div>
                            <p class="text-sm font-semibold text-slate-900">Caféine (tasses)</p>
                            <div class="mt-2 flex items-center gap-2">
                                <div class="inline-flex items-center rounded-lg border border-blue-300 bg-white px-3 py-2 text-base font-semibold text-slate-700">
                                    {{ form.caffeine }} tasse(s)
                                </div>
                                <BaseButton type="button" variant="secondary" size="sm" @click="adjustCaffeine(-1)">-</BaseButton>
                                <BaseButton type="button" variant="outline" size="sm" @click="adjustCaffeine(1)">+</BaseButton>
                            </div>
                        </div>

                        <div class="border-t border-slate-100" />

                        <!-- Hydratation -->
                        <div>
                            <p class="text-sm font-semibold text-slate-900">Hydratation</p>
                            <div class="mt-2 grid grid-cols-1 gap-2 md:grid-cols-2">
                                <div class="rounded-lg border border-blue-200 bg-white p-2.5">
                                    <div class="flex items-start justify-between">
                                        <div>
                                            <p class="text-sm font-semibold text-slate-800">Verre</p>
                                            <p class="text-xs text-slate-500">0.5L par unité</p>
                                        </div>
                                        <svg viewBox="0 0 24 24" class="h-4 w-4 text-sky-600" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                            <path d="M7 3h10l-1 16a2 2 0 0 1-2 2h-4a2 2 0 0 1-2-2L7 3z" />
                                            <path d="M8 8h8" />
                                        </svg>
                                    </div>
                                    <div class="mt-3 flex items-center justify-between">
                                        <BaseButton type="button" variant="secondary" size="sm" @click="adjustCups(-1)">-</BaseButton>
                                        <span class="text-lg font-bold">{{ form.cupCount }}</span>
                                        <BaseButton type="button" variant="outline" size="sm" @click="adjustCups(1)">+</BaseButton>
                                    </div>
                                </div>
                                <div class="rounded-lg border border-blue-200 bg-white p-2.5">
                                    <div class="flex items-start justify-between">
                                        <div>
                                            <p class="text-sm font-semibold text-slate-800">Bouteille</p>
                                            <p class="text-xs text-slate-500">1.5L par unité</p>
                                        </div>
                                        <svg viewBox="0 0 24 24" class="h-4 w-4 text-cyan-600" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                            <path d="M10 3h4v3h2v15H8V6h2z" />
                                            <path d="M10 11h4" />
                                        </svg>
                                    </div>
                                    <div class="mt-3 flex items-center justify-between">
                                        <BaseButton type="button" variant="secondary" size="sm" @click="adjustBottles(-1)">-</BaseButton>
                                        <span class="text-lg font-bold">{{ form.bottleCount }}</span>
                                        <BaseButton type="button" variant="outline" size="sm" @click="adjustBottles(1)">+</BaseButton>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-3">
                                <input v-model.number="form.customHydration" type="number" step="0.1" min="0" class="w-full rounded-lg border border-blue-300 bg-white px-3 py-2 text-sm" placeholder="Autre quantité (en litres)" />
                            </div>
                            <div class="mt-3 inline-flex rounded-lg border-2 border-violet-400 bg-white px-4 py-2 text-sm font-semibold text-violet-700">
                                Total: {{ hydrationTotal.toFixed(1) }} L
                            </div>
                        </div>

                        <div class="border-t border-slate-100" />

                        <!-- Sucre -->
                        <div>
                            <p class="text-sm font-semibold text-slate-900">Apport en sucre</p>
                            <div class="mt-3 inline-flex min-w-[160px] flex-col rounded-xl border px-3 py-2 text-left" :class="sugarBadgeClasses">
                                <span class="text-sm font-semibold">{{ sugarLabels[form.sugar] }}</span>
                                <span class="text-[11px] opacity-80">Détecté automatiquement</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Habits & sport -->
                <div v-else class="space-y-4">
                    <!-- Un seul cadre pour activité + intensité + habitudes -->
                    <div class="rounded-xl border border-blue-200 bg-white p-4 sm:p-5 space-y-4">

                        <!-- Type d'activité -->
                        <div>
                            <label class="text-sm font-semibold text-slate-900" for="activity">Type d'activité</label>
                            <select
                                id="activity"
                                v-model="form.activityType"
                                class="mt-2 w-full rounded-lg border border-blue-300 bg-white px-3 py-2.5 text-sm"
                                @change="handleActivitySelection"
                            >
                                <option disabled value="">Sélectionnez une activité</option>
                                <option v-for="item in activities" :key="item" :value="item">{{ item }}</option>
                                <option value="__add_new__">+ Ajouter une activité</option>
                            </select>
                            <div v-if="showNewActivityForm" class="mt-2 rounded-lg border border-purple-200 bg-purple-50 p-3">
                                <div class="mb-2 flex items-center justify-between">
                                    <p class="text-sm font-semibold text-slate-700">Nouvelle activité</p>
                                    <BaseButton type="button" variant="text" size="sm" @click="cancelNewActivity">×</BaseButton>
                                </div>
                                <div class="flex items-center gap-2">
                                    <input v-model="newActivityName" type="text" class="w-full rounded-lg border border-purple-300 bg-white px-3 py-2 text-sm" placeholder="Nom de l'activité" />
                                    <BaseButton type="button" variant="save" size="sm" :disabled="!newActivityName.trim()" @click="addNewActivity">Valider</BaseButton>
                                </div>
                            </div>
                        </div>

                        <div class="border-t border-slate-100" />

                        <!-- Durée -->
                        <div>
                            <label class="text-sm font-semibold text-slate-900" for="duration">
                                Durée (minutes)<span v-if="isDurationRequired" class="ml-1 text-red-500">*</span>
                            </label>
                            <input
                                id="duration"
                                v-model.number="form.activityDuration"
                                type="number"
                                min="0"
                                class="mt-2 w-full rounded-lg border bg-white px-3 py-2.5 text-sm outline-none focus:border-blue-400"
                                :class="submitAttempted && activityErrors.duration ? 'border-red-400' : 'border-blue-300'"
                            />
                            <p v-if="submitAttempted && activityErrors.duration" class="mt-1 text-sm text-red-500">{{ activityErrors.duration }}</p>
                        </div>

                        <div class="border-t border-slate-100" />

                        <!-- Intensité -->
                        <div>
                            <p class="text-sm font-semibold text-slate-900">Intensité</p>
                            <div class="mt-2 grid grid-cols-3 gap-2">
                                <BaseButton
                                    v-for="value in intensityOptions"
                                    :key="`intensity-${value}`"
                                    type="button"
                                    :variant="form.intensity === value ? (value === 'high' ? 'success' : 'outline') : 'secondary'"
                                    size="sm"
                                    @click="form.intensity = value"
                                >
                                    {{ intensityLabels[value] }}
                                </BaseButton>
                            </div>
                        </div>

                        <div class="border-t border-slate-100" />

                        <!-- Habitudes -->
                        <div class="space-y-3">
                            <p class="text-sm font-semibold text-slate-900">Habitudes</p>

                            <!-- Tabac -->
                            <div>
                            <button
                                type="button"
                                class="flex w-full items-center justify-between text-sm font-semibold text-slate-700"
                                @click="toggleTobacco"
                            >
                                <span>Tabac</span>
                                <span class="relative h-6 w-10 rounded-full transition-colors" :class="form.tobacco ? 'bg-blue-600' : 'bg-slate-300'" aria-hidden="true">
                                    <span class="absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white shadow-sm transition-transform" :class="form.tobacco ? 'translate-x-4' : 'translate-x-0'" />
                                </span>
                            </button>

                            <div v-if="form.tobacco" class="mt-3 space-y-3">
                                <div>
                                    <label
                                        class="text-base font-semibold text-slate-700"
                                    >
                                        Type <span class="text-red-500">*</span>
                                    </label>
                                    <div class="mt-2 flex flex-wrap gap-2">
                                        <BaseButton
                                            type="button"
                                            :variant="form.tobaccoTypes.cigarette ? 'outline' : 'secondary'"
                                            size="sm"
                                            @click="toggleTobaccoType('cigarette')"
                                        >
                                            Cigarette
                                        </BaseButton>
                                        <BaseButton
                                            type="button"
                                            :variant="form.tobaccoTypes.vape ? 'outline' : 'secondary'"
                                            size="sm"
                                            @click="toggleTobaccoType('vape')"
                                        >
                                            Vape
                                        </BaseButton>
                                    </div>
                                    <p
                                        v-if="
                                            submitAttempted &&
                                            tobaccoErrors.types
                                        "
                                        class="mt-1 text-sm text-red-500"
                                    >
                                        {{ tobaccoErrors.types }}
                                    </p>
                                </div>

                                <div v-if="form.tobaccoTypes.cigarette">
                                    <label
                                        class="text-base font-semibold text-slate-700"
                                    >
                                        Nombre de cigarettes par jour
                                        <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        v-model.number="form.cigarettesPerDay"
                                        type="number"
                                        min="0"
                                        placeholder="Ex: 5"
                                        class="mt-1 w-full rounded-lg border border-blue-300 bg-white px-3 py-2 text-base text-slate-700 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-200"
                                    />
                                    <p
                                        v-if="
                                            submitAttempted &&
                                            tobaccoErrors.cigarettesPerDay
                                        "
                                        class="mt-1 text-sm text-red-500"
                                    >
                                        {{ tobaccoErrors.cigarettesPerDay }}
                                    </p>
                                </div>

                                <div
                                    v-if="form.tobaccoTypes.vape"
                                    class="space-y-3"
                                >
                                    <div>
                                        <label
                                            class="text-base font-semibold text-slate-700"
                                        >
                                            Nombre de taffes prise par jour
                                            <span class="text-red-500">*</span>
                                        </label>
                                        <input
                                            v-model.number="form.vapeLiquidMl"
                                            type="number"
                                            min="0"
                                            placeholder="Ex: 3"
                                            class="mt-1 w-full rounded-lg border border-blue-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-200"
                                        />
                                        <p
                                            v-if="
                                                submitAttempted &&
                                                tobaccoErrors.vapeLiquidMl
                                            "
                                            class="mt-1 text-sm text-red-500"
                                        >
                                            {{ tobaccoErrors.vapeLiquidMl }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            </div>

                            <!-- Alcool -->
                            <div class="border-t border-slate-100 pt-3">
                            <button
                                type="button"
                                class="flex w-full items-center justify-between text-sm font-semibold text-slate-700"
                                @click="toggleAlcohol"
                            >
                                <span>Alcool<span v-if="form.alcohol" class="ml-1 text-red-500">*</span></span>
                                <span class="relative h-6 w-10 rounded-full transition-colors" :class="form.alcohol ? 'bg-blue-600' : 'bg-slate-300'" aria-hidden="true">
                                    <span class="absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white shadow-sm transition-transform" :class="form.alcohol ? 'translate-x-4' : 'translate-x-0'" />
                                </span>
                            </button>
                            <div v-if="form.alcohol" class="mt-3">
                                <label class="text-sm font-semibold text-slate-700">Nombre de verres par jour <span class="text-red-500">*</span></label>
                                <input v-model.number="form.alcoholDrinks" type="number" min="0" placeholder="Ex: 2" class="mt-1 w-full rounded-lg border border-blue-300 bg-white px-3 py-2 text-sm outline-none focus:border-blue-400" />
                                <p v-if="submitAttempted && alcoholErrors.drinks" class="mt-1 text-sm text-red-500">{{ alcoholErrors.drinks }}</p>
                            </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-12 flex items-center justify-between gap-3">
                <BaseButton
                    v-if="step === 1"
                    type="button"
                    variant="back"
                    size="md"
                    @click="goBack"
                >
                    ‹ Retour
                </BaseButton>
                <button
                    v-else
                    type="button"
                    class="rounded-xl border border-purple-300 bg-purple-50 px-5 py-3 text-sm font-semibold text-purple-700 transition-colors hover:bg-purple-100"
                    @click="goBack"
                >
                    ‹ Précédent
                </button>
                <BaseButton
                    v-if="step < 3"
                    type="button"
                    variant="save"
                    size="md"
                    @click="goNext"
                >
                    Suivant ›
                </BaseButton>
                <template v-else>
                    <div v-if="isEditMode" class="flex items-center gap-2">
                        <BaseButton
                            type="button"
                            variant="cancel"
                            size="md"
                            @click="cancelEdit"
                        >
                            Annuler les modifications
                        </BaseButton>
                        <BaseButton
                            type="button"
                            variant="save"
                            size="md"
                            @click="save"
                        >
                            ✓ Enregistrer les modifications
                        </BaseButton>
                    </div>
                    <BaseButton
                        v-else
                        type="button"
                        variant="save"
                        size="md"
                        @click="save"
                    >
                        ✓ Enregistrer la journée
                    </BaseButton>
                </template>

                <p
                    v-if="saveError"
                    class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
                >
                    {{ saveError }}
                </p>

                <ConfirmationDialog
                    :open="confirmDeleteMealOpen"
                    title="Confirmer la suppression"
                    message="Voulez-vous supprimer ce repas ?"
                    confirm-label="Supprimer"
                    cancel-label="Annuler"
                    @confirm="confirmDeleteMeal"
                    @cancel="cancelDeleteMeal"
                />
            </div>
        </div>
        <!-- End of main centered container -->
    </div>
    <!-- End of outer wrapper -->
</template>

<script setup>
import { computed, onMounted, reactive, ref } from "vue";
import { useRoute, useRouter } from "vue-router";
import IndicateurEtapes from "@/components/journal-entries/StepIndicator.vue";
import { useJournalStore } from "@/stores/journal";
import { useNotificationsStore } from "@/stores/notifications";
import ConfirmationDialog from "@/components/ui/ConfirmationDialog.vue";
import Typography from "@/components/ui/Typography.vue";
import BaseButton from "@/components/ui/BaseButton.vue";

const route = useRoute();
const router = useRouter();
const store = useJournalStore();
const notifications = useNotificationsStore();
const editEntryId = computed(() => String(route.query.edit || ""));
const isEditMode = computed(() => Boolean(editEntryId.value));

const steps = [
    { id: 1, label: "Corps & énergie" },
    { id: 2, label: "Nutrition" },
    { id: 3, label: "Habitudes & sport" },
];

const meals = [
    { id: "breakfast", label: "Petit déjeuner", icon: "&#127749;" },
    { id: "lunch", label: "Déjeuner", icon: "&#127869;" },
    { id: "dinner", label: "Dîner", icon: "&#127769;" },
    { id: "snack", label: "Snacks", icon: "&#127813;" },
];

const activities = ref([
    "Marche",
    "Course",
    "Vélo",
    "Natation",
    "Yoga",
    "Musculation",
    "Sport collectif",
]);
const sugarLabels = { low: "Faible", medium: "Modéré", high: "Élevé" };
const intensityLabels = { light: "Légère", medium: "Modérée", high: "Intense" };
const intensityOptions = ["light", "medium", "high"];

const step = ref(1);
const submitAttempted = ref(false);
const saveError = ref("");
const STATIC_ENERGY = 7;
const mealDraft = reactive({ label: "", calories: null });
const showNewActivityForm = ref(false);
const newActivityName = ref("");
const confirmDeleteMealOpen = ref(false);
const pendingDeleteMealIndex = ref(-1);

const form = reactive({
    sleep: 7,
    stress: 5,
    energy: STATIC_ENERGY,
    selectedMeal: "",
    sugar: "low",
    caffeine: 0,
    hydration: 0.5,
    customHydration: null,
    cupCount: 0,
    bottleCount: 0,
    meals: [],
    activityType: "",
    activityDuration: 0,
    intensity: "medium",
    tobacco: false,
    alcohol: false,
    tobaccoTypes: { cigarette: false, vape: false },
    cigarettesPerDay: null,
    vapeLiquidMl: null,
    alcoholDrinks: null,
});

const selectedMealLabel = computed(
    () => meals.find((item) => item.id === form.selectedMeal)?.label ?? "",
);
const hydrationTotal = computed(() => {
    const extra = Math.max(0, Number(form.customHydration) || 0);
    return form.cupCount * 0.5 + form.bottleCount * 1.5 + extra;
});
// Convert calories to a clean integer — returns null if the value is invalid
const normalizeMealCalories = (value) => {
    const number = Number(value);
    if (!Number.isFinite(number) || number < 0) return null;
    return Math.round(number);
};

// Total calories from all meals added
const mealsCaloriesTotal = computed(() =>
    form.meals.reduce((sum, meal) => {
        const calories = normalizeMealCalories(meal?.calories);
        return sum + (calories ?? 0);
    }, 0),
);

// CSS classes for the sugar badge, based on the selected sugar level
const SUGAR_BADGE = {
    high: "border-rose-300 bg-rose-100 text-rose-800",
    medium: "border-amber-300 bg-amber-100 text-amber-800",
    low: "border-emerald-300 bg-emerald-100 text-emerald-800",
};
const sugarBadgeClasses = computed(
    () => SUGAR_BADGE[form.sugar] ?? SUGAR_BADGE.low,
);

// Check tobacco fields — returns an object with error messages (null = no error)
const tobaccoErrors = computed(() => {
    const errors = { types: null, cigarettesPerDay: null, vapeLiquidMl: null };

    // If tobacco is off, no errors needed
    if (!form.tobacco) return errors;

    // At least one tobacco type must be chosen
    if (!form.tobaccoTypes.cigarette && !form.tobaccoTypes.vape) {
        errors.types = "Veuillez selectionner un type.";
    }

    // If cigarette is chosen, the daily count is required
    if (form.tobaccoTypes.cigarette && !form.cigarettesPerDay) {
        errors.cigarettesPerDay = "Veuillez remplir le champ.";
    }

    // If vape is chosen, the liquid amount is required
    if (form.tobaccoTypes.vape && !form.vapeLiquidMl) {
        errors.vapeLiquidMl = "Veuillez remplir le champ.";
    }

    return errors;
});

// True when any tobacco error exists
const hasTobaccoErrors = computed(() =>
    Object.values(tobaccoErrors.value).some(Boolean),
);

// True when an activity is selected (makes the duration field required)
const isDurationRequired = computed(() => Boolean(form.activityType));

// Check activity fields — duration is required if an activity is selected
const activityErrors = computed(() => {
    const errors = { duration: null };

    if (!isDurationRequired.value) return errors;

    if (!form.activityDuration || form.activityDuration <= 0) {
        errors.duration = "Veuillez remplir la durée (minutes).";
    }

    return errors;
});

// True when any activity error exists
const hasActivityErrors = computed(() =>
    Object.values(activityErrors.value).some(Boolean),
);

// Check alcohol fields — quantity is required if alcohol is enabled
const alcoholErrors = computed(() => {
    const errors = { drinks: null };

    if (!form.alcohol) return errors;

    if (!form.alcoholDrinks || form.alcoholDrinks <= 0) {
        errors.drinks = "Veuillez remplir le champ.";
    }

    return errors;
});

// True when any alcohol error exists
const hasAlcoholErrors = computed(() =>
    Object.values(alcoholErrors.value).some(Boolean),
);

onMounted(async () => {
    // Load all journal entries from the server
    await store.initialiser();

    // If we are creating a new entry, nothing more to do
    if (!isEditMode.value) return;

    // Try to find the entry we want to edit (by its ID from the URL)
    let entry = store.obtenirParId(editEntryId.value);

    // If not found yet, reload from server and try again
    if (!entry) {
        await store.chargerEntrees();
        entry = store.obtenirParId(editEntryId.value);
    }

    // Entry still not found — stop here (nothing to pre-fill)
    if (!entry) return;

    // Pre-fill the form with the existing entry data
    form.sleep = Number(entry.sleep ?? 7);
    form.stress = Number(entry.stress ?? 5);
    form.energy = STATIC_ENERGY;
    form.sugar = entry.sugar ?? "low";
    form.caffeine = Number(entry.caffeine ?? 0);
    form.activityType = entry.activityType ?? "";
    form.activityDuration = Number(entry.activityDuration ?? 0);
    form.intensity = entry.intensity ?? "medium";
    form.tobacco = Boolean(entry.tobacco);
    form.alcohol = Boolean(entry.alcohol);
    form.tobaccoTypes = entry.tobaccoTypes ?? { cigarette: false, vape: false };
    form.cigarettesPerDay = entry.cigarettesPerDay ?? null;
    form.vapeLiquidMl = entry.vapeLiquidMl ?? null;
    form.alcoholDrinks = entry.alcoholDrinks ?? null;
    form.cupCount = 0;
    form.bottleCount = 0;
    form.customHydration = Number(entry.hydration ?? 0);

    // The API may return different field names — normalize them to type/label
    form.meals = (entry.meals ?? []).map((m) => ({
        type: m.type || m.meal_type || "",
        label: m.label || m.description || "",
        calories: m.calories ?? null,
    }));
});

const addMeal = () => {
    if (!form.selectedMeal || !mealDraft.label.trim()) return;
    form.meals.push({
        type: form.selectedMeal,
        label: mealDraft.label.trim(),
        calories: normalizeMealCalories(mealDraft.calories),
    });
    mealDraft.label = "";
    mealDraft.calories = null;
    form.selectedMeal = "";
    notifications.itemAdded();
};

const getMealTypeLabel = (type) =>
    meals.find((item) => item.id === type)?.label ?? type;

const deleteMeal = (index) => {
    pendingDeleteMealIndex.value = index;
    confirmDeleteMealOpen.value = true;
};

const confirmDeleteMeal = () => {
    const index = pendingDeleteMealIndex.value;
    confirmDeleteMealOpen.value = false;
    pendingDeleteMealIndex.value = -1;
    if (index < 0 || index >= form.meals.length) return;
    form.meals.splice(index, 1);
    notifications.itemDeleted();
};

const cancelDeleteMeal = () => {
    confirmDeleteMealOpen.value = false;
    pendingDeleteMealIndex.value = -1;
    notifications.actionCancelled();
};

const adjustCaffeine = (delta) => {
    form.caffeine = Math.min(
        20,
        Math.max(0, Number(form.caffeine || 0) + delta),
    );
};
const adjustCups = (delta) => {
    form.cupCount = Math.max(0, form.cupCount + delta);
};
const adjustBottles = (delta) => {
    form.bottleCount = Math.max(0, form.bottleCount + delta);
};

const goNext = () => {
    submitAttempted.value = false;
    step.value = Math.min(3, step.value + 1);
};

const goBack = () => {
    if (step.value === 1) {
        router.push({ name: "journal" });
        return;
    }
    step.value = Math.max(1, step.value - 1);
};

const handleActivitySelection = () => {
    if (form.activityType === "__add_new__") {
        showNewActivityForm.value = true;
        form.activityType = "";
        return;
    }
    showNewActivityForm.value = false;
};

const cancelNewActivity = () => {
    showNewActivityForm.value = false;
    newActivityName.value = "";
    notifications.actionCancelled();
};

const addNewActivity = () => {
    const name = newActivityName.value.trim();
    if (!name) return;
    if (!activities.value.includes(name)) activities.value.push(name);
    form.activityType = name;
    showNewActivityForm.value = false;
    newActivityName.value = "";
    notifications.itemAdded();
};

const toggleTobacco = () => {
    form.tobacco = !form.tobacco;
    if (!form.tobacco) {
        form.tobaccoTypes.cigarette = false;
        form.tobaccoTypes.vape = false;
        form.cigarettesPerDay = null;
        form.vapeLiquidMl = null;
        submitAttempted.value = false;
    }
};

const toggleTobaccoType = (type) => {
    form.tobaccoTypes[type] = !form.tobaccoTypes[type];
    if (!form.tobaccoTypes.cigarette) form.cigarettesPerDay = null;
    if (!form.tobaccoTypes.vape) form.vapeLiquidMl = null;
};

const toggleAlcohol = () => {
    form.alcohol = !form.alcohol;
    if (!form.alcohol) form.alcoholDrinks = null;
};

// Go back to the journal list and mark the edit as cancelled
const cancelEdit = async () => {
    if (!isEditMode.value) return;
    await store.loadEntries();
    router.push({ name: "journal-history", query: { notice: "canceled" } });
};

// Build the data object to send to the API
function buildPayload() {
    return {
        sleep: Number(form.sleep),
        stress: Number(form.stress),
        energy: STATIC_ENERGY,
        sugar: form.sugar,
        caffeine: Number(form.caffeine),
        hydration: Number(hydrationTotal.value.toFixed(1)),
        meals: [...form.meals],
        calories: mealsCaloriesTotal.value,
        activityType: form.activityType || "Marche",
        activityDuration: Number(form.activityDuration) || 0,
        intensity: form.intensity,
        tobacco: form.tobacco,
        alcohol: form.alcohol,
        tobaccoTypes: {
            // Only keep tobacco type if tobacco is actually enabled
            cigarette: form.tobacco && form.tobaccoTypes.cigarette,
            vape: form.tobacco && form.tobaccoTypes.vape,
        },
        cigarettesPerDay:
            form.tobacco && form.tobaccoTypes.cigarette
                ? Number(form.cigarettesPerDay)
                : null,
        vapeLiquidMl:
            form.tobacco && form.tobaccoTypes.vape
                ? Number(form.vapeLiquidMl)
                : null,
        alcoholDrinks: form.alcohol ? Number(form.alcoholDrinks) : 0,
    };
}

// Get a readable error message from an API error response
function getApiError(error) {
    const status = error?.response?.status;

    if (status === 422) {
        // Server returned validation errors — show the first one
        const firstError = Object.values(error.response.data.errors || {})[0];
        return Array.isArray(firstError)
            ? firstError[0]
            : "Validation invalide.";
    }

    if (status === 401) {
        return "Session expirée. Reconnectez-vous.";
    }

    return "Erreur lors de l'enregistrement du journal.";
}

// Save or update the journal entry
const save = async () => {
    submitAttempted.value = true;

    // Stop if the user has not filled required fields
    if (
        hasActivityErrors.value ||
        hasTobaccoErrors.value ||
        hasAlcoholErrors.value
    )
        return;

    saveError.value = "";

    try {
        const payload = buildPayload();

        if (isEditMode.value) {
            // Update an existing entry
            await store.mettreAJourEntree(editEntryId.value, payload);
            router.push({
                name: "journal-history",
                query: { notice: "saved" },
            });
        } else {
            // Create a new entry
            await store.ajouterEntree(payload);
            notifications.itemAdded();
            router.push({ name: "journal" });
        }
    } catch (error) {
        saveError.value = getApiError(error);
        notifications.error(saveError.value);
    }
};
</script>

<style scoped>
.range-base {
    -webkit-appearance: none;
    appearance: none;
    width: 100%;
    height: 8px;
    border-radius: 999px;
    cursor: pointer;
    background: #dbeafe;
}
.range-base::-webkit-slider-runnable-track {
    height: 8px;
    border-radius: 999px;
    background: linear-gradient(90deg, #bfdbfe 0%, #ddd6fe 100%);
}
.range-base::-webkit-slider-thumb {
    -webkit-appearance: none;
    appearance: none;
    width: 20px;
    height: 20px;
    border: 2px solid #4338ca;
    border-radius: 999px;
    background: #4338ca;
    box-shadow:
        0 0 0 3px rgba(224, 231, 255, 0.95),
        0 2px 8px rgba(55, 48, 163, 0.45);
    margin-top: -6px;
}
.range-base::-moz-range-track {
    height: 8px;
    border: none;
    border-radius: 999px;
    background: linear-gradient(90deg, #bfdbfe 0%, #ddd6fe 100%);
}
.range-base::-moz-range-thumb {
    width: 20px;
    height: 20px;
    border: 2px solid #4338ca;
    border-radius: 999px;
    background: #4338ca;
    box-shadow:
        0 0 0 3px rgba(224, 231, 255, 0.95),
        0 2px 8px rgba(55, 48, 163, 0.45);
}
</style>
