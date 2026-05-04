<template>
    <!-- En-tête -->
    <div
        class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
    >
        <div>
            <Typography tag="h4" variant="h2-style">
                Derniers signes vitaux
            </Typography>
            <Typography tag="p" variant="paragraph" class="mt-2 text-slate-700">
                {{
                    latestVitalMeasuredAtLabel
                        ? `Dernière entrée du ${latestVitalMeasuredAtLabel}`
                        : "Aucune mesure enregistrée pour le moment."
                }}
            </Typography>
        </div>
    </div>

    <!-- Cartes des dernières valeurs — affichage uniquement -->
    <section class="mt-6 grid gap-5 xl:grid-cols-3">
        <!-- Heart Rate Card -->
        <VitalCard
            v-bind="VITAL_META.heart"
        >
            <template #value>
                <span class="text-[36px] font-bold leading-none text-slate-900">
                    {{ latestVital?.heart_rate ?? "--" }}
                    <span class="text-[18px] font-semibold text-slate-700 ml-1"
                        >bpm</span
                    >
                </span>
            </template>
        </VitalCard>

        <!-- Blood Pressure Card -->
        <BloodPressureCard
            v-bind="VITAL_META.pressure"
            unit="mmHg"
            :systolic="latestVital?.systolic_pressure"
            :diastolic="latestVital?.diastolic_pressure"
        />

        <!-- Oxygen Card -->
        <VitalCard
            v-bind="VITAL_META.oxygen"
        >
            <template #value>
                <span class="text-[36px] font-bold leading-none text-slate-900">
                    {{ latestVital?.oxygen_saturation != null ? Math.round(latestVital.oxygen_saturation) : "--" }}
                    <span class="text-[22px] font-semibold text-slate-700 ml-1"
                        >%</span
                    >
                </span>
            </template>
        </VitalCard>
    </section>

    <!-- Historique -->
    <section
        class="mt-8 rounded-2xl border border-slate-200 bg-[#f8f9fb] px-8 py-8"
    >
        <Typography tag="h2" variant="h4-style">
            Historique des mesures
        </Typography>

        <FilterCard
            class="mt-6"
            title="Signes vitaux"
            subtitle="Filtrez par date ou par type."
            :show-reset="filtresActifs"
            @reset="reinitialiserFiltres"
        >
            <input v-model="filterDate" type="date" class="input-field" />
            <select v-model="filterType" class="input-field">
                <option value="all">Tous les signes</option>
                <option value="heartRate">Rythme cardiaque</option>
                <option value="bloodPressure">Tension artérielle</option>
                <option value="saturation">Saturation O₂</option>
            </select>
        </FilterCard>

        <div class="mt-6 space-y-3.5">
            <article
                v-for="entry in filteredVitals"
                :key="entry.isoDate"
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
                        {{ entry.date }}
                    </h3>
                </div>
                <div class="grid gap-3 xl:grid-cols-3">
                    <div
                        v-for="card in entry.cards"
                        :key="card.key"
                        class="rounded-[16px] border px-5 py-4"
                        :class="card.class"
                    >
                        <p class="text-[14px] text-[#2d3f5e]">{{ card.label }}</p>
                        <p class="mt-2 text-[18px] font-bold text-[#061a45]">
                            {{ card.value }}
                            <span class="text-[14px] font-medium text-slate-500">{{ card.unit }}</span>
                        </p>
                    </div>
                </div>
            </article>

            <div
                v-if="!filteredVitals.length"
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
        <div class="w-full max-w-[520px] rounded-3xl bg-white p-8 shadow-2xl">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <Typography tag="h2" variant="h2-style">
                        {{ modalTitle }}
                    </Typography>
                    <p
                        v-if="isEditingLatest"
                        class="mt-2 text-sm font-medium text-slate-600"
                    >
                        Seule la dernière entrée peut être modifiée.
                    </p>
                </div>
                <BaseButton
                    type="button"
                    variant="outline"
                    size="sm"
                    @click="showModal = false"
                >
                    <svg
                        viewBox="0 0 24 24"
                        class="h-6 w-6"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path d="m6 6 12 12M18 6 6 18" />
                    </svg>
                </BaseButton>
            </div>

            <div class="space-y-6">
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
                        class="h-12 w-full rounded-2xl border border-slate-300 bg-slate-50 px-4 text-base font-medium outline-none focus:border-blue-500 disabled:opacity-60 transition"
                    />
                </ModalField>

                <!-- Tension -->
                <ModalField
                    label="Tension artérielle"
                    :disabled="form.skipPressure"
                    :skip-label="'Je n\'ai pas mesuré aujourd\'hui'"
                    v-model:skip="form.skipPressure"
                >
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <input
                                v-model="form.systolic"
                                type="number"
                                min="50"
                                max="300"
                                placeholder="120"
                                :disabled="form.skipPressure"
                                class="h-12 w-full rounded-2xl border border-slate-300 bg-slate-50 px-4 text-base font-medium outline-none focus:border-blue-500 disabled:opacity-60 transition"
                            />
                            <p class="mt-2 text-sm font-medium text-slate-700">
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
                                class="h-12 w-full rounded-2xl border border-slate-300 bg-slate-50 px-4 text-base font-medium outline-none focus:border-blue-500 disabled:opacity-60 transition"
                            />
                            <p
                                class="mt-2 text-right text-sm font-medium text-slate-700"
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
                        class="h-12 w-full rounded-2xl border border-slate-300 bg-slate-50 px-4 text-base font-medium outline-none focus:border-blue-500 disabled:opacity-60 transition"
                    />
                </ModalField>

                <!-- Date -->
                <div>
                    <label
                        class="mb-3 block text-base font-semibold text-slate-800"
                        >Date</label
                    >
                    <input
                        v-model="form.date"
                        type="date"
                        :disabled="isEditingLatest"
                        class="h-12 w-full rounded-2xl border border-slate-300 bg-slate-50 px-4 text-base font-medium outline-none focus:border-blue-500 disabled:cursor-not-allowed disabled:opacity-70 transition"
                    />
                    <p
                        v-if="isEditingLatest"
                        class="mt-2 text-sm font-medium text-slate-600"
                    >
                        La date reste verrouillée pour mettre à jour uniquement
                        la dernière mesure.
                    </p>
                </div>

                <p v-if="formError" class="text-sm font-semibold text-rose-600">
                    {{ formError }}
                </p>

                <BaseButton
                    type="button"
                    variant="save"
                    size="lg"
                    class="w-full mt-2"
                    @click="enregistrerMesure"
                >
                    {{
                        isEditingLatest
                            ? "Enregistrer les modifications"
                            : "Enregistrer"
                    }}
                </BaseButton>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, reactive, ref } from "vue";
import api from "@/services/api";
import { useNotificationsStore } from "@/stores/notifications";
import { formatLongDate, today } from "@/components/doctors/doctorUtilities.js";
import BloodPressureCard from "./BloodPressureCard.vue";
import VitalCard from "./VitalCard.vue";
import ModalField from "./ModalField.vue";
import Typography from "@/components/ui/Typography.vue";
import BaseButton from "@/components/ui/BaseButton.vue";
import FilterCard from "@/components/ui/FilterCard.vue";

//les cartes en haut de la deriniere
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

// props et emits
const props = defineProps({
    latestVital: { type: Object, default: null },
    historyHeartRate: { type: Array, default: () => [] },
    historySystolic: { type: Array, default: () => [] },
    historyDiastolic: { type: Array, default: () => [] },
    historySaturation: { type: Array, default: () => [] },
    vitalDateKeys: { type: Array, default: () => [] },
});
const emit = defineEmits(["refresh"]);
const notifications = useNotificationsStore();

// state local
const showModal = ref(false);
const isEditingLatest = ref(false);//true si on modifie la dernière mesure existante
const formError = ref("");
const filterDate = ref("");
const filterType = ref("all");

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

// Computed 
const latestVitalDate = computed(() => isoDate(props.latestVital?.measured_at));
const latestVitalMeasuredAtLabel = computed(() =>
    props.latestVital?.measured_at ? formatLongDate(latestVitalDate.value) : "",
);
const modalTitle = computed(() =>
    isEditingLatest.value
        ? "Modifier la dernière mesure"
        : "Ajouter une mesure",
);
//c'est pour le button de réinitialisation des filtres il s'affiche si un filtre est actif
const filtresActifs = computed(
    () => Boolean(filterDate.value) || filterType.value !== "all",
);
//les cartes à afficher dans l'historique en fonction des filtres sélectionnés
const VITAL_CARDS = [
    { key: "heartRate",     label: "Rythme cardiaque",  unit: "bpm",  class: "border-[#f4bcc3] bg-[#fff5f6]" },
    { key: "bloodPressure", label: "Tension artérielle", unit: "mmHg", class: "border-[#aac8ff] bg-[#eff6ff]" },
    { key: "saturation",    label: "Saturation O₂",     unit: "%",    class: "border-[#dcc5ff] bg-[#faf4ff]" },
];

const filteredVitals = computed(() => {
    const rows = props.vitalDateKeys
        .map((dateKey, i) => {
            const hr  = props.historyHeartRate[i] ?? null;
            const sys = props.historySystolic[i] ?? null;
            const dia = props.historyDiastolic[i] ?? null;
            const ox  = props.historySaturation[i] ?? null;
            if (![hr, sys, dia, ox].some(isValidMeasure)) return null;
            return {
                isoDate:       dateKey,
                date:          formatLongDate(dateKey),
                heartRate:     isValidMeasure(hr)  ? hr  : "--",
                bloodPressure: isValidMeasure(sys) && isValidMeasure(dia)
                    ? `${+sys}/${+dia}`
                    : "--/--", 
                saturation:    isValidMeasure(ox)  ? Math.round(+ox) : "--",
            };
        })
        //supprime les entrées sans mesure valide 
        .filter(Boolean)
        .reverse();//récent en premier
    const pool = filterDate.value ? rows : rows.slice(0, 7);//si pas de filtre de date on limite a7
    return pool
        .filter((r) => !filterDate.value || r.isoDate === filterDate.value)
        .map((r) => ({
            ...r,
            cards: VITAL_CARDS.filter(
                (c) => filterType.value === "all" || c.key === filterType.value,
            ).map((c) => ({ ...c, value: r[c.key] })),
        }))
        .filter((r) => r.cards.length);
});

//extraire juste yyyy-mm-dd sans time
function isoDate(v) {
    return v ? String(v).slice(0, 10) : today();
}
//format backend 
function toDatetime(dateStr) {
    const d = dateStr ? String(dateStr).slice(0, 10) : today();
    return `${d} 12:00:00`;
}
//convertir le string to number ou null
function toNumber(v) {
    if (v === null || v === undefined || v === "") return null;
    const n = Number(String(v).trim().replace(",", "."));
    return Number.isFinite(n) ? n : null;
}
//verifie ce qui un nombre valide 
function isValidMeasure(v) {
    return (
        v !== null && v !== undefined && v !== "" && Number.isFinite(Number(v))
    );
}

function reinitialiserFiltres() {
    filterDate.value = "";
    filterType.value = "all";
}
//remplit le formulaire avec les données de la dernière mesure si editing est true sinon vide les champs
function resetForm(editing = false) {
    formError.value = "";
    const v = props.latestVital;
    form.heartRate     = editing ? String(v?.heart_rate ?? "")         : "";
    form.systolic      = editing ? String(v?.systolic_pressure ?? "")  : "";
    form.diastolic     = editing ? String(v?.diastolic_pressure ?? "") : "";
    form.oxygen        = editing ? String(v?.oxygen_saturation ?? "")  : "";
    form.skipHeartRate = false;
    form.skipPressure  = false;
    form.skipOxygen    = false;
    form.date          = today();
}

//enregistre une nouvelle mesure ou modifie la dernière si isEditingLatest est true
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
        resetForm();
        showModal.value = false;
        emit("refresh");
    } catch (err) {
        notifications.error(
            err?.response?.data?.message ?? "Erreur lors de l'enregistrement.",
        );
    }
}

function ouvrirModalAjout() {
    const hasToday = props.latestVital?.measured_at &&
        String(props.latestVital.measured_at).slice(0, 10) === today();
    isEditingLatest.value = Boolean(hasToday);
    resetForm(isEditingLatest.value);
    showModal.value = true;
}

defineExpose({ ouvrirModalAjout });
</script>
