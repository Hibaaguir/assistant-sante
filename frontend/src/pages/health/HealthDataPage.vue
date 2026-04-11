<template>
    <div
        class="w-full px-5 py-4 sm:px-7"
    >
        <header>
            <h1
                class="text-[38px] font-bold leading-tight tracking-[-0.01em] text-purple-900"
            >
                Health Data
            </h1>
            <p class="mt-1 text-[13px] text-slate-500">
                Track your health indicators over time
            </p>
        </header>
        <NotificationsOnline />

        <!-- Observations du médecin -->
        <section
            v-if="doctorObservations.length"
            class="mt-4 space-y-3"
        >
            <h2 class="text-[16px] font-semibold text-purple-900">
                Observations de votre médecin
            </h2>
            <article
                v-for="obs in doctorObservations"
                :key="obs.id"
                class="rounded-2xl border border-purple-100 bg-gradient-to-br from-purple-50 to-white px-5 py-4"
            >
                <p class="text-[11px] font-semibold uppercase tracking-wide text-purple-500">
                    {{ formatObsDate(obs.date) }}
                </p>
                <p class="mt-2 text-[14px] leading-6 text-slate-700">
                    {{ obs.observation }}
                </p>
            </article>
        </section>

        <section
            class="mt-4 rounded-2xl border border-slate-200 bg-white p-1 shadow-sm"
        >
            <div class="grid grid-cols-3 gap-1">
                <button
                    type="button"
                    class="h-10 rounded-xl text-[15px] font-semibold transition"
                    :class="
                        activeTab === 'vitals'
                            ? 'bg-gradient-to-r from-purple-300 to-purple-400 text-purple-900'
                            : 'text-slate-600 hover:bg-slate-50'
                    "
                    @click="activeTab = 'vitals'"
                >
                    Signes vitaux
                </button>
                <button
                    type="button"
                    class="h-10 rounded-xl text-[15px] font-semibold transition"
                    :class="
                        activeTab === 'labs'
                            ? 'bg-gradient-to-r from-purple-300 to-purple-400 text-purple-900'
                            : 'text-slate-600 hover:bg-slate-50'
                    "
                    @click="activeTab = 'labs'"
                >
                    Analyse medical
                </button>
                <button
                    type="button"
                    class="h-10 rounded-xl text-[15px] font-semibold transition"
                    :class="
                        activeTab === 'treatments'
                            ? 'bg-gradient-to-r from-purple-300 to-purple-400 text-purple-900'
                            : 'text-slate-600 hover:bg-slate-50'
                    "
                    @click="activeTab = 'treatments'"
                >
                    Traitements
                </button>
            </div>
        </section>

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
import NotificationsOnline from "@/components/ui/NotificationsOnline.vue";

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
const doctorObservations = ref([]);  // [{ id, date, observation }]

function openAddModal() {
    if (activeTab.value === "labs") labsTab.value?.ouvrirModalAjout();
    else vitalsTab.value?.ouvrirModalAjout();
}

function toDate(isoDate) {
    return isoDate ? new Date(`${isoDate}T00:00:00`) : null;
}

const formatShortLabel = (iso) =>
    toDate(iso)?.toLocaleDateString("fr-FR", {
        day: "2-digit",
        month: "short",
    }) ?? "";

const formatDate = (iso) => toDate(iso)?.toLocaleDateString("fr-FR") ?? "";

const formatObsDate = (val) => {
    const iso = val ? String(val).slice(0, 10) : null;
    if (!iso) return "";
    const d = new Date(`${iso}T00:00:00`);
    return isNaN(d) ? iso : d.toLocaleDateString("fr-FR", { weekday: "long", day: "numeric", month: "long", year: "numeric" });
};


function normalizeSeries(values, fallback = 0) {
    let last = fallback;
    return (Array.isArray(values) ? values : []).map((v) => {
        const n = Number(v);
        if (Number.isFinite(n)) {
            last = n;
            return n;
        }
        return last;
    });
}

function buildLast7Days() {
    const today = new Date();
    const todayKey = today.toISOString().slice(0, 10);
    const monday = new Date(today);
    const dayOffset = (today.getDay() + 6) % 7;
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

        doctorObservations.value = Array.isArray(data.doctor_observations)
            ? data.doctor_observations
                .filter((o) => o.doctor_observation)
                .map((o) => ({
                    id: o.id,
                    date: o.date,
                    observation: o.doctor_observation,
                }))
            : [];
    } catch (error) {
        const message =
            error?.response?.data?.message ||
            "Erreur lors du chargement des donnees de sante.";
        notifications.error(message);
    }
}

onMounted(loadHealthData);
</script>
