<template>
    <Teleport to="body">
        <Transition name="modal-fade">
            <div
                v-if="estOuvert"
                class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/20 backdrop-blur-sm"
            >
                <div
                    class="relative max-h-[90vh] w-full max-w-md overflow-y-auto rounded-2xl bg-white p-6 shadow-xl"
                >
                    <!-- Fermer -->
                    <button
                        type="button"
                        class="absolute right-4 top-4 text-slate-400 transition hover:text-slate-600"
                        aria-label="Fermer le modal"
                        @click="fermer"
                    >
                        <svg
                            viewBox="0 0 24 24"
                            class="h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2.2"
                        >
                            <path
                                d="m6 6 12 12M18 6 6 18"
                                stroke-linecap="round"
                            />
                        </svg>
                    </button>

                    <!-- En-tête -->
                    <div class="mb-6">
                        <h2
                            class="text-[24px] font-semibold leading-none text-slate-900"
                        >
                            Modification du profil
                        </h2>
                        <p class="mt-2 text-sm text-slate-600">
                            Mettez à jour vos informations personnelles
                        </p>
                    </div>

                    <!-- Onglets -->
                    <div class="mb-6 flex gap-2 border-b border-slate-200">
                        <button
                            v-for="onglet in ONGLETS"
                            :key="onglet.id"
                            type="button"
                            class="pb-3 font-semibold transition"
                            :class="
                                ongletActif === onglet.id
                                    ? 'border-b-2 border-purple-600 text-purple-600'
                                    : 'text-slate-600 hover:text-slate-900'
                            "
                            @click="ongletActif = onglet.id"
                        >
                            {{ onglet.label }}
                        </button>
                    </div>

                    <!-- Formulaire -->
                    <form class="space-y-4" @submit.prevent="traiterFormulaire">
                        <!-- Tab : Informations -->
                        <div v-if="ongletActif === 'nom'" class="space-y-4">
                            <!-- Photo -->
                            <div>
                                <p
                                    class="block text-sm font-semibold text-slate-700"
                                >
                                    Photo de profil
                                </p>
                                <div class="mt-2 flex items-center gap-4">
                                    <div
                                        class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-full bg-gradient-to-br from-purple-500 to-purple-600 text-white"
                                    >
                                        <img
                                            v-if="photoApercu"
                                            :src="photoApercu"
                                            alt="Photo de profil"
                                            class="h-16 w-16 rounded-full object-cover"
                                        />
                                        <UserIcon v-else class="h-8 w-8" />
                                    </div>
                                    <div class="flex flex-wrap gap-2">
                                        <input
                                            ref="inputPhoto"
                                            type="file"
                                            accept="image/png,image/jpeg,image/webp"
                                            class="hidden"
                                            @change="selectionnerPhoto"
                                        />
                                        <button
                                            type="button"
                                            class="inline-flex h-10 items-center rounded-xl border border-slate-200 px-3 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 disabled:opacity-50"
                                            :disabled="chargement.photo"
                                            @click="ouvrirSelecteurPhoto"
                                        >
                                            {{
                                                photoApercu
                                                    ? "Modifier la photo"
                                                    : "Ajouter une photo"
                                            }}
                                        </button>
                                        <button
                                            v-if="photoApercu"
                                            type="button"
                                            class="inline-flex h-10 items-center rounded-xl border border-rose-200 px-3 text-xs font-semibold text-rose-600 transition hover:bg-rose-50 disabled:opacity-50"
                                            :disabled="chargement.photo"
                                            @click="supprimerPhoto"
                                        >
                                            Supprimer
                                        </button>
                                    </div>
                                </div>
                                <p
                                    v-if="erreurs.photo"
                                    class="mt-2 text-xs text-rose-600"
                                >
                                    {{ erreurs.photo }}
                                </p>
                            </div>

                            <!-- Nom -->
                            <div>
                                <label
                                    for="nom"
                                    class="block text-sm font-semibold text-slate-700"
                                    >Nom d'utilisateur</label
                                >
                                <input
                                    id="nom"
                                    v-model="formulaire.nom"
                                    type="text"
                                    placeholder="Votre nom complet"
                                    class="mt-2 h-12 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm transition placeholder:text-slate-400 focus:border-purple-500 focus:bg-white focus:outline-none"
                                />
                                <p
                                    v-if="erreurs.nom"
                                    class="mt-2 text-xs text-rose-600"
                                >
                                    {{ erreurs.nom }}
                                </p>
                            </div>

                            <BoutonSoumettre
                                :chargement="chargement.nom"
                                :disabled="formulaire.nom === nomOriginal"
                            >
                                {{
                                    chargement.nom
                                        ? "Enregistrement..."
                                        : "Enregistrer les modifications"
                                }}
                            </BoutonSoumettre>
                        </div>

                        <!-- Tab : Sécurité -->
                        <div v-if="ongletActif === 'mdp'" class="space-y-4">
                            <ChampMotDePasse
                                id="mdp-actuel"
                                v-model="formulaire.motDePasseActuel"
                                v-model:visible="afficherMotDePasse.actuel"
                                label="Mot de passe actuel"
                                :erreur="erreurs.motDePasseActuel"
                            />
                            <ChampMotDePasse
                                id="nouveau-mdp"
                                v-model="formulaire.nouveauMotDePasse"
                                v-model:visible="afficherMotDePasse.nouveau"
                                label="Nouveau mot de passe"
                                :erreur="erreurs.nouveauMotDePasse"
                            />
                            <ChampMotDePasse
                                id="confirm-mdp"
                                v-model="formulaire.confirmationMotDePasse"
                                v-model:visible="
                                    afficherMotDePasse.confirmation
                                "
                                label="Confirmez le mot de passe"
                                :erreur="erreurs.confirmationMotDePasse"
                            />

                            <BoutonSoumettre :chargement="chargement.mdp">
                                {{
                                    chargement.mdp
                                        ? "Mise à jour..."
                                        : "Changer le mot de passe"
                                }}
                            </BoutonSoumettre>
                        </div>
                    </form>

                    <!-- Message succès -->
                    <Transition name="fade">
                        <div
                            v-if="messageSucces"
                            class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"
                        >
                            {{ messageSucces }}
                        </div>
                    </Transition>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup>
import { ref, reactive, watch } from "vue";
import api from "@/services/api";
import { useAuthStore } from "@/stores/auth";
import { useNotificationsStore } from "@/stores/notifications";
import UserIcon from "@/components/navigation/UserIcon.vue";
import ChampMotDePasse from "@/components/profil/ChampMotDePasse.vue";
import BoutonSoumettre from "@/components/profil/BoutonSoumettre.vue";

defineProps({
    estOuvert: { type: Boolean, default: false },
});

const emit = defineEmits(["fermer", "profil-mis-a-jour"]);

const authStore = useAuthStore();
const notifications = useNotificationsStore();

// ─── Constantes ───────────────────────────────────────────────

const ONGLETS = [
    { id: "nom", label: "Informations" },
    { id: "mdp", label: "Sécurité" },
];

// ─── État ─────────────────────────────────────────────────────

const ongletActif = ref("nom");
const nomOriginal = ref(authStore.nomUtilisateur);
const messageSucces = ref("");
const inputPhoto = ref(null);
const photoApercu = ref(authStore.photoProfil || "");

const formulaire = reactive({
    nom: authStore.nomUtilisateur,
    motDePasseActuel: "",
    nouveauMotDePasse: "",
    confirmationMotDePasse: "",
});

const afficherMotDePasse = reactive({
    actuel: false,
    nouveau: false,
    confirmation: false,
});

const erreurs = reactive({
    nom: "",
    photo: "",
    motDePasseActuel: "",
    nouveauMotDePasse: "",
    confirmationMotDePasse: "",
});

const chargement = reactive({ nom: false, photo: false, mdp: false });

// ─── Helpers ──────────────────────────────────────────────────

const clearErreurs = () =>
    Object.keys(erreurs).forEach((k) => (erreurs[k] = ""));
const apiErreur = (err, champ) =>
    err?.response?.data?.errors?.[champ]?.[0] ??
    err?.response?.data?.message ??
    null;

function fermer() {
    messageSucces.value = "";
    clearErreurs();
    emit("fermer");
}

function traiterFormulaire() {
    clearErreurs();
    messageSucces.value = "";
    return ongletActif.value === "nom" ? mettreAJourNom() : changerMotDePasse();
}

watch(
    () => authStore.photoProfil,
    (v) => {
        photoApercu.value = v || "";
    },
    { immediate: true },
);

// ─── Nom ──────────────────────────────────────────────────────

async function mettreAJourNom() {
    const nom = formulaire.nom.trim();
    if (!nom) {
        erreurs.nom = "Le nom est requis.";
        return;
    }
    if (nom.length < 2) {
        erreurs.nom = "Le nom doit contenir au moins 2 caractères.";
        return;
    }
    if (nom.length > 120) {
        erreurs.nom = "Le nom ne peut pas dépasser 120 caractères.";
        return;
    }

    chargement.nom = true;
    try {
        await api.put("/profil-utilisateur/nom", { nom });
        authStore.mettreAJourUtilisateur({ name: nom });
        nomOriginal.value = nom;
        messageSucces.value = "Nom mis à jour avec succès!";
        notifications.succes("Votre profil a été mis à jour.");
        emit("profil-mis-a-jour");
        setTimeout(fermer, 1500);
    } catch (err) {
        erreurs.nom =
            apiErreur(err, "nom") ??
            "Une erreur est survenue lors de la mise à jour.";
        notifications.erreur(erreurs.nom);
    } finally {
        chargement.nom = false;
    }
}

// ─── Photo ────────────────────────────────────────────────────

const FORMATS_ACCEPTES = ["image/png", "image/jpeg", "image/webp"];

function ouvrirSelecteurPhoto() {
    erreurs.photo = "";
    inputPhoto.value?.click();
}

function toBase64(fichier) {
    return new Promise((resolve, reject) => {
        const r = new FileReader();
        r.onload = () => resolve(String(r.result || ""));
        r.onerror = () => reject(new Error("Impossible de lire le fichier."));
        r.readAsDataURL(fichier);
    });
}

async function selectionnerPhoto(event) {
    const fichier = event?.target?.files?.[0];
    if (!fichier) return;

    erreurs.photo = "";
    if (!FORMATS_ACCEPTES.includes(fichier.type)) {
        erreurs.photo = "Format non supporté. Utilisez PNG, JPG ou WEBP.";
        return;
    }
    if (fichier.size > 2 * 1024 * 1024) {
        erreurs.photo = "La photo ne doit pas dépasser 2 Mo.";
        return;
    }

    chargement.photo = true;
    try {
        const base64 = await toBase64(fichier);
        const { data } = await api.put("/profil-utilisateur/photo", {
            photo: base64,
        });
        const photo = data?.data?.photo_profil || base64;
        photoApercu.value = photo;
        authStore.mettreAJourUtilisateur({ profile_photo: photo });
        messageSucces.value = "Photo de profil mise à jour avec succès!";
        notifications.succes("Photo de profil mise à jour.");
        emit("profil-mis-a-jour");
    } catch (err) {
        erreurs.photo =
            apiErreur(err, "photo") ??
            "Une erreur est survenue lors de la mise à jour de la photo.";
        notifications.erreur(erreurs.photo);
    } finally {
        chargement.photo = false;
        if (event?.target) event.target.value = "";
    }
}

async function supprimerPhoto() {
    chargement.photo = true;
    erreurs.photo = "";
    try {
        await api.delete("/profil-utilisateur/photo");
        photoApercu.value = "";
        authStore.mettreAJourUtilisateur({ profile_photo: null });
        messageSucces.value = "Photo de profil supprimée avec succès!";
        notifications.succes("Photo de profil supprimée.");
        emit("profil-mis-a-jour");
    } catch (err) {
        erreurs.photo =
            apiErreur(err, "photo") ??
            "Une erreur est survenue lors de la suppression de la photo.";
        notifications.erreur(erreurs.photo);
    } finally {
        chargement.photo = false;
    }
}

// ─── Mot de passe ─────────────────────────────────────────────

async function changerMotDePasse() {
    const { motDePasseActuel, nouveauMotDePasse, confirmationMotDePasse } =
        formulaire;

    if (!motDePasseActuel) {
        erreurs.motDePasseActuel = "Le mot de passe actuel est requis.";
        return;
    }
    if (!nouveauMotDePasse) {
        erreurs.nouveauMotDePasse = "Le nouveau mot de passe est requis.";
        return;
    }
    if (nouveauMotDePasse.length < 8) {
        erreurs.nouveauMotDePasse =
            "Le mot de passe doit contenir au moins 8 caractères.";
        return;
    }
    if (nouveauMotDePasse !== confirmationMotDePasse) {
        erreurs.confirmationMotDePasse =
            "Les mots de passe ne correspondent pas.";
        return;
    }

    chargement.mdp = true;
    try {
        await api.post("/profil-utilisateur/changer-mot-de-passe", {
            mot_de_passe_actuel: motDePasseActuel,
            nouveau_mot_de_passe: nouveauMotDePasse,
            nouveau_mot_de_passe_confirmation: confirmationMotDePasse,
        });
        messageSucces.value = "Mot de passe changé avec succès!";
        formulaire.motDePasseActuel =
            formulaire.nouveauMotDePasse =
            formulaire.confirmationMotDePasse =
                "";
        notifications.succes("Votre mot de passe a été mis à jour.");
        setTimeout(fermer, 1500);
    } catch (err) {
        const status = err?.response?.status;
        const apiErrs = err?.response?.data?.errors;
        if (status === 422) {
            erreurs.motDePasseActuel =
                apiErrs?.mot_de_passe_actuel?.[0] ??
                err?.response?.data?.message ??
                "Le mot de passe actuel est incorrect.";
            erreurs.nouveauMotDePasse =
                apiErrs?.nouveau_mot_de_passe?.[0] ?? "";
        } else {
            erreurs.motDePasseActuel =
                "Une erreur est survenue lors du changement du mot de passe.";
        }
        notifications.erreur(
            erreurs.motDePasseActuel ||
                "Erreur lors du changement du mot de passe.",
        );
    } finally {
        chargement.mdp = false;
    }
}
</script>

<style scoped>
.modal-fade-enter-active,
.modal-fade-leave-active {
    transition: opacity 0.3s ease;
}
.modal-fade-enter-from,
.modal-fade-leave-to {
    opacity: 0;
}
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
