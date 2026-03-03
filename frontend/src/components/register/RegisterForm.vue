<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50/50 via-white to-teal-50/50">
    <div class="bg-white/80 border-b border-slate-200 backdrop-blur-sm sticky top-0 z-10">
      <div class="max-w-3xl mx-auto px-4 sm:px-6 py-6">
        <div class="text-center">
          <h1 class="text-2xl font-bold text-gray-900 mb-1">Créez votre compte</h1>
          <p class="text-sm text-gray-500">Rejoignez Assistant Santé en quelques secondes</p>
        </div>
      </div>
    </div>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 py-8 sm:py-12">
      <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8 md:p-10">
        <div class="mb-6">
          <h2 class="text-xl font-semibold text-gray-900">Informations personnelles</h2>
          <p class="text-sm text-gray-500 mt-1">Complétez les champs pour créer votre espace santé.</p>
        </div>

        <form @submit.prevent="submit" class="space-y-5">
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
            <label class="block text-sm font-medium text-gray-700 mb-2">Mot de passe <span class="text-red-600">*</span></label>
            <div class="relative">
              <svg class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" viewBox="0 0 24 24" fill="none">
                <path d="M7 11V8a5 5 0 0 1 10 0v3" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" />
                <path d="M6 11h12v10H6V11Z" stroke="currentColor" stroke-width="1.7" />
              </svg>
              <input
                v-model="form.password"
                type="password"
                placeholder="Min 8 caractères (lettres + chiffres)"
                autocomplete="new-password"
                @input="validatePasswordRequirements"
                class="h-12 pl-12 pr-4 rounded-xl border-2 bg-white text-gray-900 placeholder:text-gray-400 outline-none w-full"
                :class="errors.password ? 'border-red-300 focus:border-red-400 focus:ring-red-200' : 'border-gray-200 focus:border-teal-500 focus:ring-teal-500/20'"
              />
            </div>

            <p v-if="errors.password" class="mt-2 text-sm text-red-600">{{ errors.password }}</p>

            <div v-if="form.password" class="mt-3 space-y-1 text-xs">
              <p :class="form.password.length >= 8 ? 'text-teal-600' : 'text-gray-400'">OK Au moins 8 caractères</p>
              <p :class="/[a-zA-Z]/.test(form.password) ? 'text-teal-600' : 'text-gray-400'">OK Au moins une lettre</p>
              <p :class="/[0-9]/.test(form.password) ? 'text-teal-600' : 'text-gray-400'">OK Au moins un chiffre</p>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Confirmer le mot de passe</label>
            <input
              v-model="form.password_confirmation"
              type="password"
              placeholder="Répétez le mot de passe"
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
            <span v-if="!loading">Créer un compte</span>
            <span v-else>Création...</span>
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

          <p class="text-xs text-center text-gray-500">Vous pourrez compléter votre profil santé juste après.</p>
        </form>
      </div>

      <p class="text-center text-xs text-gray-400 mt-6">© {{ new Date().getFullYear() }} Assistant Santé</p>
    </div>
  </div>
</template>

<script setup>
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

const REQUIRED_FORM_MESSAGE = "Veuillez remplir les champs obligatoires.";
const EMAIL_ALREADY_USED_MESSAGE = "Cet email est déjà utilisé.";
const INVALID_EMAIL_MESSAGE = "Format d’email invalide.";
const PASSWORD_SHORT_MESSAGE = "Le mot de passe est trop court (min 8 caractères).";
const GENERIC_ERROR_MESSAGE = "Une erreur est survenue. Veuillez réessayer.";

const REQUIRED_FIELD_MESSAGES = {
  name: "Le nom d'utilisateur est obligatoire.",
  email: "L'adresse email est obligatoire.",
  date_of_birth: "La date de naissance est obligatoire.",
  password: "Le mot de passe est obligatoire.",
};

function clearErrors() {
  errors.name = "";
  errors.email = "";
  errors.date_of_birth = "";
  errors.password = "";
}

function clearForm() {
  form.name = "";
  form.email = "";
  form.date_of_birth = "";
  form.password = "";
  form.password_confirmation = "";
}

function isValidEmail(email) {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

function validatePassword(password) {
  if (password.length < 8) return PASSWORD_SHORT_MESSAGE;
  if (!/[a-zA-Z]/.test(password)) return "Le mot de passe doit contenir au moins une lettre.";
  if (!/[0-9]/.test(password)) return "Le mot de passe doit contenir au moins un chiffre.";
  return "";
}

function validatePasswordRequirements() {
  if (!form.password) {
    errors.password = "";
    return;
  }
  const message = validatePassword(form.password);
  if (!message) errors.password = "";
}

function handleDateBeforeInput(event) {
  lastDateInputType.value = event.inputType || "";
  if (event.data && /[^0-9/]/.test(event.data)) event.preventDefault();
}

function normalizeDateInput(value) {
  const digits = String(value || "").replace(/\D/g, "").slice(0, 8);
  if (digits.length <= 2) return digits;
  if (digits.length <= 4) return `${digits.slice(0, 2)}/${digits.slice(2)}`;
  return `${digits.slice(0, 2)}/${digits.slice(2, 4)}/${digits.slice(4, 8)}`;
}

function handleDateInput(event) {
  const raw = event?.target?.value ?? "";
  const formatted = normalizeDateInput(raw).slice(0, 10);
  form.date_of_birth = formatted;

  if (lastDateInputType.value !== "deleteContentBackward" && errors.date_of_birth) {
    validateDateFormat();
  }
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

function validateDateFormat() {
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
  const sameDate = date.getFullYear() === year && date.getMonth() === month - 1 && date.getDate() === day;
  if (!sameDate) {
    errors.date_of_birth = "Date invalide.";
    return;
  }

  if (date > new Date()) {
    errors.date_of_birth = "La date de naissance ne peut pas être dans le futur.";
    return;
  }

  if (calculateAge(date) < 18) {
    errors.date_of_birth = "Vous devez avoir au minimum 18 ans.";
    return;
  }

  errors.date_of_birth = "";
}

function convertDateFormat(dateStr) {
  const match = String(dateStr || "").match(/^(\d{2})\/(\d{2})\/(\d{4})$/);
  return match ? `${match[3]}-${match[2]}-${match[1]}` : dateStr;
}

function setMissingFieldErrors() {
  let hasMissing = false;

  if (!form.name) {
    errors.name = REQUIRED_FIELD_MESSAGES.name;
    hasMissing = true;
  }
  if (!form.email) {
    errors.email = REQUIRED_FIELD_MESSAGES.email;
    hasMissing = true;
  }
  if (!form.date_of_birth) {
    errors.date_of_birth = REQUIRED_FIELD_MESSAGES.date_of_birth;
    hasMissing = true;
  }
  if (!form.password) {
    errors.password = REQUIRED_FIELD_MESSAGES.password;
    hasMissing = true;
  }

  if (hasMissing) serverMessage.value = REQUIRED_FORM_MESSAGE;
  return hasMissing;
}

function firstMessage(value) {
  if (Array.isArray(value)) return value[0] || "";
  if (typeof value === "string") return value;
  return "";
}

function mapEmailError(message) {
  const normalized = String(message || "").toLowerCase();
  if (!normalized) return "";
  if (/(taken|already|existe|utilis|unique|déjà|deja|duplicate|1062)/.test(normalized)) return EMAIL_ALREADY_USED_MESSAGE;
  if (/(required|obligatoire|requis)/.test(normalized)) return REQUIRED_FIELD_MESSAGES.email;
  if (/(valid email|must be.*email|format|invalide|adresse e-mail)/.test(normalized)) return INVALID_EMAIL_MESSAGE;
  return "";
}

function mapPasswordError(message) {
  const normalized = String(message || "").toLowerCase();
  if (!normalized) return "";
  if (/(required|obligatoire|requis)/.test(normalized)) return REQUIRED_FIELD_MESSAGES.password;
  if (/(minimum|min\.?|at least|au moins).*(8|huit)|8.*(character|caract)|too short|trop court/.test(normalized)) {
    return PASSWORD_SHORT_MESSAGE;
  }
  return "";
}

function mapFieldValidationErrors(validationErrors = {}) {
  const nameBackend = firstMessage(validationErrors.name);
  const emailBackend = firstMessage(validationErrors.email);
  const dobBackend = firstMessage(validationErrors.date_of_birth);
  const passwordBackend = firstMessage(validationErrors.password);

  errors.name = /(required|obligatoire|requis)/i.test(nameBackend) ? REQUIRED_FIELD_MESSAGES.name : nameBackend;
  errors.email = mapEmailError(emailBackend) || emailBackend;
  errors.date_of_birth = /(required|obligatoire|requis)/i.test(dobBackend) ? REQUIRED_FIELD_MESSAGES.date_of_birth : dobBackend;
  errors.password = mapPasswordError(passwordBackend) || passwordBackend;
}

function mapTopLevelBackendMessage(message) {
  const normalized = String(message || "").toLowerCase();
  if (!normalized) return "";
  if (/(taken|already|existe|utilis|unique|déjà|deja|duplicate|1062)/.test(normalized)) return EMAIL_ALREADY_USED_MESSAGE;
  if (/(required|obligatoire|requis)/.test(normalized)) return REQUIRED_FORM_MESSAGE;
  if (/(valid email|must be.*email|format|invalide|adresse e-mail)/.test(normalized)) return INVALID_EMAIL_MESSAGE;
  if (/(minimum|min\.?|at least|au moins).*(8|huit)|8.*(character|caract)|too short|trop court/.test(normalized)) {
    return PASSWORD_SHORT_MESSAGE;
  }
  return "";
}

async function submit() {
  serverMessage.value = "";
  messageType.value = "success";
  clearErrors();

  if (setMissingFieldErrors()) return;

  if (!isValidEmail(form.email)) {
    errors.email = INVALID_EMAIL_MESSAGE;
  }

  if (form.password) {
    const passwordError = validatePassword(form.password);
    if (passwordError) errors.password = passwordError;
  }

  if (form.password !== form.password_confirmation) {
    errors.password = "Les mots de passe ne correspondent pas.";
  }

  validateDateFormat();

  if (errors.name || errors.email || errors.date_of_birth || errors.password) return;

  loading.value = true;

  try {
    const payload = {
      name: form.name,
      email: form.email,
      date_of_birth: convertDateFormat(form.date_of_birth),
      password: form.password,
      password_confirmation: form.password_confirmation,
    };

    const res = await api.post("/auth/register", payload);

    const token = res?.data?.token;
    if (token) {
      localStorage.setItem("auth_token", token);
      api.defaults.headers.common.Authorization = `Bearer ${token}`;
    }

    serverMessage.value = res?.data?.message || "Compte créé avec succès.";
    messageType.value = "success";

    clearForm();

    const destination = res?.data?.redirect_to || "/profil-sante";
    setTimeout(() => router.push(destination), 500);
  } catch (err) {
    messageType.value = "error";

    const data = err?.response?.data;
    const status = err?.response?.status;

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
      serverMessage.value = mappedMessage || GENERIC_ERROR_MESSAGE;

      if (mappedMessage === "Cet email est déjà utilisé.") {
        errors.email = mappedMessage;
      }
      if (mappedMessage === INVALID_EMAIL_MESSAGE) {
        errors.email = mappedMessage;
      }
      if (mappedMessage === PASSWORD_SHORT_MESSAGE) {
        errors.password = mappedMessage;
      }
      if (mappedMessage === REQUIRED_FORM_MESSAGE) {
        if (!form.name) errors.name = REQUIRED_FIELD_MESSAGES.name;
        if (!form.email) errors.email = REQUIRED_FIELD_MESSAGES.email;
        if (!form.date_of_birth) errors.date_of_birth = REQUIRED_FIELD_MESSAGES.date_of_birth;
        if (!form.password) errors.password = REQUIRED_FIELD_MESSAGES.password;
      }
    } else if (status === 500) {
      serverMessage.value = "Erreur serveur. Veuillez réessayer plus tard.";
    } else {
      serverMessage.value = GENERIC_ERROR_MESSAGE;
    }
  } finally {
    loading.value = false;
  }
}
</script>
