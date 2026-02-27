<template>
  <div class="space-y-10">
    <div class="text-center max-w-2xl mx-auto">
      <h2 class="text-3xl font-semibold text-gray-900 mb-3">Informations de sante</h2>
      <p class="text-gray-500 text-sm sm:text-base">
        Ces informations nous aident a personnaliser tes recommandations et a mieux suivre ton evolution
      </p>
    </div>

    <div class="space-y-8">
      <section class="space-y-4">
        <label class="text-base font-medium text-gray-900 flex items-center gap-2">
          <svg class="h-5 w-5 text-red-500" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M12 3v18M3 12h18" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
          </svg>
          Groupe sanguin <span class="text-gray-400 font-normal">(optionnel)</span>
        </label>

        <select
          v-model="form.groupe_sanguin"
          class="h-14 text-base rounded-xl border-2 border-gray-300 max-w-md w-full bg-white px-4 outline-none focus:border-teal-500"
        >
          <option value="">Selectionner votre groupe sanguin</option>
          <option v-for="group in bloodGroups" :key="group" :value="group">{{ group }}</option>
        </select>
      </section>

      <section class="space-y-4">
        <label class="text-base font-medium text-gray-900 flex items-center gap-2">
          <svg class="h-5 w-5 text-orange-500" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2" />
            <path d="M12 8v5M12 16h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
          </svg>
          Allergies <span class="text-gray-400 font-normal">(optionnel)</span>
        </label>

        <button
          type="button"
          class="h-14 w-full rounded-xl border-2 border-gray-300 bg-white px-4 text-left text-base font-semibold flex items-center justify-between"
          @click="openAllergies = !openAllergies"
        >
          <span>{{ form.allergies.length ? `${form.allergies.length} selectionne(s)` : "Selectionner vos allergies" }}</span>
          <svg class="h-4 w-4 text-gray-400" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M8 10l4 4 4-4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
        </button>

        <div v-if="form.allergies.length" class="flex flex-wrap gap-2">
          <span
            v-for="item in form.allergies"
            :key="`allergy-chip-${item}`"
            class="inline-flex items-center gap-2 rounded-lg border border-orange-200 bg-orange-50 px-3 py-1 text-sm text-orange-700"
          >
            {{ item }}
            <button type="button" @click="removeSelected('allergies', item)">x</button>
          </span>
        </div>

        <div v-if="openAllergies" class="rounded-2xl border border-gray-300 bg-white shadow-sm overflow-hidden">
          <div class="p-3 border-b">
            <input
              v-model="queryAllergies"
              type="text"
              placeholder="Rechercher..."
              class="h-11 w-full rounded-lg bg-slate-100 px-3 text-sm outline-none"
            />
          </div>

          <div class="max-h-56 overflow-auto p-2">
            <button
              v-for="item in filteredAllergies"
              :key="`allergy-opt-${item}`"
              type="button"
              class="w-full rounded-lg px-3 py-2 text-left text-sm text-slate-800 flex items-center justify-between"
              :class="isSelected('allergies', item) ? 'bg-slate-100' : ''"
              @click="toggleSelected('allergies', item)"
            >
              <span>{{ item }}</span>
              <svg v-if="isSelected('allergies', item)" class="h-4 w-4 text-teal-600" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M20 6L9 17l-5-5" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
            </button>
          </div>

          <div class="p-3 border-t bg-slate-50 flex gap-2">
            <input
              v-model="customAllergy"
              type="text"
              placeholder="Ajouter..."
              class="h-11 flex-1 rounded-lg bg-slate-100 px-3 text-sm outline-none"
              @keydown.enter.prevent="addCustom('allergies', customAllergy)"
            />
            <button
              type="button"
              class="h-11 rounded-lg bg-[#72C9C0] px-4 text-white font-semibold text-sm disabled:opacity-50"
              :disabled="!customAllergy.trim()"
              @click="addCustom('allergies', customAllergy)"
            >
              Ajouter
            </button>
          </div>
        </div>
      </section>

      <section class="space-y-4">
        <label class="text-base font-medium text-gray-900">
          Maladies chroniques <span class="text-gray-400 font-normal">(optionnel)</span>
        </label>

        <button
          type="button"
          class="h-14 w-full rounded-xl border-2 border-gray-300 bg-white px-4 text-left text-base font-semibold flex items-center justify-between"
          @click="openDiseases = !openDiseases"
        >
          <span>{{ form.maladies_chroniques.length ? `${form.maladies_chroniques.length} selectionne(s)` : "Selectionner vos maladies chroniques" }}</span>
          <svg class="h-4 w-4 text-gray-400" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M8 10l4 4 4-4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
        </button>

        <div v-if="form.maladies_chroniques.length" class="flex flex-wrap gap-2">
          <span
            v-for="item in form.maladies_chroniques"
            :key="`disease-chip-${item}`"
            class="inline-flex items-center gap-2 rounded-lg border border-blue-200 bg-blue-50 px-3 py-1 text-sm text-blue-700"
          >
            {{ item }}
            <button type="button" @click="removeSelected('maladies_chroniques', item)">x</button>
          </span>
        </div>

        <div v-if="openDiseases" class="rounded-2xl border border-gray-300 bg-white shadow-sm overflow-hidden">
          <div class="p-3 border-b">
            <input
              v-model="queryDiseases"
              type="text"
              placeholder="Rechercher..."
              class="h-11 w-full rounded-lg bg-slate-100 px-3 text-sm outline-none"
            />
          </div>

          <div class="max-h-56 overflow-auto p-2">
            <button
              v-for="item in filteredDiseases"
              :key="`disease-opt-${item}`"
              type="button"
              class="w-full rounded-lg px-3 py-2 text-left text-sm text-slate-800 flex items-center justify-between"
              :class="isSelected('maladies_chroniques', item) ? 'bg-slate-100' : ''"
              @click="toggleSelected('maladies_chroniques', item)"
            >
              <span>{{ item }}</span>
              <svg v-if="isSelected('maladies_chroniques', item)" class="h-4 w-4 text-teal-600" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M20 6L9 17l-5-5" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
            </button>
          </div>

          <div class="p-3 border-t bg-slate-50 flex gap-2">
            <input
              v-model="customDisease"
              type="text"
              placeholder="Ajouter..."
              class="h-11 flex-1 rounded-lg bg-slate-100 px-3 text-sm outline-none"
              @keydown.enter.prevent="addCustom('maladies_chroniques', customDisease)"
            />
            <button
              type="button"
              class="h-11 rounded-lg bg-[#72C9C0] px-4 text-white font-semibold text-sm disabled:opacity-50"
              :disabled="!customDisease.trim()"
              @click="addCustom('maladies_chroniques', customDisease)"
            >
              Ajouter
            </button>
          </div>
        </div>
      </section>

      <section class="bg-gradient-to-br from-teal-50 to-blue-50 rounded-2xl border-2 border-teal-100 p-6 space-y-4">
        <div class="flex items-start gap-4">
          <div class="bg-teal-100 p-3 rounded-xl">
            <svg class="h-6 w-6 text-teal-600" viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <path d="M10 14l-2 2a3 3 0 1 1-4-4l2-2m8-2 2-2a3 3 0 1 1 4 4l-2 2m-8 2 4-4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
          </div>
          <div class="flex-1">
            <h3 class="text-base font-medium text-gray-900 block mb-1">
              Traitements en cours <span class="text-gray-500 font-normal">(optionnel)</span>
            </h3>
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
          <h4 class="font-medium text-gray-900 mb-2">{{ editingTreatmentIndex > -1 ? "Modifier le traitement" : "Nouveau traitement" }}</h4>

          <div class="space-y-2">
            <label class="text-sm font-medium text-gray-900">Type de traitement</label>
            <button
              type="button"
              class="h-12 w-full rounded-lg border border-gray-200 px-4 bg-white outline-none focus:border-teal-500 text-left text-sm flex items-center justify-between"
              @click="openTreatmentTypes = !openTreatmentTypes"
            >
              <span>{{ treatment.type || "Selectionner un type" }}</span>
              <svg class="h-4 w-4 text-gray-400" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M8 10l4 4 4-4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
            </button>

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
              class="h-12 w-full rounded-lg border border-gray-200 px-4 bg-white outline-none focus:border-teal-500 text-left text-sm flex items-center justify-between"
              @click="openTreatmentNames = !openTreatmentNames"
            >
              <span>{{ treatment.name || "Selectionner ou ajouter un traitement" }}</span>
              <svg class="h-4 w-4 text-gray-400" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M8 10l4 4 4-4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
            </button>

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
              class="h-12 rounded-lg w-full border border-gray-200 bg-slate-50 px-4 outline-none focus:border-teal-500"
            />
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
                />
                <span class="whitespace-nowrap text-sm text-gray-500">
                  {{ Number(treatment.frequency_count || 0) > 1 ? "prises" : "prise" }}
                </span>
              </div>
            </div>
          </div>

          <div class="space-y-2">
            <label class="text-sm font-medium text-gray-900">Debut de traitement <span class="text-gray-400 font-normal">(JJ/MM/AAAA)</span></label>
            <input
              :value="treatment.start_date"
              type="text"
              placeholder="Ex: 01/03/2026"
              maxlength="10"
              class="h-12 rounded-lg w-full border border-gray-200 bg-slate-50 px-4 outline-none focus:border-teal-500"
              @input="(event) => handleTreatmentDateInput(event, 'start_date')"
            />
          </div>

          <div class="space-y-2">
            <label class="text-sm font-medium text-gray-900">Fin de traitement <span class="text-gray-400 font-normal">(JJ/MM/AAAA)</span></label>
            <input
              :value="treatment.end_date"
              type="text"
              placeholder="Ex: 30/03/2026"
              maxlength="10"
              class="h-12 rounded-lg w-full border border-gray-200 bg-slate-50 px-4 outline-none focus:border-teal-500"
              @input="(event) => handleTreatmentDateInput(event, 'end_date')"
            />
          </div>

          <div class="grid grid-cols-2 gap-3 pt-2">
            <button type="button" class="h-11 rounded-lg bg-teal-600 text-white font-semibold" @click="addTreatment">
              {{ editingTreatmentIndex > -1 ? "Mettre a jour" : "+ Ajouter" }}
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
                  class="text-xs font-semibold text-teal-700 hover:text-teal-800"
                  @click="editTreatment(index)"
                >
                  Modifier
                </button>
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
  </div>
</template>

<script setup>
import { computed, reactive, ref } from "vue";

const props = defineProps({
  form: { type: Object, required: true },
});

const form = props.form;
if (!Array.isArray(form.allergies)) form.allergies = [];
if (!Array.isArray(form.maladies_chroniques)) form.maladies_chroniques = [];
if (!Array.isArray(form.traitements)) form.traitements = [];

const bloodGroups = ["A+", "A-", "B+", "B-", "AB+", "AB-", "O+", "O-"];
const allergyOptions = [
  "Pollen",
  "Acariens",
  "Poils d'animaux",
  "Poussiere",
  "Arachides",
  "Fruits de mer",
  "Lait (lactose)",
  "Oeufs",
  "Gluten",
  "Penicilline",
  "Aspirine",
  "Piqures d'insectes",
  "Moisissures",
];
const diseaseOptions = [
  "Diabete",
  "Hypertension arterielle",
  "Asthme",
  "Maladie cardiaque",
  "Maladie renale chronique",
  "Maladie thyroidienne",
  "Arthrite",
  "Epilepsie",
  "Migraine chronique",
  "Maladie pulmonaire chronique",
  "Cholesterol eleve",
  "Depression",
  "Anemie",
];

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

const treatmentNameOptions = ref([
  "Paracetamol",
  "Ibuprofene",
  "Insuline",
  "Metformine",
  "Amlodipine",
  "Ventoline",
]);

const openAllergies = ref(false);
const openDiseases = ref(false);
const queryAllergies = ref("");
const queryDiseases = ref("");
const customAllergy = ref("");
const customDisease = ref("");
const showTreatmentForm = ref(false);
const editingTreatmentIndex = ref(-1);

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

const filteredAllergies = computed(() => {
  const q = queryAllergies.value.trim().toLowerCase();
  return q ? allergyOptions.filter((item) => item.toLowerCase().includes(q)) : allergyOptions;
});

const filteredDiseases = computed(() => {
  const q = queryDiseases.value.trim().toLowerCase();
  return q ? diseaseOptions.filter((item) => item.toLowerCase().includes(q)) : diseaseOptions;
});

const filteredTreatmentTypes = computed(() => {
  const q = queryTreatmentTypes.value.trim().toLowerCase();
  return q ? treatmentTypes.value.filter((item) => item.toLowerCase().includes(q)) : treatmentTypes.value;
});

const filteredTreatmentNames = computed(() => {
  const q = queryTreatmentNames.value.trim().toLowerCase();
  return q ? treatmentNameOptions.value.filter((item) => item.toLowerCase().includes(q)) : treatmentNameOptions.value;
});

function isSelected(key, value) {
  return Array.isArray(form[key]) ? form[key].includes(value) : false;
}

function toggleSelected(key, value) {
  if (!Array.isArray(form[key])) form[key] = [];
  if (form[key].includes(value)) form[key] = form[key].filter((item) => item !== value);
  else form[key] = [...form[key], value];
}

function removeSelected(key, value) {
  if (!Array.isArray(form[key])) return;
  form[key] = form[key].filter((item) => item !== value);
}

function addCustom(key, value) {
  const normalized = String(value || "").trim();
  if (!normalized) return;
  if (!Array.isArray(form[key])) form[key] = [];
  if (!form[key].includes(normalized)) form[key] = [...form[key], normalized];
  if (key === "allergies") customAllergy.value = "";
  if (key === "maladies_chroniques") customDisease.value = "";
}

function selectTreatmentType(value) {
  treatment.type = value;
  openTreatmentTypes.value = false;
}

function addCustomTreatmentType() {
  const value = customTreatmentType.value.trim();
  if (!value) return;
  if (!treatmentTypes.value.includes(value)) treatmentTypes.value = [...treatmentTypes.value, value];
  treatment.type = value;
  customTreatmentType.value = "";
  openTreatmentTypes.value = false;
}

function selectTreatmentName(value) {
  treatment.name = value;
  openTreatmentNames.value = false;
}

function addCustomTreatmentName() {
  const value = customTreatmentName.value.trim();
  if (!value) return;
  if (!treatmentNameOptions.value.includes(value)) treatmentNameOptions.value = [...treatmentNameOptions.value, value];
  treatment.name = value;
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
}

function buildTreatmentDuration() {
  if (treatment.start_date && treatment.end_date) return `${treatment.start_date} - ${treatment.end_date}`;
  return null;
}

function addTreatment() {
  if (!treatment.type || !treatment.name) return;
  if (!Array.isArray(form.traitements)) form.traitements = [];

  const nextItem = {
    type: treatment.type,
    name: treatment.name,
    dose: treatment.dose || null,
    frequency_unit: treatment.frequency_unit,
    frequency_count: Math.max(1, Number(treatment.frequency_count || 1)),
    start_date: treatment.start_date || null,
    end_date: treatment.end_date || null,
    duration: buildTreatmentDuration(),
  };

  if (editingTreatmentIndex.value > -1) {
    form.traitements.splice(editingTreatmentIndex.value, 1, nextItem);
  } else {
    form.traitements.push(nextItem);
  }

  cancelTreatment();
}

function editTreatment(index) {
  const item = form.traitements[index];
  if (!item) return;

  treatment.type = item.type || "";
  treatment.name = item.name || "";
  treatment.dose = item.dose || "";
  treatment.frequency_unit = item.frequency_unit || "jour";
  treatment.frequency_count = Number(item.frequency_count || 1);
  treatment.start_date = item.start_date || "";
  treatment.end_date = item.end_date || "";

  if (!treatment.start_date && !treatment.end_date && typeof item.duration === "string" && item.duration.includes(" - ")) {
    const parts = item.duration.split(" - ");
    treatment.start_date = parts[0] || "";
    treatment.end_date = parts[1] || "";
  }

  editingTreatmentIndex.value = index;
  showTreatmentForm.value = true;
}

function cancelTreatment() {
  showTreatmentForm.value = false;
  editingTreatmentIndex.value = -1;
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
  if (!Array.isArray(form.traitements)) return;
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

