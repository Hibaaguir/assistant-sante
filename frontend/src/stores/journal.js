/*
  Store Pinia du module journal.
  Il centralise le chargement, la transformation et la sauvegarde des entrees.
  Les pages journal consomment cet etat pour garder une logique unique.
*/

import { computed, ref } from "vue";
import { defineStore } from "pinia";
import api from "@/services/api";

function convertirEntierOuNull(value) {
  if (value == null || value === "") return null;
  const parsed = Number(value);
  if (!Number.isFinite(parsed)) return null;
  return Math.round(parsed);
}

function calculerCaloriesDepuisMeals(meals) {
  if (!Array.isArray(meals)) return 0;

  const total = meals.reduce((sum, meal) => {
    const value = Number(meal?.calories);
    if (!Number.isFinite(value) || value <= 0) return sum;
    return sum + Math.round(value);
  }, 0);

  return Math.max(0, Math.min(total, 65535));
}

function supprimerCaloriesDesMeals(meals) {
  if (!Array.isArray(meals)) return [];

  return meals.map((meal) => {
    if (!meal || typeof meal !== "object" || Array.isArray(meal)) return meal;
    const nextMeal = { ...meal };
    delete nextMeal.calories;
    return nextMeal;
  });
}

function formaterLibelleDate(dateIso) {
  if (!dateIso) return "";
  const date = new Date(`${dateIso}T00:00:00`);
  if (Number.isNaN(date.getTime())) return dateIso;
  return date.toLocaleDateString("fr-FR", {
    weekday: "long",
    day: "numeric",
    month: "long",
  });
}

function versVue(modele) {
  return {
    id: String(modele.id),
    dateIso: modele.entry_date,
    dateLabel: formaterLibelleDate(modele.entry_date),
    sleep: Number(modele.sleep ?? 0),
    stress: Number(modele.stress ?? 0),
    energy: Number(modele.energy ?? 0),
    sugar: modele.sugar ?? "low",
    caffeine: Number(modele.caffeine ?? 0),
    hydration: Number(modele.hydration ?? 0),
    meals: supprimerCaloriesDesMeals(Array.isArray(modele.meals) ? modele.meals : []),
    calories: Number(modele.calories ?? 0),
    activityType: modele.activity_type ?? "",
    activityDuration: Number(modele.activity_duration ?? 0),
    intensity: modele.intensity ?? "medium",
    tobacco: Boolean(modele.tobacco),
    alcohol: Boolean(modele.alcohol),
    tobaccoTypes: modele.tobacco_types ?? { cigarette: false, vape: false },
    cigarettesPerDay: modele.cigarettes_per_day,
    vapeFrequency: modele.vape_frequency,
    vapeLiquidMl: modele.vape_liquid_ml,
    alcoholDrinks: modele.alcohol_drinks,
  };
}

function versChargeUtile(entree) {
  const mealsBruts = Array.isArray(entree.meals) ? entree.meals : [];
  const meals = supprimerCaloriesDesMeals(mealsBruts);
  const calories = convertirEntierOuNull(entree.calories);

  return {
    entry_date: entree.dateIso || new Date().toISOString().slice(0, 10),
    sleep: convertirEntierOuNull(entree.sleep),
    stress: convertirEntierOuNull(entree.stress),
    energy: convertirEntierOuNull(entree.energy),
    sugar: entree.sugar ?? "low",
    caffeine: convertirEntierOuNull(entree.caffeine) ?? 0,
    hydration: entree.hydration ?? 0,
    meals,
    calories: calories ?? calculerCaloriesDepuisMeals(mealsBruts),
    activity_type: entree.activityType ?? null,
    activity_duration: convertirEntierOuNull(entree.activityDuration),
    intensity: entree.intensity ?? "medium",
    tobacco: Boolean(entree.tobacco),
    alcohol: Boolean(entree.alcohol),
    tobacco_types: entree.tobaccoTypes ?? { cigarette: false, vape: false },
    cigarettes_per_day: convertirEntierOuNull(entree.cigarettesPerDay),
    vape_frequency: entree.vapeFrequency ?? null,
    vape_liquid_ml: convertirEntierOuNull(entree.vapeLiquidMl),
    alcohol_drinks: convertirEntierOuNull(entree.alcoholDrinks),
  };
}

const DEFAULT_FILTER = { type: "all", month: "", date: "" };

export const useJournalStore = defineStore("journal", () => {
  const entries = ref([]);
  const loading = ref(false);
  const initialized = ref(false);

  const filter = ref({ ...DEFAULT_FILTER });

  const derniereEntree = computed(() =>
    entries.value.reduce((best, e) => (!best || e.dateIso > best.dateIso ? e : best), null)
  );

  const entreesFiltrees = computed(() => {
    const f = filter.value;

    if (f.type === "all") return entries.value;

    if (f.type === "nutrition") {
      return entries.value.filter((entree) => entree.meals.length > 0);
    }

    if (f.type === "hydration") {
      return entries.value.filter((entree) => entree.hydration > 0);
    }

    if (f.type === "activity") {
      return entries.value.filter((entree) => Boolean(entree.activityType));
    }

    if (f.type === "month") {
      if (!f.month) return [];
      return entries.value.filter((entree) => entree.dateIso.startsWith(f.month));
    }

    if (f.type === "date") {
      if (!f.date) return [];
      return entries.value.filter((entree) => entree.dateIso === f.date);
    }

    return entries.value;
  });

  const obtenirParId = (id) => entries.value.find((entree) => entree.id === String(id));

  const chargerEntrees = async () => {
    loading.value = true;
    try {
      const res = await api.get("/journal");
      entries.value = Array.isArray(res?.data?.data) ? res.data.data.map(versVue) : [];
      initialized.value = true;
    } finally {
      loading.value = false;
    }
  };

  const initialiser = async () => {
    if (initialized.value || loading.value) return;
    await chargerEntrees();
  };

  const ajouterEntree = async (entree) => {
    const chargeUtile = versChargeUtile({
      ...entree,
      dateIso: new Date().toISOString().slice(0, 10),
    });
    const res = await api.post("/journal", chargeUtile);
    if (res?.data?.data) {
      const suivant = versVue(res.data.data);
      const idx = entries.value.findIndex((item) => item.id === suivant.id);
      if (idx >= 0) entries.value[idx] = suivant;
      else entries.value.unshift(suivant);
    }
  };

  const mettreAJourEntree = async (id, patch) => {
    const current = obtenirParId(id);
    if (!current) return;
    const chargeUtile = versChargeUtile({ ...current, ...patch });
    const res = await api.put(`/journal/${id}`, chargeUtile);
    if (res?.data?.data) {
      const suivant = versVue(res.data.data);
      const idx = entries.value.findIndex((item) => item.id === String(id));
      if (idx >= 0) entries.value[idx] = suivant;
    }
  };

  const supprimerEntree = async (id) => {
    await api.delete(`/journal/${id}`);
    entries.value = entries.value.filter((entree) => entree.id !== String(id));
  };

  const definirFiltre = (nextFilter) => {
    filter.value = nextFilter;
  };

  const reinitialiserFiltre = () => { filter.value = { ...DEFAULT_FILTER } };

  return {
    entries,
    filter,
    loading,
    initialized,
    derniereEntree,
    entreesFiltrees,
    obtenirParId,
    chargerEntrees,
    initialiser,
    ajouterEntree,
    mettreAJourEntree,
    supprimerEntree,
    definirFiltre,
    reinitialiserFiltre,
  };
});

