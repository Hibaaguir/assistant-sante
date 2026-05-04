import { ref } from "vue";
import { defineStore } from "pinia";
import api from "@/services/api";

const CACHE_TTL_MS = 5 * 60 * 1000;

export const useHealthDataStore = defineStore("healthData", () => {
    const initialized = ref(false);
    const loading     = ref(false);
    let fetchedAt     = 0;

    const overview        = ref(null);
    const vitals          = ref([]);
    const treatmentChecks = ref([]);

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
            const [overviewRes, vitalsRes, checksRes] = await Promise.all([
                api.get("/health-data/overview",         { params: { days: 30 } }),
                api.get("/health-data/vitals",           { params: { days: 30 } }),
                api.get("/health-data/treatment-checks", { params: { days: 30 } }),
            ]);
            overview.value        = overviewRes?.data?.data ?? {};
            vitals.value          = Array.isArray(vitalsRes?.data?.data)  ? vitalsRes.data.data  : [];
            treatmentChecks.value = Array.isArray(checksRes?.data?.data)  ? checksRes.data.data  : [];
            fetchedAt         = Date.now();
            initialized.value = true;
        } catch (e) {
            console.error("Erreur chargement données santé :", e);
            throw e;
        } finally {
            loading.value = false;
        }
    }

    return { initialized, loading, overview, vitals, treatmentChecks, invalidate, initialize };
});
