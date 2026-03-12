<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50/50 via-white to-teal-50/50">
    <div class="bg-white/80 border-b border-slate-200 backdrop-blur-sm sticky top-0 z-10">
      <div class="max-w-3xl mx-auto px-4 sm:px-6 py-6">
        <div class="text-center">
          <h1 class="text-2xl font-bold text-gray-900 mb-1">Creez votre compte</h1>
          <p class="text-sm text-gray-500">Rejoignez Assistant Sante en quelques secondes</p>
        </div>
      </div>
    </div>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 py-8 sm:py-12">
      <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8 md:p-10">
        <div class="mb-6">
          <h2 class="text-xl font-semibold text-gray-900">Informations personnelles</h2>
          <p class="text-sm text-gray-500 mt-1">Completez les champs pour creer votre espace sante.</p>
        </div>

        <form @submit.prevent="soumettre" class="space-y-5">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Nom d'utilisateur <span class="text-red-600">*</span></label>
            <div class="relative">
              <svg class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" viewBox="0 0 24 24" fill="none">
                <path d="M20 21a8 8 0 0 0-16 0" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" />
                <path d="M12 13a5 5 0 1 0-5-5 5 5 0 0 0 5 5Z" stroke="currentColor" stroke-width="1.7" />
              </svg>
              <input
                v-model.trim="form.name"
                type="text"
                placeholder="Entrez votre nom d'utilisateur"
                autocomplete="name"
                class="h-12 pl-12 pr-4 rounded-xl border-2 bg-white text-gray-900 placeholder:text-gray-400 outline-none w-full"
                :class="errors.name ? 'border-red-300 focus:border-red-400 focus:ring-red-200' : 'border-gray-200 focus:border-teal-500 focus:ring-teal-500/20'"
              />
            </div>
            <p v-if="errors.name" class="mt-2 text-sm text-red-600">{{ errors.name }}</p>
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
            <label class="block text-sm font-medium text-gray-700 mb-2">Date de naissance <span class="text-red-600">*</span></label>
            <div class="relative">
              <svg class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" viewBox="0 0 24 24" fill="none">
                <path d="M6 4h12M6 4v14a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4M9 6v4M15 6v4M6 12h12" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
              <input
                :value="form.date_of_birth"
                type="text"
                placeholder="JJ/MM/AAAA"
                inputmode="numeric"
                maxlength="10"
                @beforeinput="gererAvantSaisieDate"
                @input="gererSaisieDate"
                @blur="validerFormatDate"
                class="h-12 pl-12 pr-4 rounded-xl border-2 bg-white text-gray-900 placeholder:text-gray-400 outline-none w-full"
                :class="errors.date_of_birth ? 'border-red-300 focus:border-red-400 focus:ring-red-200' : 'border-gray-200 focus:border-teal-500 focus:ring-teal-500/20'"
              />
            </div>
            <p v-if="errors.date_of_birth" class="mt-2 text-sm text-red-600">{{ errors.date_of_birth }}</p>
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
                placeholder="Min 8 caracteres (lettres + chiffres)"
                autocomplete="new-password"
                @input="validerReglesMotDePasse"
                class="h-12 pl-12 pr-4 rounded-xl border-2 bg-white text-gray-900 placeholder:text-gray-400 outline-none w-full"
                :class="errors.password ? 'border-red-300 focus:border-red-400 focus:ring-red-200' : 'border-gray-200 focus:border-teal-500 focus:ring-teal-500/20'"
              />
            </div>
            <div class="mt-2 space-y-1 text-sm">
              <p :class="motDePasseAlaBonneLongueur ? 'text-emerald-600' : 'text-gray-400'">Au moins 8 caracteres</p>
              <p :class="motDePasseContientLettre ? 'text-emerald-600' : 'text-gray-400'">Au moins une lettre</p>
              <p :class="motDePasseContientNombre ? 'text-emerald-600' : 'text-gray-400'">Au moins un chiffre</p>
            </div>
            <p v-if="errors.password" class="mt-2 text-sm text-red-600">{{ errors.password }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Confirmer le mot de passe</label>
            <input
              v-model="form.password_confirmation"
              type="password"
              placeholder="Repetez le mot de passe"
              autocomplete="new-password"
              class="h-12 px-4 rounded-xl border-2 bg-white text-gray-900 placeholder:text-gray-400 outline-none w-full"
              :class="errors.password ? 'border-red-300 focus:border-red-400 focus:ring-red-200' : 'border-gray-200 focus:border-teal-500 focus:ring-teal-500/20'"
            />
          </div>

          <button
            type="submit"
            :disabled="loading"
            class="w-full rounded-xl h-12 px-8 bg-gradient-to-r from-teal-600 to-blue-600 hover:from-teal-700 hover:to-blue-700 text-white font-semibold disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span v-if="!loading">Creer un compte</span>
            <span v-else>Creation...</span>
          </button>

          <p class="text-xs text-center text-gray-500">Vous pourrez completer votre profil sante juste apres.</p>
          <p class="text-xs text-center text-gray-500">
            Vous avez deja un compte ?
            <RouterLink :to="{ name: 'connexion' }" class="text-teal-700 font-semibold hover:underline">Se connecter</RouterLink>
          </p>
          <p class="text-xs text-center text-gray-500">
            Vous souhaitez creer un compte medecin ?
            <RouterLink :to="{ name: 'inscription-medecin' }" class="text-sky-700 font-semibold hover:underline">Acceder a l'inscription medecin</RouterLink>
          </p>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import api from "@/services/api";
import { computed, reactive, ref } from "vue";
import { useRouter } from "vue-router";
import { useAuthStore } from "@/stores/auth";

const router = useRouter();
const authStore = useAuthStore();

const form = reactive({
  name: "",
  email: "",
  date_of_birth: "",
  password: "",
  password_confirmation: "",
});

const errors = reactive({
  name: "",
  email: "",
  date_of_birth: "",
  password: "",
});

const loading = ref(false);
const motDePasseAlaBonneLongueur = computed(() => form.password.length >= 8);
const motDePasseContientLettre = computed(() => /[a-zA-Z]/.test(form.password));
const motDePasseContientNombre = computed(() => /[0-9]/.test(form.password));

function effacerErreurs() {
  errors.name = "";
  errors.email = "";
  errors.date_of_birth = "";
  errors.password = "";
}

function estEmailValide(email) {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

function validerMotDePasse(password) {
  if (password.length < 8) return "Le mot de passe est trop court (min 8 caracteres).";
  if (!/[a-zA-Z]/.test(password)) return "Le mot de passe doit contenir au moins une lettre.";
  if (!/[0-9]/.test(password)) return "Le mot de passe doit contenir au moins un chiffre.";
  return "";
}

function premierMessage(value) {
  if (Array.isArray(value)) return String(value[0] || "");
  return typeof value === "string" ? value : "";
}

function validerReglesMotDePasse() {
  if (!form.password || !validerMotDePasse(form.password)) errors.password = "";
}

function gererAvantSaisieDate(event) {
  if (event.data && /[^0-9/]/.test(event.data)) event.preventDefault();
}

function normaliserSaisieDate(value) {
  const digits = String(value || "").replace(/\D/g, "").slice(0, 8);
  if (digits.length <= 2) return digits;
  if (digits.length <= 4) return `${digits.slice(0, 2)}/${digits.slice(2)}`;
  return `${digits.slice(0, 2)}/${digits.slice(2, 4)}/${digits.slice(4, 8)}`;
}

function gererSaisieDate(event) {
  const raw = event?.target?.value ?? "";
  form.date_of_birth = normaliserSaisieDate(raw).slice(0, 10);
}

function validerFormatDate() {
  if (!form.date_of_birth) {
    errors.date_of_birth = "";
    return;
  }
  const match = form.date_of_birth.match(/^(\d{2})\/(\d{2})\/(\d{4})$/);
  if (!match) {
    errors.date_of_birth = "Format invalide. Utilisez JJ/MM/AAAA.";
    return;
  }
  const day = Number(match[1]);
  const month = Number(match[2]);
  const year = Number(match[3]);
  const date = new Date(year, month - 1, day);
  const isInvalidDate =
    month < 1 ||
    month > 12 ||
    day < 1 ||
    day > 31 ||
    date.getFullYear() !== year ||
    date.getMonth() !== month - 1 ||
    date.getDate() !== day;
  if (isInvalidDate) {
    errors.date_of_birth = "Date invalide. Utilisez JJ/MM/AAAA.";
    return;
  }
  errors.date_of_birth = "";
}

function convertirFormatDate(dateStr) {
  const match = String(dateStr || "").match(/^(\d{2})\/(\d{2})\/(\d{4})$/);
  return match ? `${match[3]}-${match[2]}-${match[1]}` : dateStr;
}

async function soumettre() {
  effacerErreurs();

  if (!form.name || !form.email || !form.date_of_birth || !form.password) {
    if (!form.name) errors.name = "Le nom d'utilisateur est obligatoire.";
    if (!form.email) errors.email = "L'adresse email est obligatoire.";
    if (!form.date_of_birth) errors.date_of_birth = "La date de naissance est obligatoire.";
    if (!form.password) errors.password = "Le mot de passe est obligatoire.";
    return;
  }

  if (!estEmailValide(form.email)) {
    errors.email = "Format d'email invalide.";
  }
  if (form.name.trim().length < 3) {
    errors.name = "Le nom d'utilisateur doit contenir au moins 3 caracteres.";
  }

  const passwordError = validerMotDePasse(form.password);
  if (passwordError) errors.password = passwordError;
  if (form.password !== form.password_confirmation) errors.password = "Les mots de passe ne correspondent pas.";
  validerFormatDate();
  if (errors.name || errors.email || errors.date_of_birth || errors.password) return;

  loading.value = true;

  try {
    const res = await api.post("/auth/register", {
      name: form.name,
      email: form.email,
      date_of_birth: convertirFormatDate(form.date_of_birth),
      password: form.password,
      password_confirmation: form.password_confirmation,
    });

    authStore.appliquerAuthentification(res?.data, "personnel");

    setTimeout(() => router.push(res?.data?.redirect_to || "/profil-sante"), 500);
  } catch (err) {
    const status = err?.response?.status;
    const data = err?.response?.data || {};

    if (status === 422 && data?.errors) {
      errors.name = premierMessage(data.errors.name);
      errors.email = premierMessage(data.errors.email);
      errors.date_of_birth = premierMessage(data.errors.date_of_birth);
      errors.password = premierMessage(data.errors.password);

      return;
    }

    if (status === 409 && data?.errors?.email) {
      errors.email = premierMessage(data.errors.email);
      return;
    }
  } finally {
    loading.value = false;
  }
}
</script>
