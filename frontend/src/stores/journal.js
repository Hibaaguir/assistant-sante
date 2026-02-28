/*
  Store Pinia du module journal.
  Il centralise le chargement, la transformation et la sauvegarde des entrees.
  Les pages journal consomment cet etat pour garder une logique unique.
*/

import { computed, ref } from "vue";
import { defineStore } from "pinia";
import api from "@/services/api";

function toNullableInt(value) {
  if (value === null || value === undefined || value === "") return null;
  const parsed = Number(value);
  if (!Number.isFinite(parsed)) return null;
  return Math.round(parsed);
}

function formatDateLabel(dateIso) {
  if (!dateIso) return "";
  const date = new Date(`${dateIso}T00:00:00`);
  if (Number.isNaN(date.getTime())) return dateIso;
  return date.toLocaleDateString("fr-FR", {
    weekday: "long",
    day: "numeric",
    month: "long",
  });
}

function toView(model) {
  return {
    id: String(model.id),
    dateIso: model.entry_date,
    dateLabel: formatDateLabel(model.entry_date),
    sleep: Number(model.sleep ?? 0),
    stress: Number(model.stress ?? 0),
    energy: Number(model.energy ?? 0),
    sugar: model.sugar ?? "low",
    caffeine: Number(model.caffeine ?? 0),
    hydration: Number(model.hydration ?? 0),
    meals: Array.isArray(model.meals) ? model.meals : [],
    activityType: model.activity_type ?? "",
    activityDuration: Number(model.activity_duration ?? 0),
    intensity: model.intensity ?? "medium",
    tobacco: Boolean(model.tobacco),
    alcohol: Boolean(model.alcohol),
    tobaccoTypes: model.tobacco_types ?? { cigarette: false, vape: false },
    cigarettesPerDay: model.cigarettes_per_day,
    vapeFrequency: model.vape_frequency,
    vapeLiquidMl: model.vape_liquid_ml,
    alcoholDrinks: model.alcohol_drinks,
  };
}

function toPayload(entry) {
  return {
    entry_date: entry.dateIso || new Date().toISOString().slice(0, 10),
    sleep: toNullableInt(entry.sleep),
    stress: toNullableInt(entry.stress),
    energy: toNullableInt(entry.energy),
    sugar: entry.sugar ?? "low",
    caffeine: toNullableInt(entry.caffeine) ?? 0,
    hydration: entry.hydration ?? 0,
    meals: Array.isArray(entry.meals) ? entry.meals : [],
    activity_type: entry.activityType ?? null,
    activity_duration: toNullableInt(entry.activityDuration),
    intensity: entry.intensity ?? "medium",
    tobacco: Boolean(entry.tobacco),
    alcohol: Boolean(entry.alcohol),
    tobacco_types: entry.tobaccoTypes ?? { cigarette: false, vape: false },
    cigarettes_per_day: toNullableInt(entry.cigarettesPerDay),
    vape_frequency: entry.vapeFrequency ?? null,
    vape_liquid_ml: toNullableInt(entry.vapeLiquidMl),
    alcohol_drinks: toNullableInt(entry.alcoholDrinks),
  };
}

export const useJournalStore = defineStore("journal", () => {
  const entries = ref([]);
  const loading = ref(false);
  const initialized = ref(false);

  const filter = ref({
    type: "all",
    month: "",
    date: "",
  });

  const latestEntry = computed(() => [...entries.value].sort((a, b) => b.dateIso.localeCompare(a.dateIso))[0]);

  const filteredEntries = computed(() => {
    const f = filter.value;

    if (f.type === "all" || f.type === "sleep" || f.type === "stress" || f.type === "energy") {
      return entries.value;
    }

    if (f.type === "nutrition") {
      return entries.value.filter((entry) => entry.meals.length > 0);
    }

    if (f.type === "hydration") {
      return entries.value.filter((entry) => entry.hydration > 0);
    }

    if (f.type === "activity") {
      return entries.value.filter((entry) => Boolean(entry.activityType));
    }

    if (f.type === "month") {
      if (!f.month) return [];
      return entries.value.filter((entry) => entry.dateIso.startsWith(f.month));
    }

    if (f.type === "date") {
      if (!f.date) return [];
      return entries.value.filter((entry) => entry.dateIso === f.date);
    }

    return entries.value;
  });

  const obtenirParId = (id) => entries.value.find((entry) => entry.id === String(id));

  const fetchEntries = async () => {
    loading.value = true;
    try {
      const res = await api.get("/journal");
      entries.value = Array.isArray(res?.data?.data) ? res.data.data.map(toView) : [];
      initialized.value = true;
    } finally {
      loading.value = false;
    }
  };

  const initialiser = async () => {
    if (initialized.value || loading.value) return;
    await fetchEntries();
  };

  const ajouterEntree = async (entry) => {
    const payload = toPayload({
      ...entry,
      dateIso: new Date().toISOString().slice(0, 10),
    });
    const res = await api.post("/journal", payload);
    if (res?.data?.data) {
      const next = toView(res.data.data);
      const idx = entries.value.findIndex((item) => item.id === next.id);
      if (idx >= 0) entries.value[idx] = next;
      else entries.value.unshift(next);
    }
  };

  const mettreAJourEntree = async (id, patch) => {
    const current = obtenirParId(id);
    if (!current) return;
    const payload = toPayload({ ...current, ...patch });
    const res = await api.put(`/journal/${id}`, payload);
    if (res?.data?.data) {
      const next = toView(res.data.data);
      const idx = entries.value.findIndex((item) => item.id === String(id));
      if (idx >= 0) entries.value[idx] = next;
    }
  };

  const supprimerEntree = async (id) => {
    await api.delete(`/journal/${id}`);
    entries.value = entries.value.filter((entry) => entry.id !== String(id));
  };

  const definirFiltre = (nextFilter) => {
    filter.value = nextFilter;
  };

  const reinitialiserFiltre = () => {
    filter.value = {
      type: "all",
      month: "",
      date: "",
    };
  };

  return {
    entries,
    filter,
    loading,
    initialized,
    latestEntry,
    filteredEntries,
    obtenirParId,
    fetchEntries,
    initialiser,
    ajouterEntree,
    mettreAJourEntree,
    supprimerEntree,
    definirFiltre,
    reinitialiserFiltre,
  };
});


