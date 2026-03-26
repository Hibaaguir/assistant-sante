<template>
  <!-- En-tête -->
  <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
      <h2 class="text-[20px] font-semibold leading-none text-slate-900">Derniers signes vitaux</h2>
      <p class="mt-2 text-[14px] text-slate-500">
        {{ latestVitalMeasuredAtLabel ? `Dernière entrée du ${latestVitalMeasuredAtLabel}` : "Aucune mesure enregistrée pour le moment." }}
      </p>
    </div>
  </div>

  <!-- Cartes des dernières valeurs -->
  <section class="mt-6 grid gap-5 xl:grid-cols-3">
    <VitalCard
      v-for="card in vitalCards"
      :key="card.key"
      v-bind="card"
      :can-edit="peutModifierDerniereMesure"
      :is-editing="mesureEnEdition === card.key"
      @edit="activerEditionMesure(card.key)"
      @blur="fermerEditionMesure"
    >
      <template #value>
        <component :is="card.inputComponent" v-bind="card.inputProps" />
      </template>
    </VitalCard>
  </section>

  <!-- Actions d'édition rapide -->
  <div v-if="peutModifierDerniereMesure" class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <p class="text-[13px] text-slate-500">Cliquez sur une valeur pour la modifier directement dans la carte.</p>
    <div class="flex items-center gap-2">
      <button
        v-if="editionModifiee"
        type="button"
        class="inline-flex h-11 items-center justify-center rounded-2xl border border-slate-300 bg-white px-4 text-[14px] font-semibold text-slate-700 shadow-sm transition hover:border-slate-400"
        @click="reinitialiserEdition"
      >Annuler</button>
      <button
        type="button"
        class="inline-flex h-11 items-center justify-center rounded-2xl bg-gradient-to-r from-blue-600 to-indigo-600 px-4 text-[14px] font-semibold text-white shadow-[0_8px_16px_rgba(37,99,235,0.22)] disabled:cursor-not-allowed disabled:opacity-50"
        :disabled="!editionModifiee || enregistrementEnCours"
        @click="enregistrerDepuisCartes"
      >{{ enregistrementEnCours ? "Enregistrement..." : "Enregistrer la dernière entrée" }}</button>
    </div>
  </div>

  <!-- Historique -->
  <section class="mt-8 rounded-2xl border border-slate-200 bg-[#f8f9fb] px-8 py-8">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <h2 class="text-[20px] font-semibold leading-none text-slate-900">Historique des mesures</h2>
      <button
        v-if="filtresActifs"
        type="button"
        class="inline-flex h-11 items-center justify-center rounded-2xl border border-slate-300 bg-white px-4 text-[14px] font-semibold text-slate-700 shadow-sm transition hover:border-blue-300 hover:text-blue-700"
        @click="reinitialiserFiltres"
      >Réinitialiser</button>
    </div>

    <div class="mt-8 grid gap-4 lg:grid-cols-2">
      <div>
        <label class="mb-3 block text-[14px] font-semibold text-slate-800">Filtrer par date</label>
        <div class="relative">
          <input v-model="filterDate" type="date" class="h-12 w-full rounded-2xl border border-slate-300 bg-white pl-5 pr-12 text-[16px] text-slate-900 outline-none focus:border-blue-500" />
          <CalendarIcon class="pointer-events-none absolute right-4 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-500" />
        </div>
      </div>
      <div>
        <label class="mb-3 block text-[14px] font-semibold text-slate-800">Filtrer par type</label>
        <select v-model="filterType" class="h-12 w-full rounded-2xl border border-slate-300 bg-white px-5 text-[16px] text-slate-900 outline-none focus:border-blue-500">
          <option value="all">Tous les signes</option>
          <option value="heart">Rythme cardiaque</option>
          <option value="pressure">Tension artérielle</option>
          <option value="oxygen">Saturation O₂</option>
        </select>
      </div>
    </div>

    <div class="mt-6 space-y-3.5">
      <article v-for="day in historiqueFiltre" :key="day.dateKey" class="rounded-2xl border border-slate-200 bg-white px-5 py-5">
        <div class="mb-4 flex items-center gap-3 text-slate-900">
          <CalendarIcon class="h-6 w-6 text-slate-500" />
          <h3 class="text-[22px] font-semibold leading-none">{{ day.longDate }}</h3>
        </div>
        <div class="grid gap-3 xl:grid-cols-3">
          <HistoryCard v-if="showType('heart')"    v-bind="VITAL_META.heart"    :value="day.heartRate"    unit="bpm" />
          <HistoryCard v-if="showType('pressure')" v-bind="VITAL_META.pressure" :value="day.pressure"     unit="mmHg" />
          <HistoryCard v-if="showType('oxygen')"   v-bind="VITAL_META.oxygen"   :value="day.oxygen"       unit="%" />
        </div>
      </article>

      <div v-if="!historiqueFiltre.length" class="rounded-2xl border border-slate-200 bg-white px-6 py-5 text-[14px] text-slate-600">
        Aucune mesure ne correspond aux filtres sélectionnés.
      </div>
    </div>
  </section>

  <!-- Modale ajout / modification -->
  <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/45 p-4">
    <div class="w-full max-w-[470px] rounded-3xl bg-white p-7 shadow-2xl">
      <div class="mb-4 flex items-center justify-between">
        <div>
          <h3 class="text-[24px] font-semibold leading-none text-slate-900">{{ modalTitle }}</h3>
          <p v-if="isEditingLatest" class="mt-2 text-[13px] text-slate-500">Seule la dernière entrée peut être modifiée.</p>
        </div>
        <button type="button" class="text-slate-500 hover:text-slate-700" @click="showModal = false">
          <CloseIcon />
        </button>
      </div>

      <div class="space-y-4">
        <!-- Rythme cardiaque -->
        <ModalField label="Rythme cardiaque (bpm)" :disabled="form.skipHeartRate" :skip-label="'Je n\'ai pas mesuré aujourd\'hui'" v-model:skip="form.skipHeartRate">
          <input v-model="form.heartRate" type="number" min="20" max="260" placeholder="72" :disabled="form.skipHeartRate" class="h-11 w-full rounded-2xl border border-slate-300 bg-slate-50 px-4 text-[14px] outline-none disabled:opacity-60" />
        </ModalField>

        <!-- Tension -->
        <ModalField label="Tension artérielle" :disabled="form.skipPressure" :skip-label="'Je n\'ai pas mesuré aujourd\'hui'" v-model:skip="form.skipPressure">
          <div class="grid grid-cols-2 gap-3">
            <div>
              <input v-model="form.systolic" type="number" min="50" max="300" placeholder="120" :disabled="form.skipPressure" class="h-11 w-full rounded-2xl border border-slate-300 bg-slate-50 px-4 text-[14px] outline-none disabled:opacity-60" />
              <p class="mt-1 text-[13px] text-slate-500">Systolique</p>
            </div>
            <div>
              <input v-model="form.diastolic" type="number" min="30" max="220" placeholder="80" :disabled="form.skipPressure" class="h-11 w-full rounded-2xl border border-slate-300 bg-slate-50 px-4 text-[14px] outline-none disabled:opacity-60" />
              <p class="mt-1 text-right text-[13px] text-slate-500">Diastolique</p>
            </div>
          </div>
        </ModalField>

        <!-- Saturation -->
        <ModalField label="Saturation O₂ (%)" :disabled="form.skipOxygen" :skip-label="'Je n\'ai pas mesuré aujourd\'hui'" v-model:skip="form.skipOxygen">
          <input v-model="form.oxygen" type="number" min="0" max="100" step="0.1" placeholder="98" :disabled="form.skipOxygen" class="h-11 w-full rounded-2xl border border-slate-300 bg-slate-50 px-4 text-[14px] outline-none disabled:opacity-60" />
        </ModalField>

        <!-- Date -->
        <div>
          <label class="mb-2 block text-[18px] font-semibold text-slate-700">Date</label>
          <input v-model="form.date" type="date" :disabled="isEditingLatest" class="h-11 w-full rounded-2xl border border-slate-300 bg-slate-50 px-4 text-[14px] outline-none disabled:cursor-not-allowed disabled:opacity-70" />
          <p v-if="isEditingLatest" class="mt-2 text-[13px] text-slate-500">La date reste verrouillée pour mettre à jour uniquement la dernière mesure.</p>
        </div>

        <p v-if="formError" class="text-sm font-medium text-rose-600">{{ formError }}</p>

        <button type="button" class="mt-2 h-11 w-full rounded-2xl bg-emerald-600 text-[16px] font-semibold text-white hover:bg-emerald-700" @click="enregistrerMesure">
          {{ isEditingLatest ? "Enregistrer les modifications" : "Enregistrer" }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, defineComponent, h, reactive, ref, watch } from "vue";
import api from "@/services/api";
import { useNotificationsStore } from "@/stores/notifications";

// ─── Mini-composants locaux ───────────────────────────────────────────────────

const CalendarIcon = defineComponent({
  template: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 2v3M16 2v3M3 9h18M5 5h14a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2z"/></svg>`,
});

const CloseIcon = defineComponent({
  template: `<svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 6 12 12M18 6 6 18"/></svg>`,
});

// Carte principale (carré couleur en haut)
const VitalCard = defineComponent({
  props: ["label", "accent", "bg", "border", "iconBg", "icon", "displayValue", "unit", "canEdit", "isEditing"],
  emits: ["edit", "blur"],
  setup(props, { emit, slots }) {
    return () =>
      h("article", { class: `min-h-[162px] rounded-2xl border ${props.border} ${props.bg} px-6 py-6` }, [
        h("div", { class: "flex items-start justify-between" }, [
          h("div", { class: `flex h-12 w-12 items-center justify-center rounded-xl ${props.iconBg}`, innerHTML: props.icon }),
          h("span", { class: "rounded-full bg-[#dff6e4] px-3 py-1 text-[12px] leading-none text-[#08aa48]" }, "Normal"),
        ]),
        h("p", { class: "mt-4 text-[16px] leading-none text-slate-700" }, props.label),
        h("div", { class: "mt-3 flex items-baseline gap-2" }, slots.default?.()),
      ]);
  },
});

// Carte historique (petite, dans la liste)
const HistoryCard = defineComponent({
  props: ["label", "accent", "bg", "border", "iconBg", "icon", "value", "unit"],
  setup(props) {
    return () =>
      h("article", { class: `rounded-xl border ${props.border} ${props.bg} px-4 py-3` }, [
        h("div", { class: "flex items-center gap-3" }, [
          h("div", { class: `flex h-10 w-10 items-center justify-center rounded-xl ${props.iconBg}`, innerHTML: props.icon }),
          h("div", {}, [
            h("p", { class: "text-[13px] leading-none text-slate-700" }, props.label),
            h("p", { class: "mt-2 text-[20px] font-semibold leading-none text-slate-900" }, [
              props.value,
              h("span", { class: "text-[18px] font-medium text-slate-700" }, ` ${props.unit}`),
            ]),
            h("span", { class: "mt-2 inline-block rounded-full bg-[#dff6e4] px-2.5 py-0.5 text-[12px] leading-none text-[#08aa48]" }, "Normal"),
          ]),
        ]),
      ]);
  },
});

// Champ de la modale avec checkbox "pas mesuré"
const ModalField = defineComponent({
  props: ["label", "skip", "skipLabel"],
  emits: ["update:skip"],
  setup(props, { emit, slots }) {
    return () =>
      h("div", {}, [
        h("label", { class: "mb-2 block text-[18px] font-semibold text-slate-700" }, props.label),
        slots.default?.(),
        h("label", { class: "mt-2 inline-flex items-center gap-2 text-[14px] text-slate-600" }, [
          h("input", { type: "checkbox", class: "h-4 w-4 rounded border-slate-400", checked: props.skip, onChange: (e) => emit("update:skip", e.target.checked) }),
          props.skipLabel,
        ]),
      ]);
  },
});

// ─── Métadonnées des signes vitaux (couleurs, icônes, labels) ─────────────────
const VITAL_META = {
  heart: {
    key: "heart", label: "Rythme cardiaque", unit: "bpm",
    bg: "bg-[#fdf2f5]", border: "border-[#efc4cc]",
    iconBg: "bg-[#f9e3e9] text-[#ff2458]", accent: "#ff2458",
    icon: `<svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M20.8 8.2a4.9 4.9 0 0 0-8.8-3.1 4.9 4.9 0 0 0-8.8 3.1c0 5 8.8 10.8 8.8 10.8s8.8-5.8 8.8-10.8z"/></svg>`,
  },
  pressure: {
    key: "pressure", label: "Tension artérielle", unit: "mmHg",
    bg: "bg-[#ebf6fe]", border: "border-[#a8cdfb]",
    iconBg: "bg-[#d5e7fd] text-[#2c67f6]", accent: "#2c67f6",
    icon: `<svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M3 12h4l2-6 4 12 2-6h6"/></svg>`,
  },
  oxygen: {
    key: "oxygen", label: "Saturation O₂", unit: "%",
    bg: "bg-[#f6f0fc]", border: "border-[#dbc6f7]",
    iconBg: "bg-[#eee2fc] text-[#8a2cff]", accent: "#8a2cff",
    icon: `<svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M12 3s6 6.4 6 10a6 6 0 0 1-12 0c0-3.6 6-10 6-10z"/></svg>`,
  },
};

// ─── Props / Emits ────────────────────────────────────────────────────────────
const props = defineProps({
  latestVital:      { type: Object, default: null },
  chartLabels:      { type: Array, default: () => [] },
  chartHeartRate:   { type: Array, default: () => [] },
  chartSystolic:    { type: Array, default: () => [] },
  chartDiastolic:   { type: Array, default: () => [] },
  chartSaturation:  { type: Array, default: () => [] },
  historyHeartRate: { type: Array, default: () => [] },
  historySystolic:  { type: Array, default: () => [] },
  historyDiastolic: { type: Array, default: () => [] },
  historySaturation:{ type: Array, default: () => [] },
  vitalDateKeys:    { type: Array, default: () => [] },
});
const emit = defineEmits(["refresh"]);
const notifications = useNotificationsStore();

// ─── État ─────────────────────────────────────────────────────────────────────
const showModal           = ref(false);
const isEditingLatest     = ref(false);
const enregistrementEnCours = ref(false);
const mesureEnEdition     = ref("");
const formError           = ref("");
const filterDate          = ref("");
const filterType          = ref("all");

const draft = reactive({ heartRate: "", systolic: "", diastolic: "", oxygen: "" });
const form  = reactive({ heartRate: "", systolic: "", diastolic: "", oxygen: "",
                         skipHeartRate: false, skipPressure: false, skipOxygen: false,
                         date: today() });

// ─── Computed ─────────────────────────────────────────────────────────────────
const latestHeartRate = computed(() => props.latestVital?.heart_rate ?? "--");
const latestOxygen    = computed(() => props.latestVital?.oxygen_saturation ?? "--");
const latestPressure  = computed(() => {
  const { systolic_pressure: s, diastolic_pressure: d } = props.latestVital ?? {};
  return s && d ? `${s}/${d}` : "--/--";
});
const latestVitalDate         = computed(() => isoDate(props.latestVital?.measured_at));
const latestVitalMeasuredAtLabel = computed(() => props.latestVital?.measured_at ? formatDate(latestVitalDate.value) : "");
const peutModifierDerniereMesure = computed(() => Boolean(props.latestVital?.measured_at));
const modalTitle = computed(() => isEditingLatest.value ? "Modifier la dernière mesure" : "Ajouter une mesure");

const editionModifiee = computed(() => {
  if (!peutModifierDerniereMesure.value) return false;
  const v = props.latestVital;
  return (
    String(draft.heartRate)  !== String(v?.heart_rate ?? "") ||
    String(draft.systolic)   !== String(v?.systolic_pressure ?? "") ||
    String(draft.diastolic)  !== String(v?.diastolic_pressure ?? "") ||
    String(draft.oxygen)     !== String(v?.oxygen_saturation ?? "")
  );
});

const filtresActifs = computed(() => Boolean(filterDate.value) || filterType.value !== "all");

const historiqueFiltre = computed(() => {
  const rows = props.vitalDateKeys
    .map((dateKey, i) => {
      const hr = props.historyHeartRate[i] ?? null;
      const sys = props.historySystolic[i] ?? null;
      const dia = props.historyDiastolic[i] ?? null;
      const ox  = props.historySaturation[i] ?? null;
      if (![hr, sys, dia, ox].some(isValidMeasure)) return null;
      return {
        dateKey,
        longDate: formatLongDate(dateKey),
        heartRate: hr ?? "--",
        pressure: isValidMeasure(sys) && isValidMeasure(dia) ? `${+sys}/${+dia}` : "--/--",
        oxygen: ox ?? "--",
      };
    })
    .filter(Boolean)
    .reverse();

  return filterDate.value ? rows.filter((r) => r.dateKey === filterDate.value) : rows;
});

// Cartes de la section principale — les valeurs éditables sont gérées dans le template via `draft`
const vitalCards = computed(() => [
  { ...VITAL_META.heart,    displayValue: draft.heartRate || latestHeartRate.value },
  { ...VITAL_META.pressure, displayValue: `${draft.systolic || props.latestVital?.systolic_pressure || "--"}/${draft.diastolic || props.latestVital?.diastolic_pressure || "--"}` },
  { ...VITAL_META.oxygen,   displayValue: draft.oxygen    || latestOxygen.value },
]);

// ─── Helpers ──────────────────────────────────────────────────────────────────
function today()  { return new Date().toISOString().slice(0, 10); }
function isoDate(v) { return v ? String(v).slice(0, 10) : today(); }
function toNumber(v) {
  if (v === null || v === undefined || v === "") return null;
  const n = Number(String(v).trim().replace(",", "."));
  return Number.isFinite(n) ? n : null;
}
function isValidMeasure(v) { return v !== null && v !== undefined && v !== "" && Number.isFinite(Number(v)); }
function formatDate(iso) {
  return iso ? new Date(`${iso}T00:00:00`).toLocaleDateString("fr-FR") : "";
}
function formatLongDate(iso) {
  return iso ? new Date(`${iso}T00:00:00`).toLocaleDateString("fr-FR", { weekday: "long", day: "numeric", month: "long", year: "numeric" }) : "";
}
function showType(type) { return filterType.value === "all" || filterType.value === type; }

function syncDraft() {
  const v = props.latestVital;
  draft.heartRate  = String(v?.heart_rate ?? "");
  draft.systolic   = String(v?.systolic_pressure ?? "");
  draft.diastolic  = String(v?.diastolic_pressure ?? "");
  draft.oxygen     = String(v?.oxygen_saturation ?? "");
}

function activerEditionMesure(key)  { mesureEnEdition.value = key; }
function fermerEditionMesure()      { mesureEnEdition.value = ""; }
function reinitialiserEdition()     { syncDraft(); fermerEditionMesure(); }
function reinitialiserFiltres()     { filterDate.value = ""; filterType.value = "all"; }

function resetForm() {
  formError.value = "";
  isEditingLatest.value = false;
  const v = props.latestVital;
  Object.assign(form, {
    heartRate: String(v?.heart_rate ?? 72),
    systolic:  String(v?.systolic_pressure ?? 120),
    diastolic: String(v?.diastolic_pressure ?? 80),
    oxygen:    String(v?.oxygen_saturation ?? 98),
    skipHeartRate: false, skipPressure: false, skipOxygen: false,
    date: today(),
  });
}

// ─── Actions API ─────────────────────────────────────────────────────────────
async function enregistrerMesure() {
  formError.value = "";
  const heartRate = form.skipHeartRate ? null : toNumber(form.heartRate);
  const systolic  = form.skipPressure  ? null : toNumber(form.systolic);
  const diastolic = form.skipPressure  ? null : toNumber(form.diastolic);
  const oxygen    = form.skipOxygen    ? null : toNumber(form.oxygen);

  if (heartRate === null && systolic === null && diastolic === null && oxygen === null) {
    formError.value = "Veuillez saisir au moins une mesure.";
    return;
  }
  if ((systolic === null) !== (diastolic === null)) {
    formError.value = "Veuillez remplir les deux champs de tension.";
    return;
  }

  try {
    await api.post("/health-data/vitals", {
      measured_at: isoDate(form.date),
      heart_rate: heartRate, systolic_pressure: systolic,
      diastolic_pressure: diastolic, oxygen_saturation: oxygen,
    });
    notifications.actionAjoutee();
    resetForm();
    showModal.value = false;
    emit("refresh");
  } catch (err) {
    notifications.erreur(err?.response?.data?.message ?? "Erreur lors de l'enregistrement.");
  }
}

async function enregistrerDepuisCartes() {
  if (!peutModifierDerniereMesure.value) return;

  const heartRate = toNumber(draft.heartRate);
  const systolic  = toNumber(draft.systolic);
  const diastolic = toNumber(draft.diastolic);
  const oxygen    = toNumber(draft.oxygen);

  if (heartRate === null && systolic === null && diastolic === null && oxygen === null) {
    notifications.avertissement("Veuillez renseigner au moins une valeur.");
    return;
  }
  if ((systolic === null) !== (diastolic === null)) {
    notifications.avertissement("Veuillez remplir les deux champs de tension.");
    return;
  }

  enregistrementEnCours.value = true;
  try {
    await api.post("/health-data/vitals", {
      measured_at: latestVitalDate.value,
      heart_rate: heartRate, systolic_pressure: systolic,
      diastolic_pressure: diastolic, oxygen_saturation: oxygen,
    });
    notifications.actionModifiee("Dernière entrée modifiée avec succès.");
    fermerEditionMesure();
    emit("refresh");
  } catch (err) {
    const msg = err?.response?.data?.errors
      ? Object.values(err.response.data.errors).flat()[0]
      : (err?.response?.data?.message ?? "Erreur lors de la modification.");
    notifications.erreur(msg);
  } finally {
    enregistrementEnCours.value = false;
  }
}

// ─── API publique ─────────────────────────────────────────────────────────────
function ouvrirModalAjout() { resetForm(); showModal.value = true; }

watch(() => props.latestVital, syncDraft, { immediate: true, deep: true });

defineExpose({ ouvrirModalAjout });
</script>