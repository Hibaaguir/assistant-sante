<template>
    <div
        class="min-h-screen bg-gradient-to-br from-blue-50 via-blue-50 to-blue-100 flex items-center justify-center px-4 py-8"
    >
        <div class="w-full max-w-7xl">
            <div
                class="grid grid-cols-1 lg:grid-cols-2 gap-0 items-stretch rounded-[32px] overflow-hidden shadow-2xl bg-white"
            >
                <!-- Colonne gauche - Illustration & Texte avec gradient bleu -->
                <div
                    class="hidden lg:flex flex-col items-center justify-center bg-gradient-to-br from-blue-500 via-blue-600 to-blue-700 text-white px-8 py-20 relative overflow-hidden"
                >
                    <!-- Background decorative elements -->
                    <div
                        class="absolute top-0 right-0 w-96 h-96 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/2"
                    ></div>
                    <div
                        class="absolute bottom-0 left-0 w-80 h-80 bg-white/5 rounded-full translate-y-1/2 -translate-x-1/2"
                    ></div>

                    <div class="relative z-10 text-center space-y-8">
                        <!-- Icon/Illustration -->
                        <div class="flex justify-center mb-4">
                            <div
                                class="w-24 h-24 rounded-2xl bg-white/15 backdrop-blur-lg flex items-center justify-center border border-white/20"
                            >
                                <svg
                                    class="w-12 h-12 text-white"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"
                                    />
                                </svg>
                            </div>
                        </div>

                        <!-- Title -->
                        <div class="space-y-4 max-w-sm">
                            <h2 class="text-5xl font-extrabold">
                                Espace Médecin
                            </h2>
                            <p class="text-base leading-relaxed text-white/90">
                                Connectez-vous pour accéder au portail médecin
                                et gérer vos patients.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Colonne droite - Formulaire -->
                <div
                    class="w-full flex items-center justify-center p-8 lg:p-12"
                >
                    <div class="w-full max-w-md">
                        <!-- Logo mobile -->
                        <div class="lg:hidden text-center mb-8">
                            <div class="flex justify-center mb-3">
                                <div
                                    class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center"
                                >
                                    <svg
                                        class="w-6 h-6 text-white"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"
                                        />
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Header -->
                        <div class="text-center mb-8">
                            <p
                                class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-600 mb-2"
                            >
                                Espace Médecin
                            </p>
                            <h1
                                class="text-3xl lg:text-4xl font-bold text-gray-900 mb-2"
                            >
                                <Typography tag="h1" variant="h1-style">
                                    Connexion
                                </Typography>
                            </h1>
                            <p class="text-sm text-gray-600">
                                Connectez-vous avec vos identifiants
                            </p>
                        </div>

                        <!-- Server message -->
                        <div
                            v-if="serverMessage"
                            class="rounded-xl border px-4 py-3 text-sm mb-6 backdrop-blur-sm"
                            :class="
                                messageType === 'success'
                                    ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                                    : 'border-red-200 bg-red-50 text-red-700'
                            "
                        >
                            {{ serverMessage }}
                        </div>

                        <!-- Formulaire de connexion -->
                        <form @submit.prevent="handleSubmit" class="space-y-5">
                            <!-- Champ Email -->
                            <div>
                                <label class="mb-2 block text-base font-semibold text-gray-800">
                                    Adresse email
                                </label>
                                <div class="relative">
                                    <svg
                                        class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400 pointer-events-none"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                                        />
                                    </svg>
                                    <input
                                        v-model.trim="form.email"
                                        type="email"
                                        placeholder="medecin@exemple.com"
                                        autocomplete="email"
                                        class="h-12 w-full pl-12 pr-4 rounded-xl border bg-gray-50 text-gray-900 placeholder:text-gray-400 outline-none transition-all duration-200"
                                        :class="
                                            errors.email
                                                ? 'border-red-300 focus:border-red-500 focus:bg-white focus:ring-2 focus:ring-red-200'
                                                : 'border-gray-200 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-100'
                                        "
                                    />
                                </div>
                                <p
                                    v-if="errors.email"
                                    class="mt-2 text-sm text-red-600"
                                >
                                    {{ errors.email }}
                                </p>
                            </div>

                            <!-- Champ Mot de passe -->
                            <div>
                                <label class="mb-2 block text-base font-semibold text-gray-800">
                                    Mot de passe
                                </label>
                                <div class="relative">
                                    <svg
                                        class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400 pointer-events-none"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
                                        />
                                    </svg>
                                    <input
                                        v-model="form.password"
                                        type="password"
                                        placeholder="••••••••"
                                        autocomplete="current-password"
                                        class="h-12 w-full pl-12 pr-4 rounded-xl border bg-gray-50 text-gray-900 placeholder:text-gray-400 outline-none transition-all duration-200"
                                        :class="
                                            errors.password
                                                ? 'border-red-300 focus:border-red-500 focus:bg-white focus:ring-2 focus:ring-red-200'
                                                : 'border-gray-200 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-100'
                                        "
                                    />
                                </div>
                                <p
                                    v-if="errors.password"
                                    class="mt-2 text-sm text-red-600"
                                >
                                    {{ errors.password }}
                                </p>
                            </div>

                            <!-- Bouton de connexion -->
                            <button
                                type="submit"
                                :disabled="isLoading"
                                class="h-12 w-full rounded-xl bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-sm font-semibold text-white transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed mt-6 shadow-lg hover:shadow-xl"
                            >
                                <span v-if="!isLoading">Se connecter</span>
                                <span v-else>Connexion en cours...</span>
                            </button>
                        </form>

                        <!-- Lien vers l'inscription -->
                        <p class="mt-6 text-center text-sm text-gray-600">
                            Pas encore de compte ?
                            <RouterLink
                                :to="registerLink"
                                class="font-semibold text-blue-600 hover:text-blue-700 transition-colors"
                            >
                                Créer un compte médecin
                            </RouterLink>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import Typography from "@/components/ui/Typography.vue";
import BaseButton from "@/components/ui/BaseButton.vue";
import api from "@/services/api";
import { computed, reactive, ref } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useAuthStore } from "@/stores/auth";

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();

// ─── État du formulaire ─────────────────────────────────────────────────────
const form = reactive({
    email: String(route.query.email || "").trim(),
    password: "",
});

const errors = reactive({ email: "", password: "" });
const isLoading = ref(false);
const serverMessage = ref("");
const messageType = ref("success");

// Lien vers la page d'inscription médecin (avec email pré-rempli)
const registerLink = computed(() => ({
    name: "doctor-register",
    query: { email: form.email },
}));

// ─── Fonctions ─────────────────────────────────────────────────────────────

// Vider les messages d'erreur avant chaque tentative
function clearErrors() {
    errors.email = "";
    errors.password = "";
}

// Soumission du formulaire de connexion
async function handleSubmit() {
    serverMessage.value = "";
    messageType.value = "success";
    clearErrors();

    // Validation simple : vérifier que les champs ne sont pas vides
    if (!form.email) {
        errors.email = "L'adresse email est obligatoire.";
    }
    if (!form.password) {
        errors.password = "Le mot de passe est obligatoire.";
    }
    if (!form.email || !form.password) {
        serverMessage.value = "Veuillez remplir tous les champs obligatoires.";
        messageType.value = "error";
        return;
    }

    isLoading.value = true;

    try {
        // Appel API : connexion médecin
        const res = await api.post("/auth/doctor/login", {
            email: form.email,
            password: form.password,
        });

        authStore.applyAuth(res?.data);
        serverMessage.value = res?.data?.message || "Connexion réussie.";
        messageType.value = "success";

        // Redirection vers le tableau de bord après un court délai
        setTimeout(() => router.push("/main/dashboard"), 250);
    } catch (err) {
        messageType.value = "error";
        const data = err?.response?.data;
        const status = err?.response?.status;

        if (status === 422 && data?.errors) {
            // Erreurs de validation du formulaire
            errors.email = data.errors.email?.[0] ?? "";
            errors.password = data.errors.password?.[0] ?? "";
            serverMessage.value =
                "Veuillez corriger les erreurs du formulaire.";
        } else {
            // Erreur générale (mauvais identifiants, serveur, etc.)
            serverMessage.value =
                data?.message || "Erreur lors de la connexion.";
        }
    } finally {
        isLoading.value = false;
    }
}
</script>
