import { ref } from "vue";
import { defineStore } from "pinia";
import api from "@/services/api";

const CACHE_TTL_MS = 5 * 60 * 1000; // revalide automatiquement après 5 minutes

export const useDashboardStore = defineStore("dashboard", () => {
    const initialized = ref(false);
    const loading     = ref(false);
    let fetchedAt     = 0;

    const journal           = ref([]);
    const vitals30          = ref([]);
    const vitals60          = ref([]);
    const vitals62          = ref([]);
    const vitalsChart       = ref({});   // { 7: {...}, 30: {...} }
    const treatments        = ref([]);
    const weight            = ref(null);
    const labs              = ref([]);
    const treatmentChecks90 = ref([]);

    // À appeler après chaque mutation (ajout signes vitaux, prise médicament, etc.)
    function invalidate() {
        initialized.value = false;
        fetchedAt = 0;
    }

    async function initialize() {
        const isExpired = fetchedAt > 0 && (Date.now() - fetchedAt > CACHE_TTL_MS);
        if (isExpired) initialized.value = false;
        if (initialized.value || loading.value) return;
        loading.value = true;
        try {
            const [
                journalRes,
                vitals30Res,
                vitals60Res,
                vitals62Res,
                vitalsChart7Res,
                vitalsChart30Res,
                treatmentsRes,
                weightRes,
                labsRes,
                treatmentChecksRes,
            ] = await Promise.all([
                api.get("/dashboard/journal"),
                api.get("/dashboard/vitals",       { params: { days: 30 } }),
                api.get("/dashboard/vitals",       { params: { days: 60 } }),
                api.get("/dashboard/vitals",       { params: { days: 62 } }),
                api.get("/dashboard/vitals-chart", { params: { days: 7  } }),
                api.get("/dashboard/vitals-chart", { params: { days: 30 } }),
                api.get("/dashboard/treatments"),
                api.get("/dashboard/weight"),
                api.get("/dashboard/labs"),
                api.get("/dashboard/treatment-checks", { params: { days: 90 } }),
            ]);

            journal.value           = journalRes.data?.data ?? journalRes.data ?? [];
            vitals30.value          = vitals30Res.data?.data ?? [];
            vitals60.value          = vitals60Res.data?.data ?? [];
            vitals62.value          = vitals62Res.data?.data ?? [];
            vitalsChart.value       = {
                7:  vitalsChart7Res.data?.data  ?? {},
                30: vitalsChart30Res.data?.data ?? {},
            };
            treatments.value        = treatmentsRes.data?.data ?? [];
            weight.value            = weightRes.data?.data ?? null;
            labs.value              = labsRes.data?.data ?? [];
            treatmentChecks90.value = treatmentChecksRes.data?.data ?? [];

            fetchedAt         = Date.now();
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
        journal,
        vitals30,
        vitals60,
        vitals62,
        vitalsChart,
        treatments,
        weight,
        labs,
        treatmentChecks90,
        initialize,
    };
});
