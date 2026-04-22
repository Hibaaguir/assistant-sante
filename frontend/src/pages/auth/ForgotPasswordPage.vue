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
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                                </svg>
                            </div>
                        </div>
                        <div class="space-y-4 max-w-sm">
                            <h2 class="text-5xl font-extrabold">HealthFlow</h2>
                            <p class="text-base leading-relaxed text-white/90">
                                Pas d'inquiétude — entrez votre adresse e-mail et nous vous enverrons un lien pour récupérer votre accès.
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
                            <h1 class="text-3xl lg:text-4xl font-bold text-gray-900 mb-2">Mot de passe oublié</h1>
                            <p class="text-sm text-gray-600">Entrez votre e-mail pour recevoir un lien de réinitialisation</p>
                        </div>

                        <!-- Messages -->
                        <AlertMessage :message="errorMessage" type="error" class="mb-6" />

                        <!-- Form -->
                        <form class="space-y-5" @submit.prevent="requestPasswordReset">

                            <FormField label="Adresse e-mail" :error="errors.email">
                                <template #icon>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </template>
                                <input
                                    v-model.trim="form.email"
                                    type="email"
                                    placeholder="votre@email.com"
                                    autocomplete="email"
                                    class="w-full h-12 pl-12 pr-4 rounded-xl border bg-gray-50 text-base text-gray-900 placeholder:text-gray-400 outline-none transition-all duration-200"
                                    :class="errors.email ? 'border-red-300 focus:border-red-500 focus:bg-white focus:ring-2 focus:ring-red-200' : 'border-gray-200 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-100'"
                                />
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
                                {{ loading ? "Envoi en cours..." : "Réinitialiser mon mot de passe" }}
                            </BaseButton>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref } from "vue";
import { useRouter } from "vue-router";
import api from "@/services/api";
import { useNotificationsStore } from "@/stores/notifications";
import BaseButton from "@/components/ui/BaseButton.vue";
import AlertMessage from "@/components/ui/AlertMessage.vue";
import FormField from "@/components/login/FormField.vue";

const router = useRouter();
const notifications = useNotificationsStore();

const form = reactive({ email: "" });
const errors = reactive({ email: "" });
const loading = ref(false);
const errorMessage = ref("");

async function requestPasswordReset() {
    errors.email = "";
    errorMessage.value = "";

    if (!form.email) {
        errors.email = "L'adresse e-mail est obligatoire.";
        return;
    }

    loading.value = true;
    try {
        await api.post("/auth/forgot-password", { email: form.email });
        notifications.success("Vérifiez votre boîte de messagerie pour le lien de réinitialisation.");
        form.email = "";
        setTimeout(() => router.push({ name: "login" }), 3000);
    } catch (error) {
        if (error?.response?.data?.errors?.email) {
            errors.email = error.response.data.errors.email[0];
        } else if (error?.response?.data?.message) {
            errorMessage.value = error.response.data.message;
        } else {
            errorMessage.value = "Une erreur s'est produite. Veuillez réessayer.";
        }
        notifications.error(errorMessage.value || errors.email);
    } finally {
        loading.value = false;
    }
}
</script>
