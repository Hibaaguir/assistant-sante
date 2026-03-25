<template>
  <section class="mt-8">
    <button type="button" class="inline-flex items-center gap-2 text-[14px] font-medium text-[#2454ff]" @click="$emit('back')">
      <IconeFlecheGauche class="h-[16px] w-[16px]" />
      Retour a la liste des patients
    </button>

    <div class="mt-7">
      <div class="flex items-start gap-5">
        <div class="flex h-[82px] w-[82px] shrink-0 items-center justify-center rounded-[24px] text-[19px] font-bold text-white" :style="{ backgroundColor: patient.avatarColor }">
          {{ patient.initials }}
        </div>

        <div>
          <div class="flex items-center gap-3">
            <h2 class="text-[28px] font-bold leading-none text-[#031a46]">{{ patient.name }}</h2>
            <span class="h-[13px] w-[13px] rounded-full" :style="{ backgroundColor: patient.dotColor }" />
          </div>

          <div class="mt-4 flex flex-wrap items-center gap-x-5 gap-y-2 text-[15px] text-[#41506b]">
            <span>{{ patient.age }} ans</span>
            <span>•</span>
            <span>{{ patient.gender }}</span>
            <span>•</span>
            <span class="inline-flex items-center gap-1.5">
              <IconeHorloge class="h-[16px] w-[16px]" />
              Derniere mise a jour : {{ patient.lastSeen }}
            </span>
          </div>

          <div class="mt-4 flex flex-wrap gap-3">
            <span v-for="tag in patient.detailTags" :key="tag.label" class="inline-flex h-[31px] items-center rounded-full border px-4 text-[14px] font-semibold" :class="tag.class">
              {{ tag.label }}
            </span>
          </div>

          <div class="mt-3 rounded-[10px] border border-[#dde3ee] bg-[#f8faff] px-3 py-2">
            <p class="text-[11px] font-semibold uppercase tracking-[0.04em] text-[#5f7190]">Observation</p>
            <p class="mt-0.5 text-[13px] leading-5 text-[#1f345c]">{{ observationResume }}</p>
          </div>
        </div>
      </div>
    </div>

    <section class="mt-6 rounded-[16px] border border-[#d4d9e1] bg-white p-3 shadow-[0_1px_3px_rgba(15,23,42,0.05)]">
      <article class="rounded-[12px] border border-[#d4d9e1] bg-[#fcfdff] p-3">
        <div class="flex flex-wrap items-center justify-between gap-2">
          <div class="flex items-center gap-2">
            <p class="text-[15px] font-bold text-[#041c49]">Observation generale du patient</p>
            <span class="rounded-full bg-[#f3f6fb] px-2 py-0.5 text-[11px] text-[#5c6d89]">Interne</span>
          </div>

          <button
            type="button"
            class="inline-flex h-[34px] items-center justify-center rounded-[12px] border border-[#d4d9e1] px-3 text-[13px] font-semibold text-[#40506a] transition hover:border-[#aeb9ca] hover:text-[#1a2c52]"
            @click="analysisObservationOpen = !analysisObservationOpen"
          >
            {{ analysisObservationOpen ? "Fermer" : "Ajouter" }}
          </button>
        </div>

        <div class="mt-1.5 flex items-center justify-end">
          <p v-if="analysisObservationSavedAt" class="text-[11px] text-[#6a7891]">Sauvegardee le {{ analysisObservationSavedAt }}</p>
        </div>

        <div v-if="analysisObservationOpen" class="mt-3 space-y-2.5">
          <textarea
            v-model.trim="analysisObservation"
            rows="2"
            placeholder="Exemple : etat general stable, surveillance clinique recommandee."
            class="w-full rounded-[12px] border border-[#d7dce6] bg-white px-3 py-2.5 text-[14px] leading-5 text-[#061a45] outline-none transition placeholder:text-[#9aa5ba] focus:border-[#4a55f5]"
          />

          <div class="flex flex-wrap justify-end gap-2">
            <button
              type="button"
              class="inline-flex h-[34px] items-center justify-center rounded-[12px] border border-[#d4d9e1] px-3 text-[13px] font-semibold text-[#40506a] transition hover:border-[#aeb9ca] hover:text-[#1a2c52]"
              @click="effacerObservationAnalyse"
            >
              Effacer
            </button>
            <button
              type="button"
              class="inline-flex h-[34px] items-center justify-center rounded-[12px] bg-[#3f49f4] px-3 text-[13px] font-semibold text-white transition hover:bg-[#3140ef]"
              @click="enregistrerObservationAnalyse"
            >
              Enregistrer
            </button>
          </div>
        </div>

        <div
          v-if="analysisObservationMessage"
          class="mt-2.5 rounded-[12px] border px-3 py-2 text-[12px]"
          :class="analysisObservationMessageType === 'success' ? 'border-[#c6ead0] bg-[#f2fcf4] text-[#118445]' : 'border-[#f1d4ae] bg-[#fff8ef] text-[#b46910]'"
        >
          {{ analysisObservationMessage }}
        </div>
      </article>
    </section>

    <section class="mt-8 rounded-[18px] border border-[#d4d9e1] bg-white p-[10px] shadow-[0_1px_4px_rgba(15,23,42,0.05)]">
      <div class="flex flex-wrap gap-2">
        <button
          v-for="tab in detailTabs"
          :key="tab.key"
          type="button"
          class="inline-flex h-[50px] items-center gap-2 rounded-[14px] px-5 text-[15px] font-semibold transition"
          :class="detailTab === tab.key ? 'bg-[#3f49f4] text-white shadow-[0_10px_18px_rgba(63,73,244,0.22)]' : 'text-[#384860]'"
          @click="detailTab = tab.key"
        >
          <component :is="tab.icon" class="h-[18px] w-[18px]" />
          {{ tab.label }}
        </button>
      </div>
    </section>

    <section v-if="detailTab === 'vitals'" class="mt-8 space-y-4">
      <article class="rounded-[20px] border border-[#d4d9e1] bg-white p-5 shadow-[0_1px_4px_rgba(15,23,42,0.05)]">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
          <div>
            <h3 class="text-[18px] font-bold text-[#041c49]">Filtrer les signes vitaux</h3>
            <p class="mt-1 text-[14px] text-[#5b6b84]">Affinez l'historique par date et par type de mesure.</p>
          </div>

          <button
            v-if="vitalDateFilter || vitalSignFilter !== 'all'"
            type="button"
            class="inline-flex h-[42px] items-center justify-center rounded-[14px] border border-[#d4d9e1] px-4 text-[14px] font-semibold text-[#40506a] transition hover:border-[#aeb9ca] hover:text-[#1a2c52]"
            @click="resetVitalFilters"
          >
            Reinitialiser
          </button>
        </div>

        <div class="mt-5 grid gap-4 md:grid-cols-2">
          <div>
            <label class="mb-2 block text-[14px] font-semibold text-[#23375f]">Date</label>
            <input
              v-model="vitalDateFilter"
              type="date"
              class="h-[52px] w-full rounded-[16px] border border-[#d7dce6] bg-[#fbfcfd] px-4 text-[15px] text-[#061a45] outline-none transition focus:border-[#4a55f5]"
            />
          </div>

          <div>
            <label class="mb-2 block text-[14px] font-semibold text-[#23375f]">Signe vital</label>
            <select
              v-model="vitalSignFilter"
              class="h-[52px] w-full rounded-[16px] border border-[#d7dce6] bg-[#fbfcfd] px-4 text-[15px] text-[#061a45] outline-none transition focus:border-[#4a55f5]"
            >
              <option value="all">Tous les signes</option>
              <option value="heartRate">Rythme cardiaque</option>
              <option value="bloodPressure">Tension</option>
              <option value="saturation">Saturation O2</option>
            </select>
          </div>
        </div>
      </article>

      <article v-for="entry in filteredVitalsHistory" :key="entry.isoDate || entry.date" class="rounded-[20px] border border-[#d4d9e1] bg-white p-5 shadow-[0_1px_4px_rgba(15,23,42,0.05)]">
        <div class="flex items-center gap-2 text-[16px] font-bold text-[#061a45]">
          <IconeCalendrier class="h-[18px] w-[18px]" />
          {{ entry.date }}
        </div>
        <div class="mt-4 grid gap-4 lg:grid-cols-3">
          <div
            v-for="card in entry.cards"
            :key="card.key"
            class="rounded-[16px] border px-5 py-4"
            :class="card.class"
          >
            <p class="text-[14px] text-[#455572]">{{ card.label }}</p>
            <p class="mt-2 text-[18px] font-bold text-[#061a45]">{{ card.value }}</p>
          </div>
        </div>
      </article>

      <article
        v-if="!filteredVitalsHistory.length"
        class="rounded-[20px] border border-dashed border-[#cfd6e2] bg-white px-6 py-8 text-center shadow-[0_1px_4px_rgba(15,23,42,0.05)]"
      >
        <p class="text-[17px] font-semibold text-[#10254f]">Aucun signe vital ne correspond aux filtres.</p>
        <p class="mt-2 text-[14px] text-[#5b6b84]">Essayez une autre date ou choisissez un autre type de mesure.</p>
      </article>
    </section>

    <section v-else-if="detailTab === 'analyses'" class="mt-8 space-y-4">
      <article class="rounded-[20px] border border-[#d4d9e1] bg-white p-5 shadow-[0_1px_4px_rgba(15,23,42,0.05)]">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
          <div>
            <h3 class="text-[18px] font-bold text-[#041c49]">Filtrer les analyses</h3>
            <p class="mt-1 text-[14px] text-[#5b6b84]">Affinez les resultats par date et par type d'analyse. Les 7 derniers jours sont affiches par defaut.</p>
          </div>

          <button
            v-if="analysisDateFilter || analysisTypeFilter !== 'all'"
            type="button"
            class="inline-flex h-[42px] items-center justify-center rounded-[14px] border border-[#d4d9e1] px-4 text-[14px] font-semibold text-[#40506a] transition hover:border-[#aeb9ca] hover:text-[#1a2c52]"
            @click="resetAnalysisFilters"
          >
            Reinitialiser
          </button>
        </div>

        <div class="mt-5 grid gap-4 md:grid-cols-2">
          <div>
            <label class="mb-2 block text-[14px] font-semibold text-[#23375f]">Date</label>
            <input
              v-model="analysisDateFilter"
              type="date"
              class="h-[52px] w-full rounded-[16px] border border-[#d7dce6] bg-[#fbfcfd] px-4 text-[15px] text-[#061a45] outline-none transition focus:border-[#4a55f5]"
            />
          </div>

          <div>
            <label class="mb-2 block text-[14px] font-semibold text-[#23375f]">Type d'analyse</label>
            <select
              v-model="analysisTypeFilter"
              class="h-[52px] w-full rounded-[16px] border border-[#d7dce6] bg-[#fbfcfd] px-4 text-[15px] text-[#061a45] outline-none transition focus:border-[#4a55f5]"
            >
              <option value="all">Tous les types</option>
              <option v-for="type in analysisTypes" :key="type" :value="type">{{ type }}</option>
            </select>
          </div>
        </div>
      </article>

      <article v-for="analysis in filteredAnalyses" :key="`${analysis.name}-${analysis.isoDate || analysis.date}`" class="rounded-[20px] border border-[#d4d9e1] bg-white px-6 py-5 shadow-[0_1px_4px_rgba(15,23,42,0.05)]">
        <div class="flex flex-wrap items-center gap-3">
          <h3 class="text-[18px] font-bold text-[#061a45]">{{ analysis.name }}</h3>
          <span class="inline-flex rounded-full px-3 py-1 text-[13px] font-medium" :class="analysis.badgeClass">{{ analysis.status }}</span>
        </div>
        <div class="mt-4 flex flex-wrap items-center gap-x-6 gap-y-3 text-[15px] text-[#455572]">
          <span class="text-[20px] font-bold text-[#061a45]">{{ analysis.value }}</span>
          <span>{{ analysis.range }}</span>
          <span class="inline-flex items-center gap-2">
            <IconeCalendrier class="h-[16px] w-[16px]" />
            {{ analysis.date }}
          </span>
        </div>
      </article>

      <article
        v-if="!filteredAnalyses.length"
        class="rounded-[20px] border border-dashed border-[#cfd6e2] bg-white px-6 py-8 text-center shadow-[0_1px_4px_rgba(15,23,42,0.05)]"
      >
        <p class="text-[17px] font-semibold text-[#10254f]">Aucune analyse ne correspond aux filtres.</p>
        <p class="mt-2 text-[14px] text-[#5b6b84]">Essayez une autre date ou un autre type d'analyse.</p>
      </article>
    </section>

    <section v-else class="mt-8 space-y-4">
      <article class="rounded-[20px] border border-[#d4d9e1] bg-white p-6 shadow-[0_1px_4px_rgba(15,23,42,0.05)]">
        <div class="flex items-center justify-between gap-3">
          <div>
            <h3 class="text-[18px] font-bold text-[#041c49]">Historique des prises</h3>
            <p class="mt-1 text-[14px] text-[#5b6b84]">Historique reel des traitements pris par le patient, avec 7 derniers jours affiches par defaut.</p>
          </div>
          <span class="rounded-full bg-[#eef4ff] px-3 py-1 text-[12px] font-semibold text-[#3257d6]">
            {{ patient.treatmentHistoryRows?.length || 0 }} jour<span v-if="(patient.treatmentHistoryRows?.length || 0) > 1">s</span>
          </span>
        </div>

        <article class="mt-6 rounded-[18px] border border-[#dbe2ec] bg-[#fbfcfe] p-5">
          <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
              <h4 class="text-[17px] font-bold text-[#10254f]">Filtrer l'historique des traitements</h4>
              <p class="mt-1 text-[14px] text-[#60708b]">Affinez l'affichage par date, traitement ou statut d'observance.</p>
            </div>

            <button
              v-if="treatmentDateFilter || treatmentMedicineFilter !== 'all' || treatmentStatusFilter !== 'all'"
              type="button"
              class="inline-flex h-[42px] items-center justify-center rounded-[14px] border border-[#d4d9e1] px-4 text-[14px] font-semibold text-[#40506a] transition hover:border-[#aeb9ca] hover:text-[#1a2c52]"
              @click="resetTreatmentFilters"
            >
              Reinitialiser
            </button>
          </div>

          <div class="mt-5 grid gap-4 md:grid-cols-3">
            <div>
              <label class="mb-2 block text-[14px] font-semibold text-[#23375f]">Date</label>
              <input
                v-model="treatmentDateFilter"
                type="date"
                class="h-[52px] w-full rounded-[16px] border border-[#d7dce6] bg-white px-4 text-[15px] text-[#061a45] outline-none transition focus:border-[#4a55f5]"
              />
            </div>

            <div>
              <label class="mb-2 block text-[14px] font-semibold text-[#23375f]">Traitement</label>
              <select
                v-model="treatmentMedicineFilter"
                class="h-[52px] w-full rounded-[16px] border border-[#d7dce6] bg-white px-4 text-[15px] text-[#061a45] outline-none transition focus:border-[#4a55f5]"
              >
                <option value="all">Tous les traitements</option>
                <option v-for="medicine in treatmentMedicineOptions" :key="medicine" :value="medicine">{{ medicine }}</option>
              </select>
            </div>

            <div>
              <label class="mb-2 block text-[14px] font-semibold text-[#23375f]">Observance</label>
              <select
                v-model="treatmentStatusFilter"
                class="h-[52px] w-full rounded-[16px] border border-[#d7dce6] bg-white px-4 text-[15px] text-[#061a45] outline-none transition focus:border-[#4a55f5]"
              >
                <option value="all">Tous les jours</option>
                <option value="complete">Jours complets</option>
                <option value="partial">Jours partiels</option>
              </select>
            </div>
          </div>
        </article>

        <div v-if="filteredTreatmentHistoryRows.length" class="mt-6 space-y-4">
          <article
            v-for="day in filteredTreatmentHistoryRows"
            :key="`doctor-treatment-history-${day.dateKey}`"
            class="rounded-[18px] border border-[#dbe2ec] bg-[#fbfcfe] p-5"
          >
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
              <div>
                <h4 class="text-[16px] font-bold text-[#10254f]">{{ formatTreatmentHistoryDate(day.dateKey) }}</h4>
                <p class="mt-1 text-[14px] text-[#60708b]">{{ day.taken }}/{{ day.total }} prises effectuees</p>
              </div>
              <span
                class="inline-flex h-[34px] items-center rounded-full px-3 text-[12px] font-semibold"
                :class="day.isComplete ? 'bg-[#dff6e7] text-[#15803d]' : 'bg-[#e9eef7] text-[#42526b]'"
              >
                {{ day.isComplete ? 'Jour complet' : 'Jour partiel' }}
              </span>
            </div>

            <div class="mt-4 grid gap-3 lg:grid-cols-2">
              <article
                v-for="med in day.meds"
                :key="`${day.dateKey}-${med.id}`"
                class="rounded-[16px] border border-[#e2e8f0] bg-white px-4 py-4"
              >
                <div class="flex items-start justify-between gap-3">
                  <div>
                    <p class="text-[15px] font-bold text-[#112652]">{{ med.name }}</p>
                    <p class="mt-1 text-[13px] text-[#63758d]">{{ med.dose }}</p>
                  </div>
                  <span
                    class="inline-flex h-[30px] min-w-[52px] items-center justify-center rounded-full px-3 text-[12px] font-semibold"
                    :class="med.isComplete ? 'bg-[#dff6e7] text-[#15803d]' : 'bg-[#edf2ff] text-[#3257d6]'"
                  >
                    {{ med.taken }}/{{ med.total }}
                  </span>
                </div>

                <div class="mt-4 h-[8px] rounded-full bg-[#dfe5ee]">
                  <div
                    class="h-[8px] rounded-full transition-all"
                    :class="med.isComplete ? 'bg-[#16a34a]' : 'bg-[#4f46e5]'"
                    :style="{ width: `${med.progress}%` }"
                  />
                </div>
              </article>
            </div>
          </article>
        </div>

        <div v-else class="mt-6 rounded-[16px] border border-dashed border-[#d3dbe7] bg-[#fbfcff] px-5 py-8 text-center">
          <p class="text-[15px] font-semibold text-[#10254f]">Aucun historique de prise disponible.</p>
          <p class="mt-2 text-[13px] text-[#697892]">Essayez une autre date, un autre traitement ou un autre filtre d'observance.</p>
        </div>
      </article>
    </section>
  </section>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import api from '@/services/api'
import {
  IconeFlecheGauche,
  IconeCalendrier,
  IconeHorloge,
  IconeCoeur,
  IconeLien,
  IconeOnde
} from '@/components/doctor/IconesMedecin.js'

const props = defineProps({
  patient: {
    type: Object,
    required: true
  }
})

defineEmits(['back'])

const detailTab = ref('vitals')

const detailTabs = [
  { key: 'vitals', label: 'Signes vitaux', icon: IconeCoeur },
  { key: 'analyses', label: 'Analyses', icon: IconeOnde },
  { key: 'treatments', label: 'Traitements', icon: IconeLien }
]

const vitalDateFilter = ref('')
const vitalSignFilter = ref('all')
const analysisDateFilter = ref('')
const analysisTypeFilter = ref('all')
const treatmentDateFilter = ref('')
const treatmentMedicineFilter = ref('all')
const treatmentStatusFilter = ref('all')
const analysisObservation = ref('')
const analysisObservationOpen = ref(false)
const analysisObservationSavedAt = ref('')
const analysisObservationMessage = ref('')
const analysisObservationMessageType = ref('success')
function construireCartesSignesVitaux(entree) {
  return [
    {
      key: 'heartRate',
      label: 'Rythme cardiaque',
      value: entree.heartRate,
      class: 'border-[#f4bcc3] bg-[#fff5f6]'
    },
    {
      key: 'bloodPressure',
      label: 'Tension',
      value: entree.bloodPressure,
      class: 'border-[#aac8ff] bg-[#eff6ff]'
    },
    {
      key: 'saturation',
      label: 'Saturation O2',
      value: entree.saturation,
      class: 'border-[#dcc5ff] bg-[#faf4ff]'
    }
  ]
}

const filteredVitalsHistory = computed(() => {
  const entries = Array.isArray(props.patient?.vitalsHistory) ? props.patient.vitalsHistory : []
  const visibleEntries = vitalDateFilter.value ? entries : entries.slice(0, 7)

  return visibleEntries
    .filter((entry) => !vitalDateFilter.value || entry.isoDate === vitalDateFilter.value)
    .map((entry) => {
      const cards = construireCartesSignesVitaux(entry).filter((card) => vitalSignFilter.value === 'all' || card.key === vitalSignFilter.value)
      return { ...entry, cards }
    })
    .filter((entry) => entry.cards.length > 0)
})

const analysisTypes = computed(() => {
  const analyses = Array.isArray(props.patient?.analyses) ? props.patient.analyses : []
  return [...new Set(analyses.map((analysis) => String(analysis.type || '').trim()).filter(Boolean))]
})

const analysisVisibleDateKeys = computed(() => {
  const analyses = Array.isArray(props.patient?.analyses) ? props.patient.analyses : []
  return [...new Set(analyses.map((analysis) => analysis.isoDate).filter(Boolean))].slice(0, 7)
})

const filteredAnalyses = computed(() => {
  const analyses = Array.isArray(props.patient?.analyses) ? props.patient.analyses : []
  const visibleDateKeys = analysisVisibleDateKeys.value
  const visibleAnalyses = analysisDateFilter.value
    ? analyses
    : analyses.filter((analysis) => analysis.isoDate && visibleDateKeys.includes(analysis.isoDate))

  return visibleAnalyses.filter((analysis) => {
    const dateOk = !analysisDateFilter.value || analysis.isoDate === analysisDateFilter.value
    const typeOk = analysisTypeFilter.value === 'all' || analysis.type === analysisTypeFilter.value
    return dateOk && typeOk
  })
})

const treatmentMedicineOptions = computed(() => {
  const rows = Array.isArray(props.patient?.treatmentHistoryRows) ? props.patient.treatmentHistoryRows : []
  return [...new Set(rows.flatMap((day) => day.meds.map((med) => String(med.name || '').trim())).filter(Boolean))]
})

const filteredTreatmentHistoryRows = computed(() => {
  const rows = Array.isArray(props.patient?.treatmentHistoryRows) ? props.patient.treatmentHistoryRows : []
  const visibleRows = treatmentDateFilter.value ? rows : rows.slice(0, 7)

  return visibleRows
    .filter((day) => !treatmentDateFilter.value || day.dateKey === treatmentDateFilter.value)
    .map((day) => {
      const meds = day.meds.filter((med) => treatmentMedicineFilter.value === 'all' || med.name === treatmentMedicineFilter.value)
      const total = meds.reduce((sum, med) => sum + Number(med.total || 0), 0)
      const taken = meds.reduce((sum, med) => sum + Number(med.taken || 0), 0)
      const isComplete = total > 0 && taken >= total

      return {
        ...day,
        meds,
        total,
        taken,
        isComplete,
      }
    })
    .filter((day) => day.meds.length > 0)
    .filter((day) => {
      if (treatmentStatusFilter.value === 'complete') return day.isComplete
      if (treatmentStatusFilter.value === 'partial') return !day.isComplete
      return true
    })
})

const observationResume = computed(() => {
  const text = String(analysisObservation.value || '').trim()
  if (!text) return 'Aucune observation generale pour le moment.'
  return text.length > 140 ? `${text.slice(0, 140).trim()}…` : text
})

function formaterDateObservation(dateString) {
  if (!dateString) return ''
  const date = new Date(dateString)
  if (Number.isNaN(date.getTime())) return ''
  return date.toLocaleString('fr-FR', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

function chargerObservationAnalyse() {
  analysisObservationMessage.value = ''
  analysisObservation.value = String(props.patient?.generalObservation?.text || '')
  analysisObservationSavedAt.value = formaterDateObservation(props.patient?.generalObservation?.updatedAt)
  analysisObservationOpen.value = false
}

function formatTreatmentHistoryDate(dateIso) {
  if (!dateIso) return '-'
  const date = new Date(`${dateIso}T00:00:00`)
  if (Number.isNaN(date.getTime())) return dateIso
  return date.toLocaleDateString('fr-FR', {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  })
}

function resetVitalFilters() {
  vitalDateFilter.value = ''
  vitalSignFilter.value = 'all'
}

function resetAnalysisFilters() {
  analysisDateFilter.value = ''
  analysisTypeFilter.value = 'all'
}

function resetTreatmentFilters() {
  treatmentDateFilter.value = ''
  treatmentMedicineFilter.value = 'all'
  treatmentStatusFilter.value = 'all'
}

async function enregistrerObservationAnalyse() {
  const text = String(analysisObservation.value || '').trim()

  if (!text) {
    analysisObservationMessageType.value = 'warning'
    analysisObservationMessage.value = "Ecrivez une observation avant de l'enregistrer."
    return
  }

  try {
    const response = await api.put(`/doctor-invitations/patients/${props.patient.id}/observation-generale`, {
      observation: text,
    })

    const payload = response?.data?.data?.general_observation || {}
    analysisObservation.value = String(payload?.text || text)
    analysisObservationSavedAt.value = formaterDateObservation(payload?.updated_at)
    analysisObservationMessageType.value = 'success'
    analysisObservationMessage.value = response?.data?.message || 'Observation generale enregistree.'
    analysisObservationOpen.value = false
  } catch {
    analysisObservationMessageType.value = 'warning'
    analysisObservationMessage.value = "Impossible d'enregistrer l'observation generale pour le moment."
  }
}

async function effacerObservationAnalyse() {
  try {
    const response = await api.put(`/doctor-invitations/patients/${props.patient.id}/observation-generale`, {
      observation: null,
    })

    analysisObservation.value = ''
    analysisObservationSavedAt.value = ''
    analysisObservationMessageType.value = 'success'
    analysisObservationMessage.value = response?.data?.message || 'Observation generale effacee.'
    analysisObservationOpen.value = false
  } catch {
    analysisObservationMessageType.value = 'warning'
    analysisObservationMessage.value = "Impossible d'effacer l'observation generale pour le moment."
  }
}

watch(
  () => [props.patient?.id, props.patient?.generalObservation?.updatedAt, props.patient?.generalObservation?.text],
  () => {
    chargerObservationAnalyse()
  },
  { immediate: true }
)
</script>
