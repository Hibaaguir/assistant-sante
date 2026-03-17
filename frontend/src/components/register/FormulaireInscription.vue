<template>
  <div class="min-h-screen bg-gray-50 flex items-center justify-center px-4 py-8">
    <!-- Contenu Inscription avec Retour -->
    <div class="w-full max-w-2xl">
      <!-- Bouton Retour -->
      <button
        @click="$router.back()"
        class="flex items-center gap-1 text-gray-600 hover:text-gray-900 text-sm font-medium transition-colors mb-6 ml-6"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
        </svg>
        Retour
      </button>

      <div class="bg-white rounded-3xl shadow-lg p-10">
        <!-- Logo et Titre -->
        <div class="text-center mb-8">
          <!-- Logo et nom HealthFlow -->
          <div class="flex justify-center items-center gap-3 mb-6">
            <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center flex-shrink-0">
              <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
              </svg>
            </div>
            <h1 class="text-3xl font-bold text-blue-600">HealthFlow</h1>
          </div>
          
          <h2 class="text-2xl font-bold text-gray-900 mb-2">Créer un compte</h2>
          <p class="text-base text-gray-600">Commencez votre parcours santé gratuitement</p>
        </div>

        <!-- Formulaire -->
        <form @submit.prevent="soumettre" class="space-y-5">
          <!-- Champ Nom complet -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Nom complet</label>
            <div class="relative">
              <svg class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
              </svg>
              <input
                v-model.trim="form.name"
                type="text"
                placeholder="Votre nom"
                autocomplete="name"
                class="w-full h-12 pl-12 pr-4 rounded-lg border bg-gray-50 text-base text-gray-900 placeholder:text-gray-400 outline-none transition-colors"
                :class="errors.name ? 'border-red-300 focus:border-red-500 focus:bg-white' : 'border-gray-200 focus:border-blue-500 focus:bg-white'"
              />
            </div>
            <p v-if="errors.name" class="mt-1.5 text-sm text-red-600">{{ errors.name }}</p>
          </div>

          <!-- Champ Email -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
            <div class="relative">
              <svg class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
              </svg>
              <input
                v-model.trim="form.email"
                type="email"
                placeholder="votre@email.com"
                autocomplete="email"
                class="w-full h-12 pl-12 pr-4 rounded-lg border bg-gray-50 text-base text-gray-900 placeholder:text-gray-400 outline-none transition-colors"
                :class="errors.email ? 'border-red-300 focus:border-red-500 focus:bg-white' : 'border-gray-200 focus:border-blue-500 focus:bg-white'"
              />
            </div>
            <p v-if="errors.email" class="mt-1.5 text-sm text-red-600">{{ errors.email }}</p>
          </div>

          <!-- Champ Date de naissance -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Date de naissance</label>
            <div class="relative">
              <svg class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
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
                class="w-full h-12 pl-12 pr-4 rounded-lg border bg-gray-50 text-base text-gray-900 placeholder:text-gray-400 outline-none transition-colors"
                :class="errors.date_of_birth ? 'border-red-300 focus:border-red-500 focus:bg-white' : 'border-gray-200 focus:border-blue-500 focus:bg-white'"
              />
            </div>
            <p v-if="errors.date_of_birth" class="mt-1.5 text-sm text-red-600">{{ errors.date_of_birth }}</p>
          </div>

          <!-- Champ Mot de passe -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Mot de passe</label>
            <div class="relative">
              <svg class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
              </svg>
              <input
                v-model="form.password"
                type="password"
                placeholder="••••••••"
                autocomplete="new-password"
                @input="validerReglesMotDePasse"
                class="w-full h-12 pl-12 pr-4 rounded-lg border bg-gray-50 text-base text-gray-900 placeholder:text-gray-400 outline-none transition-colors"
                :class="errors.password ? 'border-red-300 focus:border-red-500 focus:bg-white' : 'border-gray-200 focus:border-blue-500 focus:bg-white'"
              />
            </div>
            
            <!-- Critères de validation - Au dessous du champ -->
            <div class="mt-3 space-y-1.5">
              <p :class="motDePasseAlaBonneLongueur ? 'text-purple-600 font-medium' : 'text-gray-400'" class="text-xs transition-colors">
                Au moins 8 caractères
              </p>
              <p :class="motDePasseContientLettre ? 'text-purple-600 font-medium' : 'text-gray-400'" class="text-xs transition-colors">
                Au moins une lettre
              </p>
              <p :class="motDePasseContientNombre ? 'text-purple-600 font-medium' : 'text-gray-400'" class="text-xs transition-colors">
                Au moins un chiffre
              </p>
            </div>
            
            <p v-if="errors.password" class="mt-2 text-sm text-red-600">{{ errors.password }}</p>
          </div>

          <!-- Champ Confirmer le mot de passe -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Confirmer le mot de passe</label>
            <div class="relative">
              <svg class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
              </svg>
              <input
                v-model="form.password_confirmation"
                type="password"
                placeholder="••••••••"
                autocomplete="new-password"
                class="w-full h-12 pl-12 pr-4 rounded-lg border bg-gray-50 text-base text-gray-900 placeholder:text-gray-400 outline-none transition-colors"
                :class="errors.password ? 'border-red-300 focus:border-red-500 focus:bg-white' : 'border-gray-200 focus:border-blue-500 focus:bg-white'"
              />
            </div>
          </div>

          <!-- Checkbox Conditions -->
          <label class="flex items-start gap-3 cursor-pointer pt-2">
            <input
              type="checkbox"
              class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 mt-0.5 flex-shrink-0"
            />
            <span class="text-sm text-gray-700">
              J'accepte les
              <a href="#" class="text-blue-600 font-semibold hover:text-blue-700">conditions d'utilisation</a>
              et la
              <a href="#" class="text-blue-600 font-semibold hover:text-blue-700">politique de confidentialité</a>
            </span>
          </label>

          <!-- Bouton Créer mon compte -->
          <button
            type="submit"
            :disabled="loading"
            class="w-full h-12 rounded-lg bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-semibold text-base transition-all disabled:opacity-50 disabled:cursor-not-allowed mt-6"
          >
            <span v-if="!loading">Créer mon compte</span>
            <span v-else>Création...</span>
          </button>

          <!-- Séparateur -->
          <div class="flex items-center gap-3 my-5">
            <div class="flex-1 h-px bg-gray-200"></div>
            <span class="text-sm text-gray-500">ou</span>
            <div class="flex-1 h-px bg-gray-200"></div>
          </div>

          <!-- Bouton Google -->
          <button
            type="button"
            class="w-full h-12 rounded-lg border border-gray-200 hover:border-gray-300 bg-white text-gray-700 font-medium text-base transition-colors flex items-center justify-center gap-2"
          >
            <svg class="w-5 h-5" viewBox="0 0 24 24">
              <text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" font-size="10" fill="currentColor" font-weight="bold">G</text>
            </svg>
            Continuer avec Google
          </button>

          <!-- Lien connexion -->
          <p class="text-center text-sm text-gray-600 pt-4">
            Vous avez déjà un compte ?
            <RouterLink :to="{ name: 'connexion' }" class="text-blue-600 font-semibold hover:text-blue-700 transition-colors">
              Se connecter
            </RouterLink>
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
