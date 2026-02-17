<template>
  <div class="space-y-6">
    <!-- Allergies -->
    <section>
      <h3 class="text-white/90 font-semibold mb-3">Allergies</h3>
          <div class="flex flex-wrap gap-2">
            <button
              v-for="item in ALLERGIES"
              :key="item"
              type="button"
              @click="toggleMulti('allergies', item)"
              :class="[chipBase, isSelected('allergies', item) ? chipActive : chipInactive]"
            >
              {{ item }}
            </button>
            <!-- Autre -->
            <div class="flex items-center gap-2">
              <button
                type="button"
                @click="showAllergieAutre = !showAllergieAutre"
                :class="[chipBase, showAllergieAutre ? chipActive : chipInactive]"
              >
                Autre
              </button>
              <input
                v-if="showAllergieAutre"
                v-model="allergieAutreText"
                @keydown.enter.prevent="addAllergieAutre"
                placeholder="Précisez une allergie"
                class="rounded-md px-3 py-2 text-sm"
              />
              <button v-if="showAllergieAutre" type="button" @click="addAllergieAutre" class="text-sm underline">Ajouter</button>
            </div>

            <!-- custom allergies added by user -->
            <template v-if="Array.isArray(form.allergies)">
              <button
                v-for="(a, ai) in form.allergies.filter(x => !ALLERGIES.includes(x))"
                :key="'custom-all-'+ai"
                type="button"
                @click="removeCustomAllergie(a)"
                :class="[chipBase, chipActive]"
                title="Cliquer pour supprimer"
              >
                {{ a }} ×
              </button>
            </template>
          </div>
    </section>

    <!-- Maladies chroniques -->
    <section>
      <h3 class="text-white/90 font-semibold mb-3">Maladies Chroniques</h3>
      <div class="flex flex-wrap gap-2">
        <button
          v-for="item in MALADIES"
          :key="item"
          type="button"
          @click="toggleMulti('maladies_chroniques', item)"
          :class="[chipBase, isSelected('maladies_chroniques', item) ? chipActive : chipInactive]"
        >
          {{ item }}
        </button>
        <div class="flex items-center gap-2">
          <button
            type="button"
            @click="showMaladieAutre = !showMaladieAutre"
            :class="[chipBase, showMaladieAutre ? chipActive : chipInactive]"
          >
            Autre
          </button>
          <input
            v-if="showMaladieAutre"
            v-model="maladieAutreText"
            @keydown.enter.prevent="addMaladieAutre"
            placeholder="Précisez une maladie"
            class="rounded-md px-3 py-2 text-sm"
          />
          <button v-if="showMaladieAutre" type="button" @click="addMaladieAutre" class="text-sm underline">Ajouter</button>
        </div>

        <!-- custom maladies added by user -->
        <template v-if="Array.isArray(form.maladies_chroniques)">
          <button
            v-for="(m, mi) in form.maladies_chroniques.filter(x => !MALADIES.includes(x))"
            :key="'custom-mal-'+mi"
            type="button"
            @click="removeCustomMaladie(m)"
            :class="[chipBase, chipActive]"
            title="Cliquer pour supprimer"
          >
            {{ m }} ×
          </button>
        </template>
      </div>
    </section>

    <!-- Traitements & Médicaments (fusion) -->
    <section>
      <h3 class="text-white/90 font-semibold mb-3">Traitements / Médicaments</h3>

      <div class="mb-3">
        <div class="text-white/80 text-sm mb-2">Choisir le type de traitement</div>
        <div class="flex flex-wrap gap-2">
          <button
            v-for="type in TRAITEMENTS"
            :key="type"
            type="button"
            @click="currentTreatmentType = type; showTreatmentForm = true"
            :class="[chipBase, currentTreatmentType === type ? chipActive : chipInactive]"
          >
            {{ type }}
          </button>
          <!-- Autre pour traitements -->
          <div class="flex items-center gap-2">
            <button
              type="button"
              @click="showTreatmentAutre = !showTreatmentAutre"
              :class="[chipBase, showTreatmentAutre ? chipActive : chipInactive]"
            >
              Autre
            </button>
            <input
              v-if="showTreatmentAutre"
              v-model="treatmentAutreText"
              @keydown.enter.prevent="addTreatmentAutre"
              placeholder="Type personnalisé"
              class="rounded-md px-3 py-2 text-sm"
            />
            <button v-if="showTreatmentAutre" type="button" @click="addTreatmentAutre" class="text-sm underline">Ajouter</button>
          </div>
        </div>
      </div>

      <div v-if="showTreatmentForm" class="rounded-2xl bg-white/10 border border-white/15 p-4 mb-4">
        <div class="mb-2 text-white/90 font-semibold">Détails du traitement : {{ currentTreatmentType }}</div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
          <input v-model="treatmentName" placeholder="Nom du traitement" class="rounded-md px-3 py-2 text-sm" />
          <input v-model="treatmentDose" placeholder="Dose (ex: 10mg)" class="rounded-md px-3 py-2 text-sm" />
          <div>
            <label class="text-xs text-white/70">Fréquence</label>
            <select v-model="treatmentFrequencyUnit" class="block rounded-md px-3 py-2 mt-1">
              <option value="jour">Par jour</option>
              <option value="semaine">Par semaine</option>
              <option value="mois">Par mois</option>
            </select>
          </div>
          <div>
            <label class="text-xs text-white/70">Nombre par période</label>
            <select v-model.number="treatmentFrequencyCount" class="block rounded-md px-3 py-2 mt-1">
              <option v-for="n in 4" :key="n" :value="n">{{ n }}</option>
            </select>
          </div>
        </div>

        <div class="mt-3">
          <button type="button" @click="addTreatment" class="px-4 py-2 rounded bg-cyan-300 text-black font-semibold">Ajouter le traitement</button>
          <button type="button" @click="resetTreatmentForm" class="ml-3 px-4 py-2 rounded border">Annuler</button>
        </div>
      </div>

      <div v-if="Array.isArray(form.traitements) && form.traitements.length" class="space-y-2">
        <div v-for="(t, idx) in form.traitements" :key="idx" class="rounded-md bg-white/5 p-3 flex justify-between items-start">
          <div class="text-white/90">
            <div class="font-semibold">{{ t.type }} - {{ t.name || '-' }}</div>
            <div class="text-sm text-white/70">Dose: {{ t.dose || '-' }} • {{ t.frequency_count || '-' }} / {{ t.frequency_unit || '-' }}</div>
          </div>
          <button type="button" @click="removeTreatment(idx)" class="text-sm underline">Supprimer</button>
        </div>
      </div>
    </section>

    <!-- Médicaments UI removed (moved into traitements) -->

    <!-- Fumeur / Alcool -->
    <section class="grid grid-cols-1 sm:grid-cols-2 gap-3">
      <div class="rounded-2xl bg-white/10 border border-white/15 p-4 flex items-center justify-between">
        <div class="flex items-center gap-2 text-white/90 font-semibold text-sm">
          <span class="opacity-80">←</span>
          <span>Fumeur</span>
        </div>
        <button
          type="button"
          @click="form.fumeur = !form.fumeur"
          class="relative inline-flex h-5 w-10 items-center rounded-full transition-colors"
          :class="form.fumeur ? 'bg-cyan-300/90' : 'bg-white/25'"
          aria-label="Toggle fumeur"
        >
          <span
            class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"
            :class="form.fumeur ? 'translate-x-5' : 'translate-x-1'"
          />
        </button>
      </div>

      <div class="rounded-2xl bg-white/10 border border-white/15 p-4 flex items-center justify-between">
        <div class="flex items-center gap-2 text-white/90 font-semibold text-sm">
          <span class="text-pink-200">🍷</span>
          <span>Alcool</span>
        </div>
        <button
          type="button"
          @click="form.alcool = !form.alcool"
          class="relative inline-flex h-5 w-10 items-center rounded-full transition-colors"
          :class="form.alcool ? 'bg-cyan-300/90' : 'bg-white/25'"
          aria-label="Toggle alcool"
        >
          <span
            class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"
            :class="form.alcool ? 'translate-x-5' : 'translate-x-1'"
          />
        </button>
      </div>
    </section>

    <!-- Consultation médecin -->
    <section>
      <h3 class="text-white/90 font-semibold mb-2">Suivi par un médecin</h3>
      <div class="flex items-center gap-3 mb-2">
        <div class="text-white/80">Consultez-vous un médecin ?</div>
        <button type="button" @click="form.consulte_medecin = !form.consulte_medecin" :class="['px-3 py-1 rounded', form.consulte_medecin ? 'bg-cyan-300 text-black' : 'bg-white/10']">{{ form.consulte_medecin ? 'Oui' : 'Non' }}</button>
      </div>

      <div v-if="form.consulte_medecin" class="mb-2">
        <div class="text-white/70 text-sm mb-2">Voulez-vous que votre médecin consulte vos données de santé ?</div>
        <div class="flex items-center gap-2 mb-3">
          <button type="button" @click="form.medecin_peut_consulter = true" :class="['px-3 py-1 rounded', form.medecin_peut_consulter ? 'bg-cyan-300 text-black' : 'bg-white/10']">Oui</button>
          <button type="button" @click="form.medecin_peut_consulter = false" :class="['px-3 py-1 rounded', !form.medecin_peut_consulter ? 'bg-white/10' : 'bg-white/5']">Non</button>
        </div>

        <div v-if="form.medecin_peut_consulter">
          <label class="text-white/70 text-sm">Email du médecin</label>
          <input v-model="form.medecin_email" type="email" placeholder="medecin@example.com" class="block rounded-md px-3 py-2 mt-1" />
        </div>
      </div>
    </section>

    <!-- Boutons bas -->
    <div class="flex items-center justify-between pt-2">
      <button
        type="button"
        @click="$emit('retour')"
        class="inline-flex items-center gap-2 rounded-full px-6 py-3 text-white/90
               bg-white/10 border border-white/15 hover:bg-white/15 transition"
      >
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none">
          <path
            d="M15 18l-6-6 6-6"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
          />
        </svg>
        Retour
      </button>

      <button
        type="button"
        @click="$emit('enregistrer')"
        class="inline-flex items-center gap-2 rounded-full px-7 py-3 font-semibold text-white
               bg-gradient-to-r from-emerald-300 to-cyan-300
               shadow-[0_12px_30px_rgba(0,255,200,0.25)]
               hover:shadow-[0_16px_40px_rgba(0,255,200,0.35)]
               transition-all"
      >
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none">
          <path
            d="M5 13l4 4L19 7"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
          />
        </svg>
        Terminé
      </button>
    </div>
  </div>
</template>

<script setup>
const { form } = defineProps({
  form: { type: Object, required: true },
});

const ALLERGIES = [
  "Pollen",
  "Arachides",
  "Fruits de mer",
  "Lactose",
  "Gluten",
  "Pénicilline",
  "Aspirine",
  "Abeilles/Guêpes",
  "Poussière",
  "Animaux",
];

const MALADIES = [
  "Diabète",
  "Hypertension",
  "Asthme",
  "Arthrite",
  "Maladie cardiaque",
  "Cholestérol élevé",
  "Migraine chronique",
  "Thyroïde",
  "Anémie",
  "Aucune",
];

const TRAITEMENTS = [
  "Insuline",
  "Antihypertenseur",
  "Inhalateur",
  "Anti-inflammatoire",
  "Anticoagulant",
  "Statines",
  "Antidépresseur",
  "Anxiolytique",
  "Supplément vitaminique",
  "Aucun",
];

// OBJECTIFS moved to Etape1

const chipBase = "px-3 py-2 rounded-md text-[11px] font-semibold border transition select-none";
const chipInactive = "bg-white/10 border-white/15 text-white/80 hover:bg-white/15";
const chipActive = "bg-[#1B78D6] border-cyan-200/40 text-white shadow-[0_10px_30px_rgba(0,0,0,0.25)]";

// local UI state for 'Autre' inputs and treatment form
import { ref } from 'vue';
const showAllergieAutre = ref(false);
const allergieAutreText = ref('');
const showMaladieAutre = ref(false);
const maladieAutreText = ref('');

const showTreatmentForm = ref(false);
const showTreatmentAutre = ref(false);
const currentTreatmentType = ref('');
const treatmentAutreText = ref('');
const treatmentName = ref('');
const treatmentDose = ref('');
const treatmentFrequencyUnit = ref('jour');
const treatmentFrequencyCount = ref(1);

function isSelected(key, value) {
  return Array.isArray(form[key]) ? form[key].includes(value) : false;
}

function toggleMulti(key, value) {
  if (!Array.isArray(form[key])) form[key] = [];

  if (value === "Aucune" || value === "Aucun") {
    form[key] = [value];
    return;
  }

  form[key] = form[key].filter((v) => v !== "Aucune" && v !== "Aucun");

  const idx = form[key].indexOf(value);
  if (idx === -1) form[key].push(value);
  else form[key].splice(idx, 1);
}

function addAllergieAutre() {
  if (!allergieAutreText.value) return;
  if (!Array.isArray(form.allergies)) form.allergies = [];
  form.allergies.push(allergieAutreText.value);
  // keep input visible but clear text so user sees their added chip
  allergieAutreText.value = '';
}

function addMaladieAutre() {
  if (!maladieAutreText.value) return;
  if (!Array.isArray(form.maladies_chroniques)) form.maladies_chroniques = [];
  form.maladies_chroniques.push(maladieAutreText.value);
  maladieAutreText.value = '';
}

function removeCustomAllergie(value) {
  if (!Array.isArray(form.allergies)) return;
  form.allergies = form.allergies.filter((v) => v !== value);
}

function removeCustomMaladie(value) {
  if (!Array.isArray(form.maladies_chroniques)) return;
  form.maladies_chroniques = form.maladies_chroniques.filter((v) => v !== value);
}

function addTreatmentAutre() {
  if (!treatmentAutreText.value) return;
  currentTreatmentType.value = treatmentAutreText.value;
  showTreatmentAutre.value = false;
  treatmentAutreText.value = '';
  showTreatmentForm.value = true;
}

function addTreatment() {
  if (!currentTreatmentType.value) return;
  if (!Array.isArray(form.traitements)) form.traitements = [];

  form.traitements.push({
    type: currentTreatmentType.value,
    name: treatmentName.value || null,
    dose: treatmentDose.value || null,
    frequency_unit: treatmentFrequencyUnit.value,
    frequency_count: treatmentFrequencyCount.value,
  });

  resetTreatmentForm();
}

function resetTreatmentForm() {
  showTreatmentForm.value = false;
  currentTreatmentType.value = '';
  treatmentName.value = '';
  treatmentDose.value = '';
  treatmentFrequencyUnit.value = 'jour';
  treatmentFrequencyCount.value = 1;
}

function removeTreatment(idx) {
  if (!Array.isArray(form.traitements)) return;
  form.traitements.splice(idx, 1);
}
</script>
