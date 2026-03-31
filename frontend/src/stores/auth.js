/*
  Store d'authentification centralisé.
  Source unique de vérité pour l'utilisateur connecté, son rôle, son
  niveau d'accès, et la gestion du token.
*/

import { defineStore } from "pinia";
import { computed, ref } from "vue";
import api from "@/services/api";

export const useAuthStore = defineStore("auth", () => {
    const utilisateur = ref(null);
    const resolved = ref(false);

    let fetchInFlight = null;
    let deconnexionInFlight = null;

    const estConnecte = computed(() =>
        Boolean(localStorage.getItem("auth_token")),
    );
    const nomUtilisateur = computed(() => utilisateur.value?.nom || "");
    const photoProfil = computed(() => utilisateur.value?.photo_profil || "");
    const roleUtilisateur = computed(
        () => utilisateur.value?.role?.toLowerCase() || null,
    );
    const estAdministrateur = computed(
        () =>
            roleUtilisateur.value === "administrateur" ||
            roleUtilisateur.value === "admin",
    );
    const estMedecin = computed(
        () =>
            roleUtilisateur.value === "medecin" ||
            roleUtilisateur.value === "doctor",
    );
    const aProfilSante = computed(() =>
        Boolean(utilisateur.value?.has_profil_sante),
    );
    const espaceCourant = computed(() =>
        estMedecin.value
            ? "medecin"
            : estAdministrateur.value
              ? "administrateur"
              : "personnel",
    );
    const estDansEspaceMedecin = computed(() => estMedecin.value);
    const estDansEspacePersonnel = computed(
        () => !estMedecin.value && !estAdministrateur.value,
    );

    function definirPresenceProfilSante(hasProfile) {
        if (!utilisateur.value) return;
        utilisateur.value.has_profil_sante = Boolean(hasProfile);
    }

    function mettreAJourUtilisateur(changements = {}) {
        if (!utilisateur.value) return;
        utilisateur.value = {
            ...utilisateur.value,
            ...changements,
        };
    }

    function appliquerAuthentification(data) {
        if (data?.token) {
            definirToken(data.token);
        }

        utilisateur.value = data?.utilisateur ?? null;
        if (utilisateur.value) {
            utilisateur.value.has_profil_sante = Boolean(
                data?.has_profil_sante,
            );
        }

        resolved.value = true;

        return utilisateur.value;
    }

    async function chargerUtilisateur() {
        if (!localStorage.getItem("auth_token")) {
            utilisateur.value = null;
            resolved.value = true;
            return null;
        }

        if (!fetchInFlight) {
            fetchInFlight = api
                .get("/auth/me")
                .then((res) => appliquerAuthentification(res?.data))
                .catch(() => {
                    supprimerToken();
                    utilisateur.value = null;
                    resolved.value = true;
                    return null;
                })
                .finally(() => {
                    fetchInFlight = null;
                });
        }

        return fetchInFlight;
    }

    async function deconnexion(options = {}) {
        const { appelerApi = true } = options;

        if (deconnexionInFlight) {
            return deconnexionInFlight;
        }

        deconnexionInFlight = (async () => {
            const tokenPresent = Boolean(localStorage.getItem("auth_token"));

            if (appelerApi && tokenPresent) {
                try {
                    await api.post("/auth/logout");
                } catch (_) {
                    // La déconnexion locale doit toujours fonctionner même si l'API échoue.
                }
            }

            supprimerToken();
            utilisateur.value = null;
            resolved.value = false;
        })();

        try {
            await deconnexionInFlight;
        } finally {
            deconnexionInFlight = null;
        }
    }

    function supprimerToken() {
        localStorage.removeItem("auth_token");
        delete api.defaults.headers.common.Authorization;
    }

    function definirToken(token) {
        localStorage.setItem("auth_token", token);
    }

    return {
        utilisateur,
        resolved,
        espaceCourant,
        estConnecte,
        estAdministrateur,
        estMedecin,
        estDansEspaceMedecin,
        estDansEspacePersonnel,
        nomUtilisateur,
        photoProfil,
        roleUtilisateur,
        aProfilSante,
        appliquerAuthentification,
        chargerUtilisateur,
        deconnexion,
        definirPresenceProfilSante,
        mettreAJourUtilisateur,
        supprimerToken,
        definirToken,
    };
});
