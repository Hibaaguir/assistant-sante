<template>
  <div class="mx-auto max-w-[1320px] p-4 sm:p-6 lg:p-8">
    <div>
      <h2 class="text-4xl font-bold tracking-tight text-slate-900 sm:text-5xl">Nouvelle entrée</h2>
      <p class="mt-2 text-base text-slate-600">Remplissez votre journal quotidien</p>
    </div>

    <div class="mt-5 rounded-2xl border border-slate-300 bg-slate-100 p-4 sm:p-6">
      <IndicateurEtapes :current="step" :steps="steps" />

      <div class="mt-6 space-y-6">
        <div v-if="step === 1" class="space-y-6">
          <div class="space-y-2">
            <div class="flex items-center justify-between text-xl font-semibold">
              <span>Sommeil <span aria-hidden="true">&#128522;</span></span>
              <span>{{ form.sleep }}h</span>
            </div>
            <input v-model.number="form.sleep" type="range" min="0" max="12" class="range-base range-blue" />
            <div class="flex justify-between text-sm text-slate-500"><span>0h</span><span>12h</span></div>
          </div>

          <div class="space-y-2">
            <div class="flex items-center justify-between text-xl font-semibold">
              <span>Niveau de stress <span aria-hidden="true">&#128563;</span></span>
              <span>{{ form.stress }}/10</span>
            </div>
            <input v-model.number="form.stress" type="range" min="0" max="10" class="range-base range-violet" />
            <div class="flex justify-between text-sm text-slate-500"><span>Faible</span><span>Élevé</span></div>
          </div>

          <div class="space-y-2">
            <div class="flex items-center justify-between text-xl font-semibold">
              <span>Niveau d'énergie <span aria-hidden="true">&#128522;</span></span>
              <span>{{ STATIC_ENERGY }}/10</span>
            </div>
            <div class="rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-600">
              Valeur statique temporaire (prediction IA a venir)
            </div>
          </div>
        </div>

        <div v-else-if="step === 2" class="space-y-6">
          <div>
            <p class="text-xl font-semibold">Repas d'aujourd'hui</p>
            <p class="mt-1 text-sm text-slate-500">Les repas ajoutes ci-dessous seront enregistres dans votre journal du jour.</p>
            <div class="mt-3 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
              <button
                v-for="item in meals"
                :key="item.id"
                type="button"
                class="rounded-xl border px-4 py-3 text-sm font-semibold"
                :class="form.selectedMeal === item.id ? 'border-blue-500 bg-blue-50 text-blue-700' : 'border-slate-300 bg-white text-slate-700'"
                @click="form.selectedMeal = item.id"
              >
                <div v-html="item.icon" />
                <div>{{ item.label }}</div>
              </button>
            </div>

            <div v-if="form.selectedMeal" class="mt-4 rounded-xl border border-blue-200 bg-blue-50 p-3">
              <div class="mb-2 flex items-center justify-between text-sm font-semibold">
                <span>Ajouter : {{ selectedMealLabel }}</span>
                <button type="button" @click="form.selectedMeal = ''">×</button>
              </div>
              <input v-model="mealDraft.label" type="text" class="w-full rounded-lg border border-blue-300 px-3 py-2 text-sm" placeholder="Ex: Oeufs + pain complet" />
              <input v-model.number="mealDraft.calories" type="number" min="0" class="mt-2 w-full rounded-lg border border-blue-300 px-3 py-2 text-sm" placeholder="Calories (optionnel)" />
              <button type="button" class="mt-3 w-full rounded-lg bg-gradient-to-r from-blue-500 to-indigo-500 py-2 text-sm font-semibold text-white disabled:opacity-50" :disabled="!mealDraft.label.trim()" @click="ajouterRepas">Ajouter</button>
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

          <div>
            <p class="text-xl font-semibold">Caféine (tasses) <span aria-hidden="true">&#9749;</span></p>
            <div class="mt-3 flex items-center gap-2">
              <div class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-700">
                {{ form.caffeine }} tasse(s)
              </div>
              <button type="button" class="rounded-lg bg-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-400" @click="diminuerCafeine">-</button>
              <button type="button" class="rounded-lg bg-orange-500 px-4 py-2 text-sm font-semibold text-white hover:bg-orange-600" @click="augmenterCafeine">+</button>
            </div>
          </div>

          <div>
            <p class="text-xl font-semibold">Hydratation <span aria-hidden="true">&#128167;</span></p>
            <div class="mt-3 grid grid-cols-1 gap-2 md:grid-cols-2">
              <div class="rounded-xl border border-sky-300 bg-sky-50 p-3">
                <div class="flex items-start justify-between">
                  <div>
                    <p class="text-sm font-semibold text-slate-800">Verre</p>
                    <p class="text-xs text-slate-500">0.5L par unité</p>
                  </div>
                  <span class="text-sm">🥛</span>
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
                  <span class="text-sm">🧴</span>
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
            <div class="mt-2 rounded-lg bg-gradient-to-r from-blue-600 to-indigo-600 px-3 py-2 text-sm font-semibold text-white">
              <div class="flex items-center justify-between">
                <span>Total</span>
                <span>{{ hydrationTotal.toFixed(1) }} L</span>
              </div>
            </div>
          </div>

          <div>
            <p class="text-xl font-semibold">Apport en sucre</p>
            <div class="mt-3 inline-flex min-w-[160px] flex-col rounded-xl border px-3 py-2 text-left" :class="sugarBadgeClass">
              <span class="text-sm font-semibold">{{ sugarLabel[form.sugar] }}</span>
              <span class="text-[11px] opacity-80">Détecté automatiquement</span>
            </div>
          </div>
        </div>

        <div v-else class="space-y-6">
          <div>
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
                  class="rounded-lg bg-indigo-400 px-4 py-2 text-sm font-semibold text-white disabled:cursor-not-allowed disabled:opacity-50"
                  :disabled="!newActivityName.trim()"
                  @click="ajouterNouvelleActivite"
                >
                  Valider
                </button>
              </div>
            </div>
          </div>

          <div>
            <label class="text-xl font-semibold" for="duration">Durée (minutes)</label>
            <input id="duration" v-model.number="form.activityDuration" type="number" min="0" class="mt-2 w-full rounded-lg border border-slate-300 bg-white px-3 py-3 text-sm" />
          </div>

          <div>
            <p class="text-xl font-semibold">Intensité</p>
            <div class="mt-3 grid grid-cols-3 gap-2">
              <button v-for="value in intensityOptions" :key="value" type="button" class="rounded-xl border py-3 text-sm font-semibold" :class="form.intensity === value ? 'border-emerald-500 bg-emerald-50 text-emerald-700' : 'border-slate-300 bg-white'" @click="form.intensity = value">{{ intensityLabel[value] }}</button>
            </div>
          </div>

          <div class="space-y-4">
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
                  <label class="text-xs font-semibold text-slate-700">Type</label>
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
                  <label class="text-xs font-semibold text-slate-700">Nombre de cigarettes par jour</label>
                  <input v-model.number="form.cigarettesPerDay" type="number" min="0" placeholder="Ex: 5" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-200" />
                  <p v-if="submitAttempted && tobaccoErrors.cigarettesPerDay" class="mt-1 text-sm text-red-500">{{ tobaccoErrors.cigarettesPerDay }}</p>
                </div>

                <div v-if="form.tobaccoTypes.vape" class="space-y-3">
                  <div>
                    <label class="text-xs font-semibold text-slate-700">Fréquence</label>
                    <select v-model="form.vapeFrequency" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                      <option :value="null">Sélectionnez</option>
                      <option value="Par semaine">Par semaine</option>
                      <option value="Par mois">Par mois</option>
                    </select>
                    <p v-if="submitAttempted && tobaccoErrors.vapeFrequency" class="mt-1 text-sm text-red-500">{{ tobaccoErrors.vapeFrequency }}</p>
                  </div>
                  <div>
                    <label class="text-xs font-semibold text-slate-700">Nombre de liquide</label>
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
                <span>Alcool</span>
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
                <label class="text-xs font-semibold text-slate-700">Nombre de verres par jour</label>
                <input v-model.number="form.alcoholDrinks" type="number" min="0" placeholder="Ex: 2" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700" />
                <p v-if="submitAttempted && alcoholErrors.drinks" class="mt-1 text-sm text-red-500">{{ alcoholErrors.drinks }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="mt-6 flex items-center justify-between">
      <button type="button" class="rounded-xl border px-5 py-3 text-sm font-semibold" :class="step === 1 ? 'cursor-not-allowed border-slate-200 bg-slate-100 text-slate-400' : 'border-slate-300 bg-white text-slate-700'" :disabled="step === 1" @click="allerPrecedent">‹ Précédent</button>
      <button v-if="step < 3" type="button" class="rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-3 text-sm font-semibold text-white" @click="allerSuivant">Suivant ›</button>
      <button v-else type="button" class="rounded-xl bg-emerald-600 px-6 py-3 text-sm font-semibold text-white" @click="enregistrer">
        {{ isEditMode ? '✓ Enregistrer les modifications' : '✓ Enregistrer la journée' }}
      </button>
    </div>
    <p v-if="saveError" class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
      {{ saveError }}
    </p>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import IndicateurEtapes from '../../components/journal/IndicateurEtapes.vue'
import { useJournalStore } from '../../stores/journal'

const route = useRoute()
const router = useRouter()
const store = useJournalStore()
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
  vapeFrequency: null,
  vapeLiquidMl: null,
  alcoholDrinks: null
})

const selectedMealLabel = computed(() => meals.find((item) => item.id === form.selectedMeal)?.label ?? '')
const hydrationTotal = computed(() => {
  const extra = typeof form.customHydration === 'number' && form.customHydration > 0 ? form.customHydration : 0
  return (form.cupCount * 0.5) + (form.bottleCount * 1.5) + extra
})
const sugarBadgeClass = computed(() => {
  if (form.sugar === 'high') return 'border-rose-300 bg-rose-100 text-rose-800'
  if (form.sugar === 'medium') return 'border-amber-300 bg-amber-100 text-amber-800'
  return 'border-emerald-300 bg-emerald-100 text-emerald-800'
})
const tobaccoErrors = computed(() => {
  const errors = {
    types: null,
    cigarettesPerDay: null,
    vapeFrequency: null,
    vapeLiquidMl: null
  }
  if (!form.tobacco) return errors
  const hasCigarette = form.tobaccoTypes.cigarette
  const hasVape = form.tobaccoTypes.vape
  if (!hasCigarette && !hasVape) errors.types = 'Veuillez selectionner un type.'
  if (hasCigarette && (form.cigarettesPerDay === null || form.cigarettesPerDay < 0)) {
    errors.cigarettesPerDay = 'Veuillez remplir le champ.'
  }
  if (hasVape && !form.vapeFrequency) {
    errors.vapeFrequency = 'Veuillez remplir le champ.'
  }
  if (hasVape && (form.vapeLiquidMl === null || form.vapeLiquidMl < 0)) {
    errors.vapeLiquidMl = 'Veuillez remplir le champ.'
  }
  return errors
})
const hasTobaccoErrors = computed(() => Object.values(tobaccoErrors.value).some(Boolean))
const alcoholErrors = computed(() => {
  const errors = {
    drinks: null
  }
  if (!form.alcohol) return errors
  if (form.alcoholDrinks === null || form.alcoholDrinks < 0) {
    errors.drinks = 'Veuillez remplir le champ.'
  }
  return errors
})
const hasAlcoholErrors = computed(() => Object.values(alcoholErrors.value).some(Boolean))

onMounted(async () => {
  await store.initialiser()
  if (!isEditMode.value) return

  const entry = store.obtenirParId(editEntryId.value)
  if (!entry) return

  form.sleep = Number(entry.sleep ?? 7)
  form.stress = Number(entry.stress ?? 5)
  form.energy = STATIC_ENERGY
  form.sugar = entry.sugar ?? 'low'
  form.caffeine = Number(entry.caffeine ?? 0)
  form.meals = Array.isArray(entry.meals) ? [...entry.meals] : []
  form.activityType = entry.activityType ?? ''
  form.activityDuration = Number(entry.activityDuration ?? 0)
  form.intensity = entry.intensity ?? 'medium'
  form.tobacco = Boolean(entry.tobacco)
  form.alcohol = Boolean(entry.alcohol)
  form.tobaccoTypes = entry.tobaccoTypes ?? { cigarette: false, vape: false }
  form.cigarettesPerDay = entry.cigarettesPerDay ?? null
  form.vapeFrequency = entry.vapeFrequency ?? null
  form.vapeLiquidMl = entry.vapeLiquidMl ?? null
  form.alcoholDrinks = entry.alcoholDrinks ?? null

  // Preserve previously saved hydration in a simple way while keeping the current UI.
  form.cupCount = 0
  form.bottleCount = 0
  form.customHydration = Number(entry.hydration ?? 0)
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
}

const mealTypeLabel = (type) => {
  return meals.find((item) => item.id === type)?.label ?? type
}

const supprimerRepas = (index) => {
  form.meals.splice(index, 1)
}

const augmenterCafeine = () => {
  form.caffeine = Math.min(20, Number(form.caffeine || 0) + 1)
}

const diminuerCafeine = () => {
  form.caffeine = Math.max(0, Number(form.caffeine || 0) - 1)
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
}

const basculerTabac = () => {
  form.tobacco = !form.tobacco
  if (!form.tobacco) {
    form.tobaccoTypes.cigarette = false
    form.tobaccoTypes.vape = false
    form.cigarettesPerDay = null
    form.vapeFrequency = null
    form.vapeLiquidMl = null
    submitAttempted.value = false
  }
}

const basculerTypeTabac = (type) => {
  if (type === 'cigarette') {
    form.tobaccoTypes.cigarette = !form.tobaccoTypes.cigarette
    if (!form.tobaccoTypes.cigarette) {
      form.cigarettesPerDay = null
    }
    return
  }
  form.tobaccoTypes.vape = !form.tobaccoTypes.vape
  if (!form.tobaccoTypes.vape) {
    form.vapeFrequency = null
    form.vapeLiquidMl = null
  }
}

const basculerAlcool = () => {
  form.alcohol = !form.alcohol
  if (!form.alcohol) {
    form.alcoholDrinks = null
  }
}

const enregistrer = async () => {
  submitAttempted.value = true
  if (hasTobaccoErrors.value || hasAlcoholErrors.value) return
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
      vapeFrequency: form.tobacco && form.tobaccoTypes.vape ? form.vapeFrequency : null,
      vapeLiquidMl: form.tobacco && form.tobaccoTypes.vape ? Number(form.vapeLiquidMl) : null,
      alcoholDrinks: form.alcohol && form.alcoholDrinks !== null ? Number(form.alcoholDrinks) : 0
    }

    if (isEditMode.value) {
      await store.mettreAJourEntree(editEntryId.value, payload)
      router.push({ name: 'journal-history' })
      return
    }

    await store.ajouterEntree(payload)
    router.push({ name: 'journal-home' })
  } catch (error) {
    console.error('Journal save error:', error)
    if (error?.response?.status === 422 && error?.response?.data?.errors) {
      const firstError = Object.values(error.response.data.errors)?.[0]
      saveError.value = Array.isArray(firstError) ? firstError[0] : 'Validation invalide.'
      return
    }
    if (error?.response?.status === 401) {
      saveError.value = 'Session expiree. Reconnectez-vous.'
      return
    }
    saveError.value = "Erreur lors de l'enregistrement du journal."
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
}

.range-base::-webkit-slider-runnable-track {
  height: 8px;
  border-radius: 999px;
}

.range-base::-webkit-slider-thumb {
  -webkit-appearance: none;
  appearance: none;
  width: 20px;
  height: 20px;
  border: 2px solid #052e16;
  border-radius: 999px;
  box-shadow: 0 0 0 3px rgba(220, 252, 231, 0.95), 0 2px 8px rgba(2, 44, 34, 0.45);
  margin-top: -6px;
}

.range-base::-moz-range-track {
  height: 8px;
  border: none;
  border-radius: 999px;
}

.range-base::-moz-range-thumb {
  width: 20px;
  height: 20px;
  border: 2px solid #052e16;
  border-radius: 999px;
  box-shadow: 0 0 0 3px rgba(220, 252, 231, 0.95), 0 2px 8px rgba(2, 44, 34, 0.45);
}

.range-blue {
  background: #bbf7d0;
}

.range-blue::-webkit-slider-runnable-track,
.range-blue::-moz-range-track {
  background: #bbf7d0;
}

.range-blue::-webkit-slider-thumb,
.range-blue::-moz-range-thumb {
  background: #052e16;
}

.range-violet {
  background: #bbf7d0;
}

.range-violet::-webkit-slider-runnable-track,
.range-violet::-moz-range-track {
  background: #bbf7d0;
}

.range-violet::-webkit-slider-thumb,
.range-violet::-moz-range-thumb {
  background: #052e16;
}

.range-emerald {
  background: #bbf7d0;
}

.range-emerald::-webkit-slider-runnable-track,
.range-emerald::-moz-range-track {
  background: #bbf7d0;
}

.range-emerald::-webkit-slider-thumb,
.range-emerald::-moz-range-thumb {
  background: #052e16;
}
</style>

