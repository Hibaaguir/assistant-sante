<template>
  <div class="space-y-10">
    <div class="text-center max-w-2xl mx-auto">
      <h2 class="text-3xl font-semibold text-gray-900 mb-3">Traitements en cours</h2>
      <p class="text-gray-500 text-sm sm:text-base">Ajoute tes traitements pour creer un calendrier de rappels</p>
    </div>

    <section class="bg-gradient-to-br from-teal-50 to-blue-50 rounded-2xl border-2 border-teal-100 p-6 space-y-4">
      <div class="flex items-start gap-4">
        <div class="bg-teal-100 p-3 rounded-xl">
          <svg class="h-6 w-6 text-teal-600" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M10 14l-2 2a3 3 0 1 1-4-4l2-2m8-2 2-2a3 3 0 1 1 4 4l-2 2m-8 2 4-4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
        </div>
        <div class="flex-1">
          <h3 class="text-base font-medium text-gray-900 block mb-1">Traitements en cours</h3>
          <p class="text-sm text-gray-600">Ajoute tes traitements pour creer un calendrier de rappels</p>
        </div>
      </div>

      <div v-if="!showTreatmentForm">
        <button
          type="button"
          class="bg-white hover:bg-teal-50 border-2 border-teal-200 hover:border-teal-300 rounded-xl h-12 gap-2 px-4 text-sm font-semibold"
          @click="showTreatmentForm = true"
        >
          + Ajouter un traitement
        </button>
      </div>

      <div v-else class="bg-white rounded-xl border-2 border-teal-200 p-6 space-y-4">
        <h4 class="font-medium text-gray-900 mb-2">Nouveau traitement</h4>

        <div class="space-y-2">
          <label class="text-sm font-medium text-gray-900">Type de traitement</label>
          <button
            type="button"
            class="h-12 w-full rounded-lg border px-4 bg-white outline-none focus:border-teal-500 text-left text-sm flex items-center justify-between"
            :class="treatmentErrors.type ? 'border-red-300' : 'border-gray-200'"
            @click="openTreatmentTypes = !openTreatmentTypes"
          >
            <span>{{ treatment.type || "Selectionner un type" }}</span>
            <svg class="h-4 w-4 text-gray-400" viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <path d="M8 10l4 4 4-4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
          </button>
          <p v-if="treatmentErrors.type" class="text-sm text-red-600">{{ treatmentErrors.type }}</p>

          <div v-if="openTreatmentTypes" class="rounded-2xl border border-gray-300 bg-white shadow-sm overflow-hidden">
            <div class="p-3 border-b">
              <input
                v-model="queryTreatmentTypes"
                type="text"
                placeholder="Rechercher..."
                class="h-11 w-full rounded-lg bg-slate-100 px-3 text-sm outline-none"
              />
            </div>
            <div class="max-h-40 overflow-auto p-2">
              <button
                v-for="item in filteredTreatmentTypes"
                :key="`type-${item}`"
                type="button"
                class="w-full rounded-lg px-3 py-2 text-left text-sm text-slate-800"
                @click="selectTreatmentType(item)"
              >
                {{ item }}
              </button>
            </div>
            <div class="p-3 border-t bg-slate-50 flex gap-2">
              <input
                v-model="customTreatmentType"
                type="text"
                placeholder="Ajouter..."
                class="h-11 flex-1 rounded-lg bg-slate-100 px-3 text-sm outline-none"
                @keydown.enter.prevent="addCustomTreatmentType"
              />
              <button
                type="button"
                class="h-11 rounded-lg bg-[#72C9C0] px-4 text-white font-semibold text-sm disabled:opacity-50"
                :disabled="!customTreatmentType.trim()"
                @click="addCustomTreatmentType"
              >
                Ajouter
              </button>
            </div>
          </div>
        </div>

        <div class="space-y-2">
          <label class="text-sm font-medium text-gray-900">Nom du traitement</label>
          <button
            type="button"
            class="h-12 w-full rounded-lg border px-4 bg-white outline-none focus:border-teal-500 text-left text-sm flex items-center justify-between"
            :class="[
              treatmentErrors.name ? 'border-red-300' : 'border-gray-200',
              !treatment.type ? 'opacity-70 cursor-not-allowed bg-slate-50' : ''
            ]"
            :disabled="!treatment.type"
            @click="openTreatmentNames = !openTreatmentNames"
          >
            <span>{{ treatment.name || (treatment.type ? "Selectionner ou ajouter un traitement" : "Selectionner d'abord un type") }}</span>
            <svg class="h-4 w-4 text-gray-400" viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <path d="M8 10l4 4 4-4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
          </button>
          <p v-if="treatmentErrors.name" class="text-sm text-red-600">{{ treatmentErrors.name }}</p>

          <div v-if="openTreatmentNames" class="rounded-2xl border border-gray-300 bg-white shadow-sm overflow-hidden">
            <div class="p-3 border-b">
              <input
                v-model="queryTreatmentNames"
                type="text"
                placeholder="Rechercher..."
                class="h-11 w-full rounded-lg bg-slate-100 px-3 text-sm outline-none"
              />
            </div>
            <div class="max-h-40 overflow-auto p-2">
              <button
                v-for="item in filteredTreatmentNames"
                :key="`name-${item}`"
                type="button"
                class="w-full rounded-lg px-3 py-2 text-left text-sm text-slate-800"
                @click="selectTreatmentName(item)"
              >
                {{ item }}
              </button>
            </div>
            <div class="p-3 border-t bg-slate-50 flex gap-2">
              <input
                v-model="customTreatmentName"
                type="text"
                placeholder="Ajouter..."
                class="h-11 flex-1 rounded-lg bg-slate-100 px-3 text-sm outline-none"
                @keydown.enter.prevent="addCustomTreatmentName"
              />
              <button
                type="button"
                class="h-11 rounded-lg bg-[#72C9C0] px-4 text-white font-semibold text-sm disabled:opacity-50"
                :disabled="!customTreatmentName.trim()"
                @click="addCustomTreatmentName"
              >
                Ajouter
              </button>
            </div>
          </div>
        </div>

        <div class="space-y-2">
          <label class="text-sm font-medium text-gray-900">Dose</label>
          <input
            v-model.trim="treatment.dose"
            type="text"
            placeholder="Ex: 500mg, 1 comprime..."
            class="h-12 rounded-lg w-full border bg-slate-50 px-4 outline-none focus:border-teal-500"
            :class="treatmentErrors.dose ? 'border-red-300' : 'border-gray-200'"
            @input="treatmentErrors.dose = ''"
          />
          <p v-if="treatmentErrors.dose" class="text-sm text-red-600">{{ treatmentErrors.dose }}</p>
        </div>

        <div class="space-y-2">
          <label class="text-sm font-medium text-gray-900">Frequence</label>
          <div class="grid grid-cols-2 gap-3">
            <select v-model="treatment.frequency_unit" class="h-12 rounded-lg w-full border border-gray-200 px-4 bg-white outline-none focus:border-teal-500">
              <option value="jour">Par jour</option>
              <option value="semaine">Par semaine</option>
              <option value="mois">Par mois</option>
            </select>
            <div class="flex h-12 w-full items-center justify-between rounded-lg border border-gray-200 bg-slate-50 px-4 text-sm text-slate-900">
              <input
                v-model.number="treatment.frequency_count"
                type="number"
                min="1"
                class="no-spinner min-w-[4.5rem] bg-transparent text-sm outline-none"
                placeholder="Ex: 3"
                @input="treatmentErrors.frequency_count = ''"
              />
              <span class="whitespace-nowrap text-sm text-gray-500">
                {{ Number(treatment.frequency_count || 0) > 1 ? "prises" : "prise" }}
              </span>
            </div>
          </div>
          <p v-if="treatmentErrors.frequency_count" class="text-sm text-red-600">{{ treatmentErrors.frequency_count }}</p>
        </div>

        <div class="space-y-2">
          <label class="text-sm font-medium text-gray-900">Debut de traitement <span class="text-gray-400 font-normal">(JJ/MM/AAAA)</span></label>
          <input
            :value="treatment.start_date"
            type="text"
            placeholder="Ex: 01/03/2026"
            maxlength="10"
            class="h-12 rounded-lg w-full border bg-slate-50 px-4 outline-none focus:border-teal-500"
            :class="treatmentErrors.start_date ? 'border-red-300' : 'border-gray-200'"
            @input="(event) => handleTreatmentDateInput(event, 'start_date')"
          />
          <p v-if="treatmentErrors.start_date" class="text-sm text-red-600">{{ treatmentErrors.start_date }}</p>
        </div>

        <div class="space-y-2">
          <label class="text-sm font-medium text-gray-900">Fin de traitement <span class="text-gray-400 font-normal">(JJ/MM/AAAA)</span></label>
          <input
            :value="treatment.end_date"
            type="text"
            placeholder="Ex: 30/03/2026"
            maxlength="10"
            class="h-12 rounded-lg w-full border bg-slate-50 px-4 outline-none focus:border-teal-500"
            :class="treatmentErrors.end_date ? 'border-red-300' : 'border-gray-200'"
            @input="(event) => handleTreatmentDateInput(event, 'end_date')"
          />
          <p v-if="treatmentErrors.end_date" class="text-sm text-red-600">{{ treatmentErrors.end_date }}</p>
        </div>

        <div class="grid grid-cols-2 gap-3 pt-2">
          <button type="button" class="h-11 rounded-lg bg-teal-600 text-white font-semibold" @click="addTreatment">
            + Ajouter
          </button>
          <button type="button" class="h-11 rounded-lg border border-slate-300 font-semibold text-slate-900" @click="cancelTreatment">Annuler</button>
        </div>
      </div>

      <div v-if="form.traitements.length" class="bg-white rounded-xl border border-teal-100 p-4 space-y-3">
        <h4 class="font-medium text-gray-900">Traitements ajoutes ({{ form.traitements.length }})</h4>
        <div class="space-y-2">
          <div
            v-for="(item, index) in form.traitements"
            :key="`treatment-${index}-${item.type}-${item.name}`"
            class="rounded-lg border border-gray-200 bg-slate-50 px-4 py-3 flex items-start justify-between gap-3"
          >
            <div class="min-w-0">
              <p class="text-sm font-semibold text-gray-900">{{ item.name || "Traitement sans nom" }}</p>
              <p class="text-xs text-gray-600 mt-1">
                {{ item.type }}
                <span v-if="item.dose"> | {{ item.dose }}</span>
                <span v-if="item.frequency_count && item.frequency_unit"> | {{ item.frequency_count }} prise{{ Number(item.frequency_count) > 1 ? "s" : "" }} / {{ item.frequency_unit }}</span>
                <span v-if="item.start_date && item.end_date"> | {{ item.start_date }} - {{ item.end_date }}</span>
                <span v-else-if="item.duration"> | {{ item.duration }}</span>
              </p>
            </div>
            <div class="flex items-center gap-3">
              <button
                type="button"
                class="text-xs font-semibold text-red-600 hover:text-red-700"
                @click="removeTreatment(index)"
              >
                Retirer
              </button>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
/*
  Etape 3 du profil sante: traitements.
  L'utilisateur ajoute ses traitements en cours.
*/

import { computed, reactive, ref } from "vue";

const props = defineProps({
  form: { type: Object, required: true },
});

const form = props.form;
if (!Array.isArray(form.traitements)) form.traitements = [];

const treatmentTypes = ref([
  "Anti-inflammatoire",
  "Antibiotique",
  "Antidouleur",
  "Antihypertenseur",
  "Antidiabetique",
  "Anticoagulant",
  "Antiallergique",
  "Antidepresseur",
  "Corticoide",
  "Traitement hormonal",
  "Supplement vitaminique",
  "Inhalateur respiratoire",
]);

const treatmentNameOptionsByType = reactive({
  "Anti-inflammatoire": ["Ibuprofene", "Naproxene", "Diclofenac", "Ketoprofene", "Celecoxib"],
  Antibiotique: ["Amoxicilline", "Azithromycine", "Doxycycline", "Ciprofloxacine", "Cefuroxime"],
  Antidouleur: ["Paracetamol", "Tramadol", "Codeine", "Morphine", "Nefopam"],
  Antihypertenseur: ["Amlodipine", "Ramipril", "Losartan", "Bisoprolol", "Hydrochlorothiazide"],
  Antidiabetique: ["Metformine", "Gliclazide", "Empagliflozine", "Sitagliptine", "Insuline glargine"],
  Anticoagulant: ["Apixaban", "Rivaroxaban", "Warfarine", "Enoxaparine", "Dabigatran"],
  Antiallergique: ["Cetirizine", "Loratadine", "Desloratadine", "Fexofenadine", "Bilastine"],
  Antidepresseur: ["Sertraline", "Escitalopram", "Fluoxetine", "Venlafaxine", "Mirtazapine"],
  Corticoide: ["Prednisone", "Prednisolone", "Dexamethasone", "Budesonide", "Methylprednisolone"],
  "Traitement hormonal": ["Levothyroxine", "Estradiol", "Progesterone", "Testosterone", "Insuline"],
  "Supplement vitaminique": ["Vitamine D3", "Vitamine B12", "Acide folique", "Multivitamines", "Vitamine C"],
  "Inhalateur respiratoire": ["Salbutamol", "Budesonide/Formoterol", "Fluticasone/Salmeterol", "Tiotropium", "Ipratropium"],
});

const showTreatmentForm = ref(false);
const openTreatmentTypes = ref(false);
const queryTreatmentTypes = ref("");
const customTreatmentType = ref("");
const openTreatmentNames = ref(false);
const queryTreatmentNames = ref("");
const customTreatmentName = ref("");

const treatment = reactive({
  type: "",
  name: "",
  dose: "",
  frequency_unit: "jour",
  frequency_count: 1,
  start_date: "",
  end_date: "",
});
const treatmentErrors = reactive({
  type: "",
  name: "",
  dose: "",
  frequency_count: "",
  start_date: "",
  end_date: "",
});

const filteredTreatmentTypes = computed(() => {
  const q = queryTreatmentTypes.value.trim().toLowerCase();
  return q ? treatmentTypes.value.filter((item) => item.toLowerCase().includes(q)) : treatmentTypes.value;
});

const filteredTreatmentNames = computed(() => {
  const names = treatment.type ? (treatmentNameOptionsByType[treatment.type] || []) : [];
  const q = queryTreatmentNames.value.trim().toLowerCase();
  return q ? names.filter((item) => item.toLowerCase().includes(q)) : names;
});

function selectTreatmentType(value) {
  treatment.type = value;
  treatment.name = "";
  treatmentErrors.type = "";
  treatmentErrors.name = "";
  queryTreatmentNames.value = "";
  openTreatmentNames.value = false;
  openTreatmentTypes.value = false;
}

function addCustomTreatmentType() {
  const value = customTreatmentType.value.trim();
  if (!value) return;
  if (!treatmentTypes.value.includes(value)) treatmentTypes.value = [...treatmentTypes.value, value];
  if (!Array.isArray(treatmentNameOptionsByType[value])) {
    treatmentNameOptionsByType[value] = [];
  }
  treatment.type = value;
  treatment.name = "";
  treatmentErrors.type = "";
  treatmentErrors.name = "";
  customTreatmentType.value = "";
  openTreatmentTypes.value = false;
}

function selectTreatmentName(value) {
  treatment.name = value;
  treatmentErrors.name = "";
  openTreatmentNames.value = false;
}

function addCustomTreatmentName() {
  if (!treatment.type) return;
  const value = customTreatmentName.value.trim();
  if (!value) return;
  if (!Array.isArray(treatmentNameOptionsByType[treatment.type])) {
    treatmentNameOptionsByType[treatment.type] = [];
  }
  if (!treatmentNameOptionsByType[treatment.type].includes(value)) {
    treatmentNameOptionsByType[treatment.type] = [...treatmentNameOptionsByType[treatment.type], value];
  }
  treatment.name = value;
  treatmentErrors.name = "";
  customTreatmentName.value = "";
  openTreatmentNames.value = false;
}

function formatDateWithSlashes(value) {
  const digits = String(value || "").replace(/\D/g, "").slice(0, 8);
  if (digits.length <= 2) return digits;
  if (digits.length <= 4) return `${digits.slice(0, 2)}/${digits.slice(2)}`;
  return `${digits.slice(0, 2)}/${digits.slice(2, 4)}/${digits.slice(4, 8)}`;
}

function handleTreatmentDateInput(event, key) {
  const raw = event?.target?.value ?? "";
  treatment[key] = formatDateWithSlashes(raw);
  treatmentErrors[key] = "";
}

function buildTreatmentDuration() {
  if (treatment.start_date && treatment.end_date) return `${treatment.start_date} - ${treatment.end_date}`;
  return null;
}

function clearTreatmentErrors() {
  treatmentErrors.type = "";
  treatmentErrors.name = "";
  treatmentErrors.dose = "";
  treatmentErrors.frequency_count = "";
  treatmentErrors.start_date = "";
  treatmentErrors.end_date = "";
}

function parseFrDate(value) {
  const match = String(value || "").match(/^(\d{2})\/(\d{2})\/(\d{4})$/);
  if (!match) return null;

  const day = Number(match[1]);
  const month = Number(match[2]);
  const year = Number(match[3]);
  const date = new Date(year, month - 1, day);

  const isValid =
    date.getFullYear() === year &&
    date.getMonth() === month - 1 &&
    date.getDate() === day;

  return isValid ? date : null;
}

function validateTreatment() {
  clearTreatmentErrors();
  let isValid = true;

  if (!treatment.type.trim()) {
    treatmentErrors.type = "Le type de traitement est obligatoire.";
    isValid = false;
  }
  if (!treatment.name.trim()) {
    treatmentErrors.name = "Le nom du traitement est obligatoire.";
    isValid = false;
  }
  if (!treatment.dose.trim()) {
    treatmentErrors.dose = "La dose est obligatoire.";
    isValid = false;
  }

  const frequencyCount = Number(treatment.frequency_count);
  if (!Number.isFinite(frequencyCount) || frequencyCount < 1) {
    treatmentErrors.frequency_count = "La fréquence doit être au minimum de 1 prise.";
    isValid = false;
  }

  if (!treatment.start_date.trim()) {
    treatmentErrors.start_date = "La date de début est obligatoire.";
    isValid = false;
  }
  if (!treatment.end_date.trim()) {
    treatmentErrors.end_date = "La date de fin est obligatoire.";
    isValid = false;
  }

  const startDate = parseFrDate(treatment.start_date);
  const endDate = parseFrDate(treatment.end_date);

  if (treatment.start_date && !startDate) {
    treatmentErrors.start_date = "Format invalide. Utilisez JJ/MM/AAAA.";
    isValid = false;
  }
  if (treatment.end_date && !endDate) {
    treatmentErrors.end_date = "Format invalide. Utilisez JJ/MM/AAAA.";
    isValid = false;
  }
  if (startDate && endDate && endDate <= startDate) {
    treatmentErrors.end_date = "La date de fin doit être strictement après la date de début.";
    isValid = false;
  }

  return isValid;
}

function addTreatment() {
  if (!validateTreatment()) return;

  form.traitements.push({
    type: treatment.type,
    name: treatment.name,
    dose: treatment.dose || null,
    frequency_unit: treatment.frequency_unit,
    frequency_count: Math.max(1, Number(treatment.frequency_count || 1)),
    start_date: treatment.start_date || null,
    end_date: treatment.end_date || null,
    duration: buildTreatmentDuration(),
  });

  cancelTreatment();
}

function cancelTreatment() {
  clearTreatmentErrors();
  showTreatmentForm.value = false;
  openTreatmentTypes.value = false;
  openTreatmentNames.value = false;
  queryTreatmentTypes.value = "";
  queryTreatmentNames.value = "";
  customTreatmentType.value = "";
  customTreatmentName.value = "";

  treatment.type = "";
  treatment.name = "";
  treatment.dose = "";
  treatment.frequency_unit = "jour";
  treatment.frequency_count = 1;
  treatment.start_date = "";
  treatment.end_date = "";
}

function removeTreatment(index) {
  form.traitements.splice(index, 1);
}
</script>

<style scoped>
.no-spinner::-webkit-outer-spin-button,
.no-spinner::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

.no-spinner[type="number"] {
  -moz-appearance: textfield;
  appearance: textfield;
}
</style>
