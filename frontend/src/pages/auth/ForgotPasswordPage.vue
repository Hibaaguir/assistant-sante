<template>
    <div
        class="flex min-h-screen items-center justify-center bg-gray-50 px-4 py-12 sm:px-6 lg:px-8"
    >
        <div class="w-full max-w-lg space-y-6">
            <!-- Back Button -->
            <BaseButton
                type="button"
                variant="outline"
                size="sm"
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
            </BaseButton>

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
                    <Typography tag="h1" variant="h1-style">
                        HealthFlow
                    </Typography>
                </div>
            </div>

            <!-- Content -->
            <div>
                <Typography tag="h2" variant="h2-style">
                    Vous avez oublié votre mot de passe ?
                </Typography>
                <Typography tag="p" variant="paragraph" class="mt-2">
                    Entrez votre adresse e-mail et nous vous enverrons un lien
                    pour réinitialiser votre mot de passe.
                </Typography>
            </div>

            <!-- Form -->
            <form class="space-y-4" @submit.prevent="requestPasswordReset">
                <div>
                    <input
                        v-model="form.email"
                        type="email"
                        placeholder="votre.email@exemple.com"
                        class="h-12 w-full rounded-xl border border-gray-200 bg-gray-50 px-4 text-sm placeholder:text-gray-400 focus:border-purple-500 focus:bg-white focus:outline-none"
                    />
                    <p v-if="errors.email" class="mt-2 text-xs text-rose-600">
                        {{ errors.email }}
                    </p>
                </div>

                <BaseButton
                    type="submit"
                    variant="primary"
                    size="lg"
                    :disabled="loading"
                    class="w-full"
                >
                    {{ loading ? "Envoi en cours..." : "Envoyer le lien" }}
                </BaseButton>
            </form>

            <!-- Success Message -->
            <Transition name="fade">
                <div
                    v-if="successMessage"
                    class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"
                >
                    {{ successMessage }}
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

            <!-- Info Box -->
            <div class="rounded-xl bg-purple-50 p-4 text-sm text-purple-800">
                <p class="font-semibold">💡 Conseil utile</p>
                <p class="mt-1">
                    Vérifiez votre dossier de spam si vous ne recevez pas
                    l'e-mail de réinitialisation.
                </p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref } from "vue";
import Typography from "@/components/ui/Typography.vue";
import { useRouter } from "vue-router";
import api from "@/services/api";
import { useNotificationsStore } from "@/stores/notifications";
import BaseButton from "@/components/ui/BaseButton.vue";

const router = useRouter();
const notifications = useNotificationsStore();

const form = reactive({
    email: "",
});

const errors = reactive({
    email: "",
});

const loading = ref(false);
const successMessage = ref("");
const errorMessage = ref("");

async function requestPasswordReset() {
    errors.email = "";
    successMessage.value = "";
    errorMessage.value = "";

    if (!form.email) {
        errors.email = "L'adresse e-mail est obligatoire.";
        return;
    }

    loading.value = true;
    try {
        const response = await api.post("/auth/forgot-password", {
            email: form.email,
        });

        if (response?.data) {
            successMessage.value =
                "Un lien de réinitialisation a été envoyé à votre e-mail.";
            notifications.success(
                "Vérifiez votre boîte de messagerie pour le lien de réinitialisation.",
            );
            form.email = "";

            // Redirect after 3 seconds
            setTimeout(() => {
                router.push({ name: "login" });
            }, 3000);
        }
    } catch (error) {
        if (error?.response?.data?.errors?.email) {
            errors.email = error.response.data.errors.email[0];
        } else if (error?.response?.data?.message) {
            errorMessage.value = error.response.data.message;
        } else {
            errorMessage.value =
                "Une erreur s'est produite. Veuillez réessayer.";
        }
        notifications.error(errorMessage.value || errors.email);
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
