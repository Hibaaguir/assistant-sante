import { defineStore } from "pinia";
import { computed, ref } from "vue";
import api from "@/services/api";

export const useAuthStore = defineStore("auth", () => {
    const user = ref(null);
    const resolved = ref(false);

    let fetchInFlight = null;
    let logoutInFlight = null;

    const isAuthenticated = computed(() =>
        Boolean(localStorage.getItem("auth_token")),
    );
    const userName = computed(() => user.value?.name || "");
    const profilePhoto = computed(() => user.value?.photo_profil || "");
    const userRole = computed(
        () => user.value?.role?.toLowerCase() || null,
    );
    const isAdmin = computed(() => userRole.value === "admin");
    const isDoctor = computed(() => userRole.value === "doctor");
    const hasHealthProfile = computed(() =>
        Boolean(user.value?.has_profil_sante),
    );
    const currentSpace = computed(() =>
        isDoctor.value ? "doctor" : isAdmin.value ? "admin" : "user",
    );
    const isInDoctorSpace = computed(() => isDoctor.value);
    const isInUserSpace = computed(
        () => !isDoctor.value && !isAdmin.value,
    );

    function setHealthProfile(hasProfile) {
        if (!user.value) return;
        user.value.has_profil_sante = Boolean(hasProfile);
    }

    function updateUser(changes = {}) {
        if (!user.value) return;
        // Map profile_photo to photo_profil for consistency
        if (changes.profile_photo !== undefined) {
            changes.photo_profil = changes.profile_photo;
        }
        user.value = {
            ...user.value,
            ...changes,
        };
    }

    function applyAuth(data) {
        if (data?.token) {
            setToken(data.token);
        }

        user.value = data?.user ?? null;
        if (user.value) {
            // Map backend attribute names to frontend attribute names
            user.value.photo_profil =
                user.value.profile_photo || null;
            user.value.has_profil_sante = Boolean(
                data?.has_health_profile || data?.has_profil_sante,
            );
        }

        resolved.value = true;

        return user.value;
    }

    async function loadUser() {
        if (!localStorage.getItem("auth_token")) {
            user.value = null;
            resolved.value = true;
            return null;
        }

        // Retourner l'utilisateur en cache si déjà chargé — évite d'écraser
        // les changements d'état locaux (ex: setHealthProfile) à chaque navigation
        if (resolved.value && user.value) {
            return user.value;
        }

        if (!fetchInFlight) {
            fetchInFlight = api
                .get("/auth/me")
                .then((res) => applyAuth(res?.data))
                .catch(() => {
                    removeToken();
                    user.value = null;
                    resolved.value = true;
                    return null;
                })
                .finally(() => {
                    fetchInFlight = null;
                });
        }

        return fetchInFlight;
    }

    async function logout(options = {}) {
        const { callApi = true } = options;

        if (logoutInFlight) {
            return logoutInFlight;
        }

        logoutInFlight = (async () => {
            const hasToken = Boolean(localStorage.getItem("auth_token"));

            if (callApi && hasToken) {
                try {
                    await api.post("/auth/logout");
                } catch (_) {
                    // Local logout must always succeed even if the API call fails.
                }
            }

            removeToken();
            user.value = null;
            resolved.value = false;
        })();

        try {
            await logoutInFlight;
        } finally {
            logoutInFlight = null;
        }
    }

    function removeToken() {
        localStorage.removeItem("auth_token");
        delete api.defaults.headers.common.Authorization;
    }

    function setToken(token) {
        localStorage.setItem("auth_token", token);
    }

    return {
        // State
        user,
        resolved,
        // Computed
        currentSpace,
        isAuthenticated,
        isAdmin,
        isDoctor,
        isInDoctorSpace,
        isInUserSpace,
        userName,
        profilePhoto,
        userRole,
        hasHealthProfile,
        // Actions
        applyAuth,
        loadUser,
        logout,
        setHealthProfile,
        updateUser,
        removeToken,
        setToken,
    };
});
