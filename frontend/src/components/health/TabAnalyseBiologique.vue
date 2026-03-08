<template>
  <section class="mt-4 space-y-3">
    <div v-if="showLabsFilters" class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
      <div class="grid gap-3 md:grid-cols-3">
        <div>
          <label class="mb-1 block text-[12px] font-semibold text-slate-600">Type</label>
          <select v-model="labsFilterType" class="h-10 w-full rounded-xl border border-slate-300 bg-white px-3 text-[13px] text-slate-800 outline-none focus:border-blue-500">
            <option value="">Tous</option>
            <option v-for="type in labTypeOptions" :key="type" :value="type">{{ type }}</option>
          </select>
        </div>
        <div>
          <label class="mb-1 block text-[12px] font-semibold text-slate-600">Date</label>
          <input v-model="labsFilterDate" type="date" class="h-10 w-full rounded-xl border border-slate-300 bg-white px-3 text-[13px] text-slate-800 outline-none focus:border-blue-500" />
        </div>
        <div>
          <label class="mb-1 block text-[12px] font-semibold text-slate-600">Recherche</label>
          <input v-model.trim="labsFilterQuery" type="text" placeholder="Ex: CRP, TSH..." class="h-10 w-full rounded-xl border border-slate-300 bg-white px-3 text-[13px] text-slate-800 outline-none focus:border-blue-500" />
        </div>
      </div>
    </div>

    <article v-for="item in filteredAnalyses" :key="item.id" class="rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
      <div class="flex items-center justify-between">
        <div>
          <div class="flex items-center gap-3">
            <h3 class="text-[16px] font-semibold leading-none text-slate-900">{{ item.name }}</h3>
            <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-[11px] font-semibold leading-none text-emerald-700">Normal</span>
          </div>
          <div class="mt-2 flex items-center gap-4 text-slate-900">
            <p class="text-[22px] font-semibold leading-none">{{ item.value }} {{ item.unit }}</p>
            <div class="inline-flex items-center gap-2 text-[12px] text-slate-600">
              <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 2v3M16 2v3M3 9h18M5 5h14a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2z" /></svg>
              {{ item.date }}
            </div>
          </div>
        </div>
        <div class="flex items-center gap-2">
          <button
            type="button"
            class="rounded-lg border border-slate-300 px-3 py-1.5 text-[11px] font-semibold text-slate-700 hover:bg-slate-50"
            @click="ouvrirEditionAnalyse(item)"
          >
            Modifier
          </button>
          <button
            type="button"
            class="rounded-lg border border-rose-200 px-3 py-1.5 text-[11px] font-semibold text-rose-600 hover:bg-rose-50"
            @click="supprimerAnalyse(item)"
          >
            Supprimer
          </button>
        </div>
      </div>
    </article>

    <div v-if="!filteredAnalyses.length" class="rounded-2xl border border-slate-200 bg-white px-4 py-4 text-[13px] text-slate-600">
      Aucune analyse ne correspond aux filtres.
    </div>
  </section>

  <div v-if="showAnalysisModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/45 p-4">
    <div class="w-full max-w-[520px] rounded-3xl bg-white p-8 shadow-2xl">
      <div class="mb-5 flex items-center justify-between">
        <h3 class="text-[34px] font-semibold leading-none text-slate-900">{{ analysisModalTitle }}</h3>
        <button type="button" class="text-slate-500 hover:text-slate-700" @click="showAnalysisModal = false">
          <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 6 12 12M18 6 6 18" /></svg>
        </button>
      </div>

      <div class="space-y-4">
        <div>
          <label class="mb-2 block text-[13px] font-semibold text-slate-700">Type d'analyse</label>
          <select
            v-model="analysisForm.category"
            class="h-11 w-full rounded-2xl border border-slate-300 bg-white px-4 text-[15px] text-slate-800 outline-none focus:border-blue-500"
            @change="handleAnalysisCategoryChange"
          >
            <option value="">Sélectionnez</option>
            <option v-for="item in analysisCategoryOptions" :key="item" :value="item">{{ item }}</option>
          </select>
        </div>

        <div class="space-y-3">
          <div
            v-for="(result, index) in analysisForm.results"
            :key="`analysis-result-${index}`"
            class="rounded-2xl border border-slate-200 bg-slate-50 p-3"
          >
            <div class="mb-2 flex items-center justify-between">
              <div>
                <p class="text-[13px] font-semibold text-slate-700">Résultat {{ index + 1 }}</p>
                <p v-if="expandedAnalysisResultIndex !== index" class="mt-1 text-xs text-slate-500">
                  {{ getAnalysisResultSummary(result) }}
                </p>
              </div>
              <div class="flex items-center gap-2">
                <button
                  v-if="expandedAnalysisResultIndex !== index"
                  type="button"
                  class="text-xs font-semibold text-blue-600 hover:text-blue-700"
                  @click="expandedAnalysisResultIndex = index"
                >
                  Modifier
                </button>
                <button
                  v-if="analysisForm.results.length > 1 && !editingAnalysisId"
                  type="button"
                  class="text-xs font-semibold text-rose-600 hover:text-rose-700"
                  @click="removeAnalysisResult(index)"
                >
                  Supprimer
                </button>
              </div>
            </div>

            <div v-if="expandedAnalysisResultIndex === index">
            <div>
              <label class="mb-2 block text-[13px] font-semibold text-slate-700">Nom du résultat</label>
              <select
                v-model="result.result"
                :disabled="!analysisForm.category"
                class="h-11 w-full rounded-2xl border border-slate-300 bg-white px-4 text-[15px] text-slate-800 outline-none focus:border-blue-500 disabled:opacity-60"
                @change="handleAnalysisResultChange(index)"
              >
                <option value="">Sélectionnez</option>
                <option v-for="item in analysisResultOptions" :key="item.label" :value="item.label">{{ item.label }}</option>
              </select>
            </div>

            <div class="mt-3 grid grid-cols-2 gap-3">
              <div>
                <label class="mb-2 block text-[13px] font-semibold text-slate-700">Valeur</label>
                <input v-model="result.value" type="text" placeholder="5.2" class="h-11 w-full rounded-2xl border border-slate-300 bg-white px-4 text-[15px] outline-none focus:border-blue-500" />
              </div>
              <div>
                <label class="mb-2 block text-[13px] font-semibold text-slate-700">Unité</label>
                <input v-model="result.unit" type="text" placeholder="mmol/L" class="h-11 w-full rounded-2xl border border-slate-300 bg-white px-4 text-[15px] outline-none focus:border-blue-500" />
              </div>
            </div>
            </div>
          </div>

          <button
            v-if="!editingAnalysisId"
            type="button"
            class="h-10 rounded-xl border border-slate-300 px-4 text-[13px] font-semibold text-slate-700 hover:bg-slate-100"
            @click="addAnalysisResult"
          >
            + Ajouter un autre résultat
          </button>
        </div>

        <div>
          <label class="mb-2 block text-[13px] font-semibold text-slate-700">Date</label>
          <input v-model="analysisForm.date" type="date" class="h-11 w-full rounded-2xl border border-slate-300 bg-slate-50 px-4 text-[15px] outline-none focus:border-blue-500" />
        </div>

        <p v-if="analysisError" class="text-sm font-medium text-rose-600">
          {{ analysisError }}
        </p>

        <button type="button" class="mt-2 h-11 w-full rounded-2xl bg-emerald-600 text-[20px] font-semibold leading-none text-white hover:bg-emerald-700" @click="enregistrerAnalyse">
          {{ analysisSubmitLabel }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, reactive, ref } from "vue";
import api from "@/services/api";

const props = defineProps({
  analyses: { type: Array, default: () => [] },
});

const emit = defineEmits(["refresh"]);

const showAnalysisModal = ref(false);
const editingAnalysisId = ref(null);
const expandedAnalysisResultIndex = ref(0);
const analysisError = ref("");
const showLabsFilters = ref(false);
const labsFilterType = ref("");
const labsFilterDate = ref("");
const labsFilterQuery = ref("");

const analysisForm = reactive({
  category: "",
  results: [
    { result: "", value: "", unit: "" },
  ],
  date: new Date().toISOString().slice(0, 10),
});

const analysisCatalog = {
  "Biologie sanguine": [
    { label: "Glycémie", unit: "mmol/L" },
    { label: "Insuline", unit: "µIU/mL" },
    { label: "HbA1c", unit: "%" },
    { label: "CRP", unit: "mg/L" },
    { label: "Ferritine", unit: "ng/mL" },
    { label: "Créatinine", unit: "mg/L" },
    { label: "TSH", unit: "mUI/L" },
  ],
  Hématologie: [
    { label: "Hémoglobine", unit: "g/dL" },
    { label: "Hématocrite", unit: "%" },
    { label: "Globules blancs", unit: "G/L" },
    { label: "Plaquettes", unit: "G/L" },
    { label: "VGM", unit: "fL" },
  ],
  Radiologie: [
    { label: "Radiographie thoracique", unit: "" },
    { label: "Échographie abdominale", unit: "" },
    { label: "IRM cérébrale", unit: "" },
    { label: "Scanner thoracique", unit: "" },
  ],
  Hormonologie: [
    { label: "Cortisol", unit: "nmol/L" },
    { label: "FSH", unit: "UI/L" },
    { label: "LH", unit: "UI/L" },
    { label: "Prolactine", unit: "ng/mL" },
  ],
  Cardiologie: [
    { label: "Troponine", unit: "ng/L" },
    { label: "BNP", unit: "pg/mL" },
    { label: "D-dimères", unit: "mg/L" },
    { label: "CK-MB", unit: "UI/L" },
  ],
  "Fonction rénale": [
    { label: "Urée", unit: "mmol/L" },
    { label: "DFG", unit: "mL/min/1.73m²" },
    { label: "Microalbuminurie", unit: "mg/24h" },
    { label: "Acide urique", unit: "mg/L" },
  ],
  "Fonction hépatique": [
    { label: "ASAT", unit: "UI/L" },
    { label: "ALAT", unit: "UI/L" },
    { label: "Bilirubine totale", unit: "µmol/L" },
    { label: "GGT", unit: "UI/L" },
    { label: "Phosphatases alcalines", unit: "UI/L" },
  ],
  "Bilan lipidique": [
    { label: "Cholestérol total", unit: "mmol/L" },
    { label: "HDL", unit: "mmol/L" },
    { label: "LDL", unit: "mmol/L" },
    { label: "Triglycérides", unit: "mmol/L" },
  ],
  Urines: [
    { label: "Protéinurie", unit: "g/L" },
    { label: "Leucocyturie", unit: "/µL" },
    { label: "Nitrites", unit: "positif/négatif" },
    { label: "Glucosurie", unit: "g/L" },
  ],
  Microbiologie: [
    { label: "Hémoculture", unit: "positif/négatif" },
    { label: "ECBU", unit: "UFC/mL" },
    { label: "PCR virale", unit: "copies/mL" },
  ],
  Immunologie: [
    { label: "IgG", unit: "g/L" },
    { label: "IgA", unit: "g/L" },
    { label: "IgM", unit: "g/L" },
    { label: "Facteur rhumatoïde", unit: "UI/mL" },
    { label: "ANA", unit: "positif/négatif" },
  ],
};

const analysisCategoryOptions = Object.keys(analysisCatalog);
const analysisResultOptions = computed(() => analysisCatalog[analysisForm.category] ?? []);
const labTypeOptions = computed(() => {
  const values = props.analyses.map((item) => item.type).filter(Boolean);
  return [...new Set(values)];
});
const filteredAnalyses = computed(() => {
  const query = labsFilterQuery.value.trim().toLowerCase();
  return props.analyses.filter((item) => {
    const type = item.type;
    const dateIso = convertirDateIso(item.analysisDate);
    const matchType = !labsFilterType.value || type === labsFilterType.value;
    const matchDate = !labsFilterDate.value || dateIso === labsFilterDate.value;
    const haystack = `${item.name} ${item.value} ${item.unit}`.toLowerCase();
    const matchQuery = !query || haystack.includes(query);
    return matchType && matchDate && matchQuery;
  });
});
const analysisModalTitle = computed(() => (editingAnalysisId.value ? "Modifier une analyse" : "Ajouter une analyse"));
const analysisSubmitLabel = computed(() => (editingAnalysisId.value ? "Mettre à jour" : "Enregistrer"));

// Cette fonction convertit une valeur en nombre ou renvoie null.
function convertirNombreOuNull(value) {
  if (value === null || value === undefined || value === "") return null;
  const n = Number(value);
  return Number.isFinite(n) ? n : null;
}

// Cette fonction convertit une date en format ISO (YYYY-MM-DD).
function convertirDateIso(dateValue) {
  if (!dateValue) return new Date().toISOString().slice(0, 10);
  return String(dateValue).slice(0, 10);
}

// Cette fonction retourne l'option d'analyse pour une categorie + resultat.
function trouverOptionAnalyse(category, resultLabel) {
  const options = analysisCatalog[category] ?? [];
  return options.find((item) => item.label === resultLabel) ?? null;
}

// Cette fonction cree une ligne vide pour les resultats multiples.
function creerLigneResultatAnalyse() {
  return { result: "", value: "", unit: "" };
}

// Cette fonction ajoute une ligne de resultat.
function addAnalysisResult() {
  analysisForm.results.push(creerLigneResultatAnalyse());
  expandedAnalysisResultIndex.value = analysisForm.results.length - 1;
}

// Cette fonction supprime une ligne de resultat.
function removeAnalysisResult(index) {
  if (analysisForm.results.length <= 1) return;
  analysisForm.results.splice(index, 1);
  if (expandedAnalysisResultIndex.value >= analysisForm.results.length) {
    expandedAnalysisResultIndex.value = analysisForm.results.length - 1;
  }
}

// Cette fonction gere le changement de type d'analyse.
function handleAnalysisCategoryChange() {
  analysisForm.results = [creerLigneResultatAnalyse()];
  expandedAnalysisResultIndex.value = 0;
}

// Cette fonction applique l'unite par defaut selon le resultat selectionne.
function handleAnalysisResultChange(index) {
  const row = analysisForm.results[index];
  if (!row) return;

  const selected = trouverOptionAnalyse(analysisForm.category, row.result);
  if (selected?.unit) {
    row.unit = selected.unit;
  }
}

// Cette fonction remet a zero les champs du formulaire d'analyse.
function reinitialiserFormulaireAnalyse() {
  analysisError.value = "";
  analysisForm.category = "";
  analysisForm.results = [creerLigneResultatAnalyse()];
  expandedAnalysisResultIndex.value = 0;
  analysisForm.date = new Date().toISOString().slice(0, 10);
}

// Cette fonction genere un resume court d'un resultat pour l'affichage replie.
function getAnalysisResultSummary(result) {
  const resultName = result?.result?.trim();
  const value = String(result?.value ?? "").trim();
  const unit = String(result?.unit ?? "").trim();
  const left = resultName || "Resultat non renseigne";
  const right = value ? `${value}${unit ? ` ${unit}` : ""}` : "Valeur non renseignee";
  return `${left} - ${right}`;
}

// Cette fonction enregistre une analyse avec validation simple des champs.
async function enregistrerAnalyse() {
  analysisError.value = "";
  if (!analysisForm.category) {
    analysisError.value = "Veuillez choisir un type d'analyse.";
    return;
  }

  const validRows = [];
  for (const row of analysisForm.results) {
    const analysisType = String(analysisForm.category ?? "").trim();
    const analysisResult = String(row.result ?? "").trim();
    const numericValue = convertirNombreOuNull(row.value);

    if (!analysisType) {
      analysisError.value = "Le type d'analyse est obligatoire.";
      return;
    }
    if (!analysisResult) {
      analysisError.value = "Chaque resultat doit etre selectionne dans la liste.";
      return;
    }
    if (numericValue === null) {
      analysisError.value = "Chaque resultat doit avoir une valeur numerique valide.";
      return;
    }

    validRows.push({
      analysis_type: analysisType,
      analysis_result: analysisResult,
      value: numericValue,
      unit: row.unit || null,
      analysis_date: convertirDateIso(analysisForm.date),
    });
  }

  if (!validRows.length) {
    analysisError.value = "Ajoutez au moins un resultat.";
    return;
  }

  if (editingAnalysisId.value) {
    await api.put(`/health-data/labs/${editingAnalysisId.value}`, validRows[0]);
  } else {
    await Promise.all(validRows.map((payload) => api.post("/health-data/labs", payload)));
    // On reset les filtres pour afficher immediatement les nouvelles analyses ajoutees.
    labsFilterType.value = "";
    labsFilterDate.value = "";
    labsFilterQuery.value = "";
  }

  editingAnalysisId.value = null;
  reinitialiserFormulaireAnalyse();
  showAnalysisModal.value = false;
  emit("refresh");
}

// Cette fonction pre-remplit le formulaire pour modifier une analyse.
function ouvrirEditionAnalyse(item) {
  editingAnalysisId.value = item.id;
  analysisError.value = "";
  analysisForm.category = item.type ?? "";
  analysisForm.results = [
    {
      result: item.result ?? "",
      value: String(item.value ?? ""),
      unit: item.unit ?? "",
    },
  ];
  analysisForm.date = item.analysisDate ?? new Date().toISOString().slice(0, 10);
  expandedAnalysisResultIndex.value = 0;
  showAnalysisModal.value = true;
}

// Cette fonction supprime une analyse apres confirmation utilisateur.
async function supprimerAnalyse(item) {
  const ok = window.confirm(`Supprimer l'analyse "${item.name}" ?`);
  if (!ok) return;
  await api.delete(`/health-data/labs/${item.id}`);
  emit("refresh");
}

// Cette methode est exposee pour que le parent puisse ouvrir la modale d'ajout.
function openAddModal() {
  editingAnalysisId.value = null;
  reinitialiserFormulaireAnalyse();
  showAnalysisModal.value = true;
}

// Cette methode est exposee pour que le parent puisse basculer l'affichage des filtres.
function toggleFilters() {
  showLabsFilters.value = !showLabsFilters.value;
}

defineExpose({ openAddModal, toggleFilters });
</script>
