<template>
    <div
        class="min-h-screen bg-gray-50 flex items-center justify-center px-4 py-8"
    >
        <!-- Contenu Connexion avec Retour -->
        <div class="w-full max-w-lg">
            <!-- Bouton Retour -->
            <button
                @click="$router.back()"
                class="flex items-center gap-1 text-gray-600 hover:text-gray-900 text-sm font-medium transition-colors mb-6 ml-6"
            >
                <svg
                    class="w-5 h-5"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M15 19l-7-7 7-7"
                    ></path>
                </svg>
                Retour
            </button>

            <div class="bg-white rounded-3xl shadow-lg p-10">
                <!-- Logo et Titre -->
                <div class="text-center mb-8">
                    <!-- Logo et nom HealthFlow -->
                    <div class="flex justify-center items-center gap-3 mb-6">
                        <div
                            class="w-12 h-12 rounded-lg bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center flex-shrink-0"
                        >
                            <svg
                                class="w-6 h-6 text-white"
                                fill="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"
                                ></path>
                            </svg>
                        </div>
                        <h1 class="text-3xl font-bold text-blue-600">
                            HealthFlow
                        </h1>
                    </div>

                    <h2 class="text-2xl font-bold text-gray-900 mb-2">
                        Bon retour !
                    </h2>
                    <p class="text-base text-gray-600">
                        Connectez-vous à votre compte
                    </p>
                </div>

                <!-- Messages d'erreur/succès -->
                <div
                    v-if="serverMessage"
                    class="rounded-lg border px-4 py-3 text-sm mb-6"
                    :class="
                        messageType === 'success'
                            ? 'border-purple-200 bg-purple-50 text-purple-700'
                            : 'border-red-200 bg-red-50 text-red-700'
                    "
                >
                    {{ serverMessage }}
                </div>

                <!-- Formulaire -->
                <form @submit.prevent="soumettre" class="space-y-5">
                    <!-- Champ Email -->
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                            >Email</label
                        >
                        <div class="relative">
                            <svg
                                class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                                ></path>
                            </svg>
                            <input
                                v-model.trim="form.email"
                                type="email"
                                placeholder="votre@email.com"
                                autocomplete="email"
                                class="w-full h-12 pl-12 pr-4 rounded-lg border bg-gray-50 text-base text-gray-900 placeholder:text-gray-400 outline-none transition-colors"
                                :class="
                                    errors.email
                                        ? 'border-red-300 focus:border-red-500 focus:bg-white'
                                        : 'border-gray-200 focus:border-blue-500 focus:bg-white'
                                "
                            />
                        </div>
                        <p
                            v-if="errors.email"
                            class="mt-1.5 text-sm text-red-600"
                        >
                            {{ errors.email }}
                        </p>
                    </div>

                    <!-- Champ Mot de passe -->
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                            >Mot de passe</label
                        >
                        <div class="relative">
                            <svg
                                class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
                                ></path>
                            </svg>
                            <input
                                v-model="form.password"
                                :type="mostrarPassword ? 'text' : 'password'"
                                placeholder="••••••••"
                                autocomplete="current-password"
                                class="w-full h-12 pl-12 pr-12 rounded-lg border bg-gray-50 text-base text-gray-900 placeholder:text-gray-400 outline-none transition-colors"
                                :class="
                                    errors.password
                                        ? 'border-red-300 focus:border-red-500 focus:bg-white'
                                        : 'border-gray-200 focus:border-blue-500 focus:bg-white'
                                "
                            />
                            <button
                                type="button"
                                @click="mostrarPassword = !mostrarPassword"
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors"
                            >
                                <svg
                                    v-if="!mostrarPassword"
                                    class="w-5 h-5"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                    ></path>
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                    ></path>
                                </svg>
                                <svg
                                    v-else
                                    class="w-5 h-5"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-4.803m5.596-3.856a3.375 3.375 0 11-4.753 4.753m4.753-4.753L3 3m9.621 9.621L3 21m12.621-12.621l4.243-4.243m0 0a9 9 0 10-12.728 12.728m12.728-12.728L21 3"
                                    ></path>
                                </svg>
                            </button>
                        </div>
                        <p
                            v-if="errors.password"
                            class="mt-1.5 text-sm text-red-600"
                        >
                            {{ errors.password }}
                        </p>
                    </div>

                    <!-- Checkbox et lien mot de passe oublié -->
                    <div class="flex items-center justify-between pt-2">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input
                                v-model="form.rememberMe"
                                type="checkbox"
                                class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                            />
                            <span class="text-sm text-gray-700"
                                >Se souvenir de moi</span
                            >
                        </label>
                        <button
                            type="button"
                            class="text-sm text-blue-600 hover:text-blue-700 transition cursor-pointer font-semibold"
                            @click="
                                $router.push({ name: 'oublier-mot-de-passe' })
                            "
                        >
                            Mot de passe oublié ?
                        </button>
                    </div>

                    <!-- Bouton Se connecter -->
                    <button
                        type="submit"
                        :disabled="loading"
                        class="w-full h-12 rounded-lg bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-semibold text-base transition-all disabled:opacity-50 disabled:cursor-not-allowed mt-6"
                    >
                        <span v-if="!loading">Se connecter</span>
                        <span v-else>Connexion...</span>
                    </button>

                    <!-- Séparateur -->
                    <div class="flex items-center gap-3 my-5">
                        <div class="flex-1 h-px bg-gray-200"></div>
                        <span class="text-sm text-gray-500">ou</span>
                        <div class="flex-1 h-px bg-gray-200"></div>
                    </div>

                    <!-- Bouton Google -->
                    <button
                        type="button"
                        class="w-full h-12 rounded-lg border border-gray-200 hover:border-gray-300 bg-white text-gray-700 font-medium text-base transition-colors flex items-center justify-center gap-2"
                    >
                        <svg class="w-5 h-5" viewBox="0 0 24 24">
                            <text
                                x="50%"
                                y="50%"
                                dominant-baseline="middle"
                                text-anchor="middle"
                                font-size="10"
                                fill="currentColor"
                                font-weight="bold"
                            >
                                G
                            </text>
                        </svg>
                        Continuer avec Google
                    </button>

                    <!-- Lien inscription -->
                    <p class="text-center text-sm text-gray-600 pt-4">
                        Pas encore de compte ?
                        <RouterLink
                            :to="{ name: 'inscription' }"
                            class="text-blue-600 font-semibold hover:text-blue-700 transition-colors"
                        >
                            Créer un compte
                        </RouterLink>
                    </p>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import api from "@/services/api";
import { onMounted, reactive, ref } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useAuthStore } from "@/stores/auth";

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();

const form = reactive({
    email: String(route.query.email || "").trim(),
    password: "",
    rememberMe: false,
});

const errors = reactive({
    email: "",
    password: "",
});

const loading = ref(false);
const serverMessage = ref("");
const messageType = ref("success");
const mostrarPassword = ref(false);

const REQUIRED_FORM_MESSAGE = "Veuillez remplir les champs obligatoires.";
const INVALID_EMAIL_MESSAGE = "Format d'email invalide.";
const INVALID_CREDENTIALS_MESSAGE = "Email ou mot de passe invalide.";

onMounted(() => {
    const notice = String(route.query.notice || "")
        .trim()
        .toLowerCase();
    if (notice === "inscription-reussie") {
        serverMessage.value =
            "Compte cree avec succes. Vous pouvez vous connecter.";
        messageType.value = "success";
    }
});

function effacerErreurs() {
    errors.email = "";
    errors.password = "";
}

function estEmailValide(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

function premierMessage(value) {
    if (Array.isArray(value)) return value[0] || "";
    if (typeof value === "string") return value;
    return "";
}

function mapFieldValidationErrors(validationErrors = {}) {
    for (const key of ["email", "password"]) {
        errors[key] = premierMessage(validationErrors[key]);
    }
}

async function soumettre() {
    serverMessage.value = "";
    messageType.value = "success";
    effacerErreurs();

    if (!form.email || !form.password) {
        if (!form.email) errors.email = "L'adresse email est obligatoire.";
        if (!form.password)
            errors.password = "Le mot de passe est obligatoire.";
        serverMessage.value = REQUIRED_FORM_MESSAGE;
        return;
    }

    if (!estEmailValide(form.email)) {
        errors.email = INVALID_EMAIL_MESSAGE;
        serverMessage.value = INVALID_EMAIL_MESSAGE;
        return;
    }

    loading.value = true;

    try {
        const res = await api.post("/auth/login", {
            email: form.email,
            password: form.password,
        });

        authStore.appliquerAuthentification(res?.data, "personnel");

        serverMessage.value = res?.data?.message || "Connexion reussie.";
        messageType.value = "success";

        setTimeout(() => {
            router.push(res?.data?.redirect_to || "/main/dashboard");
        }, 250);
    } catch (err) {
        messageType.value = "error";

        const data = err?.response?.data;
        const status = err?.response?.status;

        if (!err?.response) {
            serverMessage.value = "Probleme reseau. Reessayez.";
            return;
        }

        if (status === 401) {
            serverMessage.value = INVALID_CREDENTIALS_MESSAGE;
            errors.email = INVALID_CREDENTIALS_MESSAGE;
            errors.password = INVALID_CREDENTIALS_MESSAGE;
            return;
        }

        if (status === 422 && data?.errors) {
            mapFieldValidationErrors(data.errors);
            serverMessage.value =
                "Veuillez corriger les erreurs du formulaire.";
            return;
        }

        serverMessage.value = data?.message || "Erreur lors de la connexion.";
    } finally {
        loading.value = false;
    }
}
</script>
