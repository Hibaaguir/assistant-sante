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
                                    fill="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"
                                    />
                                </svg>
                            </div>
                        </div>

                        <!-- Title -->
                        <div class="space-y-4 max-w-sm">
                            <h2 class="text-5xl font-extrabold">HealthFlow</h2>
                            <p class="text-base leading-relaxed text-white/90">
                                Votre plateforme de santé numérique. Accédez à
                                vos données de santé et gérez votre bien-être
                                facilement.
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
                                        fill="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"
                                        />
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Header -->
                        <div class="text-center mb-8">
                            <h1
                                class="text-3xl lg:text-4xl font-bold text-gray-900 mb-2"
                            >
                                <Typography tag="h1" variant="h1-style">
                                    Connexion
                                </Typography>
                            </h1>
                            <p class="text-sm text-gray-600">
                                Accédez à votre compte HealthFlow
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

                        <!-- Form -->
                        <form class="space-y-5" @submit.prevent="submit">
                            <!-- Email -->
                            <FormField
                                label="Adresse e-mail"
                                :error="errors.email"
                            >
                                <template #icon>
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                                    />
                                </template>
                                <input
                                    v-model.trim="form.email"
                                    type="email"
                                    placeholder="votre@email.com"
                                    autocomplete="email"
                                    class="w-full h-12 pl-12 pr-4 rounded-xl border bg-gray-50 text-base text-gray-900 placeholder:text-gray-400 outline-none transition-all duration-200"
                                    :class="
                                        errors.email
                                            ? 'border-red-300 focus:border-red-500 focus:bg-white focus:ring-2 focus:ring-red-200'
                                            : 'border-gray-200 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-100'
                                    "
                                />
                            </FormField>

                            <!-- Password -->
                            <FormField
                                label="Mot de passe"
                                :error="errors.password"
                            >
                                <template #icon>
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
                                    />
                                </template>
                                <div class="relative">
                                    <input
                                        v-model="form.password"
                                        :type="
                                            showPassword ? 'text' : 'password'
                                        "
                                        placeholder="••••••••"
                                        autocomplete="current-password"
                                        class="w-full h-12 pl-12 pr-12 rounded-xl border bg-gray-50 text-base text-gray-900 placeholder:text-gray-400 outline-none transition-all duration-200"
                                        :class="
                                            errors.password
                                                ? 'border-red-300 focus:border-red-500 focus:bg-white focus:ring-2 focus:ring-red-200'
                                                : 'border-gray-200 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-100'
                                        "
                                    />
                                    <button
                                        type="button"
                                        @click="showPassword = !showPassword"
                                        class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700 transition-colors cursor-pointer"
                                    >
                                        <svg
                                            v-if="!showPassword"
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
                                            />
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                            />
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
                                            />
                                        </svg>
                                    </button>
                                </div>
                            </FormField>

                            <!-- Forgot password -->
                            <div class="flex items-center justify-end pt-1">
                                <button
                                    type="button"
                                    @click="
                                        $router.push({
                                            name: 'forgot-password',
                                        })
                                    "
                                    class="text-sm font-semibold text-blue-600 hover:text-blue-700 transition-colors"
                                >
                                    Mot de passe oublié ?
                                </button>
                            </div>

                            <!-- Submit Button -->
                            <BaseButton
                                type="submit"
                                variant="primary"
                                size="lg"
                                fullWidth
                                :disabled="loading"
                                :loading="loading"
                                class="mt-6"
                            >
                                {{
                                    loading
                                        ? "Connexion en cours..."
                                        : "Se connecter"
                                }}
                            </BaseButton>

                            <!-- Sign up link -->
                            <p class="text-center text-sm text-gray-600 pt-4">
                                Pas encore de compte ?
                                <RouterLink
                                    :to="{ name: 'register' }"
                                    class="text-blue-600 font-semibold hover:text-blue-700 transition-colors"
                                >
                                    Créer un compte
                                </RouterLink>
                            </p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { onMounted, reactive, ref } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useAuthStore } from "@/stores/auth";
import api from "@/services/api";
import FormField from "./FormField.vue";
import Typography from "@/components/ui/Typography.vue";
import BaseButton from "@/components/ui/BaseButton.vue";

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();

// ─── State ─────────────────────────────────────────────────────

const form = reactive({
    email: String(route.query.email || "").trim(),
    password: "",
});

const errors = reactive({ email: "", password: "" });

const loading = ref(false);
const serverMessage = ref("");
const messageType = ref("success");
const showPassword = ref(false);

// ─── Constants ───────────────────────────────────────────────

const MESSAGES = {
    required: "Veuillez remplir tous les champs obligatoires.",
    invalidEmail: "Format d'e-mail invalide.",
    credentials: "E-mail ou mot de passe invalide.",
    network: "Erreur réseau. Veuillez réessayer.",
    formErrors: "Veuillez corriger les erreurs du formulaire.",
    success: "Connexion réussie.",
};

// ─── Helpers ──────────────────────────────────────────────────

const isValidEmail = (v) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v);
const firstMsg = (v) => (Array.isArray(v) ? (v[0] ?? "") : String(v ?? ""));
const clearErrors = () => {
    errors.email = "";
    errors.password = "";
};

const setError = (msg, type = "error") => {
    serverMessage.value = msg;
    messageType.value = type;
};

// ─── Lifecycle ────────────────────────────────────────────────

onMounted(() => {
    if (
        String(route.query.notice || "")
            .trim()
            .toLowerCase() === "signup-successful"
    ) {
        setError(
            "Compte créé avec succès. Vous pouvez maintenant vous connecter.",
            "success",
        );
    }
});

// ─── Submission ───────────────────────────────────────────────

async function submit() {
    clearErrors();
    setError("", "success");

    // Local validation
    if (!form.email) errors.email = "L'adresse e-mail est obligatoire.";
    if (!form.password) errors.password = "Le mot de passe est obligatoire.";
    if (!form.email || !form.password) {
        setError(MESSAGES.required);
        return;
    }

    if (!isValidEmail(form.email)) {
        errors.email = MESSAGES.invalidEmail;
        setError(MESSAGES.invalidEmail);
        return;
    }

    loading.value = true;

    try {
        const { data } = await api.post("/auth/login", {
            email: form.email,
            password: form.password,
        });

        authStore.applyAuth(data);
        setError(data?.message || MESSAGES.success, "success");

        setTimeout(
            () => router.push(data?.redirect_to || "/main/dashboard"),
            250,
        );
    } catch (err) {
        const status = err?.response?.status;
        const data = err?.response?.data;

        if (!err?.response) {
            setError(MESSAGES.network);
            return;
        }
        if (status === 401) {
            setError(MESSAGES.credentials);
            errors.email = errors.password = MESSAGES.credentials;
            return;
        }
        if (status === 422 && data?.errors) {
            for (const key of ["email", "password"])
                errors[key] = firstMsg(data.errors[key]);
            setError(MESSAGES.formErrors);
            return;
        }

        setError(data?.message || "Erreur lors de la connexion.");
    } finally {
        loading.value = false;
    }
}
</script>
