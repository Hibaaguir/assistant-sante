<template>
    <div class="min-h-screen bg-gradient-to-br from-blue-50 via-blue-50 to-blue-100 flex items-center justify-center px-4 py-8">
        <div class="w-full max-w-7xl">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-0 items-stretch rounded-[32px] overflow-hidden shadow-2xl bg-white">

                <!-- Panneau gauche décoratif, caché sur mobile -->
                <div class="hidden lg:flex flex-col items-center justify-center bg-gradient-to-br from-blue-500 via-blue-600 to-blue-700 text-white px-8 py-20 relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-96 h-96 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/2"></div>
                    <div class="absolute bottom-0 left-0 w-80 h-80 bg-white/5 rounded-full translate-y-1/2 -translate-x-1/2"></div>
                    <div class="relative z-10 text-center space-y-8">
                        <div class="flex justify-center mb-4">
                            <div class="w-24 h-24 rounded-2xl bg-white/15 backdrop-blur-lg flex items-center justify-center border border-white/20">
                                <svg class="w-12 h-12 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" />
                                </svg>
                            </div>
                        </div>
                        <div class="space-y-4 max-w-sm">
                            <h2 class="text-5xl font-extrabold">HealthFlow</h2>
                            <p class="text-base leading-relaxed text-white/90">
                                Votre plateforme de santé numérique. Créez un compte et commencez votre parcours wellbeing dès aujourd'hui.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Colonne droite : formulaire -->
                <div class="w-full flex items-center justify-center p-8 lg:p-12">
                    <div class="w-full max-w-md">

                        <!-- Logo compact affiché uniquement sur mobile -->
                        <div class="lg:hidden text-center mb-8">
                            <div class="flex justify-center mb-3">
                                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center">
                                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" />
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <div class="text-center mb-8">
                            <Typography tag="h1" variant="h1-style">Créer un compte</Typography>
                            <p class="text-sm text-gray-600">Commencez votre parcours santé</p>
                        </div>

                        <!--
                          Bandeau de retour serveur : affiché seulement si serverMessage est non vide.
                          La couleur change selon messageType ("success" = vert, autre = rouge).
                        -->
                        <div
                            v-if="serverMessage"
                            class="mb-6 rounded-xl border px-4 py-3 text-sm backdrop-blur-sm"
                            :class="messageType === 'success'
                                ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                                : 'border-red-200 bg-red-50 text-red-700'"
                        >
                            {{ serverMessage }}
                        </div>

                        <!-- @submit.prevent : soumet sans recharger la page -->
                        <form @submit.prevent="soumettre" class="space-y-4">

                            <!-- v-model.trim lie l'input à form.name en supprimant les espaces -->
                            <FormField label="Nom complet" :error="errors.name">
                                <template #icon>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </template>
                                <input v-model.trim="form.name" type="text" placeholder="Votre nom" autocomplete="name" v-bind="inputProps('name')" />
                            </FormField>

                            <FormField label="Email" :error="errors.email">
                                <template #icon>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </template>
                                <input v-model.trim="form.email" type="email" placeholder="votre@email.com" autocomplete="email" v-bind="inputProps('email')" />
                            </FormField>

                            <!--
                              On utilise :value (pas v-model) car onDateInput formate la valeur manuellement.
                              @beforeinput bloque les caractères non numériques avant qu'ils apparaissent.
                              @blur déclenche la validation quand l'utilisateur quitte le champ.
                            -->
                            <FormField label="Date de naissance" :error="errors.date_naissance">
                                <template #icon>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </template>
                                <input
                                    :value="form.date_naissance"
                                    type="text"
                                    placeholder="JJ/MM/AAAA"
                                    inputmode="numeric"
                                    maxlength="10"
                                    v-bind="inputProps('date_naissance')"
                                    @beforeinput="(e) => { if (e.data && /[^0-9/]/.test(e.data)) e.preventDefault(); }"
                                    @input="onDateInput"
                                    @blur="validerDate"
                                />
                            </FormField>

                            <!--
                              :type bascule entre "password" et "text" selon showPassword.
                              EyeButton émet @toggle → on inverse showPassword.
                            -->
                            <FormField label="Mot de passe" :error="errors.password">
                                <template #icon>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </template>
                                <div class="relative">
                                    <input
                                        v-model="form.password"
                                        :type="showPassword ? 'text' : 'password'"
                                        placeholder="••••••••"
                                        autocomplete="new-password"
                                        v-bind="inputProps('password')"
                                        @input="errors.password = ''"
                                        class="pr-12"
                                    />
                                    <EyeButton :visible="showPassword" @toggle="showPassword = !showPassword" />
                                </div>
                            </FormField>

                            <!-- v-for boucle sur passwordRules, la couleur change selon r.ok -->
                            <div class="space-y-0.5 -mt-2">
                                <p
                                    v-for="r in passwordRules"
                                    :key="r.label"
                                    class="text-xs transition-colors"
                                    :class="r.ok ? 'font-medium text-blue-600' : 'text-gray-600'"
                                >
                                    {{ r.label }}
                                </p>
                            </div>

                            <FormField label="Confirmer le mot de passe" :error="errors.password_confirmation">
                                <template #icon>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </template>
                                <div class="relative">
                                    <input
                                        v-model="form.password_confirmation"
                                        :type="showPasswordConfirm ? 'text' : 'password'"
                                        placeholder="••••••••"
                                        autocomplete="new-password"
                                        v-bind="inputProps('password_confirmation')"
                                        @input="errors.password_confirmation = ''"
                                        class="pr-12"
                                    />
                                    <EyeButton :visible="showPasswordConfirm" @toggle="showPasswordConfirm = !showPasswordConfirm" />
                                </div>
                            </FormField>

                            <label class="flex cursor-pointer items-start gap-2 pt-1">
                                <input type="checkbox" class="mt-1 h-5 w-5 shrink-0 rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                                <span class="text-sm text-gray-700">
                                    J'accepte les
                                    <a href="#" class="font-semibold text-blue-600 hover:text-blue-700">conditions</a>
                                    et
                                    <a href="#" class="font-semibold text-blue-600 hover:text-blue-700">la confidentialité</a>
                                </span>
                            </label>

                            <!-- :disabled et :loading désactivent le bouton pendant l'appel API -->
                            <BaseButton type="submit" variant="primary" size="lg" fullWidth :disabled="loading" :loading="loading" class="mt-6">
                                {{ loading ? "Création..." : "Créer un compte" }}
                            </BaseButton>

                            <p class="text-center text-sm text-gray-600 pt-4">
                                Vous avez déjà un compte ?
                                <RouterLink :to="{ name: 'login' }" class="text-blue-600 font-semibold hover:text-blue-700 transition-colors">
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
import Typography from "@/components/ui/Typography.vue";
import BaseButton from "@/components/ui/BaseButton.vue";
import EyeButton from "@/components/ui/EyeButton.vue";

// Composant local : affiche label + icône SVG à gauche + slot input + message d'erreur
const FormField = {
    props: ["label", "error"],
    template: `
    <div>
      <label class="mb-2 block text-base font-semibold text-gray-800">{{ label }}</label>
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

// Valeurs saisies dans le formulaire
const form = reactive({ name: "", email: "", date_naissance: "", password: "", password_confirmation: "" });
// Messages d'erreur par champ (vide = pas d'erreur)
const errors = reactive({ name: "", email: "", date_naissance: "", password: "", password_confirmation: "" });

const loading = ref(false);           // true pendant l'appel API
const serverMessage = ref("");        // message de retour du serveur
const messageType = ref("success");   // "success" ou "error" → couleur du bandeau
const showPassword = ref(false);
const showPasswordConfirm = ref(false);

// Retourne les classes CSS de l'input : bordure rouge si erreur, bleue au focus sinon
const inputProps = (field) => ({
    class: [
        "w-full h-12 pl-12 pr-4 rounded-xl border bg-gray-50 text-base text-gray-900 placeholder:text-gray-400 outline-none transition-all duration-200",
        errors[field]
            ? "border-red-300 focus:border-red-500 focus:bg-white focus:ring-2 focus:ring-red-200"
            : "border-gray-200 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-100",
    ],
});

// Se recalcule automatiquement à chaque frappe dans le champ mot de passe
const passwordRules = computed(() => [
    { label: "Au moins 8 caractères", ok: form.password.length >= 8 },
    { label: "Au moins une lettre",   ok: /[a-zA-Z]/.test(form.password) },
    { label: "Au moins un chiffre",   ok: /[0-9]/.test(form.password) },
]);

// Retourne un message d'erreur si le mot de passe ne respecte pas les règles, sinon ""
const pwdError = (pwd) => {
    if (pwd.length < 8)        return "Le mot de passe est trop court (min 8 caractères).";
    if (!/[a-zA-Z]/.test(pwd)) return "Le mot de passe doit contenir au moins une lettre.";
    if (!/[0-9]/.test(pwd))    return "Le mot de passe doit contenir au moins un chiffre.";
    return "";
};

// Formate la saisie en JJ/MM/AAAA en insérant les "/" automatiquement à chaque frappe
function onDateInput(e) {
    const digits = (e.target.value ?? "").replace(/\D/g, "").slice(0, 8);
    form.date_naissance =
        digits.length <= 2 ? digits
        : digits.length <= 4 ? `${digits.slice(0, 2)}/${digits.slice(2)}`
        : `${digits.slice(0, 2)}/${digits.slice(2, 4)}/${digits.slice(4, 8)}`;
}

// Valide la date quand l'utilisateur quitte le champ : format, date réelle, passé, âge >= 18 ans
function validerDate() {
    if (!form.date_naissance) { errors.date_naissance = ""; return; }

    const m = form.date_naissance.match(/^(\d{2})\/(\d{2})\/(\d{4})$/);
    if (!m) { errors.date_naissance = "Format invalide. Utilisez JJ/MM/AAAA."; return; }

    const [, d, mo, y] = m.map(Number);
    const date = new Date(y, mo - 1, d); // mois - 1 car JS compte de 0 à 11
    if (mo < 1 || mo > 12 || d < 1 || d > 31 || date.getFullYear() !== y || date.getMonth() !== mo - 1 || date.getDate() !== d) {
        errors.date_naissance = "Date invalide. Utilisez JJ/MM/AAAA."; return;
    }

    const today = new Date(); today.setHours(0, 0, 0, 0);
    if (date > today) { errors.date_naissance = "La date de naissance ne peut pas être dans le futur."; return; }

    const adultLimit = new Date(today.getFullYear() - 18, today.getMonth(), today.getDate());
    if (date > adultLimit) { errors.date_naissance = "Vous devez avoir au moins 18 ans."; return; }

    errors.date_naissance = "";
}

// Convertit JJ/MM/AAAA → AAAA-MM-JJ (format ISO attendu par le backend Laravel)
function isoDate(str) {
    const m = str.match(/^(\d{2})\/(\d{2})\/(\d{4})$/);
    return m ? `${m[3]}-${m[2]}-${m[1]}` : str;
}

// Valide le formulaire, envoie les données au serveur, gère les erreurs de réponse
async function soumettre() {
    serverMessage.value = "";
    messageType.value = "success";
    Object.keys(errors).forEach((k) => (errors[k] = "")); // réinitialise toutes les erreurs

    // Validation côté client
    if (!form.name)                  errors.name = "Le nom d'utilisateur est obligatoire.";
    if (!form.email)                 errors.email = "L'adresse email est obligatoire.";
    if (!form.date_naissance)        errors.date_naissance = "La date de naissance est obligatoire.";
    if (!form.password)              errors.password = "Le mot de passe est obligatoire.";
    if (!form.password_confirmation) errors.password_confirmation = "La confirmation du mot de passe est obligatoire.";

    if (form.name.length > 0 && form.name.length < 3)
        errors.name = "Le nom doit contenir au moins 3 caractères.";
    if (form.email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email))
        errors.email = "Format d'email invalide.";
    if (form.password) { const e = pwdError(form.password); if (e) errors.password = e; }
    if (form.password && form.password_confirmation && form.password !== form.password_confirmation)
        errors.password_confirmation = "Les mots de passe ne correspondent pas.";
    validerDate();

    // Si au moins une erreur existe, on n'envoie pas la requête
    if (Object.values(errors).some(Boolean)) return;

    loading.value = true;
    try {// data hia l reponse mtaa laravel fiha message w redirect_to w les infos du user
        const { data } = await api.post("/auth/register", {
            name: form.name,
            email: form.email,
            date_of_birth: isoDate(form.date_naissance),
            password: form.password,
            password_confirmation: form.password_confirmation,
        });

        // Succès : sauvegarde la session et redirige après 900ms
        authStore.applyAuth(data, "personnel");
        serverMessage.value = data?.message || "Compte créé avec succès.";
        setTimeout(() => router.push(data?.redirect_to || "/health-profile"), 900);

    } catch (err) {
        messageType.value = "error";
        const status = err?.response?.status;
        const data = err?.response?.data ?? {};
        // Extrait le premier message d'un tableau ou d'une chaîne
        const first = (v) => Array.isArray(v) ? String(v[0] || "") : String(v || "");

        if (!err?.response) { serverMessage.value = "Problème réseau. Réessayez."; return; }

        // 422 : erreurs de validation retournées par Laravel (champ par champ)
        if (status === 422 && data.errors) {
            errors.name  = first(data.errors.name);
            errors.email = first(data.errors.email);
            // Le champ date peut avoir plusieurs noms selon la version du backend
            errors.date_naissance = first(data.errors.date_naissance) || first(data.errors.date_of_birth) || first(data.errors.birth_date);
            errors.password = first(data.errors.password);
            errors.password_confirmation = first(data.errors.password_confirmation);
            serverMessage.value = data?.message || "Veuillez corriger les erreurs du formulaire.";
            return;
        }

        // Si le message parle de date ou d'âge, on l'affiche sous le champ date
        const backendMessage = first(data?.message || data?.error);
        if (backendMessage && /(date|birth|naissance|18\s*ans|mineur)/i.test(backendMessage))
            errors.date_naissance = backendMessage;

        // 409 : conflit (email déjà utilisé)
        if (status === 409 && data.errors?.email) {
            errors.email = first(data.errors.email);
            serverMessage.value = data?.message || errors.email || "Cet email est déjà utilisé.";
            return;
        }

        serverMessage.value = data?.message || "Erreur lors de la création du compte.";

    } finally {
        loading.value = false; // s'exécute toujours, succès ou erreur
    }
}
</script>
