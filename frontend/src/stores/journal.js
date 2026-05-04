import { computed, ref } from "vue";
import { defineStore } from "pinia";
import api from "@/services/api";
import { formatLongDate } from "@/components/doctors/doctorUtilities.js";
import { useDashboardStore } from "@/stores/dashboard";
// transformateur de données entre le format backend et le format utilisé dans le frontend, notamment pour les champs liés à l'activité physique et au tabac qui ont une structure différente entre les deux côtés
function convertToIntOrNull(value) {
    if (value == null || value === "") return null;
    const parsed = Number(value);
    if (!Number.isFinite(parsed)) return null;
    return Math.round(parsed); //10.8=>11
}
// Calculer les calories totales à partir des repas
function calculateCaloriesFromMeals(meals) {
    if (!Array.isArray(meals)) return 0;

    const total = meals.reduce((sum, meal) => {
        const value = Number(meal?.calories);
        if (!Number.isFinite(value) || value <= 0) return sum;
        return sum + Math.round(value);
    }, 0);

    return Math.max(0, Math.min(total, 65535));
}
// Convertir une entrée du format backend au format utilisé dans le frontend, en gérant les différences de structure pour les activités physiques et le tabac
function toVue(model) {
    // Physical activity: on prend la première activité s'il y en a, sinon null
    const activities = Array.isArray(model.physical_activities)
        ? model.physical_activities
        : [];
    const activity = activities[0] ?? null;

    // Tobacco: on cherche les entrées de type "cigarette" et "vape" dans la liste du backend, et on en déduit les champs à utiliser dans le frontend
    const tobaccoList = Array.isArray(model.tobacco) ? model.tobacco : [];
    const cigaretteEntry =
        tobaccoList.find((t) => t.tobacco_type === "cigarette") ?? null;
    const vapeEntry =
        tobaccoList.find((t) => t.tobacco_type === "vape") ?? null;

    return {
        id: String(model.id),
        dateIso: model.entry_date,
        dateLabel: formatLongDate(model.entry_date),
        sleep: Number(model.sleep ?? 0),
        stress: Number(model.stress ?? 0),
        energy: model.energy != null ? Number(model.energy) : null,
        sugar: model.sugar_intake ?? model.sugar ?? "low",
        caffeine: Number(model.caffeine ?? 0),
        hydration: Number(model.hydration ?? 0),
        meals: Array.isArray(model.meals) ? model.meals : [],
        calories: Number(model.calories ?? 0),
        activities: activities.map((a) => ({
            type: a.activity_type ?? "",
            duration: Number(a.duration_minutes ?? 0),
            intensity: a.intensity === "low" ? "light" : (a.intensity ?? "medium"),
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
// Convertir une entrée du format utilisé dans le frontend au format attendu par le backend, en gérant les différences de structure pour les activités physiques et le tabac
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
        sugar_intake: entry.sugar ?? "low",
        caffeine: convertToIntOrNull(entry.caffeine) ?? 0,
        hydration: entry.hydration ?? 0,
        meals,
        calories: calories ?? calculateCaloriesFromMeals(mealsBruts),
        activities: (entry.activities ?? []).map((a) => ({
            activity_type: a.type,
            activity_duration: convertToIntOrNull(a.duration),
            intensity: a.intensity === "light" ? "low" : (a.intensity ?? "medium"),
        })),
        tobacco: Boolean(entry.tobacco),
        alcohol: Boolean(entry.alcohol),
        tobacco_types: entry.tobaccoTypes ?? { cigarette: false, vape: false },
        cigarettes_per_day: convertToIntOrNull(entry.cigarettesPerDay),
        vape_liquid_ml: convertToIntOrNull(entry.vapeLiquidMl),
        alcohol_glasses: convertToIntOrNull(entry.alcoholDrinks),
    };
}
// Filtre par défaut pour les entrées du journal, utilisé pour réinitialiser le filtre ou comme état initial
const DEFAULT_FILTER = { type: "all", month: "", date: "" };

export const useJournalStore = defineStore("journal", () => {
    const entries = ref([]);
    const loading = ref(false);
    const initialized = ref(false); // si les entrées ont déjà été chargées depuis le backend, pour éviter de les recharger inutilement à chaque fois que le store est utilisé

    const filter = ref({ ...DEFAULT_FILTER });
    // Trouver la dernière entrée du journal en comparant les dates ISO, et retourner l'entrée la plus récente
    const lastEntry = computed(() =>
        entries.value.reduce((latest, current) => {
            if (!latest) return current;
            return current.dateIso > latest.dateIso ? current : latest;
        }, null),
    );
    // Appliquer les différents types de filtres aux entrées du journal en fonction du type sélectionné et des critères associés (mois, date, etc.)
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
            return entries.value.filter((entry) => entry.activities?.length > 0);
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
    // Trouver une entrée du journal par son ID, en s'assurant que l'ID est traité comme une chaîne de caractères pour éviter les problèmes de comparaison
    const findById = (id) =>
        entries.value.find((entry) => entry.id === String(id));
    // recupérer les entrées depuis le backend, les transformer en format Vue et les stocker dans le state
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
    // Initialiser le store en chargeant les entrées du journal depuis le backend, mais seulement si ce n'est pas déjà fait ou si une opération de chargement est en cours
    const initialize = async () => {
        if (initialized.value || loading.value) return;
        await loadEntries();
    };
    // Ajouter une nouvelle entrée au journal en envoyant les données au backend, puis mettre à jour le state avec la nouvelle entrée retournée par le backend
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
        useDashboardStore().invalidate();
    };
    // Récupérer une entrée depuis le backend et mettre à jour le state (utilisé en mode édition pour garantir des données fraîches)
    const fetchEntry = async (id) => {
        const res = await api.get(`/journal/${id}`);
        if (!res?.data?.data) return null;
        const next = toVue(res.data.data);
        const idx = entries.value.findIndex((item) => item.id === String(id));
        if (idx >= 0) entries.value[idx] = next;
        else entries.value.unshift(next);
        return next;
    };
    // Mettre à jour une entrée existante du journal en envoyant les données modifiées au backend, puis mettre à jour le state avec les données retournées par le backend
    const updateEntry = async (id, patch) => {
        const current = findById(id) ?? {};
        const payload = toPayload({ ...current, ...patch });
        const res = await api.put(`/journal/${id}`, payload);
        if (res?.data?.data) {
            const next = toVue(res.data.data);
            const idx = entries.value.findIndex(
                (item) => item.id === String(id),
            );
            if (idx >= 0) entries.value[idx] = next;
        }
        useDashboardStore().invalidate();
    };
    // Supprimer une entrée du journal en envoyant une requête de suppression au backend, puis mettre à jour le state en retirant l'entrée supprimée
    const deleteEntry = async (id) => {
        await api.delete(`/journal/${id}`);
        entries.value = entries.value.filter(
            (entry) => entry.id !== String(id),
        );
        useDashboardStore().invalidate();
    };
    // Mettre à jour le filtre utilisé pour afficher les entrées du journal, en remplaçant l'ancien filtre par le nouveau
    const setFilter = (nextFilter) => {
        filter.value = nextFilter;
    };
    // Réinitialiser le filtre en le remettant à sa valeur par défaut, ce qui affichera toutes les entrées du journal
    const resetFilter = () => {
        filter.value = { ...DEFAULT_FILTER };
    };

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
