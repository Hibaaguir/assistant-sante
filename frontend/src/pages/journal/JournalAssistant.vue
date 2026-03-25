<template>
  <div class="mx-auto max-w-[1320px] p-4 sm:p-6 lg:p-8">
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
      <h2 class="text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">Nouvelle entrée</h2>
      <p class="mt-2 text-sm text-slate-600 sm:text-base">Renseignez vos indicateurs quotidiens pour un suivi simple, clair et régulier.</p>
    </div>
    <NotificationsEnLigne />

    <div class="mt-5 rounded-3xl border border-slate-200 bg-white p-4 shadow-sm sm:p-6">
      <IndicateurEtapes :current="step" :steps="steps" />

      <div class="mt-6 space-y-6">
        <div v-if="step === 1" class="space-y-6">
          <div class="rounded-2xl border border-violet-200 bg-violet-50/50 p-4 sm:p-5">
            <div class="space-y-2">
            <div class="flex items-center justify-between text-xl font-semibold">
              <span>Sommeil</span>
              <span>{{ form.sleep }}h</span>
            </div>
            <input v-model.number="form.sleep" type="range" min="0" max="12" class="range-base" />
            <div class="flex justify-between text-sm text-slate-500"><span>0h</span><span>12h</span></div>
            </div>
          </div>

          <div class="rounded-2xl border border-rose-200 bg-rose-50/50 p-4 sm:p-5">
            <div class="space-y-2">
            <div class="flex items-center justify-between text-xl font-semibold">
              <span>Niveau de stress</span>
              <span>{{ form.stress }}/10</span>
            </div>
            <input v-model.number="form.stress" type="range" min="0" max="10" class="range-base" />
            <div class="flex justify-between text-sm text-slate-500"><span>Faible</span><span>Élevé</span></div>
            </div>
          </div>

          <div class="rounded-2xl border border-emerald-200 bg-emerald-50/50 p-4 sm:p-5">
            <div class="space-y-2">
            <div class="flex items-center justify-between text-xl font-semibold">
              <span>Niveau d'énergie</span>
              <span>{{ STATIC_ENERGY }}/10</span>
            </div>
            <div class="rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-600">
              Valeur statique temporaire (prediction IA a venir)
            </div>
            </div>
          </div>
        </div>

        <div v-else-if="step === 2" class="space-y-6">
          <div class="rounded-2xl border border-emerald-200 bg-emerald-50/40 p-4 sm:p-5">
            <p class="text-xl font-semibold">Repas d'aujourd'hui</p>
            <p class="mt-1 text-sm text-slate-500">Les repas ajoutes ci-dessous seront enregistres dans votre journal du jour.</p>
            <div class="mt-3 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
              <button
                v-for="item in meals"
                :key="item.id"
                type="button"
                class="rounded-xl border px-4 py-3 text-sm font-semibold"
                :class="form.selectedMeal === item.id ? 'border-violet-500 bg-violet-50 text-violet-700' : 'border-slate-300 bg-white text-slate-700'"
                @click="form.selectedMeal = item.id"
              >
                <div v-html="item.icon" />
                <div>{{ item.label }}</div>
              </button>
            </div>

            <div v-if="form.selectedMeal" class="mt-4 rounded-xl border border-violet-200 bg-violet-50 p-3">
              <div class="mb-2 flex items-center justify-between text-sm font-semibold">
                <span>Ajouter : {{ selectedMealLabel }}</span>
                <button type="button" @click="form.selectedMeal = ''">×</button>
              </div>
              <input v-model="mealDraft.label" type="text" class="w-full rounded-lg border border-violet-300 px-3 py-2 text-sm" placeholder="Ex: Oeufs + pain complet" />
              <input v-model.number="mealDraft.calories" type="number" min="0" class="mt-2 w-full rounded-lg border border-violet-300 px-3 py-2 text-sm" placeholder="Calories (optionnel)" />
              <button type="button" class="mt-3 w-full rounded-lg bg-gradient-to-r from-[#2563eb] to-[#7c3aed] py-2 text-sm font-semibold text-white disabled:opacity-50" :disabled="!mealDraft.label.trim()" @click="ajouterRepas">Ajouter</button>
            </div>

            <div class="mt-4 rounded-xl border border-slate-300 bg-white p-3">
              <p class="text-sm font-semibold text-slate-700">Repas ajoutes ({{ form.meals.length }})</p>
              <ul v-if="form.meals.length" class="mt-2 space-y-2">
                <li v-for="(meal, index) in form.meals" :key="`${meal.type}-${meal.label}-${index}`" class="flex items-center justify-between rounded-lg border border-slate-200 px-3 py-2 text-sm">
                  <span class="text-slate-700">
                    <strong>{{ mealTypeLabel(meal.type) }}</strong> - {{ meal.label }}<span v-if="meal.calories !== null && meal.calories !== undefined"> ({{ meal.calories }} kcal)</span>
                  </span>
                  <button type="button" class="text-xs font-semibold text-red-600 hover:text-red-700" @click="supprimerRepas(index)">Supprimer</button>
                </li>
              </ul>
              <p v-else class="mt-2 text-sm text-slate-500">Aucun repas ajoute pour le moment.</p>
            </div>
          </div>

          <div class="rounded-2xl border border-orange-200 bg-orange-50/40 p-4 sm:p-5">
            <p class="text-xl font-semibold">Caféine (tasses)</p>
            <div class="mt-3 flex items-center gap-2">
              <div class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-700">
                {{ form.caffeine }} tasse(s)
              </div>
              <button type="button" class="rounded-lg bg-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-400" @click="ajusterCafeine(-1)">-</button>
              <button type="button" class="rounded-lg bg-gradient-to-r from-orange-500 to-rose-500 px-4 py-2 text-sm font-semibold text-white" @click="ajusterCafeine(1)">+</button>
            </div>
          </div>

          <div class="rounded-2xl border border-sky-200 bg-sky-50/40 p-4 sm:p-5">
            <p class="text-xl font-semibold">Hydratation</p>
            <div class="mt-3 grid grid-cols-1 gap-2 md:grid-cols-2">
              <div class="rounded-xl border border-sky-300 bg-sky-50 p-3">
                <div class="flex items-start justify-between">
                  <div>
                    <p class="text-sm font-semibold text-slate-800">Verre</p>
                    <p class="text-xs text-slate-500">0.5L par unité</p>
                  </div>
                  <svg viewBox="0 0 24 24" class="h-4 w-4 text-sky-600" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M7 3h10l-1 16a2 2 0 0 1-2 2h-4a2 2 0 0 1-2-2L7 3z"/><path d="M8 8h8"/></svg>
                </div>
                <div class="mt-3 flex items-center justify-between">
                  <button type="button" class="h-7 w-7 rounded-md border bg-slate-100 text-sm text-slate-500" @click="ajusterVerres(-1)">-</button>
                  <span class="text-lg font-bold">{{ form.cupCount }}</span>
                  <button type="button" class="h-7 w-7 rounded-md bg-indigo-600 text-sm font-semibold text-white" @click="ajusterVerres(1)">+</button>
                </div>
              </div>

              <div class="rounded-xl border border-cyan-300 bg-cyan-50 p-3">
                <div class="flex items-start justify-between">
                  <div>
                    <p class="text-sm font-semibold text-slate-800">Bouteille</p>
                    <p class="text-xs text-slate-500">1.5L par unité</p>
                  </div>
                  <svg viewBox="0 0 24 24" class="h-4 w-4 text-cyan-600" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M10 3h4v3h2v15H8V6h2z"/><path d="M10 11h4"/></svg>
                </div>
                <div class="mt-3 flex items-center justify-between">
                  <button type="button" class="h-7 w-7 rounded-md border bg-slate-100 text-sm text-slate-500" @click="ajusterBouteilles(-1)">-</button>
                  <span class="text-lg font-bold">{{ form.bottleCount }}</span>
                  <button type="button" class="h-7 w-7 rounded-md bg-cyan-600 text-sm font-semibold text-white" @click="ajusterBouteilles(1)">+</button>
                </div>
              </div>
            </div>
            <div class="mt-3">
              <input v-model.number="form.customHydration" type="number" step="0.1" min="0" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm" placeholder="Autre quantité (en litres)" />
            </div>
            <div class="mt-2 rounded-lg bg-gradient-to-r from-[#2563eb] to-[#7c3aed] px-3 py-2 text-sm font-semibold text-white">
              <div class="flex items-center justify-between">
                <span>Total</span>
                <span>{{ hydrationTotal.toFixed(1) }} L</span>
              </div>
            </div>
          </div>

          <div class="rounded-2xl border border-pink-200 bg-pink-50/40 p-4 sm:p-5">
            <p class="text-xl font-semibold">Apport en sucre</p>
            <div class="mt-3 inline-flex min-w-[160px] flex-col rounded-xl border px-3 py-2 text-left" :class="sugarBadgeClass">
              <span class="text-sm font-semibold">{{ sugarLabel[form.sugar] }}</span>
              <span class="text-[11px] opacity-80">Détecté automatiquement</span>
            </div>
          </div>
        </div>

        <div v-else class="space-y-6">
          <div class="rounded-2xl border border-indigo-200 bg-indigo-50/40 p-4 sm:p-5">
            <label class="text-xl font-semibold" for="activity">Type d'activité</label>
            <select
              id="activity"
              v-model="form.activityType"
              class="mt-2 w-full rounded-lg border border-slate-300 bg-white px-3 py-3 text-sm"
              @change="gererSelectionActivite"
            >
              <option disabled value="">Sélectionnez une activité</option>
              <option v-for="item in activities" :key="item" :value="item">{{ item }}</option>
              <option value="__add_new__">+ Ajouter une activité</option>
            </select>

            <div v-if="showNewActivityForm" class="mt-3 rounded-xl border border-blue-300 bg-blue-50 p-3">
              <div class="mb-2 flex items-center justify-between">
                <p class="text-sm font-semibold text-slate-700">Nouvelle activité</p>
                <button
                  type="button"
                  class="text-sm font-semibold text-slate-500 hover:text-slate-700"
                  @click="annulerNouvelleActivite"
                >
                  ×
                </button>
              </div>
              <div class="flex items-center gap-2">
                <input
                  v-model="newActivityName"
                  type="text"
                  class="w-full rounded-lg border border-blue-300 bg-white px-3 py-2 text-sm"
                  placeholder="Nom de l'activité"
                />
                <button
                  type="button"
                  class="rounded-lg bg-gradient-to-r from-[#2563eb] to-[#7c3aed] px-4 py-2 text-sm font-semibold text-white disabled:cursor-not-allowed disabled:opacity-50"
                  :disabled="!newActivityName.trim()"
                  @click="ajouterNouvelleActivite"
                >
                  Valider
                </button>
              </div>
            </div>
          </div>

          <div class="rounded-2xl border border-emerald-200 bg-emerald-50/40 p-4 sm:p-5">
            <label class="text-xl font-semibold" for="duration">
              Durée (minutes)
              <span v-if="isDurationRequired" class="ml-1 text-red-500">*</span>
            </label>
            <input
              id="duration"
              v-model.number="form.activityDuration"
              type="number"
              min="0"
              class="mt-2 w-full rounded-lg border bg-white px-3 py-3 text-sm"
              :class="submitAttempted && activityErrors.duration ? 'border-red-400 focus:border-red-500 focus:ring-red-200' : 'border-slate-300'"
            />
            <p v-if="submitAttempted && activityErrors.duration" class="mt-1 text-sm text-red-500">{{ activityErrors.duration }}</p>
          </div>

          <div class="space-y-4 rounded-2xl border border-slate-200 bg-slate-50 p-4 sm:p-5">
            <div>
              <p class="text-xl font-semibold">Intensité</p>
              <div class="mt-3 grid grid-cols-3 gap-2">
                <button
                  v-for="value in intensityOptions"
                  :key="`intensity-${value}`"
                  type="button"
                  class="rounded-xl border py-3 text-sm font-semibold"
                  :class="form.intensity === value ? 'border-emerald-500 bg-emerald-50 text-emerald-700' : 'border-slate-300 bg-white'"
                  @click="form.intensity = value"
                >
                  {{ intensityLabel[value] }}
                </button>
              </div>
            </div>

            <p class="text-xl font-semibold">Habitude</p>

            <div class="rounded-xl bg-slate-100 p-4 ring-1 ring-slate-200">
              <button
                type="button"
                class="flex w-full items-center justify-between text-sm font-semibold text-slate-700"
                @click="basculerTabac"
              >
                <span>Tabac</span>
                <span
                  class="relative h-6 w-10 rounded-full transition-colors"
                  :class="form.tobacco ? 'bg-pink-600' : 'bg-slate-300'"
                  aria-hidden="true"
                >
                  <span
                    class="absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white shadow-sm transition-transform"
                    :class="form.tobacco ? 'translate-x-4' : 'translate-x-0'"
                  />
                </span>
              </button>

              <div v-if="form.tobacco" class="mt-4 space-y-3">
                <div>
                  <label class="text-xs font-semibold text-slate-700">
                    Type <span class="text-red-500">*</span>
                  </label>
                  <div class="mt-2 flex flex-wrap gap-2">
                    <button
                      type="button"
                      class="rounded-lg border px-3 py-2 text-sm font-semibold transition-colors"
                      :class="form.tobaccoTypes.cigarette ? 'border-indigo-300 bg-indigo-50 text-indigo-700' : 'border-slate-300 bg-white text-slate-700'"
                      @click="basculerTypeTabac('cigarette')"
                    >
                      Cigarette
                    </button>
                    <button
                      type="button"
                      class="rounded-lg border px-3 py-2 text-sm font-semibold transition-colors"
                      :class="form.tobaccoTypes.vape ? 'border-indigo-300 bg-indigo-50 text-indigo-700' : 'border-slate-300 bg-white text-slate-700'"
                      @click="basculerTypeTabac('vape')"
                    >
                      Vape
                    </button>
                  </div>
                  <p v-if="submitAttempted && tobaccoErrors.types" class="mt-1 text-sm text-red-500">{{ tobaccoErrors.types }}</p>
                </div>

                <div v-if="form.tobaccoTypes.cigarette">
                  <label class="text-xs font-semibold text-slate-700">
                    Nombre de cigarettes par jour <span class="text-red-500">*</span>
                  </label>
                  <input v-model.number="form.cigarettesPerDay" type="number" min="0" placeholder="Ex: 5" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-200" />
                  <p v-if="submitAttempted && tobaccoErrors.cigarettesPerDay" class="mt-1 text-sm text-red-500">{{ tobaccoErrors.cigarettesPerDay }}</p>
                </div>

                <div v-if="form.tobaccoTypes.vape" class="space-y-3">
                  <div>
                    <label class="text-xs font-semibold text-slate-700">
                      Nombre de taffes prise par jour <span class="text-red-500">*</span>
                    </label>
                    <input v-model.number="form.vapeLiquidMl" type="number" min="0" placeholder="Ex: 3" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-200" />
                    <p v-if="submitAttempted && tobaccoErrors.vapeLiquidMl" class="mt-1 text-sm text-red-500">{{ tobaccoErrors.vapeLiquidMl }}</p>
                  </div>
                </div>
              </div>
            </div>

            <div class="rounded-xl bg-slate-100 p-4 ring-1 ring-slate-200">
              <button
                type="button"
                class="flex w-full items-center justify-between text-sm font-semibold text-slate-700"
                @click="basculerAlcool"
              >
                <span>Alcool <span v-if="form.alcohol" class="text-red-500">*</span></span>
                <span
                  class="relative h-6 w-10 rounded-full transition-colors"
                  :class="form.alcohol ? 'bg-pink-600' : 'bg-slate-300'"
                  aria-hidden="true"
                >
                  <span
                    class="absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white shadow-sm transition-transform"
                    :class="form.alcohol ? 'translate-x-4' : 'translate-x-0'"
                  />
                </span>
              </button>

              <div v-if="form.alcohol" class="mt-4">
                <label class="text-xs font-semibold text-slate-700">
                  Nombre de verres par jour <span class="text-red-500">*</span>
                </label>
                <input v-model.number="form.alcoholDrinks" type="number" min="0" placeholder="Ex: 2" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700" />
                <p v-if="submitAttempted && alcoholErrors.drinks" class="mt-1 text-sm text-red-500">{{ alcoholErrors.drinks }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="mt-6 flex items-center justify-between gap-3">
      <button
        type="button"
        class="rounded-xl border px-5 py-3 text-sm font-semibold transition-colors"
        :class="step === 1 ? 'border-slate-300 bg-white text-slate-700 hover:bg-slate-50' : 'border-blue-300 bg-blue-50 text-blue-700 hover:bg-blue-100'"
        @click="allerPrecedent"
      >
        {{ step === 1 ? '‹ Retour' : '‹ Précédent' }}
      </button>
      <button v-if="step < 3" type="button" class="rounded-xl bg-gradient-to-r from-[#2563eb] to-[#7c3aed] px-6 py-3 text-sm font-semibold text-white shadow-md shadow-indigo-500/20" @click="allerSuivant">Suivant ›</button>
      <template v-else>
        <div v-if="isEditMode" class="flex items-center gap-2">
          <button
            type="button"
            class="rounded-xl border border-amber-300 bg-amber-50 px-6 py-3 text-sm font-semibold text-amber-700 transition-colors hover:bg-amber-100"
            @click="annulerModification"
          >
            Annuler les modifications
          </button>
          <button type="button" class="rounded-xl bg-gradient-to-r from-[#2563eb] to-[#7c3aed] px-6 py-3 text-sm font-semibold text-white shadow-md shadow-indigo-500/20" @click="enregistrer">
            ✓ Enregistrer les modifications
          </button>
        </div>
        <button v-else type="button" class="rounded-xl bg-gradient-to-r from-[#2563eb] to-[#7c3aed] px-6 py-3 text-sm font-semibold text-white shadow-md shadow-indigo-500/20" @click="enregistrer">
          ✓ Enregistrer la journée
        </button>
      </template>
    </div>
    <p v-if="saveError" class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
      {{ saveError }}
    </p>

    <DialogueConfirmation
      :open="confirmDeleteMealOpen"
      title="Confirmer la suppression"
      message="Voulez-vous supprimer ce repas ?"
      confirm-label="Supprimer"
      cancel-label="Annuler"
      @confirm="confirmSupprimerRepas"
      @cancel="cancelSupprimerRepas"
    />
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import IndicateurEtapes from '@/components/journal/IndicateurEtapes.vue'
import { useJournalStore } from '@/stores/journal'
import { useNotificationsStore } from '@/stores/notifications'
import NotificationsEnLigne from '@/components/ui/NotificationsEnLigne.vue'
import DialogueConfirmation from '@/components/ui/DialogueConfirmation.vue'

const route = useRoute()
const router = useRouter()
const store = useJournalStore()
const notifications = useNotificationsStore()
const editEntryId = computed(() => String(route.query.edit || ''))
const isEditMode = computed(() => Boolean(editEntryId.value))

const steps = [
  { id: 1, label: 'Corps & énergie' },
  { id: 2, label: 'Nutrition' },
  { id: 3, label: 'Habitudes & sport' }
]

const meals = [
  { id: 'breakfast', label: 'Petit déjeuner', icon: '&#127749;' },
  { id: 'lunch', label: 'Déjeuner', icon: '&#127869;' },
  { id: 'dinner', label: 'Dîner', icon: '&#127769;' },
  { id: 'snack', label: 'Snacks', icon: '&#127813;' }
]

const activities = ref(['Marche', 'Course', 'Vélo', 'Natation', 'Yoga', 'Musculation', 'Sport collectif'])
const sugarLabel = { low: 'Faible', medium: 'Modéré', high: 'Élevé' }
const intensityLabel = { light: 'Légère', medium: 'Modérée', high: 'Intense' }
const intensityOptions = ['light', 'medium', 'high']

const step = ref(1)
const submitAttempted = ref(false)
const saveError = ref('')
const STATIC_ENERGY = 7
const mealDraft = reactive({ label: '', calories: null })
const showNewActivityForm = ref(false)
const newActivityName = ref('')
const confirmDeleteMealOpen = ref(false)
const pendingDeleteMealIndex = ref(-1)

const form = reactive({
  sleep: 7,
  stress: 5,
  energy: STATIC_ENERGY,
  selectedMeal: '',
  sugar: 'low',
  caffeine: 0,
  hydration: 0.5,
  customHydration: null,
  cupCount: 0,
  bottleCount: 0,
  meals: [],
  activityType: '',
  activityDuration: 0,
  intensity: 'medium',
  tobacco: false,
  alcohol: false,
  tobaccoTypes: { cigarette: false, vape: false },
  cigarettesPerDay: null,
  vapeLiquidMl: null,
  alcoholDrinks: null
})

const selectedMealLabel = computed(() => meals.find((item) => item.id === form.selectedMeal)?.label ?? '')
const hydrationTotal = computed(() => {
  const extra = Math.max(0, Number(form.customHydration) || 0)
  return (form.cupCount * 0.5) + (form.bottleCount * 1.5) + extra
})
const SUGAR_BADGE = {
  high:   'border-rose-300 bg-rose-100 text-rose-800',
  medium: 'border-amber-300 bg-amber-100 text-amber-800',
  low:    'border-emerald-300 bg-emerald-100 text-emerald-800'
}
const sugarBadgeClass = computed(() => SUGAR_BADGE[form.sugar] ?? SUGAR_BADGE.low)
const tobaccoErrors = computed(() => {
  const errors = {
    types: null,
    cigarettesPerDay: null,
    vapeLiquidMl: null
  }
  if (!form.tobacco) return errors
  const hasCigarette = form.tobaccoTypes.cigarette
  const hasVape = form.tobaccoTypes.vape
  if (!hasCigarette && !hasVape) errors.types = 'Veuillez selectionner un type.'
  if (hasCigarette && (form.cigarettesPerDay === null || form.cigarettesPerDay <= 0)) {
    errors.cigarettesPerDay = 'Veuillez remplir le champ.'
  }
  if (hasVape && (form.vapeLiquidMl === null || form.vapeLiquidMl <= 0)) {
    errors.vapeLiquidMl = 'Veuillez remplir le champ.'
  }
  return errors
})
const hasTobaccoErrors = computed(() => Object.values(tobaccoErrors.value).some(Boolean))
const isDurationRequired = computed(() => Boolean(form.activityType))
const activityErrors = computed(() => {
  const errors = {
    duration: null
  }
  if (!isDurationRequired.value) return errors

  const duration = Number(form.activityDuration)
  if (!Number.isFinite(duration) || duration <= 0) {
    errors.duration = 'Veuillez remplir la durée (minutes).'
  }
  return errors
})
const hasActivityErrors = computed(() => Object.values(activityErrors.value).some(Boolean))
const alcoholErrors = computed(() => {
  const errors = {
    drinks: null
  }
  if (!form.alcohol) return errors
  if (form.alcoholDrinks === null || form.alcoholDrinks <= 0) {
    errors.drinks = 'Veuillez remplir le champ.'
  }
  return errors
})
const hasAlcoholErrors = computed(() => Object.values(alcoholErrors.value).some(Boolean))

onMounted(async () => {
  await store.initialiser()
  if (!isEditMode.value) return

  const entree = store.obtenirParId(editEntryId.value)
  if (!entree) return

  form.sleep = Number(entree.sleep ?? 7)
  form.stress = Number(entree.stress ?? 5)
  form.energy = STATIC_ENERGY
  form.sugar = entree.sugar ?? 'low'
  form.caffeine = Number(entree.caffeine ?? 0)
  form.meals = Array.isArray(entree.meals) ? [...entree.meals] : []
  form.activityType = entree.activityType ?? ''
  form.activityDuration = Number(entree.activityDuration ?? 0)
  form.intensity = entree.intensity ?? 'medium'
  form.tobacco = Boolean(entree.tobacco)
  form.alcohol = Boolean(entree.alcohol)
  form.tobaccoTypes = entree.tobaccoTypes ?? { cigarette: false, vape: false }
  form.cigarettesPerDay = entree.cigarettesPerDay ?? null
  form.vapeLiquidMl = entree.vapeLiquidMl ?? null
  form.alcoholDrinks = entree.alcoholDrinks ?? null

  // Preserve previously saved hydration in a simple way while keeping the current UI.
  form.cupCount = 0
  form.bottleCount = 0
  form.customHydration = Number(entree.hydration ?? 0)
})

const ajouterRepas = () => {
  if (!form.selectedMeal || !mealDraft.label.trim()) return

  form.meals.push({
    type: form.selectedMeal,
    label: mealDraft.label.trim(),
    calories: mealDraft.calories
  })

  mealDraft.label = ''
  mealDraft.calories = null
  form.selectedMeal = ''
  notifications.actionAjoutee()
}

const mealTypeLabel = (type) => {
  return meals.find((item) => item.id === type)?.label ?? type
}

const supprimerRepas = (index) => {
  pendingDeleteMealIndex.value = index
  confirmDeleteMealOpen.value = true
}

const confirmSupprimerRepas = () => {
  const index = pendingDeleteMealIndex.value
  confirmDeleteMealOpen.value = false
  pendingDeleteMealIndex.value = -1
  if (index < 0 || index >= form.meals.length) return
  form.meals.splice(index, 1)
  notifications.actionSupprimee()
}

const cancelSupprimerRepas = () => {
  confirmDeleteMealOpen.value = false
  pendingDeleteMealIndex.value = -1
  notifications.actionAnnulee()
}

const ajusterCafeine = (delta) => {
  form.caffeine = Math.min(20, Math.max(0, Number(form.caffeine || 0) + delta))
}

const ajusterVerres = (delta) => {
  form.cupCount = Math.max(0, form.cupCount + delta)
}

const ajusterBouteilles = (delta) => {
  form.bottleCount = Math.max(0, form.bottleCount + delta)
}

const allerSuivant = () => {
  submitAttempted.value = false
  step.value = Math.min(3, step.value + 1)
}

const allerPrecedent = () => {
  if (step.value === 1) {
    router.push({ name: 'journal' })
    return
  }
  step.value = Math.max(1, step.value - 1)
}

const gererSelectionActivite = () => {
  if (form.activityType === '__add_new__') {
    showNewActivityForm.value = true
    form.activityType = ''
    return
  }
  showNewActivityForm.value = false
}

const annulerNouvelleActivite = () => {
  showNewActivityForm.value = false
  newActivityName.value = ''
  notifications.actionAnnulee()
}

const ajouterNouvelleActivite = () => {
  const name = newActivityName.value.trim()
  if (!name) return
  if (!activities.value.includes(name)) {
    activities.value.push(name)
  }
  form.activityType = name
  showNewActivityForm.value = false
  newActivityName.value = ''
  notifications.actionAjoutee()
}

const basculerTabac = () => {
  form.tobacco = !form.tobacco
  if (!form.tobacco) {
    form.tobaccoTypes.cigarette = false
    form.tobaccoTypes.vape = false
    form.cigarettesPerDay = null
    form.vapeLiquidMl = null
    submitAttempted.value = false
  }
}

const basculerTypeTabac = (type) => {
  form.tobaccoTypes[type] = !form.tobaccoTypes[type]
  if (!form.tobaccoTypes.cigarette) form.cigarettesPerDay = null
  if (!form.tobaccoTypes.vape) form.vapeLiquidMl = null
}

const basculerAlcool = () => {
  form.alcohol = !form.alcohol
  if (!form.alcohol) {
    form.alcoholDrinks = null
  }
}

const annulerModification = async () => {
  if (!isEditMode.value) return
  await store.chargerEntrees()
  router.push({ name: 'historique-journal', query: { notice: 'canceled' } })
}

const enregistrer = async () => {
  submitAttempted.value = true
  if (hasActivityErrors.value || hasTobaccoErrors.value || hasAlcoholErrors.value) return
  saveError.value = ''
  try {
    const payload = {
      sleep: Number(form.sleep),
      stress: Number(form.stress),
      energy: STATIC_ENERGY,
      sugar: form.sugar,
      caffeine: Number(form.caffeine),
      hydration: Number(hydrationTotal.value.toFixed(1)),
      meals: [...form.meals],
      activityType: form.activityType || 'Marche',
      activityDuration: Number.isFinite(Number(form.activityDuration)) ? Number(form.activityDuration) : 0,
      intensity: form.intensity,
      tobacco: form.tobacco,
      alcohol: form.alcohol,
      tobaccoTypes: {
        cigarette: form.tobacco ? form.tobaccoTypes.cigarette : false,
        vape: form.tobacco ? form.tobaccoTypes.vape : false
      },
      cigarettesPerDay: form.tobacco && form.tobaccoTypes.cigarette ? Number(form.cigarettesPerDay) : null,
      vapeLiquidMl: form.tobacco && form.tobaccoTypes.vape ? Number(form.vapeLiquidMl) : null,
      alcoholDrinks: form.alcohol && form.alcoholDrinks !== null ? Number(form.alcoholDrinks) : 0
    }

    if (isEditMode.value) {
      await store.mettreAJourEntree(editEntryId.value, payload)
      router.push({ name: 'historique-journal', query: { notice: 'saved' } })
      return
    }

    await store.ajouterEntree(payload)
    notifications.actionAjoutee()
    router.push({ name: 'journal' })
  } catch (error) {
    console.error('Journal save error:', error)
    if (error?.response?.status === 422 && error?.response?.data?.errors) {
      const firstError = Object.values(error.response.data.errors)?.[0]
      saveError.value = Array.isArray(firstError) ? firstError[0] : 'Validation invalide.'
      notifications.avertissement(saveError.value)
      return
    }
    if (error?.response?.status === 401) {
      saveError.value = 'Session expiree. Reconnectez-vous.'
      notifications.erreur(saveError.value)
      return
    }
    saveError.value = "Erreur lors de l'enregistrement du journal."
    notifications.erreur(saveError.value)
  }
}
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
  box-shadow: 0 0 0 3px rgba(224, 231, 255, 0.95), 0 2px 8px rgba(55, 48, 163, 0.45);
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
  box-shadow: 0 0 0 3px rgba(224, 231, 255, 0.95), 0 2px 8px rgba(55, 48, 163, 0.45);
}
</style>
