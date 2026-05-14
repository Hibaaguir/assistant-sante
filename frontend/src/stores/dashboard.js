import { ref } from "vue";
import { defineStore } from "pinia";
import api from "@/services/api";

const CACHE_TTL_MS = 5 * 60 * 1000; // revalide automatiquement après 5 minutes

export const useDashboardStore = defineStore("dashboard", () => {
    const initialized = ref(false); //données chargées ou nn
    const loading = ref(false); //en cours de chargement ou nn
    let fetchedAt = 0; // timestamp du dernier fetch réussi

    const hydration = ref([]);
    const sleep = ref([]);
    const activities = ref([]);
    const vitals30 = ref([]);
    const vitals60 = ref([]);
    const vitals62 = ref([]);
    const vitalsChart = ref({}); // { 7: {...}, 30: {...} }
    const treatments = ref([]);
    const weight = ref(null);
    const labs = ref([]);
    const treatmentChecks90 = ref([]);
    const caffeine = ref([]);
    const meals = ref([]);

    // À appeler après chaque mutation (ajout signes vitaux, prise médicament, etc.)
    function invalidate() {
        initialized.value = false;
        fetchedAt = 0;
    }
// Charge les données du dashboard, avec gestion du cache
    async function initialize() {
        const isExpired =
            fetchedAt > 0 && Date.now() - fetchedAt > CACHE_TTL_MS;
        if (isExpired) initialized.value = false;
        if (initialized.value || loading.value) return;
        loading.value = true;
        try {
            const [
                hydrationRes,
                sleepRes,
                activitiesRes,
                vitals30Res,
                vitals60Res,
                vitals62Res,
                vitalsChart7Res,
                vitalsChart30Res,
                treatmentsRes,
                weightRes,
                labsRes,
                treatmentChecksRes,
                caffeineRes,
                mealsRes,
            ] = await Promise.all([
                api.get("/dashboard/hydration"),
                api.get("/dashboard/sleep"),
                api.get("/dashboard/activities"),
                api.get("/dashboard/vitals", { params: { days: 30 } }),
                api.get("/dashboard/vitals", { params: { days: 60 } }),
                api.get("/dashboard/vitals", { params: { days: 62 } }),
                api.get("/dashboard/vitals-chart", { params: { days: 7 } }),
                api.get("/dashboard/vitals-chart", { params: { days: 30 } }),
                api.get("/dashboard/treatments"),
                api.get("/dashboard/weight"),
                api.get("/dashboard/labs"),
                api.get("/dashboard/treatment-checks", {
                    params: { days: 90 },
                }),
                api.get("/dashboard/caffeine"),
                api.get("/dashboard/meals"),
            ]);

            hydration.value = hydrationRes.data?.data ?? [];
            sleep.value = sleepRes.data?.data ?? [];
            activities.value = activitiesRes.data?.data ?? [];
            vitals30.value = vitals30Res.data?.data ?? [];
            vitals60.value = vitals60Res.data?.data ?? [];
            vitals62.value = vitals62Res.data?.data ?? [];
            vitalsChart.value = {
                7: vitalsChart7Res.data?.data ?? {},
                30: vitalsChart30Res.data?.data ?? {},
            };
            treatments.value = treatmentsRes.data?.data ?? [];
            weight.value = weightRes.data?.data ?? null;
            labs.value = labsRes.data?.data ?? [];
            treatmentChecks90.value = treatmentChecksRes.data?.data ?? [];
            caffeine.value = caffeineRes.data?.data ?? [];
            meals.value = mealsRes.data?.data ?? [];
            fetchedAt = Date.now();
            initialized.value = true;
        } catch (e) {
            console.error("Erreur chargement dashboard :", e);
        } finally {
            loading.value = false;
        }
    }

    return {
        initialized,
        loading,
        invalidate,
        hydration,
        sleep,
        activities,
        vitals30,
        vitals60,
        vitals62,
        vitalsChart,
        treatments,
        weight,
        labs,
        treatmentChecks90,
        caffeine,
        meals,
        initialize,
    };
});
