<template>
    <section class="mt-4 flex justify-end">
        <button
            type="button"
            class="inline-flex h-10 items-center gap-2 rounded-xl border border-slate-300 bg-white px-4 text-[13px] font-semibold text-slate-700 shadow-sm hover:bg-slate-50"
            @click="showTreatmentHistory = !showTreatmentHistory"
        >
            <svg
                viewBox="0 0 24 24"
                class="h-4 w-4"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
            >
                <path
                    d="M3 12a9 9 0 1 0 3-6.7M3 4v5h5"
                    stroke-linecap="round"
                />
            </svg>
            {{
                showTreatmentHistory ? "Masquer historique" : "Voir historique"
            }}
        </button>
    </section>

    <section
        v-if="showTreatmentHistory"
        class="mt-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
    >
        <h2 class="text-[26px] font-bold leading-none text-slate-900">
            Historique des prises
        </h2>

        <div class="mt-6 grid gap-4 lg:grid-cols-3">
            <article
                class="rounded-2xl border border-orange-300 bg-orange-100 p-4"
            >
                <p class="text-[13px] text-orange-800">Taux d'observance</p>
                <p
                    class="mt-1 text-[24px] font-semibold leading-none text-slate-900"
                >
                    {{ treatmentHistoryStats.observance }}%
                </p>
                <p class="mt-2 text-[12px] text-slate-600">
                    {{ treatmentHistoryStats.completeDays }}/{{
                        treatmentHistoryStats.totalDays
                    }}
                    jours complets
                </p>
            </article>
            <article
                class="rounded-2xl border border-[#a8cdfb] bg-[#ebf6fe] p-4"
            >
                <p class="text-[13px] text-[#149bd7]">Prises totales</p>
                <p
                    class="mt-1 text-[24px] font-semibold leading-none text-slate-900"
                >
                    {{ treatmentHistoryStats.totalTaken }}
                </p>
                <p class="mt-2 text-[12px] text-slate-600">
                    {{ treatmentHistoryStats.periodSubtitle }}
                </p>
            </article>
            <article
                class="rounded-2xl border border-[#dbc6f7] bg-[#f6f0fc] p-4"
            >
                <p class="text-[13px] text-[#8a2cff]">Médicaments actifs</p>
                <p
                    class="mt-1 text-[24px] font-semibold leading-none text-slate-900"
                >
                    {{ treatmentHistoryStats.activeMedicines }}
                </p>
                <p class="mt-2 text-[12px] text-slate-600">
                    Traitements en cours
                </p>
            </article>
        </div>

        <div class="mt-6">
            <p class="text-[14px] font-semibold text-slate-800">Période</p>
            <div class="mt-3 flex flex-wrap gap-2">
                <button
                    v-for="period in treatmentHistoryPeriods"
                    :key="period.value"
                    type="button"
                    class="h-9 rounded-full px-4 text-[13px] font-semibold transition"
                    :class="
                        treatmentHistoryPeriod === period.value
                            ? 'bg-gradient-to-r from-purple-600 to-purple-600 text-white shadow-[0_6px_14px_rgba(147,51,234,0.32)]'
                            : 'bg-slate-100 text-slate-700 hover:bg-slate-200'
                    "
                    @click="treatmentHistoryPeriod = period.value"
                >
                    {{ period.label }}
                </button>
            </div>
        </div>

        <div class="mt-5">
            <p class="text-[14px] font-semibold text-slate-800">Traitements</p>
            <div class="mt-3 flex flex-wrap gap-2">
                <button
                    v-for="med in treatmentHistoryMedicineOptions"
                    :key="med.id"
                    type="button"
                    class="h-9 rounded-full px-4 text-[13px] font-semibold transition"
                    :class="
                        selectedTreatmentHistoryMed === med.id
                            ? 'bg-gradient-to-r from-indigo-600 to-violet-600 text-white shadow-[0_6px_14px_rgba(99,102,241,0.32)]'
                            : 'bg-slate-100 text-slate-700 hover:bg-slate-200'
                    "
                    @click="selectedTreatmentHistoryMed = med.id"
                >
                    {{ med.name }}
                </button>
            </div>
        </div>

        <div class="mt-6 space-y-3">

            <p v-if="!treatmentHistoryRows.length" class="py-6 text-center text-sm text-slate-400">
                Aucun historique disponible pour ces filtres.
            </p>

            <div v-for="day in treatmentHistoryRows" :key="day.dateKey"
                class="rounded-2xl border bg-white p-5 shadow-sm"
                :class="day.isComplete ? 'border-emerald-200' : 'border-slate-200'">

                <!-- Date + badge -->
                <div class="flex items-center justify-between">
                    <div>
                        <p class="font-bold text-slate-800 capitalize">{{ formaterDateHistoriqueTraitement(day.dateKey) }}</p>
                        <p class="mt-0.5 text-sm text-slate-400">{{ day.taken }}/{{ day.total }} prises effectuées</p>
                    </div>
                    <span class="rounded-full px-3 py-1 text-sm font-semibold"
                        :class="day.isComplete ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500'">
                        {{ day.isComplete ? '✓ Complet' : `${day.taken}/${day.total}` }}
                    </span>
                </div>

                <!-- Médicaments -->
                <div class="mt-4 space-y-2">
                    <div v-for="med in day.meds" :key="med.id"
                        class="flex items-center justify-between gap-4 rounded-xl bg-slate-50 px-4 py-3">

                        <div>
                            <p class="font-semibold text-slate-800">{{ med.name }}</p>
                            <p class="text-sm text-slate-400">{{ med.dose }}</p>
                        </div>

                        <div class="flex gap-2">
                            <button v-for="i in obtenirIndexPrises(day.dateKey, getMedFull(med.id))" :key="i"
                                type="button"
                                class="inline-flex h-9 items-center gap-2 rounded-lg border px-3 text-sm font-medium transition"
                                :class="estPriseCochee(day.dateKey, med.id, i)
                                    ? 'border-emerald-400 bg-emerald-50 text-emerald-700'
                                    : 'border-slate-200 bg-white text-slate-500 hover:border-slate-300'"
                                @click="basculerPrise(day.dateKey, getMedFull(med.id), i)">
                                <span class="inline-flex h-5 w-5 items-center justify-center rounded border"
                                    :class="estPriseCochee(day.dateKey, med.id, i)
                                        ? 'border-emerald-500 bg-emerald-500 text-white'
                                        : 'border-slate-300 bg-white'">
                                    <svg viewBox="0 0 24 24" class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="3.5">
                                        <path d="m5 13 4 4L19 7" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </span>
                                Prise {{ i }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <template v-else>
        <section
            class="mt-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
        >
            <h2 class="text-[24px] font-bold leading-none text-slate-900">
                Calendrier des traitements
            </h2>
            <p class="mt-3 text-[12px] font-normal text-slate-600">
                Cliquez sur une journée pour gérer vos prises
            </p>

            <div class="mt-6 grid grid-cols-7 gap-2.5">
                <div
                    v-for="day in treatmentDays"
                    :key="day.key"
                    class="text-center"
                >
                    <p
                        class="mb-2 text-[12px] font-normal leading-none text-slate-600"
                    >
                        {{ day.shortLabel }}
                    </p>
                    <button
                        type="button"
                        class="h-[92px] w-full rounded-xl border px-2 pb-2 pt-2 transition"
                        :disabled="Boolean(day.isFuture)"
                        :class="
                            day.isFuture
                                ? 'cursor-not-allowed border-slate-200 bg-slate-100 opacity-60'
                                : estJourComplet(day.key)
                                  ? 'border-[#08a84a] bg-[#cfddd6]'
                                  : 'border-slate-300 bg-slate-50 hover:bg-slate-100'
                        "
                        @click="ouvrirJourTraitement(day)"
                    >
                        <p
                            class="text-[32px] font-medium leading-none text-slate-900"
                        >
                            {{ day.day }}
                        </p>
                        <div class="mt-2 flex justify-center">
                            <span
                                class="inline-flex h-6 w-6 items-center justify-center rounded-full border"
                                :class="
                                    estJourComplet(day.key)
                                        ? 'border-[#08a84a] bg-[#08a84a] text-white'
                                        : 'border-slate-300 bg-white text-transparent'
                                "
                            >
                                <svg
                                    viewBox="0 0 24 24"
                                    class="h-4 w-4"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="3"
                                >
                                    <path
                                        d="m5 13 4 4L19 7"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />
                                </svg>
                            </span>
                        </div>
                    </button>
                </div>
            </div>
        </section>

        <section class="mt-6">
            <h3 class="text-[24px] font-bold leading-none text-slate-900">
                Traitements actifs
            </h3>
            <p
                v-if="!treatmentMedicines.length"
                class="mt-3 text-[13px] text-slate-500"
            >
                Aucun traitement actif dans votre profil santé.
            </p>
            <div v-else class="mt-3 space-y-4">
                <article
                    v-for="med in treatmentMedicines"
                    :key="med.id"
                    class="rounded-2xl border border-slate-200 bg-white px-6 py-5 shadow-sm"
                >
                    <p
                        class="text-[19px] font-semibold leading-none text-slate-900"
                    >
                        {{ med.name }}
                    </p>
                    <p class="mt-2 text-[12px] font-normal text-slate-700">
                        {{ med.dose }} - {{ med.freq }}
                    </p>
                    <p class="mt-1 text-[12px] font-normal text-slate-500">
                        {{ med.note }}
                    </p>
                </article>
            </div>
        </section>
    </template>

    <div
        v-if="showTreatmentModal && selectedTreatmentDay"
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/45 backdrop-blur-sm p-3 sm:p-4"
    >
        <div
            class="w-full max-w-[452px] rounded-[17px] bg-white px-8 pb-8 pt-8 shadow-2xl [font-family:Inter,system-ui,-apple-system,'Segoe_UI',Roboto,sans-serif]"
        >
            <div class="mb-7 flex items-center justify-between">
                <h3
                    class="text-[32px] font-semibold leading-none tracking-[-0.01em] text-slate-900"
                >
                    {{ selectedTreatmentDay.fullLabel }}
                </h3>
                <button
                    type="button"
                    class="text-slate-500 hover:text-slate-700"
                    @click="showTreatmentModal = false"
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
                </button>
            </div>

            <p class="text-[14px] text-slate-600">
                Marquez vos prises de médicaments
            </p>

            <div v-if="treatmentMedicines.length" class="mt-6 space-y-3.5">
                <article
                    v-for="med in treatmentMedicines"
                    :key="med.id"
                    class="min-h-[126px] rounded-2xl border px-4 pb-4 pt-4 text-left transition"
                    :class="
                        estMedicamentComplet(selectedTreatmentDay.key, med)
                            ? 'border-2 border-[#08a84a] bg-[#cfddd6]'
                            : 'border border-slate-300 bg-slate-50'
                    "
                >
                    <div class="flex items-start justify-between">
                        <div>
                            <p
                                class="text-[24px] font-semibold leading-none text-slate-900"
                            >
                                {{ med.name }}
                            </p>
                            <p
                                class="mt-1 text-[13px] leading-none text-slate-600"
                            >
                                {{ med.dose }}
                            </p>
                            <p
                                v-if="
                                    obtenirNombrePrisesPourJour(
                                        selectedTreatmentDay.key,
                                        med,
                                    ) > 1
                                "
                                class="mt-2 text-[13px] text-slate-500"
                            >
                                {{
                                    compterPrisesCompletees(
                                        selectedTreatmentDay.key,
                                        med,
                                    )
                                }}/{{
                                    obtenirNombrePrisesPourJour(
                                        selectedTreatmentDay.key,
                                        med,
                                    )
                                }}
                                prises effectuées
                            </p>
                        </div>
                        <svg
                            v-if="
                                estMedicamentComplet(
                                    selectedTreatmentDay.key,
                                    med,
                                )
                            "
                            viewBox="0 0 24 24"
                            class="h-6 w-6 text-emerald-600"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2.5"
                        >
                            <path
                                d="m5 13 4 4L19 7"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                        </svg>
                    </div>

                    <div
                        v-if="
                            obtenirNombrePrisesPourJour(
                                selectedTreatmentDay.key,
                                med,
                            ) > 0
                        "
                        class="mt-3 flex flex-wrap gap-2"
                    >
                        <button
                            v-for="doseIndex in obtenirIndexPrises(
                                selectedTreatmentDay.key,
                                med,
                            )"
                            :key="`${med.id}-dose-${doseIndex}`"
                            type="button"
                            class="inline-flex h-10 items-center gap-2 rounded-xl border px-3.5 text-[14px] font-semibold transition"
                            :class="
                                estPriseCochee(
                                    selectedTreatmentDay.key,
                                    med.id,
                                    doseIndex,
                                )
                                    ? 'border-[#08a84a] bg-white text-slate-700'
                                    : 'border-slate-300 bg-white text-slate-700 hover:bg-slate-50'
                            "
                            @click="
                                basculerPrise(
                                    selectedTreatmentDay.key,
                                    med,
                                    doseIndex,
                                )
                            "
                        >
                            <span
                                class="inline-flex h-5 w-5 items-center justify-center rounded-[4px] border"
                                :class="
                                    estPriseCochee(
                                        selectedTreatmentDay.key,
                                        med.id,
                                        doseIndex,
                                    )
                                        ? 'border-[#08a84a] bg-[#08a84a] text-white'
                                        : 'border-slate-400 bg-white text-transparent'
                                "
                            >
                                <svg
                                    viewBox="0 0 24 24"
                                    class="h-3.5 w-3.5"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="3"
                                >
                                    <path
                                        d="m5 13 4 4L19 7"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />
                                </svg>
                            </span>
                            <span>Prise {{ doseIndex }}</span>
                        </button>
                    </div>
                    <p v-else class="mt-3 text-[13px] text-slate-500">
                        Aucune prise prévue pour ce jour.
                    </p>
                </article>
            </div>
            <p
                v-else
                class="mt-6 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-[14px] text-slate-600"
            >
                Aucun traitement actif pour le moment. Ajoutez vos traitements
                dans la page Profil Santé.
            </p>

            <button
                type="button"
                class="mt-7 h-12 w-full rounded-2xl bg-gradient-to-r from-purple-600 to-purple-600 text-[16px] font-semibold leading-none text-white"
                @click="showTreatmentModal = false"
            >
                Fermer
            </button>
        </div>
    </div>
</template>

<script setup>
import { computed, ref } from "vue";
import api from "@/services/api";
import { useNotificationsStore } from "@/stores/notifications";

const MAX_DAILY_DOSES = 12;
const MAX_MONTHLY_FREQUENCY = 31;
const HISTORY_ALL_DAYS = 90;
const DEFAULT_HISTORY_DAYS = 7;
const ALLOWED_FREQUENCY_UNITS = ["jour", "semaine", "mois"];

const props = defineProps({
    treatmentMedicines: { type: Array, default: () => [] },
    treatmentChecks: { type: Object, default: () => ({}) },
    treatmentDays: { type: Array, default: () => [] },
});

defineEmits(["refresh"]);

const notifications = useNotificationsStore();

const showTreatmentModal = ref(false);
const showTreatmentHistory = ref(false);
const selectedTreatmentDayKey = ref(null);
const treatmentHistoryPeriod = ref("7");
const selectedTreatmentHistoryMed = ref("all");

const treatmentHistoryPeriods = [
    { value: "7", label: "7 derniers jours" },
    { value: "30", label: "30 derniers jours" },
    { value: "all", label: "Tout l'historique" },
];

const selectedTreatmentDay = computed(
    () =>
        props.treatmentDays.find(
            (day) => day.key === selectedTreatmentDayKey.value,
        ) ?? null,
);

const treatmentHistoryMedicineOptions = computed(() => [
    { id: "all", name: "Tous" },
    ...props.treatmentMedicines.map((med) => ({ id: med.id, name: med.name })),
]);

const filteredTreatmentHistoryMedicines = computed(() => {
    if (selectedTreatmentHistoryMed.value === "all")
        return props.treatmentMedicines;
    return props.treatmentMedicines.filter(
        (med) => med.id === selectedTreatmentHistoryMed.value,
    );
});

const treatmentHistoryPeriodDays = computed(() =>
    treatmentHistoryPeriod.value === "all"
        ? HISTORY_ALL_DAYS
        : Number(treatmentHistoryPeriod.value || DEFAULT_HISTORY_DAYS),
);

const treatmentHistoryRows = computed(() => {
    const keys = construireClesDerniersJours(treatmentHistoryPeriodDays.value);

    return keys
        .map((dateKey) => {
            const meds = filteredTreatmentHistoryMedicines.value.map((med) => {
                const total = obtenirNombrePrisesPourJour(dateKey, med);
                const taken = compterPrisesCompletees(dateKey, med);
                const progress =
                    total > 0 ? Math.round((taken / total) * 100) : 0;

                return {
                    id: med.id,
                    name: med.name,
                    dose: med.dose,
                    taken,
                    total,
                    progress,
                    isComplete: total > 0 && taken >= total,
                };
            });

            const total = meds.reduce((sum, med) => sum + med.total, 0);
            const taken = meds.reduce((sum, med) => sum + med.taken, 0);
            const hasTracked = total > 0;

            return {
                dateKey,
                meds,
                total,
                taken,
                hasTracked,
                isComplete: hasTracked && taken >= total,
            };
        })
        .filter((day) => day.hasTracked)
        .sort((a, b) => (a.dateKey < b.dateKey ? 1 : -1));
});

const treatmentHistoryStats = computed(() => {
    const rows = treatmentHistoryRows.value;
    const totalDays = rows.length;
    const completeDays = rows.filter((day) => day.isComplete).length;
    const totalTaken = rows.reduce((sum, day) => sum + day.taken, 0);
    const observance =
        totalDays > 0 ? Math.round((completeDays / totalDays) * 100) : 0;
    const periodSubtitle =
        treatmentHistoryPeriod.value === "all"
            ? "Sur tout l'historique"
            : `Sur les ${treatmentHistoryPeriod.value} derniers jours`;

    return {
        totalDays,
        completeDays,
        totalTaken,
        observance,
        periodSubtitle,
        activeMedicines: props.treatmentMedicines.length,
    };
});

// Cette fonction formate la date pour l'historique des prises (sans annee).
function formaterDateHistoriqueTraitement(dateIso) {
    if (!dateIso) return "";
    const date = construireDateDepuisCle(dateIso);

    if (!date) return "";

    return date.toLocaleDateString("fr-FR", {
        weekday: "long",
        day: "numeric",
        month: "long",
    });
}

function obtenirCleJour(date) {
    return date.toISOString().slice(0, 10);
}

function construireDateDepuisCle(dayKey) {
    const date = new Date(`${dayKey}T00:00:00`);
    return Number.isNaN(date.getTime()) ? null : date;
}

function construireClesDerniersJours(periodDays) {
    return Array.from({ length: periodDays }).map((_, idx) => {
        const date = new Date();
        date.setDate(date.getDate() - idx);
        return obtenirCleJour(date);
    });
}

function resoudreFrequenceTraitement(med) {
    let frequencyUnit = String(med?.frequency_unit || "")
        .trim()
        .toLowerCase();

    let frequencyCount = Number(med?.frequency_count ?? NaN);

    if (!Number.isFinite(frequencyCount)) {
        const match = String(med?.freq || "").match(
            /(\d+)\s*fois\s*\/\s*(jour|semaine|mois)/i,
        );
        if (match) {
            frequencyCount = Number(match[1]);
            frequencyUnit = String(match[2]).toLowerCase();
        }
    }

    if (!ALLOWED_FREQUENCY_UNITS.includes(frequencyUnit)) {
        frequencyUnit = "jour";
    }

    if (!Number.isFinite(frequencyCount) || frequencyCount < 1) {
        frequencyCount = Number(med?.doses_per_day ?? 1);
    }

    return {
        unit: frequencyUnit,
        count: Math.max(
            1,
            Math.min(Math.round(frequencyCount), MAX_MONTHLY_FREQUENCY),
        ),
    };
}

function obtenirJoursRepartis(totalSlots, periodLength) {
    const slots = Math.max(1, Math.min(Math.round(totalSlots), periodLength));
    const indices = new Set();

    for (let i = 0; i < slots; i += 1) {
        const value = Math.floor((i * periodLength) / slots) + 1;
        indices.add(Math.max(1, Math.min(value, periodLength)));
    }

    return Array.from(indices).sort((a, b) => a - b);
}

function estPrisePrevueCeJour(dayKey, med) {
    const { unit, count } = resoudreFrequenceTraitement(med);

    if (unit === "jour") return true;

    const date = construireDateDepuisCle(dayKey);
    if (!date) return true;

    if (unit === "semaine") {
        const weekDay = ((date.getDay() + 6) % 7) + 1;
        const plannedDays = obtenirJoursRepartis(Math.min(count, 7), 7);
        return plannedDays.includes(weekDay);
    }

    const daysInMonth = new Date(
        date.getFullYear(),
        date.getMonth() + 1,
        0,
    ).getDate();
    const plannedMonthDays = obtenirJoursRepartis(
        Math.min(count, daysInMonth),
        daysInMonth,
    );
    return plannedMonthDays.includes(date.getDate());
}

function obtenirNombrePrisesPourJour(dayKey, med) {
    const { unit, count } = resoudreFrequenceTraitement(med);

    if (unit === "jour") {
        return Math.max(1, Math.min(count, MAX_DAILY_DOSES));
    }

    return estPrisePrevueCeJour(dayKey, med) ? 1 : 0;
}

function obtenirIndexPrises(dayKey, med) {
    return Array.from(
        { length: obtenirNombrePrisesPourJour(dayKey, med) },
        (_, idx) => idx + 1,
    );
}

function construireClePrise(medId, doseIndex) {
    return `${medId}__dose_${doseIndex}`;
}

function assurerSuiviJour(dayKey) {
    if (!props.treatmentChecks[dayKey]) props.treatmentChecks[dayKey] = {};

    for (const med of props.treatmentMedicines) {
        const frequency = resoudreFrequenceTraitement(med);
        const doses =
            frequency.unit === "jour"
                ? Math.max(1, Math.min(frequency.count, MAX_DAILY_DOSES))
                : estPrisePrevueCeJour(dayKey, med)
                  ? 1
                  : 0;

        const maxDoses = frequency.unit === "jour" ? MAX_DAILY_DOSES : 1;

        for (let i = doses + 1; i <= maxDoses; i += 1) {
            const key = construireClePrise(med.id, i);
            if (key in props.treatmentChecks[dayKey]) {
                delete props.treatmentChecks[dayKey][key];
            }
        }

        for (let i = 1; i <= doses; i += 1) {
            const key = construireClePrise(med.id, i);
            if (typeof props.treatmentChecks[dayKey][key] !== "boolean") {
                props.treatmentChecks[dayKey][key] = false;
            }
        }
    }
}

function estPriseCochee(dayKey, medId, doseIndex) {
    return Boolean(
        props.treatmentChecks[dayKey]?.[construireClePrise(medId, doseIndex)],
    );
}

function compterPrisesCompletees(dayKey, med) {
    const doses = obtenirNombrePrisesPourJour(dayKey, med);
    let completed = 0;

    for (let i = 1; i <= doses; i += 1) {
        if (estPriseCochee(dayKey, med.id, i)) completed += 1;
    }

    return completed;
}

function estMedicamentComplet(dayKey, med) {
    const expectedDoses = obtenirNombrePrisesPourJour(dayKey, med);
    if (expectedDoses <= 0) return true;
    return compterPrisesCompletees(dayKey, med) >= expectedDoses;
}

function estJourComplet(dayKey) {
    if (estJourFutur(dayKey)) return false;

    const dayChecks = props.treatmentChecks[dayKey];
    if (!dayChecks) return false;
    if (!props.treatmentMedicines.length) return false;

    const hasPlannedDose = props.treatmentMedicines.some(
        (med) => obtenirNombrePrisesPourJour(dayKey, med) > 0,
    );
    if (!hasPlannedDose) return false;

    return props.treatmentMedicines.every((med) =>
        estMedicamentComplet(dayKey, med),
    );
}

function estJourFutur(dayKey) {
    const todayKey = obtenirCleJour(new Date());
    return String(dayKey || "") > todayKey;
}

async function synchroniserSuiviTraitements() {
    if (!props.treatmentMedicines.length) return;

    const checks = [];

    for (const day of props.treatmentDays) {
        if (estJourFutur(day.key)) continue;

        assurerSuiviJour(day.key);

        for (const med of props.treatmentMedicines) {
            const doses = obtenirNombrePrisesPourJour(day.key, med);

            for (let i = 1; i <= doses; i += 1) {
                const doseKey = construireClePrise(med.id, i);
                checks.push({
                    check_date: day.key,
                    medication_key: doseKey,
                    treatment_name: med.name,
                    dose: med.dose,
                    taken: Boolean(props.treatmentChecks[day.key][doseKey]),
                });
            }
        }
    }

    await api.post("/health-data/treatment-checks/sync", { checks });
}

async function basculerPrise(dayKey, med, doseIndex) {
    if (estJourFutur(dayKey)) return;

    assurerSuiviJour(dayKey);
    const maxDosesForDay = obtenirNombrePrisesPourJour(dayKey, med);
    if (doseIndex < 1 || doseIndex > maxDosesForDay) return;

    const key = construireClePrise(med.id, doseIndex);
    const previousValue = Boolean(props.treatmentChecks[dayKey][key]);
    props.treatmentChecks[dayKey][key] = !previousValue;

    try {
        await synchroniserSuiviTraitements();
        notifications.itemUpdated();
    } catch (error) {
        props.treatmentChecks[dayKey][key] = previousValue;
        const message =
            error?.response?.data?.message || "Erreur lors de la mise a jour.";
        notifications.error(message);
    }
}

// Retrouver le médicament complet (avec fréquence) depuis son id
function getMedFull(medId) {
    return props.treatmentMedicines.find((m) => m.id === medId);
}

function ouvrirJourTraitement(day) {
    if (!day || day.isFuture || estJourFutur(day.key)) return;

    assurerSuiviJour(day.key);
    selectedTreatmentDayKey.value = day.key;
    showTreatmentModal.value = true;
}
</script>
