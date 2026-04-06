<template>
    <div
        class="flex min-h-screen items-center justify-center bg-gray-50 px-4 py-12 sm:px-6 lg:px-8"
    >
        <div class="w-full max-w-lg space-y-6">
            <!-- Back Button -->
            <button
                type="button"
                class="flex items-center gap-2 text-sm font-semibold text-slate-600 transition hover:text-slate-900"
                @click="$router.push({ name: 'login' })"
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
            <div v-if="!resetOk">
                <h2 class="text-2xl font-bold text-gray-900">
                    Réinitialiser votre mot de passe
                </h2>
                <p class="mt-2 text-sm text-gray-600">
                    Entrez votre nouveau mot de passe pour réinitialiser l'accès
                    à votre compte.
                </p>
            </div>

            <!-- Form -->
            <form
                v-if="!resetOk"
                class="space-y-4"
                @submit.prevent="resetPassword"
            >
                <div>
                    <label
                        for="email"
                        class="block text-sm font-semibold text-gray-700"
                        >Adresse e-mail</label
                    >
                    <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        placeholder="votre.email@exemple.com"
                        disabled
                        class="mt-2 h-12 w-full rounded-xl border border-gray-200 bg-gray-100 px-4 text-sm text-gray-500 placeholder:text-gray-400 focus:outline-none"
                    />
                </div>

                <div>
                    <label
                        for="password"
                        class="block text-sm font-semibold text-gray-700"
                        >Nouveau mot de passe</label
                    >
                    <div class="relative mt-2">
                        <input
                            id="password"
                            v-model="form.password"
                            :type="showPassword.new ? 'text' : 'password'"
                            placeholder="••••••••"
                            class="h-12 w-full rounded-xl border border-gray-200 bg-gray-50 px-4 text-sm placeholder:text-gray-400 focus:border-purple-500 focus:bg-white focus:outline-none"
                        />
                        <button
                            type="button"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                            @click="showPassword.new = !showPassword.new"
                        >
                            <svg
                                v-if="showPassword.new"
                                viewBox="0 0 24 24"
                                class="h-5 w-5"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path
                                    d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"
                                />
                                <circle cx="12" cy="12" r="3" />
                            </svg>
                            <svg
                                v-else
                                viewBox="0 0 24 24"
                                class="h-5 w-5"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path
                                    d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"
                                />
                                <line x1="1" y1="1" x2="23" y2="23" />
                            </svg>
                        </button>
                    </div>
                    <p
                        v-if="errors.password"
                        class="mt-2 text-xs text-rose-600"
                    >
                        {{ errors.password }}
                    </p>
                </div>

                <div>
                    <label
                        for="confirm-password"
                        class="block text-sm font-semibold text-gray-700"
                        >Confirmer le mot de passe</label
                    >
                    <div class="relative mt-2">
                        <input
                            id="confirm-password"
                            v-model="form.passwordConfirmation"
                            :type="
                                showPassword.confirmation ? 'text' : 'password'
                            "
                            placeholder="••••••••"
                            class="h-12 w-full rounded-xl border border-gray-200 bg-gray-50 px-4 text-sm placeholder:text-gray-400 focus:border-purple-500 focus:bg-white focus:outline-none"
                        />
                        <button
                            type="button"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                            @click="
                                showPassword.confirmation =
                                    !showPassword.confirmation
                            "
                        >
                            <svg
                                v-if="showPassword.confirmation"
                                viewBox="0 0 24 24"
                                class="h-5 w-5"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path
                                    d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"
                                />
                                <circle cx="12" cy="12" r="3" />
                            </svg>
                            <svg
                                v-else
                                viewBox="0 0 24 24"
                                class="h-5 w-5"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path
                                    d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"
                                />
                                <line x1="1" y1="1" x2="23" y2="23" />
                            </svg>
                        </button>
                    </div>
                    <p
                        v-if="errors.passwordConfirmation"
                        class="mt-2 text-xs text-rose-600"
                    >
                        {{ errors.passwordConfirmation }}
                    </p>
                </div>

                <button
                    type="submit"
                    :disabled="loading"
                    class="h-12 w-full rounded-2xl bg-gradient-to-r from-purple-600 to-purple-700 font-semibold text-white transition hover:shadow-lg disabled:cursor-not-allowed disabled:opacity-50"
                >
                    {{ loading ? "Resetting..." : "Reset my password" }}
                </button>
            </form>

            <!-- Success Message -->
            <Transition name="fade">
                <div v-if="resetOk" class="space-y-4">
                    <div
                        class="rounded-xl border border-emerald-200 bg-emerald-50 p-4"
                    >
                        <div class="flex gap-3">
                            <div
                                class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-emerald-100"
                            >
                                <svg
                                    viewBox="0 0 24 24"
                                    class="h-5 w-5 text-emerald-600"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <polyline points="20 6 9 17 4 12" />
                                </svg>
                            </div>
                            <div>
                                <p class="font-semibold text-emerald-900">
                                    Success!
                                </p>
                                <p class="mt-1 text-sm text-emerald-700">
                                    Your password has been reset successfully.
                                </p>
                                <p class="mt-2 text-xs text-emerald-600">
                                    You will be redirected to the login page
                                    shortly.
                                </p>
                            </div>
                        </div>
                    </div>
                    <button
                        type="button"
                        class="h-12 w-full rounded-2xl bg-gradient-to-r from-purple-600 to-purple-700 font-semibold text-white transition hover:shadow-lg"
                        @click="$router.push({ name: 'login' })"
                    >
                        Go to Login
                    </button>
                </div>
            </Transition>

            <!-- Error Message -->
            <Transition name="fade">
                <div
                    v-if="errorMessage"
                    class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700"
                >
                    {{ errorMessage }}
                </div>
            </Transition>

            <!-- Info box -->
            <div class="rounded-xl bg-purple-50 p-4 text-sm text-purple-800">
                <p class="font-semibold">🔒 Security</p>
                <p class="mt-1">
                    We will never ask you for your password via email. Keep it
                    secret.
                </p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref, onMounted } from "vue";
import { useRouter, useRoute } from "vue-router";
import api from "@/services/api";
import { useNotificationsStore } from "@/stores/notifications";

const router = useRouter();
const route = useRoute();
const notifications = useNotificationsStore();

const form = reactive({
    email: "",
    password: "",
    passwordConfirmation: "",
    token: "",
});

const showPassword = reactive({
    new: false,
    confirmation: false,
});

const errors = reactive({
    password: "",
    passwordConfirmation: "",
});

const loading = ref(false);
const errorMessage = ref("");
const resetOk = ref(false);
const userRole = ref(null);

onMounted(() => {
    const email = route.query.email;
    const token = route.query.token;

    if (!email || !token) {
        errorMessage.value = "Invalid or expired reset link.";
        setTimeout(() => {
            router.push({ name: "login" });
        }, 2000);
        return;
    }

    form.email = email;
    form.token = token;
});

async function resetPassword() {
    errors.password = "";
    errors.passwordConfirmation = "";
    errorMessage.value = "";

    if (!form.password) {
        errors.password = "Password is required.";
        return;
    }

    if (form.password.length < 8) {
        errors.password = "Password must contain at least 8 characters.";
        return;
    }

    if (form.password !== form.passwordConfirmation) {
        errors.passwordConfirmation = "Passwords do not match.";
        return;
    }

    loading.value = true;
    try {
        const response = await api.post("/auth/reset-password", {
            email: form.email,
            token: form.token,
            password: form.password,
            password_confirmation: form.passwordConfirmation,
        });

        if (response?.data) {
            const role = response?.data?.data?.role;
            userRole.value = role;
            resetOk.value = true;
            notifications.success("Your password has been reset successfully.");

            // All users go to the same login page
            setTimeout(() => {
                router.push({ name: "login" });
            }, 3000);
        }
    } catch (error) {
        if (error?.response?.data?.message) {
            errorMessage.value = error.response.data.message;
        } else if (error?.response?.data?.errors) {
            const apiErrors = error.response.data.errors;
            if (apiErrors.password) {
                errors.password = apiErrors.password[0];
            }
            if (apiErrors.password_confirmation) {
                errors.passwordConfirmation =
                    apiErrors.password_confirmation[0];
            }
        } else {
            errorMessage.value = "An error occurred. Please try again.";
        }
        notifications.error(errorMessage.value || "Error resetting password.");
    } finally {
        loading.value = false;
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
