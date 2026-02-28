<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50/50 via-white to-teal-50/50">
    <div class="bg-white/80 border-b border-slate-200 backdrop-blur-sm sticky top-0 z-10">
      <div class="max-w-3xl mx-auto px-4 sm:px-6 py-6">
        <div class="text-center">
          <h1 class="text-2xl font-bold text-gray-900 mb-1">Creation de ton compte</h1>
          <p class="text-sm text-gray-500">Rejoins Assistant Sante en quelques secondes</p>
        </div>
      </div>
    </div>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 py-8 sm:py-12">
      <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8 md:p-10">
        <div class="mb-6">
          <h2 class="text-xl font-semibold text-gray-900">Informations personnelles</h2>
          <p class="text-sm text-gray-500 mt-1">Complete les champs pour creer ton espace sante.</p>
        </div>

        <form @submit.prevent="submit" class="space-y-5">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Nom d'utilisateur</label>
            <div class="relative">
              <svg class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" viewBox="0 0 24 24" fill="none">
                <path d="M20 21a8 8 0 0 0-16 0" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" />
                <path d="M12 13a5 5 0 1 0-5-5 5 5 0 0 0 5 5Z" stroke="currentColor" stroke-width="1.7" />
              </svg>
              <input
                v-model.trim="form.name"
                type="text"
                placeholder="Entrer votre nom d'utilisateur"
                autocomplete="name"
                class="h-12 pl-12 pr-4 rounded-xl border-2 bg-white text-gray-900 placeholder:text-gray-400 outline-none w-full"
                :class="errors.name ? 'border-red-300 focus:border-red-400 focus:ring-red-200' : 'border-gray-200 focus:border-teal-500 focus:ring-teal-500/20'"
              />
            </div>
            <p v-if="errors.name" class="mt-2 text-sm text-red-600">{{ errors.name }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
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
            <label class="block text-sm font-medium text-gray-700 mb-2">Date de naissance</label>
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
                @beforeinput="handleDateBeforeInput"
                @input="handleDateInput"
                @blur="validateDateFormat"
                class="h-12 pl-12 pr-4 rounded-xl border-2 bg-white text-gray-900 placeholder:text-gray-400 outline-none w-full"
                :class="errors.date_of_birth ? 'border-red-300 focus:border-red-400 focus:ring-red-200' : 'border-gray-200 focus:border-teal-500 focus:ring-teal-500/20'"
              />
            </div>
            <p v-if="errors.date_of_birth" class="mt-2 text-sm text-red-600">{{ errors.date_of_birth }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Mot de passe</label>
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
                @input="validatePasswordRequirements"
                class="h-12 pl-12 pr-4 rounded-xl border-2 bg-white text-gray-900 placeholder:text-gray-400 outline-none w-full"
                :class="errors.password ? 'border-red-300 focus:border-red-400 focus:ring-red-200' : 'border-gray-200 focus:border-teal-500 focus:ring-teal-500/20'"
              />
            </div>

            <p v-if="errors.password" class="mt-2 text-sm text-red-600">{{ errors.password }}</p>

            <div v-if="form.password" class="mt-3 space-y-1 text-xs">
              <p :class="form.password.length >= 8 ? 'text-teal-600' : 'text-gray-400'">OK Au moins 8 caracteres</p>
              <p :class="/[a-zA-Z]/.test(form.password) ? 'text-teal-600' : 'text-gray-400'">OK Au moins une lettre</p>
              <p :class="/[0-9]/.test(form.password) ? 'text-teal-600' : 'text-gray-400'">OK Au moins un chiffre</p>
            </div>
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

          <div
            v-if="serverMessage"
            class="rounded-xl border px-4 py-3 text-sm"
            :class="messageType === 'success'
              ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
              : 'border-red-200 bg-red-50 text-red-700'"
          >
            {{ serverMessage }}
          </div>

          <p class="text-xs text-center text-gray-500">Tu pourras completer ton profil sante juste apres.</p>
        </form>
      </div>

      <p class="text-center text-xs text-gray-400 mt-6">© {{ new Date().getFullYear() }} Assistant Sante</p>
    </div>
  </div>
</template>

<script setup>
/*
  Formulaire d'inscription utilisateur.
  Cette vue gere la validation locale (email, date, mot de passe) puis l'appel API.
  Les messages backend sont mappes vers des erreurs lisibles pour l'etudiant.
*/

import api from "@/services/api";
import { reactive, ref } from "vue";
import { useRouter } from "vue-router";

const router = useRouter();

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
const serverMessage = ref("");
const messageType = ref("success");
const lastDateInputType = ref("");

// Reset simple des erreurs de formulaire entre deux soumissions.
function clearErrors() {
  errors.name = "";
  errors.email = "";
  errors.date_of_birth = "";
  errors.password = "";
}

function isValidEmail(email) {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

function validatePassword(password) {
  if (password.length < 8) return "Le mot de passe doit contenir au minimum 8 caracteres";
  if (!/[a-zA-Z]/.test(password)) return "Le mot de passe doit contenir au moins une lettre";
  if (!/[0-9]/.test(password)) return "Le mot de passe doit contenir au moins un chiffre";
  return "";
}

function validatePasswordRequirements() {
  const passwordError = validatePassword(form.password);
  if (form.password && !passwordError) errors.password = "";
}

// On accepte seulement chiffres + "/" et on garde la frappe naturelle.
function handleDateBeforeInput(event) {
  lastDateInputType.value = event.inputType || "";
  if (event.data && /[^0-9/]/.test(event.data)) {
    event.preventDefault();
  }
}

function normalizeDateInput(value) {
  const digits = String(value || "").replace(/\D/g, "").slice(0, 8);
  if (digits.length <= 2) return digits;
  if (digits.length <= 4) return `${digits.slice(0, 2)}/${digits.slice(2)}`;
  return `${digits.slice(0, 2)}/${digits.slice(2, 4)}/${digits.slice(4, 8)}`;
}

function handleDateInput(event) {
  const raw = event?.target?.value ?? "";
  const cleaned = String(raw).replace(/[^0-9/]/g, "");
  const formatted = normalizeDateInput(cleaned).slice(0, 10);

  form.date_of_birth = formatted;

  if (lastDateInputType.value !== "deleteContentBackward" && errors.date_of_birth) {
    validateDateFormat();
  }
}

// Validation stricte JJ/MM/AAAA + verification d'age minimum.
function validateDateFormat() {
  if (!form.date_of_birth) {
    errors.date_of_birth = "";
    return;
  }

  const dateRegex = /^(\d{2})\/(\d{2})\/(\d{4})$/;
  const match = form.date_of_birth.match(dateRegex);

  if (!match) {
    errors.date_of_birth = "Format invalide. Utilisez JJ/MM/AAAA";
    return;
  }

  const day = parseInt(match[1]);
  const month = parseInt(match[2]);
  const year = parseInt(match[3]);

  const date = new Date(year, month - 1, day);
  if (date.getFullYear() !== year || date.getMonth() !== month - 1 || date.getDate() !== day) {
    errors.date_of_birth = "Date invalide";
    return;
  }

  if (date > new Date()) {
    errors.date_of_birth = "La date de naissance ne peut pas etre dans le futur";
    return;
  }

  const age = calculateAge(date);
  if (age < 18) {
    errors.date_of_birth = "Vous devez avoir au minimum 18 ans";
    return;
  }

  errors.date_of_birth = "";
}

function calculateAge(birthDate) {
  const today = new Date();
  let age = today.getFullYear() - birthDate.getFullYear();
  const monthDiff = today.getMonth() - birthDate.getMonth();

  if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
    age--;
  }

  return age;
}

// Conversion UI (JJ/MM/AAAA) vers format API (YYYY-MM-DD).
function convertDateFormat(dateStr) {
  const dateRegex = /^(\d{2})\/(\d{2})\/(\d{4})$/;
  const match = dateStr.match(dateRegex);
  if (match) {
    return `${match[3]}-${match[2]}-${match[1]}`;
  }
  return dateStr;
}

function setMissingFieldErrors() {
  let hasMissing = false;

  if (!form.name) {
    errors.name = "Veuillez remplir tous les champs requis.";
    hasMissing = true;
  }

  if (!form.email) {
    errors.email = "Veuillez remplir tous les champs requis.";
    hasMissing = true;
  }

  if (!form.date_of_birth) {
    errors.date_of_birth = "Veuillez remplir tous les champs requis.";
    hasMissing = true;
  }

  if (!form.password) {
    errors.password = "Veuillez remplir tous les champs requis.";
    hasMissing = true;
  }

  if (hasMissing) {
    serverMessage.value = "Veuillez remplir tous les champs requis.";
  }

  return hasMissing;
}

// Helpers pour normaliser les messages serveur dans un langage utilisateur.
function firstMessage(fieldErrors) {
  return Array.isArray(fieldErrors) ? fieldErrors[0] || "" : "";
}

function mapEmailError(message) {
  const normalized = String(message || "").toLowerCase();
  if (!normalized) return "";
  if (/taken|already|existe|utilis|unique|déjà|deja/.test(normalized)) return "Cet email est déjà utilisé.";
  if (/required|obligatoire|requis/.test(normalized)) return "Veuillez remplir tous les champs requis.";
  if (/valid email|must be.*email|format|invalide|adresse e-mail/.test(normalized)) return "Format d’email invalide.";
  return "";
}

function mapPasswordError(message) {
  const normalized = String(message || "").toLowerCase();
  if (!normalized) return "";
  if (/required|obligatoire|requis/.test(normalized)) return "Veuillez remplir tous les champs requis.";
  if (/(minimum|min\.?|at least|au moins).*(8|huit)|8.*(character|caract)|too short|trop court/.test(normalized)) {
    return "Le mot de passe est trop court (min 8 caractères).";
  }
  return "";
}

function mapFieldValidationErrors(validationErrors = {}) {
  const emailBackend = firstMessage(validationErrors.email);
  const passwordBackend = firstMessage(validationErrors.password);
  const nameBackend = firstMessage(validationErrors.name);
  const dobBackend = firstMessage(validationErrors.date_of_birth);

  const mappedEmail = mapEmailError(emailBackend);
  const mappedPassword = mapPasswordError(passwordBackend);

  errors.email = mappedEmail || emailBackend || "";
  errors.password = mappedPassword || passwordBackend || "";

  if (/required|obligatoire|requis/i.test(nameBackend || "")) {
    errors.name = "Veuillez remplir tous les champs requis.";
  } else {
    errors.name = nameBackend || "";
  }

  if (/required|obligatoire|requis/i.test(dobBackend || "")) {
    errors.date_of_birth = "Veuillez remplir tous les champs requis.";
  } else {
    errors.date_of_birth = dobBackend || "";
  }
}

function mapTopLevelBackendMessage(message) {
  const normalized = String(message || "").toLowerCase();
  if (!normalized) return "";
  if (/taken|already|existe|utilis|unique|déjà|deja/.test(normalized)) return "Cet email est déjà utilisé.";
  if (/required|obligatoire|requis/.test(normalized)) return "Veuillez remplir tous les champs requis.";
  if (/valid email|must be.*email|format|invalide|adresse e-mail/.test(normalized)) return "Format d’email invalide.";
  if (/(minimum|min\.?|at least|au moins).*(8|huit)|8.*(character|caract)|too short|trop court/.test(normalized)) {
    return "Le mot de passe est trop court (min 8 caractères).";
  }
  return "";
}

async function submit() {
  // Pipeline de soumission: reset -> validation locale -> appel API -> mapping erreurs.
  serverMessage.value = "";
  messageType.value = "success";
  clearErrors();

  if (setMissingFieldErrors()) return;

  if (!isValidEmail(form.email)) {
    errors.email = "Format d’email invalide.";
  }

  if (form.password) {
    const passwordError = validatePassword(form.password);
    if (passwordError) {
      errors.password =
        form.password.length < 8
          ? "Le mot de passe est trop court (min 8 caractères)."
          : passwordError;
    }
  }

  if (form.password !== form.password_confirmation) {
    errors.password = "Les mots de passe ne correspondent pas";
  }

  validateDateFormat();

  if (errors.name || errors.email || errors.date_of_birth || errors.password) return;

  loading.value = true;

  try {
    const submitData = {
      name: form.name,
      email: form.email,
      date_of_birth: convertDateFormat(form.date_of_birth),
      password: form.password,
      password_confirmation: form.password_confirmation,
    };

    const res = await api.post("/auth/register", submitData);

    const token = res.data.token;
    localStorage.setItem("auth_token", token);
    api.defaults.headers.common["Authorization"] = `Bearer ${token}`;

    serverMessage.value = res.data.message || "Compte cree avec succes";
    messageType.value = "success";

    form.name = "";
    form.email = "";
    form.date_of_birth = "";
    form.password = "";
    form.password_confirmation = "";

    const destination = res?.data?.redirect_to || "/profil-sante";
    setTimeout(() => {
      router.push(destination);
    }, 500);
  } catch (err) {
    messageType.value = "error";
    const data = err?.response?.data;
    const status = err?.response?.status;

    console.error("Registration error:", { status, data, error: err.message });

    if (!err?.response) {
      serverMessage.value = "Problème réseau. Réessayez.";
      return;
    }

    if (status === 409) {
      errors.email = "Cet email est déjà utilisé.";
      serverMessage.value = "Cet email est déjà utilisé.";
    } else if (status === 422 && data?.errors) {
      mapFieldValidationErrors(data.errors);
      const knownFieldMessages = [errors.name, errors.email, errors.date_of_birth, errors.password].filter(Boolean);
      const uniqueMessages = [...new Set(knownFieldMessages)];
      serverMessage.value = uniqueMessages.length === 1 ? uniqueMessages[0] : "Veuillez corriger les erreurs du formulaire.";
    } else if (data?.message) {
      const mappedMessage = mapTopLevelBackendMessage(data.message);
      serverMessage.value = mappedMessage || "Une erreur est survenue. Veuillez réessayer.";

      if (mappedMessage === "Cet email est déjà utilisé.") {
        errors.email = mappedMessage;
      }
      if (mappedMessage === "Format d’email invalide.") {
        errors.email = mappedMessage;
      }
      if (mappedMessage === "Le mot de passe est trop court (min 8 caractères).") {
        errors.password = mappedMessage;
      }
      if (mappedMessage === "Veuillez remplir tous les champs requis.") {
        if (!form.name) errors.name = mappedMessage;
        if (!form.email) errors.email = mappedMessage;
        if (!form.date_of_birth) errors.date_of_birth = mappedMessage;
        if (!form.password) errors.password = mappedMessage;
      }
    } else if (status === 500) {
      serverMessage.value = "Erreur serveur. Veuillez reessayer plus tard.";
    } else {
      serverMessage.value = "Une erreur est survenue. Veuillez réessayer.";
    }
  } finally {
    loading.value = false;
  }
}
</script>
