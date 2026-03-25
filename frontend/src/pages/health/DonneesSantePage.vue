<template>
  <div class="mx-auto max-w-[1180px] px-5 py-4 sm:px-7">
    <header>
      <h1 class="text-[28px] font-semibold leading-tight tracking-[-0.01em] text-slate-900">Données de santé</h1>
      <p class="mt-1 text-[13px] text-slate-500">Suivez vos indicateurs de santé dans le temps</p>
    </header>
    <NotificationsEnLigne />

    <section class="mt-4 rounded-2xl border border-slate-200 bg-white p-1 shadow-sm">
      <div class="grid grid-cols-3 gap-1">
        <button
          type="button"
          class="h-10 rounded-xl text-[15px] font-semibold transition"
          :class="activeTab === 'vitals' ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white' : 'text-slate-600 hover:bg-slate-50'"
          @click="activeTab = 'vitals'"
        >
          Signes vitaux
        </button>
        <button
          type="button"
          class="h-10 rounded-xl text-[15px] font-semibold transition"
          :class="activeTab === 'labs' ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white' : 'text-slate-600 hover:bg-slate-50'"
          @click="activeTab = 'labs'"
        >
          Analyse medical
        </button>
        <button
          type="button"
          class="h-10 rounded-xl text-[15px] font-semibold transition"
          :class="activeTab === 'treatments' ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white' : 'text-slate-600 hover:bg-slate-50'"
          @click="activeTab = 'treatments'"
        >
          Traitements
        </button>
      </div>
    </section>

    <div v-if="afficherBoutonAjout" class="mt-4 flex justify-end gap-2">
      <button
        v-if="activeTab === 'labs'"
        type="button"
        class="inline-flex h-10 items-center gap-2 rounded-xl border border-slate-300 bg-white px-4 text-[13px] font-semibold text-slate-700 shadow-sm hover:bg-slate-50"
        @click="labsTab?.basculerFiltres()"
      >
        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
          <path d="M3 5h18M7 12h10M10 19h4" stroke-linecap="round" />
        </svg>
        Filtrer
      </button>

      <button
        type="button"
        class="inline-flex h-10 items-center gap-2 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 px-4 text-[13px] font-semibold text-white shadow-[0_8px_16px_rgba(37,99,235,0.22)]"
        @click="ouvrirModalAjout"
      >
        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
          <path d="M12 5v14M5 12h14" stroke-linecap="round" />
        </svg>
        {{ libelleBoutonAjout }}
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
      @refresh="chargerDonneesSante"
    />

    <TabAnalyseBiologique
      v-else-if="activeTab === 'labs'"
      ref="labsTab"
      :analyses="analyses"
      @refresh="chargerDonneesSante"
    />

    <TabTraitements
      v-else
      :treatment-medicines="treatmentMedicines"
      :treatment-checks="treatmentChecks"
      :treatment-days="treatmentDays"
      @refresh="chargerDonneesSante"
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
import NotificationsEnLigne from "@/components/ui/NotificationsEnLigne.vue";

// Refs vers les composants enfants pour appeler leurs methodes exposees.
const vitalsTab = ref(null);
const labsTab = ref(null);
const notifications = useNotificationsStore();

const activeTab = ref("vitals");

const afficherBoutonAjout = computed(() => activeTab.value !== "treatments");
const libelleBoutonAjout = computed(() => (activeTab.value === "labs" ? "Ajouter une analyse" : "Ajouter une mesure"));

// Donnees brutes rechargees depuis l'API.
const analyses = ref([]);
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
const treatmentDays = ref(construire7DerniersJours());

function ouvrirModalAjout() {
  if (activeTab.value === "labs") labsTab.value?.ouvrirModalAjout();
  else vitalsTab.value?.ouvrirModalAjout();
}

function convertirDateIso(dateValue) {
  if (!dateValue) return new Date().toISOString().slice(0, 10);
  return String(dateValue).slice(0, 10);
}

function versDate(dateIso) {
  return dateIso ? new Date(`${dateIso}T00:00:00`) : null;
}
const formaterLibelle = (iso) => versDate(iso)?.toLocaleDateString("fr-FR", { day: "2-digit", month: "short" }) ?? "";
const formaterDate = (iso) => versDate(iso)?.toLocaleDateString("fr-FR") ?? "";

function normaliserSerie(values, fallback = 0) {
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

function construire7DerniersJours() {
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
      shortLabel: date.toLocaleDateString("fr-FR", { weekday: "short" }).replace(".", ""),
      fullLabel: date.toLocaleDateString("fr-FR", { weekday: "long", day: "numeric", month: "long" }),
      day: date.getDate(),
      isFuture: key > todayKey,
    };
  });
}

function obtenirNombrePrises(med) {
  const count = Number(med?.doses_per_day ?? 1);
  if (!Number.isFinite(count)) return 1;
  return Math.max(1, Math.min(Math.round(count), 12));
}

function construireClePrise(medId, doseIndex) {
  return `${medId}__dose_${doseIndex}`;
}

function assurerSuiviJour(dayKey) {
  if (!treatmentChecks[dayKey]) treatmentChecks[dayKey] = {};
  for (const med of treatmentMedicines.value) {
    const doses = obtenirNombrePrises(med);
    for (let i = 1; i <= doses; i += 1) {
      const key = construireClePrise(med.id, i);
      if (typeof treatmentChecks[dayKey][key] !== "boolean") {
        treatmentChecks[dayKey][key] = false;
      }
    }
  }
}

async function chargerDonneesSante() {
  try {
    const res = await api.get("/health-data/overview", { params: { days: 7 } });
    const data = res?.data?.data ?? {};
    const treatmentHistoryRes = await api.get("/health-data/treatment-checks", { params: { days: 90 } });
    const treatmentHistoryData = Array.isArray(treatmentHistoryRes?.data?.data) ? treatmentHistoryRes.data.data : [];

  latestVital.value = data.latest_vitals ?? null;
  analyses.value = Array.isArray(data.lab_results)
    ? data.lab_results.map((item) => ({
        id: item.id,
        type: item.analysis_type ?? "",
        result: item.analysis_result ?? "",
        name: `${item.analysis_type ?? ""} - ${item.analysis_result ?? ""}`.replace(/ - $/, ""),
        value: item.value,
        unit: item.unit ?? "",
        date: formaterDate(item.analysis_date),
        analysisDate: item.analysis_date,
      }))
    : [];

  const chartData = data.vitals_chart ?? {};
  const labelSource = Array.isArray(chartData.labels) && chartData.labels.length > 0
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
  labels.value = labelSource.map(formaterLibelle);
  heartRateValues.value = normaliserSerie(chartData.heart_rate, 70);
  systolicValues.value = normaliserSerie(chartData.systolic_pressure, 120);
  diastolicValues.value = normaliserSerie(chartData.diastolic_pressure, 80);
  saturationValues.value = normaliserSerie(chartData.oxygen_saturation, 98);
  treatmentMedicines.value = Array.isArray(data.treatment_medicines) ? data.treatment_medicines : [];

  for (const day of treatmentDays.value) assurerSuiviJour(day.key);

    const allTreatmentChecks = [
      ...(Array.isArray(data.treatment_checks) ? data.treatment_checks : []),
      ...treatmentHistoryData,
    ];

    if (allTreatmentChecks.length) {
      for (const item of allTreatmentChecks) {
        assurerSuiviJour(item.check_date);
        treatmentChecks[item.check_date][item.medication_key] = Boolean(item.taken);
        if (item.medication_key && !String(item.medication_key).includes("__dose_")) {
          treatmentChecks[item.check_date][construireClePrise(item.medication_key, 1)] = Boolean(item.taken);
        }
      }
    }
  } catch (error) {
    const message = error?.response?.data?.message || "Erreur lors du chargement des donnees de sante.";
    notifications.erreur(message);
  }
}

onMounted(chargerDonneesSante);
</script>
