<template>
    <div class="w-full px-5 py-4 sm:px-7 bg-white">
        <Typography tag="h1" variant="h1-style"> Données de santé </Typography>
        <Typography tag="h4" variant="h4-style">
            Suivez vos indicateurs de santé au fil du temps
        </Typography>
        <!-- Observations du médecin -->
        <section v-if="doctorLatestObservation" class="mt-4 space-y-3">
            <Typography tag="h3" variant="h3-style">
                Observations de votre médecin
            </Typography>
            <article
                class="rounded-2xl border border-slate-200 bg-white px-5 py-4"
            >
                <Typography tag="h5" variant="h5-style" class="mb-2">
                    {{ formatObsDate(doctorLatestObservation.date) }}
                </Typography>
                <Typography tag="h6" variant="h5-style">
                    {{ doctorLatestObservation.observation }}
                </Typography>
            </article>
        </section>

        <!-- TabBar replaces 3 repeated button blocks — add tabs here to extend -->
        <TabBar
            v-model="activeTab"
            class="mt-4"
            :tabs="[
                { value: 'vitals', label: 'Signes vitaux' },
                { value: 'labs', label: 'Analyse medical' },
                { value: 'treatments', label: 'Traitements' },
            ]"
        />

        <div v-if="showAddButton" class="mt-4 flex justify-end gap-2">
            <button
                v-if="activeTab === 'labs'"
                type="button"
                class="inline-flex h-10 items-center gap-2 rounded-xl border border-slate-300 bg-white px-4 text-[13px] font-semibold text-slate-700 shadow-sm hover:bg-slate-50"
                @click="labsTab?.basculerFiltres()"
            >
                <svg
                    viewBox="0 0 24 24"
                    class="h-4 w-4"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    aria-hidden="true"
                >
                    <path d="M3 5h18M7 12h10M10 19h4" stroke-linecap="round" />
                </svg>
                Filtrer
            </button>

            <button
                type="button"
                class="inline-flex h-10 items-center gap-2 rounded-xl bg-gradient-to-r from-teal-600 to-teal-700 px-4 text-[13px] font-semibold text-white shadow-[0_8px_16px_rgba(13,148,136,0.22)] hover:shadow-[0_8px_16px_rgba(13,148,136,0.32)]"
                @click="openAddModal"
            >
                <svg
                    viewBox="0 0 24 24"
                    class="h-4 w-4"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    aria-hidden="true"
                >
                    <path d="M12 5v14M5 12h14" stroke-linecap="round" />
                </svg>
                {{ addButtonLabel }}
            </button>
        </div>

        <TabSignesVitaux
            v-if="activeTab === 'vitals'"
            ref="vitalsTab"
            :latest-vital="latestVital"
            :chart-labels="labels"
            :chart-heart-rate="heartRateValues"
            :chart-systolic="systolicValues"
            :chart-diastolic="diastolicValues"
            :chart-saturation="saturationValues"
            :history-heart-rate="historyHeartRateValues"
            :history-systolic="historySystolicValues"
            :history-diastolic="historyDiastolicValues"
            :history-saturation="historySaturationValues"
            :vital-date-keys="vitalDateKeys"
            @refresh="loadHealthData"
        />

        <TabAnalyseBiologique
            v-else-if="activeTab === 'labs'"
            ref="labsTab"
            :analyses="labResults"
            @refresh="loadHealthData"
        />

        <TabTraitements
            v-else
            :treatment-medicines="treatmentMedicines"
            :treatment-checks="treatmentChecks"
            :treatment-days="treatmentDays"
            @refresh="loadHealthData"
        />
    </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from "vue";
import api from "@/services/api";
import TabSignesVitaux from "@/components/health/TabSignesVitaux.vue";
import TabAnalyseBiologique from "@/components/health/TabAnalyseBiologique.vue";
import TabTraitements from "@/components/health/TabTraitements.vue";
import { useNotificationsStore } from "@/stores/notifications";
import Typography from "@/components/ui/Typography.vue";
import TabBar from "@/components/ui/TabBar.vue";

const vitalsTab = ref(null);
const labsTab = ref(null);
const notifications = useNotificationsStore();

const activeTab = ref("vitals");

const showAddButton = computed(() => activeTab.value !== "treatments");
const addButtonLabel = computed(() =>
    activeTab.value === "labs" ? "Ajouter une analyse" : "Ajouter une mesure",
);

const labResults = ref([]);
const latestVital = ref(null);
const labels = ref([]);
const vitalDateKeys = ref([]);
const heartRateValues = ref([]);
const systolicValues = ref([]);
const diastolicValues = ref([]);
const saturationValues = ref([]);
const historyHeartRateValues = ref([]);
const historySystolicValues = ref([]);
const historyDiastolicValues = ref([]);
const historySaturationValues = ref([]);
const treatmentMedicines = ref([]);
const treatmentChecks = reactive({});
const treatmentDays = ref(buildLast7Days());
const doctorLatestObservation = ref(null); // { id, date, observation } | null

function openAddModal() {
    if (activeTab.value === "labs") labsTab.value?.ouvrirModalAjout();
    else vitalsTab.value?.ouvrirModalAjout();
}

function toDate(rawDate) {
    if (!rawDate) return null;

    const text = String(rawDate).trim();
    if (!text) return null;

    // Accept legacy French format DD/MM/YYYY.
    const frMatch = text.match(/^(\d{2})\/(\d{2})\/(\d{4})$/);
    if (frMatch) {
        const day = Number(frMatch[1]);
        const month = Number(frMatch[2]);
        const year = Number(frMatch[3]);
        const date = new Date(year, month - 1, day);
        const isValid =
            date.getFullYear() === year &&
            date.getMonth() === month - 1 &&
            date.getDate() === day;
        return isValid ? date : null;
    }

    // If the string contains a time component, parse the full datetime and
    // extract the local calendar date to avoid UTC-offset day shifts.
    if (text.includes("T")) {
        const parsed = new Date(text);
        if (Number.isNaN(parsed.getTime())) return null;
        return new Date(
            parsed.getFullYear(),
            parsed.getMonth(),
            parsed.getDate(),
        );
    }

    const isoMatch = text.match(/^(\d{4})-(\d{2})-(\d{2})$/);
    if (isoMatch) {
        const year = Number(isoMatch[1]);
        const month = Number(isoMatch[2]);
        const day = Number(isoMatch[3]);
        const date = new Date(year, month - 1, day);
        const isValid =
            date.getFullYear() === year &&
            date.getMonth() === month - 1 &&
            date.getDate() === day;
        return isValid ? date : null;
    }

    const parsed = new Date(text);
    return Number.isNaN(parsed.getTime()) ? null : parsed;
}

const formatShortLabel = (iso) =>
    toDate(iso)?.toLocaleDateString("fr-FR", {
        day: "2-digit",
        month: "short",
    }) ?? "";

const formatDate = (iso) => toDate(iso)?.toLocaleDateString("fr-FR") ?? "";

const formatObsDate = (val) => {
    const d = toDate(val);
    return !d
        ? String(val || "")
        : d.toLocaleDateString("fr-FR", {
              weekday: "long",
              day: "numeric",
              month: "long",
              year: "numeric",
          });
};

function normalizeSeries(values, fallback = 0) {
    if (!Array.isArray(values)) return [];
    let last = fallback;
    return values.map((v) => {
        const n = Number(v);
        if (!isNaN(n)) {
            last = n;
            return n;
        }
        return last; // valeur manquante → on répète la dernière connue
    });
}

function buildLast7Days() {
    const today = new Date();
    const todayKey = today.toISOString().slice(0, 10);
    const monday = new Date(today);
    // getDay() : 0=Dimanche, 1=Lundi, ..., 6=Samedi
    // On veut que Lundi soit le premier jour (offset = 0)
    const dayOfWeek = today.getDay();
    const dayOffset = dayOfWeek === 0 ? 6 : dayOfWeek - 1;
    monday.setDate(today.getDate() - dayOffset);

    return Array.from({ length: 7 }).map((_, idx) => {
        const date = new Date(monday);
        date.setDate(monday.getDate() + idx);
        const key = date.toISOString().slice(0, 10);
        return {
            key,
            shortLabel: date
                .toLocaleDateString("fr-FR", { weekday: "short" })
                .replace(".", ""),
            fullLabel: date.toLocaleDateString("fr-FR", {
                weekday: "long",
                day: "numeric",
                month: "long",
            }),
            day: date.getDate(),
            isFuture: key > todayKey,
        };
    });
}

function buildDoseKey(medId, doseIndex) {
    return `${medId}__dose_${doseIndex}`;
}

function ensureDayTracking(dayKey) {
    if (!treatmentChecks[dayKey]) treatmentChecks[dayKey] = {};
    for (const med of treatmentMedicines.value) {
        const doses = Math.max(
            1,
            Math.min(Math.round(Number(med?.doses_per_day ?? 1)), 12),
        );
        for (let i = 1; i <= doses; i += 1) {
            const key = buildDoseKey(med.id, i);
            if (typeof treatmentChecks[dayKey][key] !== "boolean") {
                treatmentChecks[dayKey][key] = false;
            }
        }
    }
}

async function loadHealthData() {
    try {
        const res = await api.get("/health-data/overview", {
            params: { days: 7 },
        });
        const data = res?.data?.data ?? {};
        const historyRes = await api.get("/health-data/treatment-checks", {
            params: { days: 90 },
        });
        const historyData = Array.isArray(historyRes?.data?.data)
            ? historyRes.data.data
            : [];

        latestVital.value = data.latest_vitals ?? null;
        labResults.value = Array.isArray(data.lab_results)
            ? data.lab_results.map((item) => ({
                  id: item.id,
                  type: item.analysis_type ?? "",
                  result: item.result_name ?? "",
                  name: `${item.analysis_type ?? ""} - ${item.result_name ?? ""}`.replace(
                      / - $/,
                      "",
                  ),
                  value: item.value,
                  unit: item.unit ?? "",
                  date: formatDate(item.analysis_date),
                  analysisDate: item.analysis_date,
              }))
            : [];

        const chartData = data.vitals_chart ?? {};
        const labelSource =
            Array.isArray(chartData.labels) && chartData.labels.length > 0
                ? chartData.labels
                : treatmentDays.value.map((day) => day.key);

        const keepRaw = (values = []) =>
            labelSource.map((_, index) => {
                const value = values[index];
                return value == null || value === "" ? null : value;
            });

        vitalDateKeys.value = [...labelSource];
        historyHeartRateValues.value = keepRaw(chartData.heart_rate);
        historySystolicValues.value = keepRaw(chartData.systolic_pressure);
        historyDiastolicValues.value = keepRaw(chartData.diastolic_pressure);
        historySaturationValues.value = keepRaw(chartData.oxygen_saturation);
        labels.value = labelSource.map(formatShortLabel);
        heartRateValues.value = normalizeSeries(chartData.heart_rate, 70);
        systolicValues.value = normalizeSeries(
            chartData.systolic_pressure,
            120,
        );
        diastolicValues.value = normalizeSeries(
            chartData.diastolic_pressure,
            80,
        );
        saturationValues.value = normalizeSeries(
            chartData.oxygen_saturation,
            98,
        );
        treatmentMedicines.value = Array.isArray(data.treatment_medicines)
            ? data.treatment_medicines
            : [];

        for (const day of treatmentDays.value) ensureDayTracking(day.key);

        const allChecks = [
            ...(Array.isArray(data.treatment_checks)
                ? data.treatment_checks
                : []),
            ...historyData,
        ];

        if (allChecks.length) {
            for (const item of allChecks) {
                ensureDayTracking(item.check_date);
                treatmentChecks[item.check_date][item.medication_key] = Boolean(
                    item.taken,
                );
                if (
                    item.medication_key &&
                    !String(item.medication_key).includes("__dose_")
                ) {
                    treatmentChecks[item.check_date][
                        buildDoseKey(item.medication_key, 1)
                    ] = Boolean(item.taken);
                }
            }
        }

        const latestObservation = Array.isArray(data.doctor_observations)
            ? data.doctor_observations.find((o) => o?.doctor_observation)
            : null;

        doctorLatestObservation.value = latestObservation
            ? {
                  id: latestObservation.id,
                  date: latestObservation.date,
                  observation: latestObservation.doctor_observation,
              }
            : null;
    } catch (error) {
        const message =
            error?.response?.data?.message ||
            "Erreur lors du chargement des donnees de sante.";
        notifications.error(message);
    }
}

onMounted(loadHealthData);
</script>
