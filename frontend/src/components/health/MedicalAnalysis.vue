<template>
    <section class="mt-4 space-y-3">
        <!-- Filtres -->
        <FilterCard
            title="Analyses"
            subtitle="Filtrez par type ou par date."
            :show-reset="!!(filters.type || filters.date)"
            @reset="reinitialiserFiltres"
        >
            <select v-model="filters.type" class="input-field">
                <option value="">Tous les types</option>
                <option v-for="opt in labTypeOptions" :key="opt" :value="opt">
                    {{ opt }}
                </option>
            </select>
            <input v-model="filters.date" type="date" class="input-field" />
        </FilterCard>

        <!-- Liste des analyses -->
        <article
            v-for="item in filteredAnalyses"
            :key="item.id"
            class="rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm"
        >
            <div class="flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-3">
                        <Typography
                            tag="h3"
                            variant="h5-style"
                            class="text-slate-900"
                        >
                            {{ item.name }}
                        </Typography>
                    </div>
                    <div class="mt-2 flex items-center gap-4 text-slate-900">
                        <p class="text-[22px] font-semibold leading-none">
                            {{ item.value }} {{ item.unit }}
                        </p>
                        <span
                            class="inline-flex items-center gap-2 text-base font-medium text-slate-700"
                        >
                            <CalendarIcon />
                            {{ item.date }}
                        </span>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <BaseButton
                        type="button"
                        variant="update"
                        size="sm"
                        @click="ouvrirEditionAnalyse(item)"
                    >
                        Modifier
                    </BaseButton>
                    <BaseButton
                        type="button"
                        variant="delete"
                        size="sm"
                        @click="supprimerAnalyse(item)"
                    >
                        Supprimer
                    </BaseButton>
                </div>
            </div>
        </article>

        <div
            v-if="!filteredAnalyses.length"
            class="rounded-2xl border border-slate-200 bg-white px-4 py-4 text-[13px] text-slate-600"
        >
            Aucune analyse ne correspond aux filtres.
        </div>
    </section>

    <!-- Modale ajout / modification -->
    <div
        v-if="showAnalysisModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/45 backdrop-blur-sm p-4"
    >
        <div class="w-full max-w-[520px] rounded-3xl bg-white p-8 shadow-2xl">
            <div class="mb-5 flex items-center justify-between">
                <Typography tag="h3" variant="h3-style">
                    {{ modalTitle }}
                </Typography>
                <BaseButton
                    type="button"
                    variant="outline"
                    size="sm"
                    @click="showAnalysisModal = false"
                >
                    <CloseIcon />
                </BaseButton>
            </div>

            <div class="space-y-4">
                <!-- Type d'analyse -->
                <div>
                    <label
                        class="mb-3 block text-base font-semibold text-slate-800"
                        >Type d'analyse
                        <span class="text-rose-600">*</span></label
                    >
                    <select
                        v-model="form.category"
                        class="h-12 w-full rounded-2xl border border-slate-300 bg-white px-4 text-base font-medium text-slate-800 outline-none focus:border-blue-500 transition"
                        @change="onCategoryChange"
                    >
                        <option value="">Sélectionnez</option>
                        <option
                            v-for="cat in categoryOptions"
                            :key="cat"
                            :value="cat"
                        >
                            {{ cat }}
                        </option>
                    </select>
                </div>

                <!-- Résultats -->
                <div class="space-y-4">
                    <div
                        v-for="(row, index) in form.results"
                        :key="index"
                        class="rounded-2xl border border-slate-200 bg-slate-50 p-5"
                    >
                        <div class="mb-3 flex items-center justify-between">
                            <div>
                                <p
                                    class="text-base font-semibold text-slate-800"
                                >
                                    Résultat {{ index + 1 }}
                                </p>
                                <p
                                    v-if="expandedIndex !== index"
                                    class="mt-2 text-sm font-medium text-slate-600"
                                >
                                    {{ summarizeRow(row) }}
                                </p>
                            </div>
                            <div class="flex items-center gap-3">
                                <BaseButton
                                    v-if="expandedIndex !== index"
                                    type="button"
                                    variant="update"
                                    size="sm"
                                    @click="expandedIndex = index"
                                >
                                    Modifier
                                </BaseButton>
                                <BaseButton
                                    v-if="!editingId && form.results.length > 1"
                                    type="button"
                                    variant="delete"
                                    size="sm"
                                    @click="removeRow(index)"
                                >
                                    Supprimer
                                </BaseButton>
                            </div>
                        </div>

                        <div v-if="expandedIndex === index" class="space-y-4">
                            <div>
                                <label
                                    class="mb-3 block text-base font-semibold text-slate-800"
                                    >Nom du résultat
                                    <span class="text-rose-600">*</span></label
                                >
                                <select
                                    v-model="row.result"
                                    :disabled="!form.category"
                                    class="h-12 w-full rounded-2xl border border-slate-300 bg-white px-4 text-base font-medium text-slate-800 outline-none focus:border-blue-500 transition disabled:opacity-60"
                                    @change="onResultChange(index)"
                                >
                                    <option value="">Sélectionnez</option>
                                    <option
                                        v-for="opt in resultOptions"
                                        :key="opt.label"
                                        :value="opt.label"
                                    >
                                        {{ opt.label }}
                                    </option>
                                </select>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label
                                        class="mb-3 block text-base font-semibold text-slate-800"
                                        >Valeur
                                        <span class="text-rose-600"
                                            >*</span
                                        ></label
                                    >
                                    <input
                                        v-model="row.value"
                                        type="text"
                                        placeholder="5.2"
                                        class="h-12 w-full rounded-2xl border border-slate-300 bg-white px-4 text-base font-medium outline-none focus:border-blue-500 transition"
                                    />
                                </div>
                                <div>
                                    <label
                                        class="mb-3 block text-base font-semibold text-slate-800"
                                        >Unité
                                        <span class="text-rose-600"
                                            >*</span
                                        ></label
                                    >
                                    <input
                                        v-model="row.unit"
                                        type="text"
                                        placeholder="mmol/L"
                                        class="h-12 w-full rounded-2xl border border-slate-300 bg-white px-4 text-base font-medium outline-none focus:border-blue-500 transition"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <BaseButton
                        v-if="!editingId"
                        type="button"
                        variant="add"
                        size="md"
                        @click="addRow"
                    >
                        + Ajouter un autre résultat
                    </BaseButton>
                </div>

                <!-- Date -->
                <div>
                    <label
                        class="mb-3 block text-base font-semibold text-slate-800"
                        >Date <span class="text-rose-600">*</span></label
                    >
                    <input
                        v-model="form.date"
                        type="date"
                        class="h-12 w-full rounded-2xl border border-slate-300 bg-white px-4 text-base font-medium outline-none focus:border-blue-500 transition"
                    />
                </div>

                <p v-if="formError" class="text-sm font-semibold text-rose-600">
                    {{ formError }}
                </p>

                <BaseButton
                    type="button"
                    variant="save"
                    size="lg"
                    class="w-full mt-2"
                    @click="saveAnalysis"
                >
                    {{ modalSubmitLabel }}
                </BaseButton>
            </div>
        </div>
    </div>

    <!-- Confirmation suppression -->
    <DialogueConfirmation
        :open="showDeleteConfirm"
        title="Supprimer l'analyse"
        :message="deleteMessage"
        confirm-label="Supprimer"
        cancel-label="Annuler"
        @cancel="cancelDelete"
        @confirm="confirmDelete"
    />
</template>

<script setup>
import { computed, reactive, ref } from "vue";
import api from "@/services/api";
import { useNotificationsStore } from "@/stores/notifications";
import DialogueConfirmation from "@/components/ui/ConfirmationDialog.vue";
import Typography from "@/components/ui/Typography.vue";
import BaseButton from "@/components/ui/BaseButton.vue";
import FilterCard from "@/components/ui/FilterCard.vue";
import { today } from "@/components/doctors/doctorUtilities.js";

const CalendarIcon = {
    template: `<svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 2v3M16 2v3M3 9h18M5 5h14a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2z"/></svg>`,
};
const CloseIcon = {
    template: `<svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 6 12 12M18 6 6 18"/></svg>`,
};

//props et emits
const props = defineProps({ analyses: { type: Array, default: () => [] } });
const emit = defineEmits(["refresh"]);
const notifications = useNotificationsStore();

//cle : type d'analyse, valeur : liste de résultats possibles avec unité suggérée
const CATALOG = {
    "Biologie sanguine": [
        { label: "Glycémie", unit: "mmol/L" },
        { label: "Insuline", unit: "µIU/mL" },
        { label: "HbA1c", unit: "%" },
        { label: "CRP", unit: "mg/L" },
        { label: "Ferritine", unit: "ng/mL" },
        { label: "Créatinine", unit: "mg/L" },
        { label: "TSH", unit: "mUI/L" },
    ],
    "Hématologie": [
        { label: "Hémoglobine", unit: "g/dL" },
        { label: "Hématocrite", unit: "%" },
        { label: "Globules blancs", unit: "G/L" },
        { label: "Plaquettes", unit: "G/L" },
        { label: "VGM", unit: "fL" },
    ],
    "Radiologie": [
        { label: "Radiographie thoracique", unit: "" },
        { label: "Échographie abdominale", unit: "" },
        { label: "IRM cérébrale", unit: "" },
        { label: "Scanner thoracique", unit: "" },
    ],
    "Hormonologie": [
        { label: "Cortisol", unit: "nmol/L" },
        { label: "FSH", unit: "UI/L" },
        { label: "LH", unit: "UI/L" },
        { label: "Prolactine", unit: "ng/mL" },
    ],
    "Cardiologie": [
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
    "Urines": [
        { label: "Protéinurie", unit: "g/L" },
        { label: "Leucocyturie", unit: "/µL" },
        { label: "Nitrites", unit: "positif/négatif" },
        { label: "Glucosurie", unit: "g/L" },
    ],
    "Microbiologie": [
        { label: "Hémoculture", unit: "positif/négatif" },
        { label: "ECBU", unit: "UFC/mL" },
        { label: "PCR virale", unit: "copies/mL" },
    ],
    "Immunologie": [
        { label: "IgG", unit: "g/L" },
        { label: "IgA", unit: "g/L" },
        { label: "IgM", unit: "g/L" },
        { label: "Facteur rhumatoïde", unit: "UI/mL" },
        { label: "ANA", unit: "positif/négatif" },
    ],
};

//les états locaux
const showAnalysisModal = ref(false); //affiche la modale d'ajout / modification
const showDeleteConfirm = ref(false);
const editingId = ref(null); //id de l'analyse en cours d'édition id null si ajout
const expandedIndex = ref(0); // contrôle quelle ligne de résultat est ouverte dans le formulaire
const formError = ref("");
const pendingDelete = ref(null);


const filters = reactive({ type: "", date: "" });
const form = reactive({ category: "", results: [emptyRow()], date: today() });
 
//Extrait toutes les clés du CATALOG
const categoryOptions = Object.keys(CATALOG);
//Extrait la liste des résultats possibles en fonction de la catégorie sélectionnée dans le formulaire
const resultOptions = computed(() => CATALOG[form.category] ?? []);
//Extrait la liste des types d'analyses présents dans les données pour alimenter le filtre de type d'analyse
const labTypeOptions = computed(() => [
    ...new Set(//Set supprime automatiquement les doublons 
        props.analyses.map((a) => a.type))]);
//Applique les filtres de type et de date sur les analyses à afficher
const filteredAnalyses = computed(() => {
    return props.analyses.filter((a) => {
        const typeMatch = !filters.type || a.type === filters.type;
        const dateMatch = !filters.date || isoDate(a.analysisDate) === filters.date;
        return typeMatch && dateMatch;
    });
});

//les textes dynamiques
const modalTitle = computed(() =>
    editingId.value ? "Modifier une analyse" : "Ajouter une analyse",
);
const modalSubmitLabel = computed(() =>
    editingId.value ? "Mettre à jour" : "Enregistrer",
);
const deleteMessage = computed(() => {
    const name = pendingDelete.value?.name
        ? `"${pendingDelete.value.name}"`
        : "cet élément";
    return `Vous êtes sur le point de supprimer ${name}. Cette action est irréversible.`;
});


//fonction qui formate une date au format ISO YYYY-MM-DD ou retourne la date du jour si la valeur est falsy ou invalide
function isoDate(v) {
    return v ? String(v).slice(0, 10) : today();
}
// Convertit une valeur en nombre ou retourne null si la conversion échoue ou si la valeur n'est pas finie (ex: "5.2abc" retourne null)
function toNumber(v) {
    const n = Number(v);
    return Number.isFinite(n) ? n : null;
}
// Retourne une ligne vide pour initialiser le formulaire ou ajouter une nouvelle ligne de résultat
function emptyRow() {
    return { result: "", value: "", unit: "" };
}
// Résume une ligne de résultat pour l'affichage dans la liste "Glycémie - 5.2 mmol/L"
function summarizeRow(row) {
    const name = row.result || "Résultat non renseigné";
    const val = String(row.value ?? "").trim();
    const right = val
        ? `${val}${row.unit ? ` ${row.unit}` : ""}`
        : "Valeur non renseignée";
    return `${name} — ${right}`;
}
function resetForm() {
    formError.value = "";
    form.category = "";
    form.results = [emptyRow()];
    form.date = today();
    expandedIndex.value = 0;
}
// Ajoute une nouvelle ligne vide dans le formulaire quand on clique sur ajouter resultat 
function addRow() {
    form.results.push(emptyRow());
    expandedIndex.value = form.results.length - 1;
}
//supprimer une ligne de résultat du formulaire d'édition uniquement en mode ajout
function removeRow(i) {
    form.results.splice(i, 1);
    if (expandedIndex.value >= form.results.length)
        expandedIndex.value = form.results.length - 1;
}
//Réinitialise les résultats quand on change de catégorie
function onCategoryChange() {
    form.results = [emptyRow()];
    expandedIndex.value = 0;
}
// Remplit automatiquement l'unité quand on choisit un résultat dans le select
function onResultChange(i) {
    const opt = (CATALOG[form.category] ?? []).find(
        (o) => o.label === form.results[i].result,
    );
    if (opt?.unit) form.results[i].unit = opt.unit;
}

async function saveAnalysis() {
    formError.value = "";
    if (!form.category) {
        formError.value = "Veuillez choisir un type d'analyse.";
        return;
    }
    const analysisDate = form.date || "";
    if (!analysisDate) {
        formError.value = "La date de l'analyse est obligatoire.";
        return;
    }

    for (let i = 0; i < form.results.length; i++) {
        const row = form.results[i];

        // Vérifie que le nom du résultat est sélectionné
        if (!row.result) {
            formError.value = "Chaque résultat doit être sélectionné.";
            expandedIndex.value = i;
            return;
        }

        // Vérifie que la valeur est un nombre non vide
        if (String(row.value ?? "").trim() === "" || toNumber(row.value) === null) {
            formError.value = "Veuillez saisir une valeur numérique pour chaque résultat.";
            expandedIndex.value = i;
            return;
        }

        // Vérifie que l'unité n'est pas vide
        if (!row.unit?.trim()) {
            formError.value = "Veuillez saisir une unité pour chaque résultat.";
            expandedIndex.value = i;
            return;
        }
    }
    // Prépare les données à envoyer à l'API
    const rows = form.results.map((row) => ({
        analysis_type: form.category,
        result_name: row.result,
        value: toNumber(row.value),
        unit: row.unit.trim(),
        analysis_date: isoDate(analysisDate),
    }));
// Envoie les requêtes à l'API une requête PUT si édition, sinon une requête POST par résultat
    try {
        if (editingId.value) {
            await api.put(`/health-data/labs/${editingId.value}`, rows[0]);
            notifications.itemUpdated();
        } else {
            await Promise.all(
                rows.map((p) => api.post("/health-data/labs", p)),
            );
            notifications.itemAdded();
            filters.type = "";
            filters.date = "";
        }
        editingId.value = null;
        resetForm();
        showAnalysisModal.value = false;
        emit("refresh");
    } catch (err) {
        notifications.error(
            err?.response?.data?.message ?? "Erreur lors de l'enregistrement.",
        );
    }
}
// Ouvre la modale d'édition et pré-remplit le formulaire avec les données de l'item ciblé
function ouvrirEditionAnalyse(item) {
    editingId.value = item.id;
    formError.value = "";
    form.category = item.type ?? "";
    form.results = [
        {
            result: item.result ?? "",
            value: String(item.value ?? ""),
            unit: item.unit ?? "",
        },
    ];
    form.date = item.analysisDate ?? today();
    expandedIndex.value = 0;
    showAnalysisModal.value = true;
}

// Lance la confirmation de suppression pour l'item ciblé
function supprimerAnalyse(item) {
    pendingDelete.value = item;
    showDeleteConfirm.value = true;
}
function cancelDelete() {
    pendingDelete.value = null;
    showDeleteConfirm.value = false;
}
// Confirme la suppression de l'item ciblé
async function confirmDelete() {
    if (!pendingDelete.value?.id) return;
    try {
        await api.delete(`/health-data/labs/${pendingDelete.value.id}`);
        notifications.itemDeleted();
        emit("refresh");
    } catch (err) {
        notifications.error(
            err?.response?.data?.message ?? "Erreur lors de la suppression.",
        );
    } finally {
        cancelDelete();
    }
}

// Ouvre la modale d'ajout en réinitialisant le formulaire
function ouvrirModalAjout() {
    editingId.value = null;
    resetForm();
    showAnalysisModal.value = true;
}
function reinitialiserFiltres() {
    filters.type = "";
    filters.date = "";
}
//expose les function pour etre accesible au parent
defineExpose({ ouvrirModalAjout, reinitialiserFiltres });
</script>