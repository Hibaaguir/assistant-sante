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
          Groupe sanguin <span class=" text-red-600">*</span>
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
          Allergies 
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
          Maladies chroniques 
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

    </div>
  </div>
</template>

<script setup>
/*
  Etape 2 du profil sante.
  L'utilisateur renseigne groupe sanguin, allergies et maladies chroniques.
*/

import { computed, ref } from "vue";

const props = defineProps({
  form: { type: Object, required: true },
});

const form = props.form;
if (!Array.isArray(form.allergies)) form.allergies = [];
if (!Array.isArray(form.maladies_chroniques)) form.maladies_chroniques = [];

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

const openAllergies = ref(false);
const openDiseases = ref(false);
const queryAllergies = ref("");
const queryDiseases = ref("");
const customAllergy = ref("");
const customDisease = ref("");

const filteredAllergies = computed(() => {
  const q = queryAllergies.value.trim().toLowerCase();
  return q ? allergyOptions.filter((item) => item.toLowerCase().includes(q)) : allergyOptions;
});

const filteredDiseases = computed(() => {
  const q = queryDiseases.value.trim().toLowerCase();
  return q ? diseaseOptions.filter((item) => item.toLowerCase().includes(q)) : diseaseOptions;
});

function isSelected(key, value) {
  return form[key].includes(value);
}

function toggleSelected(key, value) {
  if (form[key].includes(value)) form[key] = form[key].filter((item) => item !== value);
  else form[key] = [...form[key], value];
}

function removeSelected(key, value) {
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
</script>
