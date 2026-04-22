<template>
    <div
        class="min-h-screen bg-gradient-to-br from-blue-50 via-blue-50 to-blue-100 flex items-center justify-center px-4 py-8"
    >
        <div class="w-full max-w-7xl">
            <div
                class="grid grid-cols-1 lg:grid-cols-2 gap-0 items-stretch rounded-[32px] overflow-hidden shadow-2xl bg-white"
            >
                <!-- Colonne gauche -->
                <div
                    class="hidden lg:flex flex-col items-center justify-center bg-gradient-to-br from-blue-500 via-blue-600 to-blue-700 text-white px-8 py-20 relative overflow-hidden"
                >
                    <div class="absolute top-0 right-0 w-96 h-96 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/2"></div>
                    <div class="absolute bottom-0 left-0 w-80 h-80 bg-white/5 rounded-full translate-y-1/2 -translate-x-1/2"></div>

                    <div class="relative z-10 text-center space-y-8">
                        <div class="flex justify-center mb-4">
                            <div class="w-24 h-24 rounded-2xl bg-white/15 backdrop-blur-lg flex items-center justify-center border border-white/20">
                                <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                                </svg>
                            </div>
                        </div>
                        <div class="space-y-4 max-w-sm">
                            <h2 class="text-5xl font-extrabold">HealthFlow</h2>
                            <p class="text-base leading-relaxed text-white/90">
                                Choisissez un nouveau mot de passe sécurisé pour protéger votre compte.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Colonne droite -->
                <div class="w-full flex items-center justify-center p-8 lg:p-12">
                    <div class="w-full max-w-md">

                        <!-- Logo mobile -->
                        <div class="lg:hidden text-center mb-8">
                            <div class="flex justify-center mb-3">
                                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center">
                                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" />
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Header -->
                        <div class="text-center mb-8">
                            <h1 class="text-3xl lg:text-4xl font-bold text-gray-900 mb-2">Nouveau mot de passe</h1>
                            <p class="text-sm text-gray-600">Entrez et confirmez votre nouveau mot de passe</p>
                        </div>

                        <!-- Messages -->
                        <AlertMessage :message="errorMessage" type="error" class="mb-6" />

                        <!-- Succès -->
                        <Transition name="fade">
                            <div v-if="resetOk" class="space-y-4">
                                <AlertMessage message="Votre mot de passe a été réinitialisé avec succès. Vous serez redirigé vers la connexion." type="success" />
                                <BaseButton
                                    type="button"
                                    variant="primary"
                                    size="lg"
                                    fullWidth
                                    @click="$router.push({ name: 'login' })"
                                >
                                    Aller à la connexion
                                </BaseButton>
                            </div>
                        </Transition>

                        <!-- Form -->
                        <form v-if="!resetOk" class="space-y-5" @submit.prevent="resetPassword">

                            <!-- Email (disabled) -->
                            <FormField label="Adresse e-mail">
                                <template #icon>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </template>
                                <input
                                    v-model="form.email"
                                    type="email"
                                    disabled
                                    class="w-full h-12 pl-12 pr-4 rounded-xl border border-gray-200 bg-gray-100 text-base text-gray-500 outline-none cursor-not-allowed"
                                />
                            </FormField>

                            <!-- Nouveau mot de passe -->
                            <FormField label="Nouveau mot de passe" :error="errors.password">
                                <template #icon>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </template>
                                <div class="relative">
                                    <input
                                        v-model="form.password"
                                        :type="showPassword.new ? 'text' : 'password'"
                                        placeholder="••••••••"
                                        autocomplete="new-password"
                                        class="w-full h-12 pl-12 pr-12 rounded-xl border bg-gray-50 text-base text-gray-900 placeholder:text-gray-400 outline-none transition-all duration-200"
                                        :class="errors.password ? 'border-red-300 focus:border-red-500 focus:bg-white focus:ring-2 focus:ring-red-200' : 'border-gray-200 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-100'"
                                    />
                                    <EyeButton :visible="showPassword.new" @toggle="showPassword.new = !showPassword.new" />
                                </div>
                            </FormField>

                            <!-- Confirmer mot de passe -->
                            <FormField label="Confirmer le mot de passe" :error="errors.passwordConfirmation">
                                <template #icon>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </template>
                                <div class="relative">
                                    <input
                                        v-model="form.passwordConfirmation"
                                        :type="showPassword.confirmation ? 'text' : 'password'"
                                        placeholder="••••••••"
                                        autocomplete="new-password"
                                        class="w-full h-12 pl-12 pr-12 rounded-xl border bg-gray-50 text-base text-gray-900 placeholder:text-gray-400 outline-none transition-all duration-200"
                                        :class="errors.passwordConfirmation ? 'border-red-300 focus:border-red-500 focus:bg-white focus:ring-2 focus:ring-red-200' : 'border-gray-200 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-100'"
                                    />
                                    <EyeButton :visible="showPassword.confirmation" @toggle="showPassword.confirmation = !showPassword.confirmation" />
                                </div>
                            </FormField>

                            <BaseButton
                                type="submit"
                                variant="primary"
                                size="lg"
                                fullWidth
                                :disabled="loading"
                                :loading="loading"
                                class="mt-2"
                            >
                                {{ loading ? "Réinitialisation en cours..." : "Réinitialiser mon mot de passe" }}
                            </BaseButton>
                        </form>

                        <!-- Retour connexion -->
                        <p v-if="!resetOk" class="text-center text-sm text-gray-600 pt-6">
                            <button
                                type="button"
                                class="text-blue-600 font-semibold hover:text-blue-700 transition-colors"
                                @click="$router.push({ name: 'login' })"
                            >
                                Retour à la connexion
                            </button>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref, onMounted } from "vue";
import { useRouter, useRoute } from "vue-router";
import api from "@/services/api";
import { useNotificationsStore } from "@/stores/notifications";
import BaseButton from "@/components/ui/BaseButton.vue";
import AlertMessage from "@/components/ui/AlertMessage.vue";
import EyeButton from "@/components/ui/EyeButton.vue";
import FormField from "@/components/login/FormField.vue";

const router = useRouter();
const route = useRoute();
const notifications = useNotificationsStore();

const form = reactive({ email: "", password: "", passwordConfirmation: "", token: "" });
const showPassword = reactive({ new: false, confirmation: false });
const errors = reactive({ password: "", passwordConfirmation: "" });
const loading = ref(false);
const errorMessage = ref("");
const resetOk = ref(false);

onMounted(() => {
    const email = route.query.email;
    const token = route.query.token;

    if (!email || !token) {
        errorMessage.value = "Lien de réinitialisation invalide ou expiré.";
        setTimeout(() => router.push({ name: "login" }), 2000);
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
        errors.password = "Le mot de passe est obligatoire.";
        return;
    }
    if (form.password.length < 8) {
        errors.password = "Le mot de passe doit contenir au moins 8 caractères.";
        return;
    }
    if (form.password !== form.passwordConfirmation) {
        errors.passwordConfirmation = "Les mots de passe ne correspondent pas.";
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
            resetOk.value = true;
            notifications.success("Mot de passe réinitialisé avec succès.");
            setTimeout(() => router.push({ name: "login" }), 3000);
        }
    } catch (error) {
        if (error?.response?.data?.message) {
            errorMessage.value = error.response.data.message;
        } else if (error?.response?.data?.errors) {
            const apiErrors = error.response.data.errors;
            if (apiErrors.password) errors.password = apiErrors.password[0];
            if (apiErrors.password_confirmation) errors.passwordConfirmation = apiErrors.password_confirmation[0];
        } else {
            errorMessage.value = "Une erreur s'est produite. Veuillez réessayer.";
        }
        notifications.error(errorMessage.value || "Erreur lors de la réinitialisation.");
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
