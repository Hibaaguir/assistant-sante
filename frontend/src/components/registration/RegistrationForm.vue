+<!--
  InscriptionPage.vue
  Formulaire d'inscription : nom, email, date de naissance, mot de passe.
-->
<template>
    <div
        class="min-h-screen bg-gradient-to-br from-purple-200 via-purple-200 to-purple-300 flex items-center justify-center px-4 py-8"
    >
        <div class="w-full max-w-6xl">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">
                <!-- Colonne gauche - Illustration & Texte -->
                <div
                    class="hidden lg:flex flex-col items-center justify-center text-slate-50 px-8 py-16"
                >
                    <div class="mb-10 relative">
                        <div
                            class="absolute inset-0 bg-gradient-to-br from-purple-500/30 to-purple-600/10 rounded-3xl blur-2xl scale-105"
                        ></div>
                        <img
                            src="https://img.freepik.com/vecteurs-premium/appeler-medecin-ambulance-main-tenir-telephone-mobile-coeur-rouge-ligne-rythme-cardiaque-cardiogramme-ecran-document-assurance-maladie-signe-croise-accord-medical-rapport-diagnostic-clinique_284092-711.jpg?semt=ais_hybrid&w=600&q=85"
                            alt="Illustration médicale"
                            class="relative w-72 h-80 object-cover rounded-3xl shadow-2xl border border-white/10"
                            loading="lazy"
                        />
                    </div>
                    <div class="text-center space-y-4 max-w-md">
                        <h2
                            class="text-5xl font-extrabold bg-gradient-to-r from-purple-600 to-purple-700 bg-clip-text text-transparent"
                        >
                            HealthFlow
                        </h2>
                        <p class="text-base leading-relaxed text-white/85">
                            Votre plateforme de santé numérique. Créez un compte
                            et commencez votre parcours wellbeing dès
                            aujourd'hui.
                        </p>
                    </div>
                </div>

                <!-- Colonne droite - Formulaire -->
                <div class="w-full max-w-lg mx-auto">
                    <div class="bg-white rounded-3xl shadow-2xl p-6 lg:p-8">
                        <!-- Logo -->
                        <div class="text-center mb-6">
                            <div class="flex justify-center mb-3">
                                <div
                                    class="w-10 h-10 rounded-lg bg-gradient-to-br from-purple-500 to-purple-600 flex items-center justify-center"
                                >
                                    <svg
                                        class="w-5 h-5 text-white"
                                        fill="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"
                                        />
                                    </svg>
                                </div>
                            </div>
                            <h1 class="text-3xl font-bold text-gray-900 mb-3">
                                Créer un compte
                            </h1>
                            <p class="text-xs text-gray-500">
                                Commencez votre parcours santé
                            </p>
                        </div>

                        <!-- Message serveur -->
                        <div
                            v-if="serverMessage"
                            class="mb-6 rounded-lg border px-4 py-3 text-sm"
                            :class="
                                messageType === 'success'
                                    ? 'border-purple-200 bg-purple-50 text-purple-700'
                                    : 'border-red-200 bg-red-50 text-red-700'
                            "
                        >
                            {{ serverMessage }}
                        </div>

                        <form @submit.prevent="soumettre" class="space-y-3">
                            <!-- Nom -->
                            <FormField label="Nom complet" :error="errors.name">
                                <template #icon>
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                                    />
                                </template>
                                <input
                                    v-model.trim="form.name"
                                    type="text"
                                    placeholder="Votre nom"
                                    autocomplete="name"
                                    v-bind="inputProps('name')"
                                />
                            </FormField>

                            <!-- Email -->
                            <FormField label="Email" :error="errors.email">
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
                                    v-bind="inputProps('email')"
                                />
                            </FormField>

                            <!-- Date de naissance -->
                            <FormField
                                label="Date de naissance"
                                :error="errors.date_naissance"
                            >
                                <template #icon>
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                                    />
                                </template>
                                <input
                                    :value="form.date_naissance"
                                    type="text"
                                    placeholder="JJ/MM/AAAA"
                                    inputmode="numeric"
                                    maxlength="10"
                                    v-bind="inputProps('date_naissance')"
                                    @beforeinput="
                                        (e) => {
                                            if (
                                                e.data &&
                                                /[^0-9/]/.test(e.data)
                                            )
                                                e.preventDefault();
                                        }
                                    "
                                    @input="onDateInput"
                                    @blur="validerDate"
                                />
                            </FormField>

                            <!-- Mot de passe -->
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
                                <input
                                    v-model="form.password"
                                    type="password"
                                    placeholder="••••••••"
                                    autocomplete="new-password"
                                    v-bind="inputProps('password')"
                                    @input="errors.password = ''"
                                />
                            </FormField>

                            <!-- Critères mot de passe -->
                            <div class="space-y-0.5 -mt-2">
                                <p
                                    v-for="r in passwordRules"
                                    :key="r.label"
                                    class="text-xs transition-colors"
                                    :class="
                                        r.ok
                                            ? 'font-medium text-purple-600'
                                            : 'text-gray-400'
                                    "
                                >
                                    {{ r.label }}
                                </p>
                            </div>

                            <!-- Confirmation mot de passe -->
                            <FormField
                                label="Confirmer le mot de passe"
                                :error="errors.password_confirmation"
                            >
                                <template #icon>
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
                                    />
                                </template>
                                <input
                                    v-model="form.password_confirmation"
                                    type="password"
                                    placeholder="••••••••"
                                    autocomplete="new-password"
                                    v-bind="inputProps('password_confirmation')"
                                    @input="errors.password_confirmation = ''"
                                />
                            </FormField>

                            <!-- CGU -->
                            <label
                                class="flex cursor-pointer items-start gap-2 pt-1"
                            >
                                <input
                                    type="checkbox"
                                    class="mt-0.5 h-4 w-4 shrink-0 rounded border-gray-300 text-purple-600 focus:ring-purple-500"
                                />
                                <span class="text-xs text-gray-600">
                                    J'accepte les
                                    <a
                                        href="#"
                                        class="font-semibold text-purple-600 hover:text-purple-700"
                                        >conditions</a
                                    >
                                    et
                                    <a
                                        href="#"
                                        class="font-semibold text-purple-600 hover:text-purple-700"
                                        >la confidentialité</a
                                    >
                                </span>
                            </label>

                            <!-- Soumettre -->
                            <button
                                type="submit"
                                :disabled="loading"
                                class="w-full h-11 rounded-xl bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white font-semibold text-base transition-all disabled:opacity-50 disabled:cursor-not-allowed mt-4"
                            >
                                {{
                                    loading ? "Création..." : "Créer un compte"
                                }}
                            </button>

                            <!-- Connexion -->
                            <p class="text-center text-xs text-gray-500 pt-2">
                                Vous avez déjà un compte ?
                                <RouterLink
                                    :to="{ name: 'login' }"
                                    class="text-purple-600 font-semibold hover:text-purple-700 transition-colors"
                                >
                                    Se connecter
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
import { computed, reactive, ref } from "vue";
import { useRouter } from "vue-router";
import api from "@/services/api";
import { useAuthStore } from "@/stores/auth";

// ─── Sous-composant champ formulaire ─────────────────────────────────────────
const FormField = {
    props: ["label", "error"],
    template: `
    <div>
      <label class="mb-2 block text-sm font-medium text-gray-700">{{ label }}</label>
      <div class="relative">
        <svg class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <slot name="icon" />
        </svg>
        <slot />
      </div>
      <p v-if="error" class="mt-1.5 text-sm text-red-600">{{ error }}</p>
    </div>`,
};

const router = useRouter();
const authStore = useAuthStore();

// ─── État ─────────────────────────────────────────────────────────────────────
const form = reactive({
    name: "",
    email: "",
    date_naissance: "",
    password: "",
    password_confirmation: "",
});
const errors = reactive({
    name: "",
    email: "",
    date_naissance: "",
    password: "",
    password_confirmation: "",
});
const loading = ref(false);
const serverMessage = ref("");
const messageType = ref("success");

// ─── Classes input dynamiques ─────────────────────────────────────────────────
const inputProps = (field) => ({
    class: [
        "w-full h-12 pl-12 pr-4 rounded-lg border bg-gray-50 text-base text-gray-900 placeholder:text-gray-400 outline-none transition-colors",
        errors[field]
            ? "border-red-300 focus:border-red-500 focus:bg-white"
            : "border-gray-200 focus:border-purple-500 focus:bg-white",
    ],
});

// ─── Critères mot de passe ────────────────────────────────────────────────────
const passwordRules = computed(() => [
    { label: "Au moins 8 caractères", ok: form.password.length >= 8 },
    { label: "Au moins une lettre", ok: /[a-zA-Z]/.test(form.password) },
    { label: "Au moins un chiffre", ok: /[0-9]/.test(form.password) },
]);

const pwdError = (pwd) => {
    if (pwd.length < 8)
        return "Le mot de passe est trop court (min 8 caractères).";
    if (!/[a-zA-Z]/.test(pwd))
        return "Le mot de passe doit contenir au moins une lettre.";
    if (!/[0-9]/.test(pwd))
        return "Le mot de passe doit contenir au moins un chiffre.";
    return "";
};

// ─── Date de naissance ────────────────────────────────────────────────────────
function onDateInput(e) {
    const digits = (e.target.value ?? "").replace(/\D/g, "").slice(0, 8);
    form.date_naissance =
        digits.length <= 2
            ? digits
            : digits.length <= 4
              ? `${digits.slice(0, 2)}/${digits.slice(2)}`
              : `${digits.slice(0, 2)}/${digits.slice(2, 4)}/${digits.slice(4, 8)}`;
}

function validerDate() {
    const m = form.date_naissance.match(/^(\d{2})\/(\d{2})\/(\d{4})$/);
    if (!form.date_naissance) {
        errors.date_naissance = "";
        return;
    }
    if (!m) {
        errors.date_naissance = "Format invalide. Utilisez JJ/MM/AAAA.";
        return;
    }
    const [, d, mo, y] = m.map(Number);
    const date = new Date(y, mo - 1, d);
    if (
        mo < 1 ||
        mo > 12 ||
        d < 1 ||
        d > 31 ||
        date.getFullYear() !== y ||
        date.getMonth() !== mo - 1 ||
        date.getDate() !== d
    ) {
        errors.date_naissance = "Date invalide. Utilisez JJ/MM/AAAA.";
        return;
    }

    const today = new Date();
    today.setHours(0, 0, 0, 0);
    if (date > today) {
        errors.date_naissance =
            "La date de naissance ne peut pas être dans le futur.";
        return;
    }

    const adultLimit = new Date(
        today.getFullYear() - 18,
        today.getMonth(),
        today.getDate(),
    );
    if (date > adultLimit) {
        errors.date_naissance = "Vous devez avoir au moins 18 ans.";
        return;
    }

    errors.date_naissance = "";
}

function isoDate(str) {
    const m = str.match(/^(\d{2})\/(\d{2})\/(\d{4})$/);
    return m ? `${m[3]}-${m[2]}-${m[1]}` : str;
}

// ─── Soumission ───────────────────────────────────────────────────────────────
async function soumettre() {
    serverMessage.value = "";
    messageType.value = "success";
    Object.keys(errors).forEach((k) => (errors[k] = ""));

    // Validation champs obligatoires
    if (!form.name) errors.name = "Le nom d'utilisateur est obligatoire.";
    if (!form.email) errors.email = "L'adresse email est obligatoire.";
    if (!form.date_naissance)
        errors.date_naissance = "La date de naissance est obligatoire.";
    if (!form.password) errors.password = "Le mot de passe est obligatoire.";
    if (!form.password_confirmation)
        errors.password_confirmation =
            "La confirmation du mot de passe est obligatoire.";

    // Validation format
    if (form.name.length > 0 && form.name.length < 3)
        errors.name = "Le nom doit contenir au moins 3 caractères.";
    if (form.email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email))
        errors.email = "Format d'email invalide.";
    if (form.password) {
        const e = pwdError(form.password);
        if (e) errors.password = e;
    }
    if (form.password && form.password_confirmation) {
        if (form.password !== form.password_confirmation)
            errors.password_confirmation =
                "Les mots de passe ne correspondent pas.";
    }
    validerDate();

    if (Object.values(errors).some(Boolean)) return;

    loading.value = true;
    try {
        const { data } = await api.post("/auth/register", {
            name: form.name,
            email: form.email,
            date_of_birth: isoDate(form.date_naissance),
            password: form.password,
            password_confirmation: form.password_confirmation,
        });
        authStore.applyAuth(data, "personnel");
        serverMessage.value = data?.message || "Compte créé avec succès.";
        setTimeout(
            () => router.push(data?.redirect_to || "/health-profile"),
            900,
        );
    } catch (err) {
        messageType.value = "error";
        const status = err?.response?.status;
        const data = err?.response?.data ?? {};
        const first = (v) =>
            Array.isArray(v) ? String(v[0] || "") : String(v || "");

        if (!err?.response) {
            serverMessage.value = "Problème réseau. Réessayez.";
            return;
        }
        if (status === 422 && data.errors) {
            errors.name = first(data.errors.name);
            errors.email = first(data.errors.email);
            errors.date_naissance = first(data.errors.date_naissance);
            if (!errors.date_naissance)
                errors.date_naissance = first(data.errors.date_of_birth);
            if (!errors.date_naissance)
                errors.date_naissance = first(data.errors.birth_date);
            errors.password = first(data.errors.password);
            errors.password_confirmation =
                first(data.errors.password_confirmation);
            serverMessage.value =
                data?.message || "Veuillez corriger les erreurs du formulaire.";
            return;
        }

        const backendMessage = first(data?.message || data?.error);
        if (
            backendMessage &&
            /(date|birth|naissance|18\s*ans|mineur)/i.test(backendMessage)
        ) {
            errors.date_naissance = backendMessage;
        }
        if (status === 409 && data.errors?.email) {
            errors.email = first(data.errors.email);
            serverMessage.value =
                data?.message || errors.email || "Cet email est déjà utilisé.";
            return;
        }
        serverMessage.value =
            data?.message || "Erreur lors de la création du compte.";
    } finally {
        loading.value = false;
    }
}
</script>
