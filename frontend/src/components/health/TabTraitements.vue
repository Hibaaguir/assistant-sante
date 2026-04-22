<template>
    <section class="mt-4 flex justify-end">
        <BaseButton
            type="button"
            variant="outline"
            size="md"
            class="text-black"
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
        </BaseButton>
    </section>

    <section
        v-if="showTreatmentHistory"
        class="mt-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
    >
        <Typography tag="h2" variant="h3-style">
            Historique des prises
        </Typography>

        <div class="mt-6 grid gap-4 md:grid-cols-3">
            <article
                v-for="card in treatmentHistoryCards"
                :key="card.key"
                class="rounded-[18px] border bg-white px-5 py-6 shadow-[0_1px_3px_rgba(15,23,42,0.05)]"
                :class="card.borderClass"
            >
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-lg font-semibold text-black">
                            {{ card.label }}
                        </p>
                        <p class="mt-4 text-4xl font-bold leading-none text-black">
                            {{ card.value }}
                        </p>
                        <p class="mt-2 text-[14px] font-medium text-black">
                            {{ card.subtitle }}
                        </p>
                    </div>
                    <div
                        class="grid place-items-center h-12 w-12 shrink-0 rounded-[15px]"
                        :class="card.iconWrapClass"
                    >
                        <component
                            :is="card.icon"
                            class="size-6"
                            :class="card.iconClass"
                        />
                    </div>
                </div>
            </article>
        </div>

        <FilterCard
            class="mt-6"
            title="Historique"
            subtitle="Filtrez par date, médicament ou observance."
            :show-reset="!!(treatDate || treatMed !== 'all' || treatStatus !== 'all')"
            @reset="treatDate = ''; treatMed = 'all'; treatStatus = 'all';"
        >
            <input v-model="treatDate" type="date" class="input-field" />
            <select v-model="treatMed" class="input-field">
                <option value="all">Tous les médicaments</option>
                <option
                    v-for="med in treatmentMedicineOptions"
                    :key="med.id"
                    :value="med.id"
                >
                    {{ med.name }}
                </option>
            </select>
            <select v-model="treatStatus" class="input-field">
                <option value="all">Toute observance</option>
                <option value="complete">Complet</option>
                <option value="partial">Partiel</option>
            </select>
        </FilterCard>

        <div class="mt-6 space-y-3">
            <p
                v-if="!treatmentHistoryRows.length"
                class="py-6 text-center text-sm text-slate-400"
            >
                Aucun historique disponible pour ces filtres.
            </p>

            <div
                v-for="day in treatmentHistoryRows"
                :key="day.dateKey"
                class="rounded-2xl border border-slate-200 bg-white px-6 py-5 shadow-sm"
                :class="
                    day.isComplete ? 'border-emerald-200' : 'border-slate-200'
                "
            >
                <!-- Date + badge -->
                <div class="flex items-center justify-between">
                    <div>
                        <p
                            class="text-[19px] font-semibold leading-none text-blue-900 capitalize"
                        >
                            {{ formaterDateHistoriqueTraitement(day.dateKey) }}
                        </p>
                        <p class="mt-2 text-[13px] font-medium text-slate-800">
                            {{ day.taken }}/{{ day.total }} prises effectuées
                        </p>
                    </div>
                    <span
                        class="rounded-full px-3 py-1 text-sm font-semibold"
                        :class="
                            day.isComplete
                                ? 'bg-emerald-100 text-emerald-700'
                                : 'bg-slate-100 text-slate-500'
                        "
                    >
                        {{
                            day.isComplete
                                ? "✓ Complet"
                                : `${day.taken}/${day.total}`
                        }}
                    </span>
                </div>

                <!-- Médicaments -->
                <div class="mt-3 space-y-3">
                    <div
                        v-for="med in day.meds"
                        :key="med.id"
                        class="rounded-xl border-2 border-slate-200 bg-slate-50 p-4 flex items-center justify-between gap-4"
                    >
                        <div>
                            <p
                                class="text-[19px] font-semibold leading-none text-slate-900"
                            >
                                {{ med.name }}
                            </p>
                            <p
                                class="mt-2 text-[13px] font-medium text-slate-800"
                            >
                                {{ med.dose }}
                            </p>
                        </div>

                        <div class="flex gap-2">
                            <BaseButton
                                v-for="i in obtenirIndexPrises(
                                    day.dateKey,
                                    getMedFull(med.id),
                                )"
                                :key="i"
                                type="button"
                                :variant="
                                    estPriseCochee(day.dateKey, med.id, i)
                                        ? 'success'
                                        : 'outline'
                                "
                                size="sm"
                                class="text-black"
                                @click="
                                    basculerPrise(
                                        day.dateKey,
                                        getMedFull(med.id),
                                        i,
                                    )
                                "
                            >
                                <span
                                    class="inline-flex h-5 w-5 items-center justify-center rounded border"
                                    :class="
                                        estPriseCochee(day.dateKey, med.id, i)
                                            ? 'border-emerald-500 bg-emerald-500 text-white'
                                            : 'border-slate-300 bg-white'
                                    "
                                >
                                    <svg
                                        viewBox="0 0 24 24"
                                        class="h-3 w-3"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="3.5"
                                    >
                                        <path
                                            d="m5 13 4 4L19 7"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        />
                                    </svg>
                                </span>
                                Prise {{ i }}
                            </BaseButton>
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
            <Typography tag="h2" variant="h3-style">
                Calendrier des traitements
            </Typography>
            <Typography tag="p" variant="paragraph">
                Cliquez sur une journée pour gérer vos prises
            </Typography>

            <div class="mt-6 grid grid-cols-7 gap-2.5">
                <div
                    v-for="day in treatmentDays"
                    :key="day.key"
                    class="text-center"
                >
                    <p
                        class="mb-2 text-[14px] font-semibold leading-none text-slate-800"
                    >
                        {{ day.shortLabel }}
                    </p>
                    <button
                        type="button"
                        :disabled="Boolean(day.isFuture)"
                        @click="ouvrirJourTraitement(day)"
                        :class="[
                            'w-full rounded-xl border-2 px-2 py-3 transition shadow-sm font-bold',
                            estJourComplet(day.key)
                                ? 'border-green-500 bg-gradient-to-r from-green-100 to-green-200 text-green-900 hover:shadow-md'
                                : estJourPartiel(day.key)
                                    ? 'border-slate-400 bg-gradient-to-r from-slate-100 to-slate-200 text-slate-700 hover:shadow-md'
                                    : 'border-gray-300 bg-white text-slate-900 hover:border-gray-400',
                            'disabled:cursor-not-allowed disabled:opacity-50',
                        ]"
                    >
                        <p class="text-[32px] font-medium leading-none">
                            {{ day.day }}
                        </p>
                        <div class="mt-2 flex justify-center">
                            <span
                                class="inline-flex h-6 w-6 items-center justify-center rounded-full border"
                                :class="
                                    estJourComplet(day.key)
                                        ? 'border-green-500 bg-green-500 text-white'
                                        : estJourPartiel(day.key)
                                            ? 'border-slate-400 bg-slate-400 text-white'
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
                                        v-if="estJourPartiel(day.key) && !estJourComplet(day.key)"
                                        d="M5 12h14"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />
                                    <path
                                        v-else
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
            <div v-else class="mt-3 flex flex-wrap gap-4">
                <article
                    v-for="med in treatmentMedicines"
                    :key="med.id"
                    class="rounded-2xl border border-slate-200 bg-white px-7 py-6 shadow-sm min-w-[200px] flex-1 basis-48 max-w-xs cursor-pointer select-none transition-all duration-150 active:scale-95 active:shadow-inner hover:shadow-md hover:border-blue-300"
                >
                    <p
                        class="text-[19px] font-semibold leading-none text-slate-900"
                    >
                        {{ med.name }}
                    </p>
                    <p class="mt-2 text-[13px] font-medium text-slate-800">
                        {{ med.dose }} - {{ med.freq }}
                    </p>
                    <p class="mt-1 text-[13px] font-medium text-slate-700">
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
                <BaseButton
                    type="button"
                    variant="outline"
                    size="sm"
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
                </BaseButton>
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
                        <BaseButton
                            v-for="doseIndex in obtenirIndexPrises(
                                selectedTreatmentDay.key,
                                med,
                            )"
                            :key="`${med.id}-dose-${doseIndex}`"
                            type="button"
                            :variant="
                                estPriseCochee(
                                    selectedTreatmentDay.key,
                                    med.id,
                                    doseIndex,
                                )
                                    ? 'success'
                                    : 'outline'
                            "
                            size="sm"
                            @click="
                                basculerPrise(
                                    selectedTreatmentDay.key,
                                    med,
                                    doseIndex,
                                )
                            "
                        >
                            <span
                                v-if="
                                    estPriseCochee(
                                        selectedTreatmentDay.key,
                                        med.id,
                                        doseIndex,
                                    )
                                "
                            >
                                <svg
                                    viewBox="0 0 24 24"
                                    class="inline h-3.5 w-3.5 mr-1"
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
                        </BaseButton>
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

            <BaseButton
                type="button"
                variant="outline"
                fullWidth
                @click="showTreatmentModal = false"
            >
                Fermer
            </BaseButton>
        </div>
    </div>
</template>

<script setup>
import { computed, ref } from "vue";
import api from "@/services/api";
import { useNotificationsStore } from "@/stores/notifications";
import Typography from "@/components/ui/Typography.vue";
import BaseButton from "@/components/ui/BaseButton.vue";
import FilterCard from "@/components/ui/FilterCard.vue";
import {
    IconCalendar,
    IconCheckCircle,
    IconPill,
} from "@/components/doctors/DoctorIcons.js";

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
const treatDate = ref("");
const treatMed = ref("all");
const treatStatus = ref("all");


const TREATMENT_HISTORY_CARD_CONFIG = [
    {
        key: "observance",
        label: "Taux d'observance",
        borderClass: "border-[#f3b8bb]",
        icon: IconCheckCircle,
        iconWrapClass: "bg-[#fee3e5]",
        iconClass: "text-[#ff1f2d]",
    },
    {
        key: "totalTaken",
        label: "Prises totales",
        borderClass: "border-[#f0cb58]",
        icon: IconCalendar,
        iconWrapClass: "bg-[#fff0c8]",
        iconClass: "text-[#ef7a00]",
    },
    {
        key: "activeMedicines",
        label: "Médicaments actifs",
        borderClass: "border-[#b5e6c6]",
        icon: IconPill,
        iconWrapClass: "bg-[#d2f3de]",
        iconClass: "text-[#07b33f]",
    },
];

const selectedTreatmentDay = computed(
    () =>
        props.treatmentDays.find(
            (day) => day.key === selectedTreatmentDayKey.value,
        ) ?? null,
);

const treatmentMedicineOptions = computed(() =>
    props.treatmentMedicines.map((med) => ({ id: med.id, name: med.name })),
);

const filteredTreatmentHistoryMedicines = computed(() => {
    if (treatMed.value === "all") return props.treatmentMedicines;
    return props.treatmentMedicines.filter((med) => med.id === treatMed.value);
});

const treatmentHistoryRows = computed(() => {
    const keys = construireClesDerniersJours(HISTORY_ALL_DAYS);

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
        .filter((day) => !treatDate.value || day.dateKey === treatDate.value)
        .filter((day) => {
            if (treatStatus.value === "complete") return day.isComplete;
            if (treatStatus.value === "partial") return !day.isComplete;
            return true;
        })
        .sort((a, b) => (a.dateKey < b.dateKey ? 1 : -1));
});

const treatmentHistoryStats = computed(() => {
    const rows = treatmentHistoryRows.value;
    const totalDays = rows.length;
    const completeDays = rows.filter((day) => day.isComplete).length;
    const totalTaken = rows.reduce((sum, day) => sum + day.taken, 0);
    const observance =
        totalDays > 0 ? Math.round((completeDays / totalDays) * 100) : 0;
    const periodSubtitle = treatDate.value
        ? `Le ${treatDate.value}`
        : "Sur tout l'historique";

    return {
        totalDays,
        completeDays,
        totalTaken,
        observance,
        periodSubtitle,
        activeMedicines: props.treatmentMedicines.length,
    };
});

const treatmentHistoryCards = computed(() => {
    const stats = treatmentHistoryStats.value;

    return TREATMENT_HISTORY_CARD_CONFIG.map((card) => {
        if (card.key === "observance") {
            return {
                ...card,
                value: `${stats.observance}%`,
                subtitle: `${stats.completeDays}/${stats.totalDays} jours complets`,
            };
        }

        if (card.key === "totalTaken") {
            return {
                ...card,
                value: stats.totalTaken,
                subtitle: stats.periodSubtitle,
            };
        }

        return {
            ...card,
            value: stats.activeMedicines,
            subtitle: "Traitements en cours",
        };
    });
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

function estJourPartiel(dayKey) {
    if (estJourFutur(dayKey) || estJourComplet(dayKey)) return false;
    const dayChecks = props.treatmentChecks[dayKey];
    if (!dayChecks) return false;
    return props.treatmentMedicines.some((med) => {
        const doses = obtenirNombrePrisesPourJour(dayKey, med);
        return doses > 0 && compterPrisesCompletees(dayKey, med) > 0;
    });
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
        if (!previousValue && estJourComplet(dayKey)) {
            notifications.success(
                "Toutes les prises de la journée ont été complétées. Continuez ainsi !",
                "Journée complète",
            );
        } else {
            notifications.itemUpdated();
        }
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
