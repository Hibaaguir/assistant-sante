import { computed, ref } from "vue";
import { defineStore } from "pinia";
import api from "@/services/api";
import { formatLongDate } from "@/components/doctors/doctorUtilities.js";
import { useDashboardStore } from "@/stores/dashboard";
// Convertit une valeur en entier arrondi ou retourne null isfinite si la valeur n'est pas un nombre valide
function convertToIntOrNull(value) {
    if (value == null || value === "") return null;
    const parsed = Number(value);
    if (!Number.isFinite(parsed)) return null;
    return Math.round(parsed);
}
// Calcule le total des calories à partir d'une liste de repas en s'assurant que le total est un entier entre 0 et 65535
function calculateCaloriesFromMeals(meals) {
    if (!Array.isArray(meals)) return 0;
    const total = meals.reduce((sum, meal) => {
        const value = Number(meal?.calories);
        if (!Number.isFinite(value) || value <= 0) return sum;
        return sum + Math.round(value);
    }, 0);
    return Math.max(0, Math.min(total, 65535));
}

// Transforme une entrée backend → format Vue
function toVue(model) {
    const activities = Array.isArray(model.physical_activities) ? model.physical_activities : [];
    const tobaccoList = Array.isArray(model.tobacco) ? model.tobacco : [];
    const cigaretteEntry = tobaccoList.find((t) => t.tobacco_type === "cigarette") ?? null;
    const vapeEntry = tobaccoList.find((t) => t.tobacco_type === "vape") ?? null;

    return {
        id: String(model.id),
        dateIso: model.entry_date,
        dateLabel: formatLongDate(model.entry_date),
        sleep: Number(model.sleep ?? 0),
        stress: Number(model.stress ?? 0),
        energy: model.energy != null ? Number(model.energy) : null,
        sugar: model.sugar_intake ?? "medium",
        caffeine: Number(model.caffeine ?? 0),
        hydration: Number(model.hydration ?? 0),
        meals: Array.isArray(model.meals)
            ? model.meals.map((meal) => ({
                  meal_type: meal.meal_type ?? null,
                  description: meal.description ?? "",
                  calories: meal.calories != null ? Number(meal.calories) : null,
              }))
            : [],
        calories: Number(model.calories ?? 0),
        activities: activities.map((a) => ({
            type: a.activity_type ?? "",
            duration: Number(a.duration_minutes ?? 0),
            intensity: a.intensity ?? "medium",
        })),
        tobacco: tobaccoList.length > 0,
        alcohol: Boolean(model.alcohol),
        tobaccoTypes: {
            cigarette: !!cigaretteEntry,
            vape: !!vapeEntry,
        },
        cigarettesPerDay: cigaretteEntry?.cigarettes_per_day ?? null,
        vapeLiquidMl: vapeEntry?.puffs_per_day ?? null,
        alcoholDrinks: model.alcohol_glasses,
    };
}

// Transforme le format Vue → payload backend
function toPayload(entry) {
    const mealsBruts = Array.isArray(entry.meals) ? entry.meals : [];
    // Le formulaire utilise type/label ; les entrées éditées utilisent meal_type/description
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
        sugar_intake: entry.sugar ?? "medium",
        caffeine: convertToIntOrNull(entry.caffeine) ?? 0,
        hydration: entry.hydration ?? 0,
        meals,
        calories: calories ?? calculateCaloriesFromMeals(mealsBruts),
        activities: (entry.activities ?? []).map((a) => ({
            activity_type: a.type,
            activity_duration: convertToIntOrNull(a.duration),
            intensity: a.intensity,
        })),
        tobacco: Boolean(entry.tobacco),
        alcohol: Boolean(entry.alcohol),
        tobacco_types: entry.tobaccoTypes ?? { cigarette: false, vape: false },
        cigarettes_per_day: convertToIntOrNull(entry.cigarettesPerDay),
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
// trouve l'entrée la plus récente parmi toutes les entrées utuliser dans journalhome
    const lastEntry = computed(() =>
        entries.value.reduce((latest, current) => {
            if (!latest) return current;
            return current.dateIso > latest.dateIso ? current : latest;
        }, null),
    );
// Applique le filtre sélectionné aux entrées du journal utuliser dans journalhistory
    const filteredEntries = computed(() => {
        const f = filter.value;
        const all = entries.value;

        if (f.type === "all")       return all;
        if (f.type === "nutrition") return all.filter((e) => e.meals.length > 0);
        if (f.type === "hydration") return all.filter((e) => e.hydration > 0);
        if (f.type === "activity")  return all.filter((e) => e.activities.length > 0);
        if (f.type === "sleep")     return all.filter((e) => e.sleep > 0);
        if (f.type === "stress")    return all.filter((e) => e.stress > 0);
        if (f.type === "energy")    return all.filter((e) => e.energy != null && e.energy > 0);
        if (f.type === "month")     return f.month ? all.filter((e) => e.dateIso.startsWith(f.month)) : [];
        if (f.type === "date")      return f.date  ? all.filter((e) => e.dateIso === f.date) : [];

        return all;
    });

    const findById = (id) => entries.value.find((e) => e.id === String(id));

    // Insère ou remplace une entrée dans le state local
    const upsertEntry = (next) => {
        const idx = entries.value.findIndex((e) => e.id === next.id);
        if (idx >= 0) entries.value[idx] = next;
        else entries.value.unshift(next);
    };
// Charge les entrées depuis le backend et les stocke localement
    const loadEntries = async () => {
        loading.value = true;
        try {
            const res = await api.get("/journal");
            entries.value = Array.isArray(res?.data?.data) ? res.data.data.map(toVue) : [];
            initialized.value = true;
        } finally {
            loading.value = false;
        }
    };

    const initialize = async () => {
        if (initialized.value || loading.value) return;
        await loadEntries();
    };
// Ajoute une nouvelle entrée au journal en l'envoyant au backend puis met à jour le state local
    const addEntry = async (entry) => {
        const payload = toPayload({ ...entry, dateIso: new Date().toISOString().slice(0, 10) });
        const res = await api.post("/journal", payload);
        if (res?.data?.data) upsertEntry(toVue(res.data.data));
        useDashboardStore().invalidate();
    };
// Récupère une entrée spécifique depuis le backend la stocke localement et la retourne
    const fetchEntry = async (id) => {
        const res = await api.get(`/journal/${id}`);
        if (!res?.data?.data) return null;
        const next = toVue(res.data.data);
        upsertEntry(next);
        return next;
    };
// Met à jour une entrée existante en envoyant les modifications au backend puis met à jour le state local
    const updateEntry = async (id, patch) => {
        const payload = toPayload({ ...(findById(id) ?? {}), ...patch });
        const res = await api.put(`/journal/${id}`, payload);
        if (res?.data?.data) {
            const next = toVue(res.data.data);
            const idx = entries.value.findIndex((e) => e.id === next.id);
            if (idx >= 0) entries.value[idx] = next;
        }
        useDashboardStore().invalidate();
    };
// Supprime une entrée en l'envoyant au backend puis met à jour le state local pour la retirer
    const deleteEntry = async (id) => {
        await api.delete(`/journal/${id}`);
        entries.value = entries.value.filter((e) => e.id !== String(id));
        useDashboardStore().invalidate();
    };

    const setFilter = (nextFilter) => { filter.value = nextFilter; };
    const resetFilter = () => { filter.value = { ...DEFAULT_FILTER }; };

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
        fetchEntry,
        addEntry,
        updateEntry,
        deleteEntry,
        setFilter,
        resetFilter,
    };
});
