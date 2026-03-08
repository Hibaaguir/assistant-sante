<template>
  <section class="mt-4 flex justify-end">
    <button
      type="button"
      class="inline-flex h-10 items-center gap-2 rounded-xl border border-slate-300 bg-white px-4 text-[13px] font-semibold text-slate-700 shadow-sm hover:bg-slate-50"
      @click="showTreatmentHistory = !showTreatmentHistory"
    >
      <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M3 12a9 9 0 1 0 3-6.7M3 4v5h5" stroke-linecap="round" />
      </svg>
      {{ showTreatmentHistory ? "Masquer historique" : "Voir historique" }}
    </button>
  </section>

  <section v-if="showTreatmentHistory" class="mt-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
    <h2 class="text-[22px] font-semibold leading-none text-slate-900">Historique des prises</h2>

    <div class="mt-6 grid gap-4 lg:grid-cols-3">
      <article class="rounded-2xl border border-emerald-200 bg-emerald-50/60 p-4">
        <p class="text-[13px] text-slate-700">Taux d'observance</p>
        <p class="mt-1 text-[24px] font-semibold leading-none text-slate-900">{{ treatmentHistoryStats.observance }}%</p>
        <p class="mt-2 text-[12px] text-slate-600">{{ treatmentHistoryStats.completeDays }}/{{ treatmentHistoryStats.totalDays }} jours complets</p>
      </article>
      <article class="rounded-2xl border border-blue-200 bg-blue-50/60 p-4">
        <p class="text-[13px] text-slate-700">Prises totales</p>
        <p class="mt-1 text-[24px] font-semibold leading-none text-slate-900">{{ treatmentHistoryStats.totalTaken }}</p>
        <p class="mt-2 text-[12px] text-slate-600">{{ treatmentHistoryStats.periodSubtitle }}</p>
      </article>
      <article class="rounded-2xl border border-violet-200 bg-violet-50/60 p-4">
        <p class="text-[13px] text-slate-700">Médicaments actifs</p>
        <p class="mt-1 text-[24px] font-semibold leading-none text-slate-900">{{ treatmentHistoryStats.activeMedicines }}</p>
        <p class="mt-2 text-[12px] text-slate-600">Traitements en cours</p>
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
          :class="treatmentHistoryPeriod === period.value
            ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-[0_6px_14px_rgba(59,130,246,0.32)]'
            : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
          @click="treatmentHistoryPeriod = period.value"
        >
          {{ period.label }}
        </button>
      </div>
    </div>

    <div class="mt-5">
      <p class="text-[14px] font-semibold text-slate-800">Médicament</p>
      <div class="mt-3 flex flex-wrap gap-2">
        <button
          v-for="med in treatmentHistoryMedicineOptions"
          :key="med.id"
          type="button"
          class="h-9 rounded-full px-4 text-[13px] font-semibold transition"
          :class="selectedTreatmentHistoryMed === med.id
            ? 'bg-gradient-to-r from-indigo-600 to-violet-600 text-white shadow-[0_6px_14px_rgba(99,102,241,0.32)]'
            : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
          @click="selectedTreatmentHistoryMed = med.id"
        >
          {{ med.name }}
        </button>
      </div>
    </div>

    <div class="relative mt-6 space-y-4 pl-7">
      <div class="absolute left-[12px] top-0 h-full w-px bg-blue-200"></div>
      <article
        v-for="day in treatmentHistoryRows"
        :key="`history-${day.dateKey}`"
        class="relative rounded-2xl border p-4 shadow-sm"
        :class="day.isComplete ? 'border-emerald-300 bg-emerald-50/50' : 'border-slate-200 bg-white'"
      >
        <span
          class="absolute -left-[22px] top-6 inline-flex h-4 w-4 rounded-full ring-4 ring-white"
          :class="day.isComplete ? 'bg-emerald-500' : 'bg-slate-300'"
        ></span>

        <div class="flex items-start justify-between gap-3">
          <div>
            <h3 class="text-[16px] font-semibold leading-none text-slate-900">{{ formaterDateHistoriqueTraitement(day.dateKey) }}</h3>
            <p class="mt-2 text-[14px] text-slate-700">{{ day.taken }}/{{ day.total }} prises effectuées</p>
          </div>
          <span
            v-if="day.isComplete"
            class="inline-flex h-8 items-center gap-1 rounded-full bg-emerald-600 px-4 text-[12px] font-semibold text-white"
          >
            <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="3"><path d="m5 13 4 4L19 7" stroke-linecap="round" stroke-linejoin="round" /></svg>
            Complet
          </span>
        </div>

        <div class="mt-4 grid gap-3 lg:grid-cols-2">
          <article v-for="med in day.meds" :key="`${day.dateKey}-${med.id}`" class="rounded-xl border border-slate-200 bg-white p-3">
            <div class="flex items-center gap-3">
              <span
                class="inline-flex h-8 min-w-[32px] items-center justify-center rounded-lg px-2 text-[12px] font-semibold"
                :class="med.isComplete ? 'bg-emerald-100 text-emerald-700' : 'bg-blue-100 text-blue-700'"
              >
                {{ med.isComplete ? "✓" : `${med.taken}/${med.total}` }}
              </span>
              <div>
                <p class="text-[16px] font-semibold leading-none text-slate-900">{{ med.name }}</p>
                <p class="mt-1 text-[13px] text-slate-600">{{ med.dose }}</p>
              </div>
            </div>
            <div class="mt-3 h-1.5 overflow-hidden rounded-full bg-slate-200">
              <div
                class="h-full rounded-full"
                :class="med.isComplete ? 'bg-emerald-600' : 'bg-blue-600'"
                :style="{ width: `${med.progress}%` }"
              ></div>
            </div>
          </article>
        </div>
      </article>

      <div v-if="!treatmentHistoryRows.length" class="rounded-2xl border border-slate-200 bg-white px-4 py-4 text-[13px] text-slate-600">
        Aucun historique disponible pour ces filtres.
      </div>
    </div>
  </section>

  <template v-else>
    <section class="mt-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
      <h2 class="text-[20px] font-semibold leading-none text-slate-900">Calendrier des traitements</h2>
      <p class="mt-3 text-[12px] font-normal text-slate-600">Cliquez sur une journée pour gérer vos prises</p>

      <div class="mt-6 grid grid-cols-7 gap-2.5">
        <div v-for="day in treatmentDays" :key="day.key" class="text-center">
          <p class="mb-2 text-[12px] font-normal leading-none text-slate-600">{{ day.shortLabel }}</p>
          <button
            type="button"
            class="h-[92px] w-full rounded-xl border px-2 pb-2 pt-2 transition"
            :class="estJourComplet(day.key) ? 'border-[#08a84a] bg-[#cfddd6]' : 'border-slate-300 bg-slate-50 hover:bg-slate-100'"
            @click="ouvrirJourTraitement(day)"
          >
            <p class="text-[32px] font-medium leading-none text-slate-900">{{ day.day }}</p>
            <div class="mt-2 flex justify-center">
              <span
                class="inline-flex h-6 w-6 items-center justify-center rounded-full border"
                :class="estJourComplet(day.key) ? 'border-[#08a84a] bg-[#08a84a] text-white' : 'border-slate-300 bg-white text-transparent'"
              >
                <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="3"><path d="m5 13 4 4L19 7" stroke-linecap="round" stroke-linejoin="round" /></svg>
              </span>
            </div>
          </button>
        </div>
      </div>
    </section>

    <section class="mt-6">
      <h3 class="text-[20px] font-semibold leading-none text-slate-900">Traitements actifs</h3>
      <p v-if="!treatmentMedicines.length" class="mt-3 text-[13px] text-slate-500">
        Aucun traitement actif dans votre profil santé.
      </p>
      <div v-else class="mt-3 space-y-4">
        <article v-for="med in treatmentMedicines" :key="med.id" class="rounded-2xl border border-slate-200 bg-white px-6 py-5 shadow-sm">
          <p class="text-[19px] font-semibold leading-none text-slate-900">{{ med.name }}</p>
          <p class="mt-2 text-[12px] font-normal text-slate-700">{{ med.dose }} - {{ med.freq }}</p>
          <p class="mt-1 text-[12px] font-normal text-slate-500">{{ med.note }}</p>
        </article>
      </div>
    </section>
  </template>

  <div v-if="showTreatmentModal && selectedTreatmentDay" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/45 p-3 sm:p-4">
    <div class="w-full max-w-[452px] rounded-[17px] bg-white px-8 pb-8 pt-8 shadow-2xl [font-family:Inter,system-ui,-apple-system,'Segoe_UI',Roboto,sans-serif]">
      <div class="mb-7 flex items-center justify-between">
        <h3 class="text-[32px] font-semibold leading-none tracking-[-0.01em] text-slate-900">{{ selectedTreatmentDay.fullLabel }}</h3>
        <button type="button" class="text-slate-500 hover:text-slate-700" @click="showTreatmentModal = false">
          <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 6 12 12M18 6 6 18" /></svg>
        </button>
      </div>

      <p class="text-[14px] text-slate-600">Marquez vos prises de médicaments</p>

      <div v-if="treatmentMedicines.length" class="mt-6 space-y-3.5">
        <article
          v-for="med in treatmentMedicines"
          :key="med.id"
          class="min-h-[126px] rounded-2xl border px-4 pb-4 pt-4 text-left transition"
          :class="estMedicamentComplet(selectedTreatmentDay.key, med) ? 'border-2 border-[#08a84a] bg-[#cfddd6]' : 'border border-slate-300 bg-slate-50'"
        >
          <div class="flex items-start justify-between">
            <div>
              <p class="text-[24px] font-semibold leading-none text-slate-900">{{ med.name }}</p>
              <p class="mt-1 text-[13px] leading-none text-slate-600">{{ med.dose }}</p>
              <p v-if="obtenirNombrePrises(med) > 1" class="mt-2 text-[13px] text-slate-500">
                {{ compterPrisesCompletees(selectedTreatmentDay.key, med) }}/{{ obtenirNombrePrises(med) }} prises effectuées
              </p>
            </div>
            <svg
              v-if="estMedicamentComplet(selectedTreatmentDay.key, med)"
              viewBox="0 0 24 24"
              class="h-6 w-6 text-emerald-600"
              fill="none"
              stroke="currentColor"
              stroke-width="2.5"
            ><path d="m5 13 4 4L19 7" stroke-linecap="round" stroke-linejoin="round" /></svg>
          </div>

          <div class="mt-3 flex flex-wrap gap-2">
            <button
              v-for="doseIndex in obtenirIndexPrises(med)"
              :key="`${med.id}-dose-${doseIndex}`"
              type="button"
              class="inline-flex h-10 items-center gap-2 rounded-xl border px-3.5 text-[14px] font-semibold transition"
              :class="estPriseCochee(selectedTreatmentDay.key, med.id, doseIndex) ? 'border-[#08a84a] bg-white text-slate-700' : 'border-slate-300 bg-white text-slate-700 hover:bg-slate-50'"
              @click="basculerPrise(selectedTreatmentDay.key, med, doseIndex)"
            >
              <span
                class="inline-flex h-5 w-5 items-center justify-center rounded-[4px] border"
                :class="estPriseCochee(selectedTreatmentDay.key, med.id, doseIndex) ? 'border-[#08a84a] bg-[#08a84a] text-white' : 'border-slate-400 bg-white text-transparent'"
              >
                <svg viewBox="0 0 24 24" class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="3"><path d="m5 13 4 4L19 7" stroke-linecap="round" stroke-linejoin="round" /></svg>
              </span>
              <span>Prise {{ doseIndex }}</span>
            </button>
          </div>
        </article>
      </div>
      <p v-else class="mt-6 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-[14px] text-slate-600">
        Aucun traitement actif pour le moment. Ajoutez vos traitements dans la page Profil Santé.
      </p>

      <button type="button" class="mt-7 h-12 w-full rounded-2xl bg-gradient-to-r from-blue-600 to-indigo-600 text-[16px] font-semibold leading-none text-white" @click="showTreatmentModal = false">
        Fermer
      </button>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from "vue";
import api from "@/services/api";

const props = defineProps({
  treatmentMedicines: { type: Array, default: () => [] },
  treatmentChecks: { type: Object, default: () => ({}) },
  treatmentDays: { type: Array, default: () => [] },
});

const emit = defineEmits(["refresh"]);

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

const selectedTreatmentDay = computed(() =>
  props.treatmentDays.find((day) => day.key === selectedTreatmentDayKey.value) ?? null,
);

const treatmentHistoryMedicineOptions = computed(() => [
  { id: "all", name: "Tous" },
  ...props.treatmentMedicines.map((med) => ({ id: med.id, name: med.name })),
]);

const treatmentHistoryRows = computed(() => {
  const periodDays = treatmentHistoryPeriod.value === "all"
    ? 90
    : Number(treatmentHistoryPeriod.value || 7);

  const keys = Array.from({ length: periodDays }).map((_, idx) => {
    const date = new Date();
    date.setDate(date.getDate() - idx);
    return date.toISOString().slice(0, 10);
  });

  return keys
    .map((dateKey) => {
      const meds = props.treatmentMedicines
        .filter((med) => selectedTreatmentHistoryMed.value === "all" || med.id === selectedTreatmentHistoryMed.value)
        .map((med) => {
          const total = obtenirNombrePrises(med);
          const taken = compterPrisesCompletees(dateKey, med);
          const progress = total > 0 ? Math.round((taken / total) * 100) : 0;

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
  const observance = totalDays > 0 ? Math.round((completeDays / totalDays) * 100) : 0;
  const periodSubtitle = treatmentHistoryPeriod.value === "all"
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
  const date = new Date(`${dateIso}T00:00:00`);
  return date.toLocaleDateString("fr-FR", {
    weekday: "long",
    day: "numeric",
    month: "long",
  });
}

// Cette fonction retourne le nombre de prises quotidien pour un medicament.
function obtenirNombrePrises(med) {
  const count = Number(med?.doses_per_day ?? 1);
  if (!Number.isFinite(count)) return 1;
  return Math.max(1, Math.min(Math.round(count), 12));
}

// Cette fonction genere la liste des numeros de prises.
function obtenirIndexPrises(med) {
  return Array.from({ length: obtenirNombrePrises(med) }, (_, idx) => idx + 1);
}

// Cette fonction cree une cle unique pour une prise de medicament.
function construireClePrise(medId, doseIndex) {
  return `${medId}__dose_${doseIndex}`;
}

// Cette fonction initialise les cases de suivi pour un jour donne.
function assurerSuiviJour(dayKey) {
  if (!props.treatmentChecks[dayKey]) props.treatmentChecks[dayKey] = {};
  for (const med of props.treatmentMedicines) {
    const doses = obtenirNombrePrises(med);
    for (let i = 1; i <= doses; i += 1) {
      const key = construireClePrise(med.id, i);
      if (typeof props.treatmentChecks[dayKey][key] !== "boolean") {
        props.treatmentChecks[dayKey][key] = false;
      }
    }
  }
}

// Cette fonction verifie si une prise est marquee comme effectuee.
function estPriseCochee(dayKey, medId, doseIndex) {
  return Boolean(props.treatmentChecks[dayKey]?.[construireClePrise(medId, doseIndex)]);
}

// Cette fonction compte le nombre de prises cochees pour un jour.
function compterPrisesCompletees(dayKey, med) {
  const doses = obtenirNombrePrises(med);
  let completed = 0;
  for (let i = 1; i <= doses; i += 1) {
    if (estPriseCochee(dayKey, med.id, i)) completed += 1;
  }
  return completed;
}

// Cette fonction verifie si toutes les prises du medicament sont faites.
function estMedicamentComplet(dayKey, med) {
  return compterPrisesCompletees(dayKey, med) >= obtenirNombrePrises(med);
}

// Cette fonction verifie si tous les medicaments du jour sont complets.
function estJourComplet(dayKey) {
  const dayChecks = props.treatmentChecks[dayKey];
  if (!dayChecks) return false;
  if (!props.treatmentMedicines.length) return false;
  return props.treatmentMedicines.every((med) => estMedicamentComplet(dayKey, med));
}

// Cette fonction envoie l'etat des prises de traitement au serveur.
async function synchroniserSuiviTraitements() {
  if (!props.treatmentMedicines.length) return;

  const checks = [];
  for (const day of props.treatmentDays) {
    assurerSuiviJour(day.key);
    for (const med of props.treatmentMedicines) {
      const doses = obtenirNombrePrises(med);
      for (let i = 1; i <= doses; i += 1) {
        const doseKey = construireClePrise(med.id, i);
        checks.push({
          check_date: day.key,
          medication_key: doseKey,
          medication_name: med.name,
          dose: med.dose,
          taken: Boolean(props.treatmentChecks[day.key][doseKey]),
        });
      }
    }
  }
  await api.post("/health-data/treatment-checks/sync", { checks });
}

// Cette fonction coche ou decoche une prise puis synchronise le suivi.
async function basculerPrise(dayKey, med, doseIndex) {
  assurerSuiviJour(dayKey);
  const key = construireClePrise(med.id, doseIndex);
  props.treatmentChecks[dayKey][key] = !props.treatmentChecks[dayKey][key];
  await synchroniserSuiviTraitements();
}

// Cette fonction ouvre la modale de suivi pour un jour precis.
function ouvrirJourTraitement(day) {
  assurerSuiviJour(day.key);
  selectedTreatmentDayKey.value = day.key;
  showTreatmentModal.value = true;
}
</script>
