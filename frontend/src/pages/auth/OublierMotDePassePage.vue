<template>
    <div
        class="flex min-h-screen items-center justify-center bg-gray-50 px-4 py-12 sm:px-6 lg:px-8"
    >
        <div class="w-full max-w-lg space-y-6">
            <!-- Button Retour -->
            <button
                type="button"
                class="flex items-center gap-2 text-sm font-semibold text-slate-600 transition hover:text-slate-900"
                @click="$router.push({ name: 'connexion' })"
            >
                <svg
                    viewBox="0 0 24 24"
                    class="h-4 w-4"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2.5"
                >
                    <path
                        d="m15 18-6-6 6-6"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />
                </svg>
                Retour à la connexion
            </button>

            <!-- Header -->
            <div class="flex items-center gap-2">
                <div
                    class="flex h-12 w-12 items-center justify-center rounded-lg bg-gradient-to-br from-purple-600 to-purple-700"
                >
                    <svg
                        viewBox="0 0 24 24"
                        class="h-6 w-6 stroke-white"
                        fill="none"
                        stroke-width="2"
                    >
                        <circle cx="12" cy="12" r="10" />
                        <path d="M12 6v6l4 2" />
                    </svg>
                </div>
                <div>
                    <h1
                        class="text-4xl font-extrabold bg-gradient-to-r from-purple-600 to-purple-700 bg-clip-text text-transparent"
                    >
                        HealthFlow
                    </h1>
                </div>
            </div>

            <!-- Content -->
            <div>
                <h2 class="text-2xl font-bold text-gray-900">
                    Mot de passe oublié ?
                </h2>
                <p class="mt-2 text-sm text-gray-600">
                    Entrez votre adresse email et nous vous enverrons un lien
                    pour réinitialiser votre mot de passe.
                </p>
            </div>

            <!-- Form -->
            <form class="space-y-4" @submit.prevent="demanderReinit">
                <div>
                    <input
                        v-model="formulaire.email"
                        type="email"
                        placeholder="votre.email@exemple.com"
                        class="h-12 w-full rounded-xl border border-gray-200 bg-gray-50 px-4 text-sm placeholder:text-gray-400 focus:border-purple-500 focus:bg-white focus:outline-none"
                    />
                    <p v-if="erreurs.email" class="mt-2 text-xs text-rose-600">
                        {{ erreurs.email }}
                    </p>
                </div>

                <button
                    type="submit"
                    :disabled="chargement"
                    class="h-12 w-full rounded-2xl bg-gradient-to-r from-purple-600 to-purple-700 font-semibold text-white transition hover:shadow-lg disabled:cursor-not-allowed disabled:opacity-50"
                >
                    {{ chargement ? "Envoi en cours..." : "Envoyer le lien" }}
                </button>
            </form>

            <!-- Message de succès -->
            <Transition name="fade">
                <div
                    v-if="messageSucces"
                    class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"
                >
                    {{ messageSucces }}
                </div>
            </Transition>

            <!-- Message d'erreur -->
            <Transition name="fade">
                <div
                    v-if="messageErreur"
                    class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700"
                >
                    {{ messageErreur }}
                </div>
            </Transition>

            <!-- Info box -->
            <div class="rounded-xl bg-purple-50 p-4 text-sm text-purple-800">
                <p class="font-semibold">💡 Conseil utile</p>
                <p class="mt-1">
                    Vérifiez votre dossier spam si vous ne recevez pas l'email
                    de réinitialisation.
                </p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref } from "vue";
import { useRouter } from "vue-router";
import api from "@/services/api";
import { useNotificationsStore } from "@/stores/notifications";

const router = useRouter();
const notifications = useNotificationsStore();

const formulaire = reactive({
    email: "",
});

const erreurs = reactive({
    email: "",
});

const chargement = ref(false);
const messageSucces = ref("");
const messageErreur = ref("");

async function demanderReinit() {
    erreurs.email = "";
    messageSucces.value = "";
    messageErreur.value = "";

    if (!formulaire.email) {
        erreurs.email = "L'email est requis.";
        return;
    }

    chargement.value = true;
    try {
        const response = await api.post("/auth/oublier-mot-de-passe", {
            email: formulaire.email,
        });

        if (response?.data) {
            messageSucces.value =
                "Un lien de réinitialisation a été envoyé à votre email.";
            notifications.succes(
                "Vérifiez votre boîte mail pour le lien de réinitialisation.",
            );
            formulaire.email = "";

            // Rediriger après 3 secondes
            setTimeout(() => {
                router.push({ name: "connexion" });
            }, 3000);
        }
    } catch (error) {
        if (error?.response?.data?.errors?.email) {
            erreurs.email = error.response.data.errors.email[0];
        } else if (error?.response?.data?.message) {
            messageErreur.value = error.response.data.message;
        } else {
            messageErreur.value =
                "Une erreur est survenue. Veuillez réessayer.";
        }
        notifications.erreur(messageErreur.value || erreurs.email);
    } finally {
        chargement.value = false;
    }
}
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
