<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50/50 via-white to-teal-50/50">
    <div class="bg-white/80 border-b border-slate-200 backdrop-blur-sm sticky top-0 z-10">
      <div class="max-w-3xl mx-auto px-4 sm:px-6 py-6">
        <div class="text-center">
          <h1 class="text-2xl font-bold text-gray-900 mb-1">Connectez-vous</h1>
          <p class="text-sm text-gray-500">Accedez a votre espace Assistant Sante</p>
        </div>
      </div>
    </div>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 py-8 sm:py-12">
      <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8 md:p-10">
        <div class="mb-6">
          <h2 class="text-xl font-semibold text-gray-900">Connexion</h2>
          <p class="text-sm text-gray-500 mt-1">Entrez vos identifiants pour continuer.</p>
        </div>

        <form @submit.prevent="submit" class="space-y-5">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Type de compte <span class="text-red-600">*</span></label>
            <select
              v-model="form.role"
              class="h-12 px-4 rounded-xl border-2 bg-white text-gray-900 outline-none w-full"
              :class="errors.role ? 'border-red-300 focus:border-red-400 focus:ring-red-200' : 'border-gray-200 focus:border-teal-500 focus:ring-teal-500/20'"
            >
              <option value="user">Utilisateur</option>
              <option value="medecin">Medecin</option>
            </select>
            <p v-if="errors.role" class="mt-2 text-sm text-red-600">{{ errors.role }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Email <span class="text-red-600">*</span></label>
            <div class="relative">
              <svg class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" viewBox="0 0 24 24" fill="none">
                <path d="M4 6h16v12H4V6Z" stroke="currentColor" stroke-width="1.7" />
                <path d="m4 7 8 6 8-6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
              <input
                v-model.trim="form.email"
                type="email"
                placeholder="votrenom@exemple.com"
                autocomplete="email"
                class="h-12 pl-12 pr-4 rounded-xl border-2 bg-white text-gray-900 placeholder:text-gray-400 outline-none w-full"
                :class="errors.email ? 'border-red-300 focus:border-red-400 focus:ring-red-200' : 'border-gray-200 focus:border-teal-500 focus:ring-teal-500/20'"
              />
            </div>
            <p v-if="errors.email" class="mt-2 text-sm text-red-600">{{ errors.email }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Mot de passe <span class="text-red-600">*</span></label>
            <div class="relative">
              <svg class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" viewBox="0 0 24 24" fill="none">
                <path d="M7 11V8a5 5 0 0 1 10 0v3" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" />
                <path d="M6 11h12v10H6V11Z" stroke="currentColor" stroke-width="1.7" />
              </svg>
              <input
                v-model="form.password"
                type="password"
                placeholder="Votre mot de passe"
                autocomplete="current-password"
                class="h-12 pl-12 pr-4 rounded-xl border-2 bg-white text-gray-900 placeholder:text-gray-400 outline-none w-full"
                :class="errors.password ? 'border-red-300 focus:border-red-400 focus:ring-red-200' : 'border-gray-200 focus:border-teal-500 focus:ring-teal-500/20'"
              />
            </div>
            <p v-if="errors.password" class="mt-2 text-sm text-red-600">{{ errors.password }}</p>
          </div>

          <button
            type="submit"
            :disabled="loading"
            class="w-full rounded-xl h-12 px-8 bg-gradient-to-r from-teal-600 to-blue-600 hover:from-teal-700 hover:to-blue-700 text-white font-semibold disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span v-if="!loading">Se connecter</span>
            <span v-else>Connexion...</span>
          </button>

          <div
            v-if="serverMessage"
            class="rounded-xl border px-4 py-3 text-sm"
            :class="messageType === 'success'
              ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
              : 'border-red-200 bg-red-50 text-red-700'"
          >
            {{ serverMessage }}
          </div>

          <p class="text-xs text-center text-gray-500">
            Vous n'avez pas de compte ?
            <RouterLink :to="{ name: 'register' }" class="text-teal-700 font-semibold hover:underline">
              Choisir un type de compte
            </RouterLink>
          </p>
        </form>
      </div>

      <p class="text-center text-xs text-gray-400 mt-6">Copyright {{ new Date().getFullYear() }} Assistant Sante</p>
    </div>
  </div>
</template>

<script setup>
import api from "@/services/api";
import { reactive, ref } from "vue";
import { useRouter } from "vue-router";

const router = useRouter();

const form = reactive({
  role: "user",
  email: "",
  password: "",
});

const errors = reactive({
  role: "",
  email: "",
  password: "",
});

const loading = ref(false);
const serverMessage = ref("");
const messageType = ref("success");

const REQUIRED_FORM_MESSAGE = "Veuillez remplir les champs obligatoires.";
const INVALID_EMAIL_MESSAGE = "Format d'email invalide.";
const INVALID_CREDENTIALS_MESSAGE = "Email ou mot de passe invalide.";

function clearErrors() {
  errors.role = "";
  errors.email = "";
  errors.password = "";
}

function isValidEmail(email) {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

function firstMessage(value) {
  if (Array.isArray(value)) return value[0] || "";
  if (typeof value === "string") return value;
  return "";
}

function mapFieldValidationErrors(validationErrors = {}) {
  const emailBackend = firstMessage(validationErrors.email);
  const passwordBackend = firstMessage(validationErrors.password);
  const roleBackend = firstMessage(validationErrors.role);

  errors.role = roleBackend || "";
  errors.email = emailBackend || "";
  errors.password = passwordBackend || "";
}

async function submit() {
  serverMessage.value = "";
  messageType.value = "success";
  clearErrors();

  if (!form.role || !form.email || !form.password) {
    if (!form.role) errors.role = "Le type de compte est obligatoire.";
    if (!form.email) errors.email = "L'adresse email est obligatoire.";
    if (!form.password) errors.password = "Le mot de passe est obligatoire.";
    serverMessage.value = REQUIRED_FORM_MESSAGE;
    return;
  }

  if (!isValidEmail(form.email)) {
    errors.email = INVALID_EMAIL_MESSAGE;
    serverMessage.value = INVALID_EMAIL_MESSAGE;
    return;
  }

  loading.value = true;

  try {
    const res = await api.post("/auth/login", {
      role: form.role,
      email: form.email,
      password: form.password,
    });

    const token = res?.data?.token;
    if (token) {
      localStorage.setItem("auth_token", token);
      api.defaults.headers.common.Authorization = `Bearer ${token}`;
    }

    serverMessage.value = res?.data?.message || "Connexion reussie.";
    messageType.value = "success";

    setTimeout(() => router.push(res?.data?.redirect_to || "/main/dashboard"), 250);
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
      serverMessage.value = "Veuillez corriger les erreurs du formulaire.";
      return;
    }

    serverMessage.value = data?.message || "Erreur lors de la connexion.";
  } finally {
    loading.value = false;
  }
}
</script>
