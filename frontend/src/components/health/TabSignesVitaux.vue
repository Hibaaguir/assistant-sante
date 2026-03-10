<template>
  <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
      <h2 class="text-[20px] font-semibold leading-none text-slate-900">Derniers signes vitaux</h2>
      <p class="mt-2 text-[14px] text-slate-500">
        {{ latestVitalMeasuredAtLabel ? `Derniere entree du ${latestVitalMeasuredAtLabel}` : "Aucune mesure enregistree pour le moment." }}
      </p>
    </div>

    <button
      v-if="canEditLatestVital"
      type="button"
      class="inline-flex h-11 items-center gap-2 rounded-2xl border border-slate-300 bg-white px-4 text-[14px] font-semibold text-slate-700 shadow-sm transition hover:border-blue-300 hover:text-blue-700"
      @click="openEditLatestModal"
    >
      <svg viewBox="0 0 24 24" class="h-4.5 w-4.5" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
        <path d="M12 20h9" stroke-linecap="round" />
        <path d="m16.5 3.5 4 4L7 21l-4 1 1-4L16.5 3.5Z" stroke-linejoin="round" />
      </svg>
      Modifier la derniere entree
    </button>
  </div>

  <section class="mt-6 grid gap-5 xl:grid-cols-3">
    <article class="min-h-[162px] rounded-2xl border border-[#efc4cc] bg-[#fdf2f5] px-6 py-6">
      <div class="flex items-start justify-between">
        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-[#f9e3e9] text-[#ff2458]">
          <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true">
            <path d="M20.8 8.2a4.9 4.9 0 0 0-8.8-3.1 4.9 4.9 0 0 0-8.8 3.1c0 5 8.8 10.8 8.8 10.8s8.8-5.8 8.8-10.8z" />
          </svg>
        </div>
        <span class="rounded-full bg-[#dff6e4] px-3 py-1 text-[12px] leading-none text-[#08aa48]">Normal</span>
      </div>
      <p class="mt-4 text-[16px] leading-none text-slate-700">Rythme cardiaque</p>
      <p class="mt-3 text-[40px] font-semibold leading-none text-slate-900">{{ latestHeartRate }} <span class="text-[34px] font-medium text-slate-700">bpm</span></p>
    </article>

    <article class="min-h-[162px] rounded-2xl border border-[#a8cdfb] bg-[#ebf6fe] px-6 py-6">
      <div class="flex items-start justify-between">
        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-[#d5e7fd] text-[#2c67f6]">
          <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true">
            <path d="M3 12h4l2-6 4 12 2-6h6" />
          </svg>
        </div>
        <span class="rounded-full bg-[#dff6e4] px-3 py-1 text-[12px] leading-none text-[#08aa48]">Normal</span>
      </div>
      <p class="mt-4 text-[16px] leading-none text-slate-700">Tension artérielle</p>
      <p class="mt-3 text-[40px] font-semibold leading-none text-slate-900">{{ latestPressure }} <span class="text-[34px] font-medium text-slate-700">mmHg</span></p>
    </article>

    <article class="min-h-[162px] rounded-2xl border border-[#dbc6f7] bg-[#f6f0fc] px-6 py-6">
      <div class="flex items-start justify-between">
        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-[#eee2fc] text-[#8a2cff]">
          <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true">
            <path d="M12 3s6 6.4 6 10a6 6 0 0 1-12 0c0-3.6 6-10 6-10z" />
          </svg>
        </div>
        <span class="rounded-full bg-[#dff6e4] px-3 py-1 text-[12px] leading-none text-[#08aa48]">Normal</span>
      </div>
      <p class="mt-4 text-[16px] leading-none text-slate-700">Saturation O₂</p>
      <p class="mt-3 text-[40px] font-semibold leading-none text-slate-900">{{ latestOxygen }} <span class="text-[34px] font-medium text-slate-700">%</span></p>
    </article>
  </section>

  <section class="mt-8 rounded-2xl border border-slate-200 bg-[#f8f9fb] px-8 py-8">
    <h2 class="text-[20px] font-semibold leading-none text-slate-900">Historique des mesures</h2>

    <div class="mt-8 grid gap-4 lg:grid-cols-2">
      <div>
        <label class="mb-3 block text-[14px] font-semibold text-slate-800">Filtrer par date</label>
        <div class="relative">
          <input
            v-model="vitalFilterDate"
            type="date"
            class="h-12 w-full rounded-2xl border border-slate-300 bg-white pl-5 pr-12 text-[16px] text-slate-900 outline-none focus:border-blue-500"
          />
          <svg viewBox="0 0 24 24" class="pointer-events-none absolute right-4 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-500" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M8 2v3M16 2v3M3 9h18M5 5h14a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2z" />
          </svg>
        </div>
      </div>

      <div>
        <label class="mb-3 block text-[14px] font-semibold text-slate-800">Filtrer par type</label>
        <select
          v-model="vitalFilterType"
          class="h-12 w-full rounded-2xl border border-slate-300 bg-white px-5 text-[16px] text-slate-900 outline-none focus:border-blue-500"
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
        v-for="day in filteredVitalHistory"
        :key="day.dateKey"
        class="rounded-2xl border border-slate-200 bg-white px-5 py-5"
      >
        <div class="mb-4 flex items-center gap-3 text-slate-900">
          <svg viewBox="0 0 24 24" class="h-6 w-6 text-slate-500" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M8 2v3M16 2v3M3 9h18M5 5h14a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2z" />
          </svg>
          <h3 class="text-[22px] font-semibold leading-none">{{ day.longDate }}</h3>
        </div>

        <div class="grid gap-3 xl:grid-cols-3">
          <article v-if="vitalFilterType === 'all' || vitalFilterType === 'heart'" class="rounded-xl border border-[#efc4cc] bg-[#fdf2f5] px-4 py-3">
            <div class="flex items-center gap-3">
              <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#f9e3e9] text-[#ff2458]">
                <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.9">
                  <path d="M20.8 8.2a4.9 4.9 0 0 0-8.8-3.1 4.9 4.9 0 0 0-8.8 3.1c0 5 8.8 10.8 8.8 10.8s8.8-5.8 8.8-10.8z" />
                </svg>
              </div>
              <div>
                <p class="text-[13px] leading-none text-slate-700">Rythme cardiaque</p>
                <p class="mt-2 text-[20px] font-semibold leading-none text-slate-900">{{ day.heartRate }} <span class="text-[18px] font-medium text-slate-700">bpm</span></p>
                <span class="mt-2 inline-block rounded-full bg-[#dff6e4] px-2.5 py-0.5 text-[12px] leading-none text-[#08aa48]">Normal</span>
              </div>
            </div>
          </article>

          <article v-if="vitalFilterType === 'all' || vitalFilterType === 'pressure'" class="rounded-xl border border-[#a8cdfb] bg-[#ebf6fe] px-4 py-3">
            <div class="flex items-center gap-3">
              <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#d5e7fd] text-[#2c67f6]">
                <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.9">
                  <path d="M3 12h4l2-6 4 12 2-6h6" />
                </svg>
              </div>
              <div>
                <p class="text-[13px] leading-none text-slate-700">Tension artérielle</p>
                <p class="mt-2 text-[20px] font-semibold leading-none text-slate-900">{{ day.pressure }} <span class="text-[18px] font-medium text-slate-700">mmHg</span></p>
                <span class="mt-2 inline-block rounded-full bg-[#dff6e4] px-2.5 py-0.5 text-[12px] leading-none text-[#08aa48]">Normal</span>
              </div>
            </div>
          </article>

          <article v-if="vitalFilterType === 'all' || vitalFilterType === 'oxygen'" class="rounded-xl border border-[#dbc6f7] bg-[#f6f0fc] px-4 py-3">
            <div class="flex items-center gap-3">
              <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#eee2fc] text-[#8a2cff]">
                <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.9">
                  <path d="M12 3s6 6.4 6 10a6 6 0 0 1-12 0c0-3.6 6-10 6-10z" />
                </svg>
              </div>
              <div>
                <p class="text-[13px] leading-none text-slate-700">Saturation O₂</p>
                <p class="mt-2 text-[20px] font-semibold leading-none text-slate-900">{{ day.oxygen }} <span class="text-[18px] font-medium text-slate-700">%</span></p>
                <span class="mt-2 inline-block rounded-full bg-[#dff6e4] px-2.5 py-0.5 text-[12px] leading-none text-[#08aa48]">Normal</span>
              </div>
            </div>
          </article>
        </div>
      </article>

      <div v-if="!filteredVitalHistory.length" class="rounded-2xl border border-slate-200 bg-white px-6 py-5 text-[14px] text-slate-600">
        Aucune mesure ne correspond aux filtres selectionnes.
      </div>
    </div>
  </section>

  <div v-if="showVitalsModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/45 p-4">
    <div class="w-full max-w-[470px] rounded-3xl bg-white p-7 shadow-2xl">
      <div class="mb-4 flex items-center justify-between">
        <div>
          <h3 class="text-[24px] font-semibold leading-none text-slate-900">{{ vitalModalTitle }}</h3>
          <p v-if="isEditingLatestVital" class="mt-2 text-[13px] text-slate-500">Seule la derniere entree peut etre modifiee.</p>
        </div>
        <button type="button" class="text-slate-500 hover:text-slate-700" @click="showVitalsModal = false">
          <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 6 12 12M18 6 6 18" /></svg>
        </button>
      </div>

      <div class="space-y-4">
        <div>
          <label class="mb-2 block text-[18px] font-semibold text-slate-700">Rythme cardiaque (bpm)</label>
          <input
            v-model="vitalForm.heartRate"
            type="number"
            min="20"
            max="260"
            placeholder="72"
            :disabled="vitalForm.skipHeartRate"
            class="h-11 w-full rounded-2xl border border-slate-300 bg-slate-50 px-4 text-[14px] outline-none disabled:opacity-60"
          />
          <label class="mt-2 inline-flex items-center gap-2 text-[14px] text-slate-600">
            <input v-model="vitalForm.skipHeartRate" type="checkbox" class="h-4 w-4 rounded border-slate-400" />
            Je n'ai pas mesuré aujourd'hui
          </label>
        </div>

        <div>
          <label class="mb-2 block text-[18px] font-semibold text-slate-700">Tension artérielle</label>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <input
                v-model="vitalForm.systolic"
                type="number"
                min="50"
                max="300"
                placeholder="120"
                :disabled="vitalForm.skipPressure"
                class="h-11 w-full rounded-2xl border border-slate-300 bg-slate-50 px-4 text-[14px] outline-none disabled:opacity-60"
              />
              <p class="mt-1 text-[13px] text-slate-500">Systolique</p>
            </div>
            <div>
              <input
                v-model="vitalForm.diastolic"
                type="number"
                min="30"
                max="220"
                placeholder="80"
                :disabled="vitalForm.skipPressure"
                class="h-11 w-full rounded-2xl border border-slate-300 bg-slate-50 px-4 text-[14px] outline-none disabled:opacity-60"
              />
              <p class="mt-1 text-right text-[13px] text-slate-500">Diastolique</p>
            </div>
          </div>
          <label class="mt-2 inline-flex items-center gap-2 text-[14px] text-slate-600">
            <input v-model="vitalForm.skipPressure" type="checkbox" class="h-4 w-4 rounded border-slate-400" />
            Je n'ai pas mesuré aujourd'hui
          </label>
        </div>

        <div>
          <label class="mb-2 block text-[18px] font-semibold text-slate-700">Saturation O₂ (%)</label>
          <input
            v-model="vitalForm.oxygen"
            type="number"
            min="0"
            max="100"
            step="0.1"
            placeholder="98"
            :disabled="vitalForm.skipOxygen"
            class="h-11 w-full rounded-2xl border border-slate-300 bg-slate-50 px-4 text-[14px] outline-none disabled:opacity-60"
          />
          <label class="mt-2 inline-flex items-center gap-2 text-[14px] text-slate-600">
            <input v-model="vitalForm.skipOxygen" type="checkbox" class="h-4 w-4 rounded border-slate-400" />
            Je n'ai pas mesuré aujourd'hui
          </label>
        </div>

        <div>
          <label class="mb-2 block text-[18px] font-semibold text-slate-700">Date</label>
          <input
            v-model="vitalForm.date"
            type="date"
            :disabled="isEditingLatestVital"
            class="h-11 w-full rounded-2xl border border-slate-300 bg-slate-50 px-4 text-[14px] outline-none disabled:cursor-not-allowed disabled:opacity-70"
          />
          <p v-if="isEditingLatestVital" class="mt-2 text-[13px] text-slate-500">La date reste verrouillee pour mettre a jour uniquement la derniere mesure.</p>
        </div>

        <p v-if="vitalError" class="text-sm font-medium text-rose-600">
          {{ vitalError }}
        </p>

        <button type="button" class="mt-2 h-11 w-full rounded-2xl bg-emerald-600 text-[16px] font-semibold text-white hover:bg-emerald-700" @click="enregistrerMesure">
          {{ isEditingLatestVital ? "Enregistrer les modifications" : "Enregistrer" }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, reactive, ref } from "vue";
import api from "@/services/api";
import { useNotificationsStore } from "@/stores/notifications";

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

const showVitalsModal = ref(false);
const vitalError = ref("");
const vitalFilterDate = ref("");
const vitalFilterType = ref("all");
const isEditingLatestVital = ref(false);

const vitalForm = reactive({
  heartRate: "",
  systolic: "",
  diastolic: "",
  oxygen: "",
  skipHeartRate: false,
  skipPressure: false,
  skipOxygen: false,
  date: new Date().toISOString().slice(0, 10),
});

// Constantes graphique (conservees pour usage futur du graphique SVG).
const chart = { width: 980, height: 350, left: 70, right: 40, top: 40, bottom: 30, minY: 0, maxY: 140 };
const yTicks = [0, 35, 70, 105, 140];
const selectedSeries = reactive({ rhythm: true, tension: true, saturation: true });
const hoveredIndex = ref(null);
const chartRef = ref(null);

// Computed derives des props.
const latestHeartRate = computed(() => props.latestVital?.heart_rate ?? "--");
const latestPressure = computed(() => {
  const s = props.latestVital?.systolic_pressure;
  const d = props.latestVital?.diastolic_pressure;
  return s && d ? `${s}/${d}` : "--/--";
});
const latestOxygen = computed(() => props.latestVital?.oxygen_saturation ?? "--");
const latestVitalMeasuredDate = computed(() => convertirDateIso(props.latestVital?.measured_at));
const latestVitalMeasuredAtLabel = computed(() => (props.latestVital?.measured_at ? formaterDate(latestVitalMeasuredDate.value) : ""));
const canEditLatestVital = computed(() =>
  Boolean(
    props.latestVital?.measured_at &&
    [props.latestVital?.heart_rate, props.latestVital?.systolic_pressure, props.latestVital?.diastolic_pressure, props.latestVital?.oxygen_saturation].some(estValeurMesuree)
  )
);
const vitalModalTitle = computed(() => (isEditingLatestVital.value ? "Modifier la derniere mesure" : "Ajouter une mesure"));

const filteredVitalHistory = computed(() => {
  const rows = props.vitalDateKeys
    .map((dateKey, index) => {
      const heartRate = props.historyHeartRate[index] ?? null;
      const systolic = props.historySystolic[index] ?? null;
      const diastolic = props.historyDiastolic[index] ?? null;
      const oxygen = props.historySaturation[index] ?? null;
      const hasAny = [heartRate, systolic, diastolic, oxygen].some(estValeurMesuree);

      return {
        dateKey,
        longDate: formaterDateLongue(dateKey),
        hasAny,
        heartRate: heartRate ?? "--",
        pressure: estValeurMesuree(systolic) && estValeurMesuree(diastolic)
          ? `${Number(systolic)}/${Number(diastolic)}`
          : "--/--",
        oxygen: oxygen ?? "--",
      };
    })
    .filter((row) => row.hasAny)
    .reverse();

  if (!vitalFilterDate.value) return rows;
  return rows.filter((row) => row.dateKey === vitalFilterDate.value);
});

const plottedSeries = computed(() => [
  { key: "heart", color: "#ef4444", values: props.chartHeartRate, points: construirePoints(props.chartHeartRate) },
  { key: "sys", color: "#3b82f6", values: props.chartSystolic, points: construirePoints(props.chartSystolic) },
  { key: "sat", color: "#8b5cf6", values: props.chartSaturation, points: construirePoints(props.chartSaturation) },
]);
const visibleSeries = computed(() =>
  plottedSeries.value.filter((series) => {
    if (series.key === "heart") return selectedSeries.rhythm;
    if (series.key === "sys") return selectedSeries.tension;
    if (series.key === "sat") return selectedSeries.saturation;
    return false;
  }),
);

const hoverIndex = computed(() => hoveredIndex.value);
const tooltipTop = 84;
const tooltipLeft = computed(() => {
  if (hoverIndex.value === null) return 0;
  const x = convertirXEnPx(hoverIndex.value);
  return Math.min(Math.max(x + 10, chart.left + 8), chart.width - 360);
});

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

// Cette fonction verifie qu'une mesure est reellement presente (et pas null/vide).
function estValeurMesuree(value) {
  if (value === null || value === undefined || value === "") return false;
  return Number.isFinite(Number(value));
}

// Cette fonction formate une date courte pour l'axe du graphique.
function formaterLibelle(dateIso) {
  if (!dateIso) return "";
  const date = new Date(`${dateIso}T00:00:00`);
  return date.toLocaleDateString("fr-FR", { day: "2-digit", month: "short" });
}

// Cette fonction formate une date en affichage francais.
function formaterDate(dateIso) {
  if (!dateIso) return "";
  const date = new Date(`${dateIso}T00:00:00`);
  return date.toLocaleDateString("fr-FR");
}

// Cette fonction formate une date longue avec jour de semaine (ex: jeudi 26 février 2026).
function formaterDateLongue(dateIso) {
  if (!dateIso) return "";
  const date = new Date(`${dateIso}T00:00:00`);
  return date.toLocaleDateString("fr-FR", {
    weekday: "long",
    day: "numeric",
    month: "long",
    year: "numeric",
  });
}

// Cette fonction remplace les valeurs invalides par la derniere valeur valide.
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

// Cette fonction initialise le formulaire des signes vitaux avec des valeurs visibles.
function reinitialiserFormulaireVital() {
  vitalError.value = "";
  isEditingLatestVital.value = false;
  vitalForm.heartRate = String(props.latestVital?.heart_rate ?? 72);
  vitalForm.systolic = String(props.latestVital?.systolic_pressure ?? 120);
  vitalForm.diastolic = String(props.latestVital?.diastolic_pressure ?? 80);
  vitalForm.oxygen = String(props.latestVital?.oxygen_saturation ?? 98);
  vitalForm.skipHeartRate = false;
  vitalForm.skipPressure = false;
  vitalForm.skipOxygen = false;
  vitalForm.date = new Date().toISOString().slice(0, 10);
}

// Cette fonction convertit un index de point en position X du graphique.
function convertirXEnPx(index) {
  if (props.chartLabels.length <= 1) return chart.left;
  const usable = chart.width - chart.left - chart.right;
  const step = usable / (props.chartLabels.length - 1);
  return chart.left + index * step;
}

// Cette fonction convertit une valeur en position Y du graphique.
function convertirYEnPx(value) {
  const n = Number(value);
  if (!Number.isFinite(n)) return chart.height - chart.bottom;
  const usable = chart.height - chart.top - chart.bottom;
  const ratio = (n - chart.minY) / (chart.maxY - chart.minY);
  return chart.height - chart.bottom - ratio * usable;
}

// Cette fonction construit la chaine de points SVG pour une courbe.
function construirePoints(values) {
  return values.map((v, i) => `${convertirXEnPx(i)},${convertirYEnPx(v)}`).join(" ");
}

// Cette fonction met a jour le point survole dans le graphique.
function gererMouvementGraphique(event) {
  if (!chartRef.value || props.chartLabels.length === 0) return;
  const rect = chartRef.value.getBoundingClientRect();
  const localX = ((event.clientX - rect.left) / rect.width) * chart.width;
  const usable = chart.width - chart.left - chart.right;
  const step = props.chartLabels.length > 1 ? usable / (props.chartLabels.length - 1) : usable;
  const nearest = Math.round((localX - chart.left) / step);
  hoveredIndex.value = Math.min(Math.max(nearest, 0), props.chartLabels.length - 1);
}

// Cette fonction retire le survol quand la souris sort du graphique.
function gererSortieGraphique() {
  hoveredIndex.value = null;
}

// Cette fonction active ou desactive une serie du graphique.
function basculerSerie(key) {
  const activeCount = [selectedSeries.rhythm, selectedSeries.tension, selectedSeries.saturation].filter(Boolean).length;
  if (selectedSeries[key] && activeCount === 1) return;
  selectedSeries[key] = !selectedSeries[key];
}

// Cette fonction enregistre une mesure vitale puis emet un evenement de rechargement.
async function enregistrerMesure() {
  vitalError.value = "";
  const measuredAt = convertirDateIso(vitalForm.date);
  const heartRate = vitalForm.skipHeartRate ? null : convertirNombreOuNull(vitalForm.heartRate);
  const systolic = vitalForm.skipPressure ? null : convertirNombreOuNull(vitalForm.systolic);
  const diastolic = vitalForm.skipPressure ? null : convertirNombreOuNull(vitalForm.diastolic);
  const oxygen = vitalForm.skipOxygen ? null : convertirNombreOuNull(vitalForm.oxygen);

  if (heartRate === null && systolic === null && diastolic === null && oxygen === null) {
    vitalError.value = "Veuillez saisir au moins une mesure ou cocher les options de non-mesure.";
    return;
  }

  if ((systolic === null) !== (diastolic === null)) {
    vitalError.value = "Veuillez remplir les deux champs de tension (systolique et diastolique).";
    return;
  }

  try {
    await api.post("/health-data/vitals", {
      measured_at: measuredAt,
      heart_rate: heartRate,
      systolic_pressure: systolic,
      diastolic_pressure: diastolic,
      oxygen_saturation: oxygen,
    });
    notifications.actionAdded();
    reinitialiserFormulaireVital();
    showVitalsModal.value = false;
    emit("refresh");
  } catch (error) {
    const message = error?.response?.data?.message || "Erreur lors de l'enregistrement.";
    notifications.error(message);
  }
}

// Cette methode est exposee pour que le parent puisse ouvrir la modale d'ajout.
function openAddModal() {
  reinitialiserFormulaireVital();
  showVitalsModal.value = true;
}

function openEditLatestModal() {
  if (!canEditLatestVital.value) {
    notifications.warning("Aucune derniere mesure disponible a modifier.");
    return;
  }

  vitalError.value = "";
  isEditingLatestVital.value = true;
  vitalForm.heartRate = String(props.latestVital?.heart_rate ?? "");
  vitalForm.systolic = String(props.latestVital?.systolic_pressure ?? "");
  vitalForm.diastolic = String(props.latestVital?.diastolic_pressure ?? "");
  vitalForm.oxygen = String(props.latestVital?.oxygen_saturation ?? "");
  vitalForm.skipHeartRate = !estValeurMesuree(props.latestVital?.heart_rate);
  vitalForm.skipPressure = !estValeurMesuree(props.latestVital?.systolic_pressure) && !estValeurMesuree(props.latestVital?.diastolic_pressure);
  vitalForm.skipOxygen = !estValeurMesuree(props.latestVital?.oxygen_saturation);
  vitalForm.date = latestVitalMeasuredDate.value;
  showVitalsModal.value = true;
}

defineExpose({ openAddModal, openEditLatestModal });
</script>
