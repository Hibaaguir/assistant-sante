<template>
    <div class="min-h-screen bg-[#f5f7fb] px-4 py-10">
        <div class="mx-auto max-w-md">
            <div
                class="rounded-[24px] border border-slate-200 bg-white p-8 shadow-[0_18px_45px_rgba(15,23,42,0.06)]"
            >
                <!-- En-tête -->
                <p
                    class="text-xs font-semibold uppercase tracking-[0.18em] text-sky-700"
                >
                    Espace Médecin
                </p>
                <h1
                    class="text-4xl font-extrabold bg-gradient-to-r from-purple-600 to-purple-700 bg-clip-text text-transparent sm:text-5xl"
                >
                    Connexion
                </h1>
                <p class="mt-2 text-sm leading-6 text-slate-600">
                    Connectez-vous avec votre email et mot de passe.
                </p>

                <!-- Formulaire de connexion -->
                <form @submit.prevent="handleSubmit" class="mt-8 space-y-4">
                    <!-- Champ Email -->
                    <div>
                        <label
                            class="mb-2 block text-sm font-medium text-slate-700"
                        >
                            Adresse email
                        </label>
                        <input
                            v-model.trim="form.email"
                            type="email"
                            placeholder="medecin@exemple.com"
                            autocomplete="email"
                            class="h-12 w-full rounded-2xl border px-4 text-slate-900 outline-none transition"
                            :class="
                                errors.email
                                    ? 'border-red-300 focus:border-red-400'
                                    : 'border-slate-200 focus:border-sky-500'
                            "
                        />
                        <p
                            v-if="errors.email"
                            class="mt-2 text-sm text-red-600"
                        >
                            {{ errors.email }}
                        </p>
                    </div>

                    <!-- Champ Mot de passe -->
                    <div>
                        <label
                            class="mb-2 block text-sm font-medium text-slate-700"
                        >
                            Mot de passe
                        </label>
                        <input
                            v-model="form.password"
                            type="password"
                            placeholder="Votre mot de passe"
                            autocomplete="current-password"
                            class="h-12 w-full rounded-2xl border px-4 text-slate-900 outline-none transition"
                            :class="
                                errors.password
                                    ? 'border-red-300 focus:border-red-400'
                                    : 'border-slate-200 focus:border-sky-500'
                            "
                        />
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
                        class="h-12 w-full rounded-2xl bg-slate-950 text-sm font-semibold text-white transition hover:bg-slate-800 disabled:opacity-50"
                    >
                        <span v-if="!isLoading">Se connecter</span>
                        <span v-else>Connexion en cours...</span>
                    </button>

                    <!-- Message du serveur (succès ou erreur) -->
                    <div
                        v-if="serverMessage"
                        class="rounded-2xl border px-4 py-3 text-sm"
                        :class="
                            messageType === 'success'
                                ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                                : 'border-red-200 bg-red-50 text-red-700'
                        "
                    >
                        {{ serverMessage }}
                    </div>
                </form>

                <!-- Lien vers l'inscription -->
                <p class="mt-6 text-sm text-slate-600">
                    Pas encore de compte ?
                    <RouterLink
                        :to="registerLink"
                        class="font-semibold text-sky-700 hover:underline"
                    >
                        Créer un compte médecin
                    </RouterLink>
                </p>
            </div>
        </div>
    </div>
</template>

<script setup>
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

        authStore.applyAuth(res?.data, "medecin");
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
            serverMessage.value = "Veuillez corriger les erreurs du formulaire.";
        } else {
            // Erreur générale (mauvais identifiants, serveur, etc.)
            serverMessage.value = data?.message || "Erreur lors de la connexion.";
        }
    } finally {
        isLoading.value = false;
    }
}
</script>
