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
                <div v-if="step === 1" class="space-y-4">
                    <!-- Sommeil -->
                    <div class="rounded-2xl bg-indigo-50 p-5 sm:p-6">
                        <div class="flex items-center justify-between mb-5">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-500">
                                    <svg viewBox="0 0 24 24" class="h-5 w-5 text-white" fill="currentColor" aria-hidden="true">
                                        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                                    </svg>
                                </div>
                                <span class="text-base font-semibold text-slate-800">Sommeil</span>
                            </div>
                            <span class="text-2xl font-bold text-indigo-600">{{ form.sleep }}h</span>
                        </div>
                        <input
                            v-model.number="form.sleep"
                            type="range"
                            min="0"
                            max="12"
                            class="range-base range-sleep"
                            :style="getRangeFillStyle(form.sleep, 12, '#4f46e5', '#c7d2fe')"
                        />
                        <div class="mt-2 flex justify-between text-xs text-slate-400">
                            <span>0h</span><span>12h</span>
                        </div>
                    </div>

                    <!-- Niveau de stress -->
                    <div class="rounded-2xl bg-rose-50 p-5 sm:p-6">
                        <div class="flex items-center justify-between mb-5">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-rose-500">
                                    <svg viewBox="0 0 24 24" class="h-5 w-5 text-white" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                                    </svg>
                                </div>
                                <span class="text-base font-semibold text-slate-800">Niveau de stress</span>
                            </div>
                            <span class="text-2xl font-bold text-rose-500">{{ form.stress }}/10</span>
                        </div>
                        <input
                            v-model.number="form.stress"
                            type="range"
                            min="0"
                            max="10"
                            class="range-base range-stress"
                            :style="getRangeFillStyle(form.stress, 10, '#f43f5e', '#fecdd3')"
                        />
                        <div class="mt-2 flex justify-between text-xs text-slate-400">
                            <span>Faible</span><span>Élevé</span>
                        </div>
                    </div>
                </div>
                <div v-else-if="step === 2" class="space-y-4">
                    <!-- Bloc 1 : Repas -->
                    <div class="rounded-xl border border-blue-200 bg-white p-4">
                        <p class="text-sm font-semibold text-slate-900">
                            Repas d'aujourd'hui
                        </p>
                        <p class="mt-0.5 text-xs text-slate-400">
                            Les repas ajoutés seront enregistrés dans votre
                            journal du jour.
                        </p>

                        <div
                            class="mt-3 grid gap-2 sm:grid-cols-2 xl:grid-cols-4"
                        >
                            <button
                                v-for="item in meals"
                                :key="item.id"
                                type="button"
                                class="rounded-lg border px-3 py-2 text-sm font-semibold transition-colors"
                                :class="
                                    form.selectedMeal === item.id
                                        ? 'border-blue-500 bg-blue-50 text-blue-700'
                                        : 'border-slate-200 bg-white text-slate-600 hover:border-blue-300'
                                "
                                @click="
                                    form.selectedMeal = item.id;
                                    mealError = '';
                                "
                            >
                                <div v-html="item.icon" />
                                <div>{{ item.label }}</div>
                            </button>
                        </div>

                        <div
                            v-if="form.selectedMeal"
                            class="mt-3 border-t border-slate-100 pt-3"
                        >
                            <div
                                class="mb-2 flex items-center justify-between text-sm font-semibold text-slate-700"
                            >
                                <span
                                    >{{ selectedMealLabel }}
                                    <span class="text-red-500">*</span></span
                                >
                                <button
                                    type="button"
                                    class="flex h-6 w-6 items-center justify-center rounded-full bg-red-100 text-sm font-bold text-red-500 hover:bg-red-200 transition-colors"
                                    @click="form.selectedMeal = ''"
                                >
                                    ×
                                </button>
                            </div>
                            <input
                                v-model="mealDraft.label"
                                type="text"
                                class="w-full rounded-lg border px-3 py-1.5 text-sm outline-none transition"
                                :class="
                                    mealError
                                        ? 'border-red-400 focus:border-red-500'
                                        : 'border-slate-200 focus:border-blue-400'
                                "
                                placeholder="Ex: Oeufs + pain complet"
                                @input="mealError = ''"
                            />
                            <p
                                v-if="mealError"
                                class="mt-1 text-xs font-medium text-red-500"
                            >
                                {{ mealError }}
                            </p>
                            <input
                                v-model.number="mealDraft.calories"
                                type="number"
                                min="0"
                                class="mt-1.5 w-full rounded-lg border border-slate-200 px-3 py-1.5 text-sm outline-none focus:border-blue-400"
                                placeholder="Calories (optionnel)"
                            />
                            <div class="mt-3 flex justify-end">
                                <BaseButton
                                    type="button"
                                    variant="add"
                                    size="md"
                                    class-name="min-w-40 border-blue-500 bg-blue-50 text-blue-700 hover:bg-blue-100"
                                    @click="addMeal"
                                >
                                    Ajouter
                                </BaseButton>
                            </div>
                        </div>

                        <div
                            v-if="form.meals.length"
                            class="mt-3 border-t border-slate-100 pt-3"
                        >
                            <ul class="space-y-1.5">
                                <li
                                    v-for="(meal, index) in form.meals"
                                    :key="`${meal.type}-${meal.label}-${index}`"
                                    class="flex items-center justify-between rounded-lg bg-slate-50 px-3 py-1.5 text-sm"
                                >
                                    <span class="text-slate-700"
                                        ><strong>{{
                                            getMealTypeLabel(meal.type)
                                        }}</strong>
                                        — {{ meal.label
                                        }}<span v-if="meal.calories != null">
                                            ({{ meal.calories }} kcal)</span
                                        ></span
                                    >
                                    <BaseButton
                                        type="button"
                                        variant="delete"
                                        size="sm"
                                        @click="deleteMeal(index)"
                                        >Supprimer</BaseButton
                                    >
                                </li>
                            </ul>
                            <p
                                class="mt-2 text-xs font-semibold text-green-700"
                            >
                                Total calories : {{ mealsCaloriesTotal }} kcal
                            </p>
                        </div>
                        <p v-else class="mt-3 text-xs text-slate-400">
                            Aucun repas ajouté pour le moment.
                        </p>
                    </div>

                    <!-- Bloc 2 : Caféine + Apport en sucre + Hydratation -->
                    <div class="space-y-3">
                        <!-- Ligne : Caféine + Apport en sucre -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">

                            <!-- Caféine -->
                            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                                <div class="flex items-center gap-2.5 mb-4">
                                    <div class="flex h-9 w-9 items-center justify-center rounded-full bg-orange-50">
                                        <span class="text-xl leading-none">☕</span>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-slate-800">Caféine</p>
                                        <p class="text-xs text-slate-400">Tasses</p>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between gap-3">
                                    <BaseButton
                                        type="button"
                                        variant="secondary"
                                        size="sm"
                                        class-name="!px-4 !py-2"
                                        @click="adjustCaffeine(-1)"
                                    >-</BaseButton>
                                    <span class="flex-1 text-center rounded-xl border-2 border-slate-200 bg-white py-1.5 text-2xl font-bold text-slate-800">{{ form.caffeine }}</span>
                                    <BaseButton
                                        type="button"
                                        variant="outline"
                                        size="sm"
                                        class-name="!px-4 !py-2 !bg-blue-600 !text-white !border-blue-600"
                                        @click="adjustCaffeine(1)"
                                    >+</BaseButton>
                                </div>
                            </div>

                            <!-- Apport en sucre -->
                            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                                <div class="flex items-center gap-2.5 mb-4">
                                    <div class="flex h-9 w-9 items-center justify-center rounded-full bg-green-50">
                                        <span class="text-xl leading-none">🍎</span>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-slate-800">Apport en sucre</p>
                                        <p class="text-xs text-slate-400">Estimation</p>
                                    </div>
                                </div>
                                <div class="flex gap-2">
                                    <BaseButton
                                        v-for="opt in sugarOptions"
                                        :key="opt"
                                        type="button"
                                        :variant="form.sugar === opt ? 'outline' : 'secondary'"
                                        size="sm"
                                        :class-name="form.sugar === opt ? 'flex-1 !bg-blue-600 !text-white !border-blue-600' : 'flex-1'"
                                        @click="form.sugar = opt"
                                    >
                                        {{ sugarLabels[opt] }}
                                    </BaseButton>
                                </div>
                            </div>
                        </div>

                        <!-- Hydratation -->
                        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                            <div class="flex items-center gap-2.5 mb-4">
                                <div class="flex h-9 w-9 items-center justify-center rounded-full bg-blue-50">
                                    <svg viewBox="0 0 24 24" class="h-5 w-5 text-blue-500" fill="currentColor" aria-hidden="true">
                                        <path d="M12 2c-.3 0-8 9.1-8 13a8 8 0 0016 0C20 11.1 12.3 2 12 2z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-slate-800">Hydratation</p>
                                    <p class="text-xs text-slate-400">Suivez votre consommation d'eau</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-3 gap-3">
                                <!-- Verre 0.5L -->
                                <div>
                                    <p class="text-xs text-slate-500 mb-2">Verre (0.5L)</p>
                                    <div class="flex items-center gap-1">
                                        <BaseButton
                                            type="button"
                                            variant="secondary"
                                            size="sm"
                                            class-name="!px-3 !py-2"
                                            @click="adjustCups(-1)"
                                        >-</BaseButton>
                                        <span class="flex-1 text-center rounded-lg border-2 border-slate-200 bg-white py-1 text-lg font-bold text-slate-800">{{ form.cupCount }}</span>
                                        <BaseButton
                                            type="button"
                                            variant="outline"
                                            size="sm"
                                            class-name="!px-3 !py-2 !bg-blue-600 !text-white !border-blue-600"
                                            @click="adjustCups(1)"
                                        >+</BaseButton>
                                    </div>
                                </div>

                                <!-- Bouteille 1.5L -->
                                <div>
                                    <p class="text-xs text-slate-500 mb-2">Bouteille (1.5L)</p>
                                    <div class="flex items-center gap-1">
                                        <BaseButton
                                            type="button"
                                            variant="secondary"
                                            size="sm"
                                            class-name="!px-3 !py-2"
                                            @click="adjustBottles(-1)"
                                        >-</BaseButton>
                                        <span class="flex-1 text-center rounded-lg border-2 border-slate-200 bg-white py-1 text-lg font-bold text-slate-800">{{ form.bottleCount }}</span>
                                        <BaseButton
                                            type="button"
                                            variant="outline"
                                            size="sm"
                                            class-name="!px-3 !py-2 !bg-blue-600 !text-white !border-blue-600"
                                            @click="adjustBottles(1)"
                                        >+</BaseButton>
                                    </div>
                                </div>

                                <!-- Autre litres -->
                                <div>
                                    <p class="text-xs text-slate-500 mb-2">Autre (litres)</p>
                                    <input
                                        v-model.number="form.customHydration"
                                        type="number"
                                        step="0.1"
                                        min="0"
                                        class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 outline-none focus:border-blue-400"
                                        placeholder="0.0"
                                    />
                                </div>
                            </div>

                            <!-- Total hydratation -->
                            <div class="mt-4 rounded-xl border-2 border-blue-600 bg-white px-4 py-2.5 inline-flex items-center gap-3">
                                <p class="text-xs font-semibold text-blue-600">Total hydratation</p>
                                <p class="text-xl font-bold text-blue-700">{{ hydrationTotal.toFixed(1) }} L</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Habits & sport -->
                <div v-else class="space-y-4">

                    <!-- Activités physiques -->
                    <div class="rounded-2xl border border-slate-200 bg-white p-4 sm:p-5 shadow-sm">
                        <p class="text-sm font-semibold text-slate-900 mb-4">Activités physiques</p>

                        <div class="space-y-4">
                            <div
                                v-for="(act, i) in form.activities"
                                :key="i"
                                class="space-y-3"
                                :class="{ 'border-t border-slate-100 pt-4': i > 0 }"
                            >
                                <!-- Select + supprimer -->
                                <div class="flex items-center gap-2">
                                    <select
                                        v-model="act.type"
                                        class="flex-1 rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none focus:border-blue-400"
                                    >
                                        <option value="">Sélectionnez une activité</option>
                                        <option v-for="item in activityOptions" :key="item" :value="item">{{ item }}</option>
                                    </select>
                                    <BaseButton
                                        v-if="form.activities.length > 1"
                                        type="button"
                                        variant="delete"
                                        size="sm"
                                        @click="removeActivity(i)"
                                    >×</BaseButton>
                                </div>

                                <!-- Durée -->
                                <input
                                    v-model.number="act.duration"
                                    type="number"
                                    min="0"
                                    placeholder="Durée (min)"
                                    class="w-full rounded-lg border bg-white px-3 py-2.5 text-sm outline-none focus:border-blue-400"
                                    :class="submitAttempted && act.type && (!act.duration || act.duration <= 0) ? 'border-red-400' : 'border-slate-200'"
                                />

                                <!-- Intensité -->
                                <div class="flex gap-2">
                                    <button
                                        v-for="opt in intensityOptions"
                                        :key="opt"
                                        type="button"
                                        class="flex-1 rounded-lg py-2.5 text-sm font-medium transition-colors"
                                        :class="act.intensity === opt ? 'border-2 border-blue-600 bg-white text-blue-600' : 'border border-slate-200 bg-white text-slate-500 hover:bg-slate-50'"
                                        @click="act.intensity = opt"
                                    >
                                        {{ intensityLabels[opt] }}
                                    </button>
                                </div>

                                <p v-if="submitAttempted && act.type && (!act.duration || act.duration <= 0)" class="text-sm text-red-500">
                                    La durée est obligatoire.
                                </p>
                            </div>
                        </div>

                        <!-- Ajouter une activité -->
                        <button
                            type="button"
                            class="mt-4 w-full rounded-xl border-2 border-blue-600 bg-white py-3 text-sm font-semibold text-blue-600 transition-colors hover:bg-blue-50"
                            @click="addActivity"
                        >
                            + Ajouter une activité
                        </button>
                    </div>

                    <!-- Habitudes -->
                    <div class="rounded-2xl border border-slate-200 bg-white p-4 sm:p-5 shadow-sm">
                        <p class="text-sm font-semibold text-slate-900 mb-1">Habitudes</p>

                        <!-- Tabac -->
                        <div class="py-3">
                            <button
                                type="button"
                                class="flex w-full items-center justify-between text-sm font-medium text-slate-700"
                                @click="toggleTobacco"
                            >
                                <span>Tabac</span>
                                <span
                                    class="relative h-6 w-11 rounded-full transition-colors duration-200"
                                    :class="form.tobacco ? 'bg-blue-500' : 'bg-slate-200'"
                                    aria-hidden="true"
                                >
                                    <span
                                        class="absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white shadow transition-transform duration-200"
                                        :class="form.tobacco ? 'translate-x-5' : 'translate-x-0'"
                                    />
                                </span>
                            </button>

                            <div v-if="form.tobacco" class="mt-4 space-y-3">
                                <div>
                                    <p class="text-xs font-semibold text-slate-600 mb-2">Type <span class="text-red-500">*</span></p>
                                    <div class="flex gap-2">
                                        <BaseButton
                                            type="button"
                                            :variant="form.tobaccoTypes.cigarette ? 'outline' : 'secondary'"
                                            size="sm"
                                            :class-name="form.tobaccoTypes.cigarette ? 'flex-1 !bg-blue-600 !text-white !border-blue-600' : 'flex-1'"
                                            @click="toggleTobaccoType('cigarette')"
                                        >Cigarette</BaseButton>
                                        <BaseButton
                                            type="button"
                                            :variant="form.tobaccoTypes.vape ? 'outline' : 'secondary'"
                                            size="sm"
                                            :class-name="form.tobaccoTypes.vape ? 'flex-1 !bg-blue-600 !text-white !border-blue-600' : 'flex-1'"
                                            @click="toggleTobaccoType('vape')"
                                        >Vape</BaseButton>
                                    </div>
                                    <p v-if="submitAttempted && tobaccoErrors.types" class="mt-1 text-sm text-red-500">{{ tobaccoErrors.types }}</p>
                                </div>

                                <div v-if="form.tobaccoTypes.cigarette">
                                    <p class="text-xs font-semibold text-slate-600 mb-1">Cigarettes par jour <span class="text-red-500">*</span></p>
                                    <input
                                        v-model.number="form.cigarettesPerDay"
                                        type="number"
                                        min="0"
                                        placeholder="Ex: 5"
                                        class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-blue-400"
                                    />
                                    <p v-if="submitAttempted && tobaccoErrors.cigarettesPerDay" class="mt-1 text-sm text-red-500">{{ tobaccoErrors.cigarettesPerDay }}</p>
                                </div>

                                <div v-if="form.tobaccoTypes.vape">
                                    <p class="text-xs font-semibold text-slate-600 mb-1">Taffes par jour <span class="text-red-500">*</span></p>
                                    <input
                                        v-model.number="form.vapeLiquidMl"
                                        type="number"
                                        min="0"
                                        placeholder="Ex: 3"
                                        class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-blue-400"
                                    />
                                    <p v-if="submitAttempted && tobaccoErrors.vapeLiquidMl" class="mt-1 text-sm text-red-500">{{ tobaccoErrors.vapeLiquidMl }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="border-t border-slate-100" />

                        <!-- Alcool -->
                        <div class="py-3">
                            <button
                                type="button"
                                class="flex w-full items-center justify-between text-sm font-medium text-slate-700"
                                @click="toggleAlcohol"
                            >
                                <span>Alcool<span v-if="form.alcohol" class="ml-1 text-red-500">*</span></span>
                                <span
                                    class="relative h-6 w-11 rounded-full transition-colors duration-200"
                                    :class="form.alcohol ? 'bg-blue-500' : 'bg-slate-200'"
                                    aria-hidden="true"
                                >
                                    <span
                                        class="absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white shadow transition-transform duration-200"
                                        :class="form.alcohol ? 'translate-x-5' : 'translate-x-0'"
                                    />
                                </span>
                            </button>

                            <div v-if="form.alcohol" class="mt-4">
                                <p class="text-xs font-semibold text-slate-600 mb-1">Verres par jour <span class="text-red-500">*</span></p>
                                <input
                                    v-model.number="form.alcoholDrinks"
                                    type="number"
                                    min="0"
                                    placeholder="Ex: 2"
                                    class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-blue-400"
                                />
                                <p v-if="submitAttempted && alcoholErrors.drinks" class="mt-1 text-sm text-red-500">{{ alcoholErrors.drinks }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <div class="mt-4 flex items-center justify-between gap-3">
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

const activityOptions = [
    "Marche",
    "Course",
    "Vélo",
    "Natation",
    "Yoga",
    "Musculation",
    "Sport collectif",
];
const intensityLabels = { light: "Légère", medium: "Modérée", high: "Intense" };
const intensityOptions = ["light", "medium", "high"];
const sugarOptions = ["low", "medium", "high"];
const sugarLabels = { low: "Faible", medium: "Modéré", high: "Élevé" };

const step = ref(1);
const submitAttempted = ref(false);
const saveError = ref("");
const mealDraft = reactive({ label: "", calories: null });
const mealError = ref("");
const confirmDeleteMealOpen = ref(false);
const pendingDeleteMealIndex = ref(-1);

const form = reactive({
    sleep: 7,
    stress: 5,
    selectedMeal: "",
    caffeine: 0,
    hydration: 0.5,
    customHydration: null,
    cupCount: 0,
    bottleCount: 0,
    meals: [],
    sugar: "medium",
    activities: [{ type: "", duration: 0, intensity: "medium" }],
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

// Build a filled-track gradient for range inputs (left side = light blue fill)
const getRangeFillStyle = (value, max, fillColor = '#4f46e5', trackColor = '#c7d2fe') => {
    const numericMax = Number(max);
    const safeMax =
        Number.isFinite(numericMax) && numericMax > 0 ? numericMax : 1;
    const numericValue = Number(value);
    const clampedValue = Number.isFinite(numericValue)
        ? Math.min(Math.max(numericValue, 0), safeMax)
        : 0;
    const percent = (clampedValue / safeMax) * 100;

    return {
        background: `linear-gradient(90deg, ${fillColor} ${percent}%, ${trackColor} ${percent}%)`,
    };
};

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

// True when an activity type is selected but its duration is missing
const hasActivityErrors = computed(() =>
    form.activities.some((a) => a.type && (!a.duration || a.duration <= 0)),
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
    await store.initialize();

    if (!isEditMode.value) return;

    // Always fetch the entry fresh from the server in edit mode to guarantee
    // up-to-date data (activities, meals, etc.) regardless of store cache state
    const entry = await store.fetchEntry(editEntryId.value);
    if (!entry) return;

    form.sleep = Number(entry.sleep ?? 7);
    form.stress = Number(entry.stress ?? 5);
    form.caffeine = Number(entry.caffeine ?? 0);
    form.activities = (entry.activities ?? []).map((a) => ({ ...a }));
    form.tobacco = Boolean(entry.tobacco);
    form.alcohol = Boolean(entry.alcohol);
    form.tobaccoTypes = entry.tobaccoTypes ?? { cigarette: false, vape: false };
    form.cigarettesPerDay = entry.cigarettesPerDay ?? null;
    form.vapeLiquidMl = entry.vapeLiquidMl ?? null;
    form.alcoholDrinks = entry.alcoholDrinks ?? null;
    form.sugar = entry.sugar ?? "medium";
    form.cupCount = 0;
    form.bottleCount = 0;
    form.customHydration = Number(entry.hydration ?? 0);
    form.meals = (entry.meals ?? []).map((m) => ({
        type: m.type || m.meal_type || "",
        label: m.label || m.description || "",
        calories: m.calories ?? null,
    }));
});

const addMeal = () => {
    if (!form.selectedMeal) return;
    if (!mealDraft.label.trim()) {
        mealError.value = "La description du repas est obligatoire.";
        return;
    }
    mealError.value = "";
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
    if (step.value === 2 && form.selectedMeal && !mealDraft.label.trim()) {
        mealError.value =
            "Veuillez décrire le repas sélectionné avant de continuer.";
        return;
    }
    mealError.value = "";
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

const addActivity = () => {
    form.activities.push({ type: "", duration: 0, intensity: "medium" });
};

const removeActivity = (index) => {
    form.activities.splice(index, 1);
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

// Calcule le niveau d'énergie (1-10) à partir du sommeil, du stress et de la caféine
function calculateEnergy(sleep, stress, caffeine) {
    const sleepScore = sleep >= 7 && sleep <= 9 ? 10
        : (sleep >= 6 || sleep === 10) ? 7
        : sleep >= 5 ? 5
        : sleep >= 4 ? 3
        : 1;
    const stressScore = Math.max(1, 10 - Number(stress));
    const caffeineBonus = caffeine >= 1 && caffeine <= 3 ? 1 : caffeine >= 6 ? -1 : 0;
    return Math.max(1, Math.min(10, Math.round(sleepScore * 0.5 + stressScore * 0.5 + caffeineBonus)));
}

// Build the data object to send to the API
function buildPayload() {
    return {
        sleep: Number(form.sleep),
        stress: Number(form.stress),
        energy: calculateEnergy(Number(form.sleep), Number(form.stress), Number(form.caffeine)),
        caffeine: Number(form.caffeine),
        hydration: Number(hydrationTotal.value.toFixed(1)),
        sugar: form.sugar,
        meals: [...form.meals],
        calories: mealsCaloriesTotal.value,
        activities: form.activities.filter((a) => a.type),
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
            await store.updateEntry(editEntryId.value, payload);
            router.push({
                name: "journal-history",
                query: { notice: "saved" },
            });
        } else {
            // Create a new entry
            await store.addEntry(payload);
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
}
.range-base::-webkit-slider-runnable-track {
    height: 8px;
    border-radius: 999px;
    background: transparent;
}

/* ── Sommeil (indigo) ── */
.range-sleep::-webkit-slider-thumb {
    -webkit-appearance: none;
    appearance: none;
    width: 22px;
    height: 22px;
    border: 2.5px solid #4f46e5;
    border-radius: 999px;
    background: white;
    box-shadow:
        0 0 0 3px rgba(199, 210, 254, 0.8),
        0 2px 8px rgba(79, 70, 229, 0.3);
    margin-top: -7px;
}
.range-sleep::-moz-range-track {
    height: 8px;
    border: none;
    border-radius: 999px;
    background: #c7d2fe;
}
.range-sleep::-moz-range-progress {
    height: 8px;
    border: none;
    border-radius: 999px;
    background: #4f46e5;
}
.range-sleep::-moz-range-thumb {
    width: 22px;
    height: 22px;
    border: 2.5px solid #4f46e5;
    border-radius: 999px;
    background: white;
    box-shadow:
        0 0 0 3px rgba(199, 210, 254, 0.8),
        0 2px 8px rgba(79, 70, 229, 0.3);
}

/* ── Stress (rose) ── */
.range-stress::-webkit-slider-thumb {
    -webkit-appearance: none;
    appearance: none;
    width: 22px;
    height: 22px;
    border: 2.5px solid #f43f5e;
    border-radius: 999px;
    background: white;
    box-shadow:
        0 0 0 3px rgba(254, 205, 211, 0.8),
        0 2px 8px rgba(244, 63, 94, 0.3);
    margin-top: -7px;
}
.range-stress::-moz-range-track {
    height: 8px;
    border: none;
    border-radius: 999px;
    background: #fecdd3;
}
.range-stress::-moz-range-progress {
    height: 8px;
    border: none;
    border-radius: 999px;
    background: #f43f5e;
}
.range-stress::-moz-range-thumb {
    width: 22px;
    height: 22px;
    border: 2.5px solid #f43f5e;
    border-radius: 999px;
    background: white;
    box-shadow:
        0 0 0 3px rgba(254, 205, 211, 0.8),
        0 2px 8px rgba(244, 63, 94, 0.3);
}
</style>
