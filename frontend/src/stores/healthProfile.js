import { ref } from "vue";
import { defineStore } from "pinia";
import api from "@/services/api";

export const useHealthProfileStore = defineStore("healthProfile", () => {
    const initialized      = ref(false);
    const loading          = ref(false);

    const profileData      = ref(null);
    const userInfo         = ref({});
    const treatmentCatalog = ref({});

    function invalidate() {
        initialized.value = false;
    }

    async function initialize() {
        if (initialized.value || loading.value) return;
        loading.value = true;
        try {
            const [profileRes, catalogRes] = await Promise.all([
                api.get("/health-profile"),
                api.get("/treatment-catalog").catch(() => ({ data: { data: {} } })),
            ]);
            profileData.value      = profileRes?.data?.data  || null;
            userInfo.value         = profileRes?.data?.user  || {};
            treatmentCatalog.value = catalogRes?.data?.data  || {};
            initialized.value = true;
        } finally {
            loading.value = false;
        }
    }

    return { initialized, loading, profileData, userInfo, treatmentCatalog, invalidate, initialize };
});
