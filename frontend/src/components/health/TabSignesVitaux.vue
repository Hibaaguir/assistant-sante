<template>
    <!-- En-tête -->
    <div
        class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
    >
        <div>
            <h2
                class="text-[clamp(1.75rem,1.4rem+0.9vw,2.2rem)] font-extrabold leading-tight text-black"
            >
                Derniers signes vitaux
            </h2>
            <p class="mt-2 text-[15px] leading-7 text-black">
                {{
                    latestVitalMeasuredAtLabel
                        ? `Dernière entrée du ${latestVitalMeasuredAtLabel}`
                        : "Aucune mesure enregistrée pour le moment."
                }}
            </p>
        </div>
    </div>

    <!-- Cartes des dernières valeurs — toujours visibles -->
    <section class="mt-6 grid gap-5 xl:grid-cols-3">
        <!-- Heart Rate Card -->
        <VitalCard
            v-bind="VITAL_META.heart"
            :display-value="draft.heartRate"
            unit="bpm"
            :can-edit="peutModifierDerniereMesure"
            :is-editing="mesureEnEdition === 'heart'"
            @edit="activerEditionMesure('heart')"
            @blur="fermerEditionMesure"
            @update:value="(value) => mettreAJourDraft('heart', value)"
        >
            <template #value>
                <span class="text-[36px] font-bold leading-none text-slate-900">
                    {{ draft.heartRate || '--' }}
                    <span class="text-[14px] font-medium text-slate-400 ml-2"
                        >bpm</span
                    >
                </span>
            </template>
        </VitalCard>

        <!-- Blood Pressure Card -->
        <BloodPressureCard
            v-bind="VITAL_META.pressure"
            unit="mmHg"
            :systolic="draft.systolic"
            :diastolic="draft.diastolic"
            :can-edit="peutModifierDerniereMesure"
            @update:systolic="
                (value) => mettreAJourDraft('pressure-systolic', value)
            "
            @update:diastolic="
                (value) => mettreAJourDraft('pressure-diastolic', value)
            "
        />

        <!-- Oxygen Card -->
        <VitalCard
            v-bind="VITAL_META.oxygen"
            :display-value="draft.oxygen"
            unit="%"
            :can-edit="peutModifierDerniereMesure"
            :is-editing="mesureEnEdition === 'oxygen'"
            @edit="activerEditionMesure('oxygen')"
            @blur="fermerEditionMesure"
            @update:value="(value) => mettreAJourDraft('oxygen', value)"
        >
            <template #value>
                <span class="text-[36px] font-bold leading-none text-slate-900">
                    {{ draft.oxygen || '--' }}
                    <span class="text-[14px] font-medium text-slate-400 ml-2"
                        >%</span
                    >
                </span>
            </template>
        </VitalCard>
    </section>

    <!-- Actions d'édition rapide -->
    <div
        v-if="peutModifierDerniereMesure"
        class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
    >
        <p class="text-[15px] leading-7 text-black">
            Cliquez sur une valeur pour la modifier directement dans la carte.
        </p>
        <div class="flex items-center gap-2">
            <button
                v-if="editionModifiee"
                type="button"
                class="inline-flex h-11 items-center justify-center rounded-2xl border border-slate-300 bg-slate-50 px-4 text-[14px] font-semibold text-slate-600 shadow-sm transition hover:bg-slate-100"
                @click="reinitialiserEdition"
            >
                Annuler
            </button>
            <button
                type="button"
                class="inline-flex h-11 items-center justify-center rounded-2xl bg-gradient-to-r from-emerald-400 to-emerald-500 px-4 text-[14px] font-semibold text-white shadow-[0_8px_16px_rgba(16,185,129,0.22)] disabled:cursor-not-allowed disabled:opacity-50"
                :disabled="!editionModifiee || enregistrementEnCours"
                @click="enregistrerDepuisCartes"
            >
                {{
                    enregistrementEnCours
                        ? "Enregistrement..."
                        : "Enregistrer la dernière entrée"
                }}
            </button>
        </div>
    </div>

    <!-- Historique -->
    <section
        class="mt-8 rounded-2xl border border-slate-200 bg-[#f8f9fb] px-8 py-8"
    >
        <div
            class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
        >
            <h2
                class="text-[clamp(1.75rem,1.4rem+0.9vw,2.2rem)] font-extrabold leading-tight text-black"
            >
                Historique des mesures
            </h2>
            <button
                v-if="filtresActifs"
                type="button"
                class="inline-flex h-11 items-center justify-center rounded-2xl border border-slate-300 bg-white px-4 text-[14px] font-semibold text-slate-700 shadow-sm transition hover:border-purple-300 hover:text-purple-700"
                @click="reinitialiserFiltres"
            >
                Réinitialiser
            </button>
        </div>

        <div class="mt-8 grid gap-4 lg:grid-cols-2">
            <div>
                <label
                    class="mb-3 block text-[14px] font-semibold text-slate-800"
                    >Filtrer par date</label
                >
                <div class="relative">
                    <input
                        v-model="filterDate"
                        type="date"
                        class="h-12 w-full rounded-2xl border border-slate-300 bg-white pl-5 pr-12 text-[16px] text-slate-900 outline-none focus:border-purple-500"
                    />
                    <!-- Calendar icon (inline SVG — no render function needed) -->
                    <svg
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        class="pointer-events-none absolute right-4 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-500"
                    >
                        <path
                            d="M8 2v3M16 2v3M3 9h18M5 5h14a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2z"
                        />
                    </svg>
                </div>
            </div>
            <div>
                <label
                    class="mb-3 block text-[14px] font-semibold text-slate-800"
                    >Filtrer par type</label
                >
                <select
                    v-model="filterType"
                    class="h-12 w-full rounded-2xl border border-slate-300 bg-white px-5 text-[16px] text-slate-900 outline-none focus:border-purple-500"
                >
                    <option value="all">Tous les signes</option>
                    <option value="heart">Rythme cardiaque</option>
                    <option value="pressure">Tension artérielle</option>
                    <option value="oxygen">Saturation O₂</option>
                </select>
            </div>
        </div>

        <div class="mt-6 space-y-3.5">
            <article
                v-for="day in historiqueFiltre"
                :key="day.dateKey"
                class="rounded-2xl border border-slate-200 bg-white px-5 py-5"
            >
                <div class="mb-4 flex items-center gap-3 text-slate-900">
                    <svg
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        class="h-6 w-6 text-slate-500"
                    >
                        <path
                            d="M8 2v3M16 2v3M3 9h18M5 5h14a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2z"
                        />
                    </svg>
                    <h3 class="text-[22px] font-semibold leading-none">
                        {{ day.longDate }}
                    </h3>
                </div>
                <div class="grid gap-3 xl:grid-cols-3">
                    <HistoryCard
                        v-if="showType('heart')"
                        v-bind="VITAL_META.heart"
                        :value="day.heartRate"
                        unit="bpm"
                    />
                    <HistoryCard
                        v-if="showType('pressure')"
                        v-bind="VITAL_META.pressure"
                        :value="day.pressure"
                        unit="mmHg"
                    />
                    <HistoryCard
                        v-if="showType('oxygen')"
                        v-bind="VITAL_META.oxygen"
                        :value="day.oxygen"
                        unit="%"
                    />
                </div>
            </article>

            <div
                v-if="!historiqueFiltre.length"
                class="rounded-2xl border border-slate-200 bg-white px-6 py-5 text-[15px] leading-7 text-black"
            >
                Aucune mesure ne correspond aux filtres sélectionnés.
            </div>
        </div>
    </section>

    <!-- Modale ajout / modification -->
    <div
        v-if="showModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/45 backdrop-blur-sm p-4"
    >
        <div class="w-full max-w-[470px] rounded-3xl bg-white p-7 shadow-2xl">
            <div class="mb-4 flex items-center justify-between">
                <div>
                    <h3
                        class="text-[24px] font-semibold leading-none text-slate-900"
                    >
                        {{ modalTitle }}
                    </h3>
                    <p
                        v-if="isEditingLatest"
                        class="mt-2 text-[14px] leading-6 text-black"
                    >
                        Seule la dernière entrée peut être modifiée.
                    </p>
                </div>
                <button
                    type="button"
                    class="text-slate-500 hover:text-slate-700"
                    @click="showModal = false"
                >
                    <!-- Close icon (inline SVG — no render function needed) -->
                    <svg
                        viewBox="0 0 24 24"
                        class="h-6 w-6"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path d="m6 6 12 12M18 6 6 18" />
                    </svg>
                </button>
            </div>

            <div class="space-y-4">
                <!-- Rythme cardiaque -->
                <ModalField
                    label="Rythme cardiaque (bpm)"
                    :disabled="form.skipHeartRate"
                    :skip-label="'Je n\'ai pas mesuré aujourd\'hui'"
                    v-model:skip="form.skipHeartRate"
                >
                    <input
                        v-model="form.heartRate"
                        type="number"
                        min="20"
                        max="260"
                        placeholder="72"
                        :disabled="form.skipHeartRate"
                        class="h-11 w-full rounded-2xl border border-slate-300 bg-slate-50 px-4 text-[14px] outline-none disabled:opacity-60"
                    />
                </ModalField>

                <!-- Tension -->
                <ModalField
                    label="Tension artérielle"
                    :disabled="form.skipPressure"
                    :skip-label="'Je n\'ai pas mesuré aujourd\'hui'"
                    v-model:skip="form.skipPressure"
                >
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <input
                                v-model="form.systolic"
                                type="number"
                                min="50"
                                max="300"
                                placeholder="120"
                                :disabled="form.skipPressure"
                                class="h-11 w-full rounded-2xl border border-slate-300 bg-slate-50 px-4 text-[14px] outline-none disabled:opacity-60"
                            />
                            <p class="mt-1 text-[14px] text-black">
                                Systolique
                            </p>
                        </div>
                        <div>
                            <input
                                v-model="form.diastolic"
                                type="number"
                                min="30"
                                max="220"
                                placeholder="80"
                                :disabled="form.skipPressure"
                                class="h-11 w-full rounded-2xl border border-slate-300 bg-slate-50 px-4 text-[14px] outline-none disabled:opacity-60"
                            />
                            <p
                                class="mt-1 text-right text-[14px] text-black"
                            >
                                Diastolique
                            </p>
                        </div>
                    </div>
                </ModalField>

                <!-- Saturation -->
                <ModalField
                    label="Saturation O₂ (%)"
                    :disabled="form.skipOxygen"
                    :skip-label="'Je n\'ai pas mesuré aujourd\'hui'"
                    v-model:skip="form.skipOxygen"
                >
                    <input
                        v-model="form.oxygen"
                        type="number"
                        min="0"
                        max="100"
                        step="1"
                        placeholder="98"
                        :disabled="form.skipOxygen"
                        class="h-11 w-full rounded-2xl border border-slate-300 bg-slate-50 px-4 text-[14px] outline-none disabled:opacity-60"
                    />
                </ModalField>

                <!-- Date -->
                <div>
                    <label
                        class="mb-2 block text-[18px] font-semibold text-slate-700"
                        >Date</label
                    >
                    <input
                        v-model="form.date"
                        type="date"
                        :disabled="isEditingLatest"
                        class="h-11 w-full rounded-2xl border border-slate-300 bg-slate-50 px-4 text-[14px] outline-none disabled:cursor-not-allowed disabled:opacity-70"
                    />
                    <p
                        v-if="isEditingLatest"
                        class="mt-2 text-[14px] leading-6 text-black"
                    >
                        La date reste verrouillée pour mettre à jour uniquement
                        la dernière mesure.
                    </p>
                </div>

                <p v-if="formError" class="text-sm font-medium text-rose-600">
                    {{ formError }}
                </p>

                <button
                    type="button"
                    class="mt-2 h-11 w-full rounded-2xl bg-gradient-to-r from-emerald-400 to-emerald-500 text-[16px] font-semibold text-white hover:from-emerald-500 hover:to-emerald-600"
                    @click="enregistrerMesure"
                >
                    {{
                        isEditingLatest
                            ? "Enregistrer les modifications"
                            : "Enregistrer"
                    }}
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref, watch } from "vue";
import api from "@/services/api";
import { useNotificationsStore } from "@/stores/notifications";
// Proper .vue components — no render functions needed
import BloodPressureCard from "./BloodPressureCard.vue";
import VitalCard from "./VitalCard.vue";
import HistoryCard from "./HistoryCard.vue";
import ModalField from "./ModalField.vue";

// ─── Métadonnées des signes vitaux (couleurs, icônes, labels) ─────────────────
const VITAL_META = {
    heart: {
        key: "heart",
        label: "Rythme cardiaque",
        unit: "bpm",
        bg: "bg-[#fdf2f5]",
        border: "border-[#efc4cc]",
        iconBg: "bg-[#f9e3e9] text-[#ff2458]",
        accent: "#ff2458",
        icon: `<svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M20.8 8.2a4.9 4.9 0 0 0-8.8-3.1 4.9 4.9 0 0 0-8.8 3.1c0 5 8.8 10.8 8.8 10.8s8.8-5.8 8.8-10.8z"/></svg>`,
    },
    pressure: {
        key: "pressure",
        label: "Tension artérielle",
        unit: "mmHg",
        bg: "bg-[#ebf6fe]",
        border: "border-[#a8cdfb]",
        iconBg: "bg-[#d5e7fd] text-[#149bd7]",
        accent: "#149bd7",
        icon: `<svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M3 12h4l2-6 4 12 2-6h6"/></svg>`,
    },
    oxygen: {
        key: "oxygen",
        label: "Saturation O₂",
        unit: "%",
        bg: "bg-[#f6f0fc]",
        border: "border-[#dbc6f7]",
        iconBg: "bg-[#eee2fc] text-[#8a2cff]",
        accent: "#8a2cff",
        icon: `<svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M12 3s6 6.4 6 10a6 6 0 0 1-12 0c0-3.6 6-10 6-10z"/></svg>`,
    },
};

// ─── Props / Emits ────────────────────────────────────────────────────────────
const props = defineProps({
    latestVital: { type: Object, default: null },
    chartLabels: { type: Array, default: () => [] },
    chartHeartRate: { type: Array, default: () => [] },
    chartSystolic: { type: Array, default: () => [] },
    chartDiastolic: { type: Array, default: () => [] },
    chartSaturation: { type: Array, default: () => [] },
    historyHeartRate: { type: Array, default: () => [] },
    historySystolic: { type: Array, default: () => [] },
    historyDiastolic: { type: Array, default: () => [] },
    historySaturation: { type: Array, default: () => [] },
    vitalDateKeys: { type: Array, default: () => [] },
});
const emit = defineEmits(["refresh"]);
const notifications = useNotificationsStore();

// ─── État ─────────────────────────────────────────────────────────────────────
const showModal = ref(false);
const isEditingLatest = ref(false);
const enregistrementEnCours = ref(false);
const mesureEnEdition = ref("");
const formError = ref("");
const filterDate = ref("");
const filterType = ref("all");

const draft = reactive({
    heartRate: "",
    systolic: "",
    diastolic: "",
    oxygen: "",
});
const form = reactive({
    heartRate: "",
    systolic: "",
    diastolic: "",
    oxygen: "",
    skipHeartRate: false,
    skipPressure: false,
    skipOxygen: false,
    date: today(),
});

// ─── Watchers ────────────────────────────────────────────────────────────────
// Initialiser et réinitialiser le draft au montage et quand latestVital change
onMounted(() => {
    syncDraft();
});
watch(
    () => props.latestVital,
    () => {
        syncDraft();
    },
);

// ─── Computed ─────────────────────────────────────────────────────────────────
const latestVitalDate = computed(() => isoDate(props.latestVital?.measured_at));
const latestVitalMeasuredAtLabel = computed(() =>
    props.latestVital?.measured_at ? formatDate(latestVitalDate.value) : "",
);
const peutModifierDerniereMesure = computed(() =>
    Boolean(props.latestVital?.measured_at),
);
const modalTitle = computed(() =>
    isEditingLatest.value
        ? "Modifier la dernière mesure"
        : "Ajouter une mesure",
);

const editionModifiee = computed(() => {
    if (!peutModifierDerniereMesure.value) return false;
    const v = props.latestVital;
    return (
        String(draft.heartRate) !== String(v?.heart_rate ?? "") ||
        String(draft.systolic) !== String(v?.systolic_pressure ?? "") ||
        String(draft.diastolic) !== String(v?.diastolic_pressure ?? "") ||
        String(draft.oxygen) !== String(v?.oxygen_saturation ?? "")
    );
});

const filtresActifs = computed(
    () => Boolean(filterDate.value) || filterType.value !== "all",
);

const historiqueFiltre = computed(() => {
    const rows = props.vitalDateKeys
        .map((dateKey, i) => {
            const hr = props.historyHeartRate[i] ?? null;
            const sys = props.historySystolic[i] ?? null;
            const dia = props.historyDiastolic[i] ?? null;
            const ox = props.historySaturation[i] ?? null;
            if (![hr, sys, dia, ox].some(isValidMeasure)) return null;
            return {
                dateKey,
                longDate: formatLongDate(dateKey),
                heartRate: hr ?? "--",
                pressure:
                    isValidMeasure(sys) && isValidMeasure(dia)
                        ? `${+sys}/${+dia}`
                        : "--/--",
                oxygen: ox ?? "--",
            };
        })
        .filter(Boolean)
        .reverse();

    return filterDate.value
        ? rows.filter((r) => r.dateKey === filterDate.value)
        : rows;
});

// ─── Helpers ──────────────────────────────────────────────────────────────────
function today() {
    return new Date().toISOString().slice(0, 10);
}
function isoDate(v) {
    return v ? String(v).slice(0, 10) : today();
}
// Format YYYY-MM-DD → YYYY-MM-DD 00:00:00 for the API
function toDatetime(dateStr) {
    const d = dateStr ? String(dateStr).slice(0, 10) : today();
    return `${d} 00:00:00`;
}
function toNumber(v) {
    if (v === null || v === undefined || v === "") return null;
    const n = Number(String(v).trim().replace(",", "."));
    return Number.isFinite(n) ? n : null;
}
function isValidMeasure(v) {
    return (
        v !== null && v !== undefined && v !== "" && Number.isFinite(Number(v))
    );
}
function formatDate(iso) {
    return iso ? new Date(`${iso}T00:00:00`).toLocaleDateString("fr-FR") : "";
}
function formatLongDate(iso) {
    return iso
        ? new Date(`${iso}T00:00:00`).toLocaleDateString("fr-FR", {
              weekday: "long",
              day: "numeric",
              month: "long",
              year: "numeric",
          })
        : "";
}
function showType(type) {
    return filterType.value === "all" || filterType.value === type;
}

function syncDraft() {
    const v = props.latestVital;
    draft.heartRate = String(v?.heart_rate ?? "");
    draft.systolic = String(v?.systolic_pressure ?? "");
    draft.diastolic = String(v?.diastolic_pressure ?? "");
    const ox = v?.oxygen_saturation;
    draft.oxygen = ox != null ? String(Math.round(Number(ox))) : "";
}

function mettreAJourDraft(cardKey, value) {
    if (cardKey === "heart") {
        draft.heartRate = value;
    } else if (cardKey === "pressure-systolic") {
        draft.systolic = String(value);
    } else if (cardKey === "pressure-diastolic") {
        draft.diastolic = String(value);
    } else if (cardKey === "oxygen") {
        draft.oxygen = value;
    }
}

function activerEditionMesure(key) {
    mesureEnEdition.value = key;
}
function fermerEditionMesure() {
    mesureEnEdition.value = "";
}
function reinitialiserEdition() {
    syncDraft();
    fermerEditionMesure();
}
function reinitialiserFiltres() {
    filterDate.value = "";
    filterType.value = "all";
}

function resetForm() {
    formError.value = "";
    isEditingLatest.value = false;
    const v = props.latestVital;
    Object.assign(form, {
        heartRate: String(v?.heart_rate ?? 72),
        systolic: String(v?.systolic_pressure ?? 120),
        diastolic: String(v?.diastolic_pressure ?? 80),
        oxygen: String(v?.oxygen_saturation ?? 98),
        skipHeartRate: false,
        skipPressure: false,
        skipOxygen: false,
        date: today(),
    });
}

// ─── Actions API ─────────────────────────────────────────────────────────────
async function enregistrerMesure() {
    formError.value = "";
    const heartRate = form.skipHeartRate ? null : toNumber(form.heartRate);
    const systolic = form.skipPressure ? null : toNumber(form.systolic);
    const diastolic = form.skipPressure ? null : toNumber(form.diastolic);
    const rawOxygen = form.skipOxygen ? null : toNumber(form.oxygen);
    const oxygen = rawOxygen !== null ? Math.round(rawOxygen) : null;

    if (
        heartRate === null &&
        systolic === null &&
        diastolic === null &&
        oxygen === null
    ) {
        formError.value = "Veuillez saisir au moins une mesure.";
        return;
    }
    if ((systolic === null) !== (diastolic === null)) {
        formError.value = "Veuillez remplir les deux champs de tension.";
        return;
    }

    try {
        await api.post("/health-data/vitals", {
            measured_at: toDatetime(form.date),
            heart_rate: heartRate,
            systolic_pressure: systolic,
            diastolic_pressure: diastolic,
            oxygen_saturation: oxygen,
        });
        notifications.itemAdded();
        // Mettre à jour le draft avec les nouvelles valeurs pour affichage immédiat
        draft.heartRate = String(heartRate ?? "");
        draft.systolic = String(systolic ?? "");
        draft.diastolic = String(diastolic ?? "");
        draft.oxygen = String(oxygen ?? "");
        resetForm();
        showModal.value = false;
        emit("refresh");
    } catch (err) {
        notifications.error(
            err?.response?.data?.message ?? "Erreur lors de l'enregistrement.",
        );
    }
}

async function enregistrerDepuisCartes() {
    if (!peutModifierDerniereMesure.value) return;

    const heartRate = toNumber(draft.heartRate);
    const systolic = toNumber(draft.systolic);
    const diastolic = toNumber(draft.diastolic);
    const rawOxygen = toNumber(draft.oxygen);
    const oxygen = rawOxygen !== null ? Math.round(rawOxygen) : null;

    if (
        heartRate === null &&
        systolic === null &&
        diastolic === null &&
        oxygen === null
    ) {
        notifications.warning("Veuillez renseigner au moins une valeur.");
        return;
    }
    if ((systolic === null) !== (diastolic === null)) {
        notifications.warning(
            "Veuillez remplir les deux champs de tension.",
        );
        return;
    }

    enregistrementEnCours.value = true;
    try {
        await api.post("/health-data/vitals", {
            measured_at: toDatetime(today()),
            heart_rate: heartRate,
            systolic_pressure: systolic,
            diastolic_pressure: diastolic,
            oxygen_saturation: oxygen,
        });
        notifications.itemUpdated("Dernière entrée modifiée avec succès.");
        // Mettre à jour le draft avec les nouvelles valeurs pour affichage immédiat
        draft.heartRate = String(heartRate ?? "");
        draft.systolic = String(systolic ?? "");
        draft.diastolic = String(diastolic ?? "");
        draft.oxygen = String(oxygen ?? "");
        fermerEditionMesure();
        emit("refresh");
    } catch (err) {
        const msg = err?.response?.data?.errors
            ? Object.values(err.response.data.errors).flat()[0]
            : (err?.response?.data?.message ??
              "Erreur lors de la modification.");
        notifications.error(msg);
    } finally {
        enregistrementEnCours.value = false;
    }
}

// ─── API publique ─────────────────────────────────────────────────────────────
function ouvrirModalAjout() {
    resetForm();
    showModal.value = true;
}

watch(() => props.latestVital, syncDraft, { immediate: true, deep: true });

defineExpose({ ouvrirModalAjout });
</script>
