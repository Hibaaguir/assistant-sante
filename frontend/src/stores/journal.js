/*
  Pinia store for the journal module.
  It centralizes loading, transforming, and saving entries.
  Journal pages use this state to maintain single source of truth.
*/

import { computed, ref } from "vue";
import { defineStore } from "pinia";
import api from "@/services/api";

function convertToIntOrNull(value) {
    if (value == null || value === "") return null;
    const parsed = Number(value);
    if (!Number.isFinite(parsed)) return null;
    return Math.round(parsed);
}

function calculateCaloriesFromMeals(meals) {
    if (!Array.isArray(meals)) return 0;

    const total = meals.reduce((sum, meal) => {
        const value = Number(meal?.calories);
        if (!Number.isFinite(value) || value <= 0) return sum;
        return sum + Math.round(value);
    }, 0);

    return Math.max(0, Math.min(total, 65535));
}

function formatDateLabel(dateIso) {
    if (!dateIso) return "";
    const date = new Date(`${dateIso}T00:00:00`);
    if (Number.isNaN(date.getTime())) return dateIso;
    return date.toLocaleDateString("en-US", {
        weekday: "long",
        day: "numeric",
        month: "long",
    });
}

function toVue(model) {
    // Physical activity: one record per entry
    const activities = Array.isArray(model.physical_activities) ? model.physical_activities : [];
    const activity = activities[0] ?? null;

    // Tobacco: one record per type (cigarette / vape)
    const tobaccoList = Array.isArray(model.tobacco) ? model.tobacco : [];
    const cigaretteEntry = tobaccoList.find((t) => t.tobacco_type === "cigarette") ?? null;
    const vapeEntry = tobaccoList.find((t) => t.tobacco_type === "vape") ?? null;

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
        calories: Number(model.calories ?? 0),
        activityType: activity?.activity_type ?? "",
        activityDuration: Number(activity?.duration_minutes ?? 0),
        intensity: activity?.intensity ?? "medium",
        tobacco: tobaccoList.length > 0,
        alcohol: Boolean(model.alcohol),
        tobaccoTypes: {
            cigarette: !!cigaretteEntry,
            vape: !!vapeEntry,
        },
        cigarettesPerDay: cigaretteEntry?.cigarettes_per_day ?? null,
        vapeFrequency: null,
        vapeLiquidMl: vapeEntry?.puffs_per_day ?? null,
        alcoholDrinks: model.alcohol_glasses,
    };
}

function toPayload(entry) {
    const mealsBruts = Array.isArray(entry.meals) ? entry.meals : [];
    const meals = mealsBruts.map((meal) => ({
        meal_type: meal.meal_type || meal.type || null,
        description: meal.description || meal.label || "",
        calories: convertToIntOrNull(meal.calories),
    }));
    const calories = convertToIntOrNull(entry.calories);

    return {
        entry_date: entry.dateIso || new Date().toISOString().slice(0, 10),
        sleep: convertToIntOrNull(entry.sleep),
        stress: convertToIntOrNull(entry.stress),
        energy: convertToIntOrNull(entry.energy),
        sugar: entry.sugar ?? "low",
        caffeine: convertToIntOrNull(entry.caffeine) ?? 0,
        hydration: entry.hydration ?? 0,
        meals,
        calories: calories ?? calculateCaloriesFromMeals(mealsBruts),
        activity_type: entry.activityType ?? null,
        activity_duration: convertToIntOrNull(entry.activityDuration),
        intensity: entry.intensity ?? "medium",
        tobacco: Boolean(entry.tobacco),
        alcohol: Boolean(entry.alcohol),
        tobacco_types: entry.tobaccoTypes ?? { cigarette: false, vape: false },
        cigarettes_per_day: convertToIntOrNull(entry.cigarettesPerDay),
        vape_frequency: entry.vapeFrequency ?? null,
        vape_liquid_ml: convertToIntOrNull(entry.vapeLiquidMl),
        alcohol_glasses: convertToIntOrNull(entry.alcoholDrinks),
    };
}

const DEFAULT_FILTER = { type: "all", month: "", date: "" };

export const useJournalStore = defineStore("journal", () => {
    const entries = ref([]);
    const loading = ref(false);
    const initialized = ref(false);

    const filter = ref({ ...DEFAULT_FILTER });

    const lastEntry = computed(() =>
        entries.value.reduce(
            (best, e) => (!best || e.dateIso > best.dateIso ? e : best),
            null,
        ),
    );

    const filteredEntries = computed(() => {
        const f = filter.value;

        if (f.type === "all") return entries.value;

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
            return entries.value.filter((entry) =>
                entry.dateIso.startsWith(f.month),
            );
        }

        if (f.type === "date") {
            if (!f.date) return [];
            return entries.value.filter((entry) => entry.dateIso === f.date);
        }

        return entries.value;
    });

    const findById = (id) =>
        entries.value.find((entry) => entry.id === String(id));

    const loadEntries = async () => {
        loading.value = true;
        try {
            const res = await api.get("/journal");
            entries.value = Array.isArray(res?.data?.data)
                ? res.data.data.map(toVue)
                : [];
            initialized.value = true;
        } finally {
            loading.value = false;
        }
    };

    const initialize = async () => {
        if (initialized.value || loading.value) return;
        await loadEntries();
    };

    const addEntry = async (entry) => {
        const payload = toPayload({
            ...entry,
            dateIso: new Date().toISOString().slice(0, 10),
        });
        const res = await api.post("/journal", payload);
        if (res?.data?.data) {
            const next = toVue(res.data.data);
            const idx = entries.value.findIndex((item) => item.id === next.id);
            if (idx >= 0) entries.value[idx] = next;
            else entries.value.unshift(next);
        }
    };

    const updateEntry = async (id, patch) => {
        const current = findById(id);
        if (!current) return;
        const payload = toPayload({ ...current, ...patch });
        const res = await api.put(`/journal/${id}`, payload);
        if (res?.data?.data) {
            const next = toVue(res.data.data);
            const idx = entries.value.findIndex(
                (item) => item.id === String(id),
            );
            if (idx >= 0) entries.value[idx] = next;
        }
    };

    const deleteEntry = async (id) => {
        await api.delete(`/journal/${id}`);
        entries.value = entries.value.filter(
            (entry) => entry.id !== String(id),
        );
    };

    const setFilter = (nextFilter) => {
        filter.value = nextFilter;
    };

    const resetFilter = () => {
        filter.value = { ...DEFAULT_FILTER };
    };

    // Backward compatibility aliases
    const derniereEntree = lastEntry;
    const entreesFiltrees = filteredEntries;
    const obtenirParId = findById;
    const chargerEntrees = loadEntries;
    const initialiser = initialize;
    const ajouterEntree = addEntry;
    const mettreAJourEntree = updateEntry;
    const supprimerEntree = deleteEntry;
    const definirFiltre = setFilter;
    const reinitialiserFiltre = resetFilter;

    return {
        entries,
        filter,
        loading,
        initialized,
        lastEntry,
        filteredEntries,
        findById,
        loadEntries,
        initialize,
        addEntry,
        updateEntry,
        deleteEntry,
        setFilter,
        resetFilter,
        // Backward compatibility
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
