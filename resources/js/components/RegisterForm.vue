<template>
  <!-- Background clair style Med -->
  <div class="min-h-screen bg-gradient-to-b from-[#EEF7FB] to-white px-4 py-10">
    <div class="max-w-md mx-auto">
      <!-- Header -->
      <div class="text-center mb-8">
        <!-- mini logo -->
        <div class="mx-auto w-16 h-16 rounded-2xl bg-[#0C86C6] flex items-center justify-center shadow-sm">
          <span class="text-white font-extrabold text-2xl">Med</span>
        </div>

        <h1 class="mt-5 text-3xl font-extrabold tracking-tight text-[#0B2B4B]">
          Créer votre compte
        </h1>
        <p class="mt-2 text-sm text-[#335A78]">
          Accédez à votre assistant santé
        </p>

        <!-- ligne jaune comme la capture -->
        <div class="mt-5 h-1 w-full rounded-full bg-[#F5C400] shadow-[0_2px_0_rgba(0,0,0,0.06)]"></div>
      </div>

      <!-- Card -->
      <div class="bg-white rounded-2xl border border-slate-200/70 shadow-sm overflow-hidden">
        <!-- top bar light -->
        <div class="px-6 py-4 bg-[#F6FBFE] border-b border-slate-200/70">
          <h2 class="text-[#0B2B4B] font-bold text-lg">Informations</h2>
          <p class="text-xs text-[#335A78] mt-1">Veuillez remplir le formulaire</p>
        </div>

        <div class="p-6">
          <form @submit.prevent="submit" class="space-y-4">
            <!-- Username -->
            <div>
              <label class="block text-sm font-semibold text-[#0B2B4B] mb-2">
                Nom d’utilisateur
              </label>
              <div class="relative">
                <input
                  v-model.trim="form.name"
                  type="text"
                  placeholder="Entrer votre nom d’utilisateur"
                  autocomplete="username"
                  class="w-full rounded-xl bg-white border px-4 py-3 pr-12 text-[#0B2B4B] placeholder:text-slate-400
                         outline-none focus:ring-4 focus:ring-[#0C86C6]/15 focus:border-[#0C86C6]"
                  :class="errors.name ? 'border-red-400' : 'border-slate-200'"
                />
                <span class="pointer-events-none absolute inset-y-0 right-4 flex items-center text-slate-400">
                  <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                    <path d="M20 21a8 8 0 0 0-16 0" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                    <path d="M12 13a5 5 0 1 0-5-5 5 5 0 0 0 5 5Z" stroke="currentColor" stroke-width="1.7"/>
                  </svg>
                </span>
              </div>
              <p v-if="errors.name" class="mt-2 text-sm text-red-600">{{ errors.name }}</p>
            </div>

            <!-- Email -->
            <div>
              <label class="block text-sm font-semibold text-[#0B2B4B] mb-2">
                Email
              </label>
              <div class="relative">
                <input
                  v-model.trim="form.email"
                  type="email"
                  placeholder="votrenom@campagne.com"
                  autocomplete="email"
                  class="w-full rounded-xl bg-white border px-4 py-3 pr-12 text-[#0B2B4B] placeholder:text-slate-400
                         outline-none focus:ring-4 focus:ring-[#0C86C6]/15 focus:border-[#0C86C6]"
                  :class="errors.email ? 'border-red-400' : 'border-slate-200'"
                />
                <span class="pointer-events-none absolute inset-y-0 right-4 flex items-center text-slate-400">
                  <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                    <path d="M4 6h16v12H4V6Z" stroke="currentColor" stroke-width="1.7"/>
                    <path d="m4 7 8 6 8-6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                  </svg>
                </span>
              </div>
              <p v-if="errors.email" class="mt-2 text-sm text-red-600">{{ errors.email }}</p>
            </div>

            <!-- Date de naissance -->
            <div>
              <label class="block text-sm font-semibold text-[#0B2B4B] mb-2">
                Date de naissance
              </label>
              <div class="relative">
                <input
                  v-model="form.date_of_birth"
                  type="text"
                  placeholder="JJ/MM/AAAA"
                  @blur="validateDateFormat"
                  class="w-full rounded-xl bg-white border px-4 py-3 pr-12 text-[#0B2B4B] placeholder:text-slate-400
                         outline-none focus:ring-4 focus:ring-[#0C86C6]/15 focus:border-[#0C86C6]"
                  :class="errors.date_of_birth ? 'border-red-400' : 'border-slate-200'"
                />
                <span class="pointer-events-none absolute inset-y-0 right-4 flex items-center text-slate-400">
                  <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                    <path d="M6 4h12M6 4v14a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4M9 6v4M15 6v4M6 12h12" 
                          stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                  </svg>
                </span>
              </div>
              <p v-if="errors.date_of_birth" class="mt-2 text-sm text-red-600">{{ errors.date_of_birth }}</p>
            </div>

            <!-- Password -->
            <div>
              <label class="block text-sm font-semibold text-[#0B2B4B] mb-2">
                Mot de passe
              </label>
              <div class="relative">
                <input
                  v-model="form.password"
                  type="password"
                  placeholder="Min 8 caractères (lettres + chiffres)"
                  autocomplete="new-password"
                  @input="validatePasswordRequirements"
                  class="w-full rounded-xl bg-white border px-4 py-3 pr-12 text-[#0B2B4B] placeholder:text-slate-400
                         outline-none focus:ring-4 focus:ring-[#0C86C6]/15 focus:border-[#0C86C6]"
                  :class="errors.password ? 'border-red-400' : 'border-slate-200'"
                />
                <span class="pointer-events-none absolute inset-y-0 right-4 flex items-center text-slate-400">
                  <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                    <path d="M7 11V8a5 5 0 0 1 10 0v3" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                    <path d="M6 11h12v10H6V11Z" stroke="currentColor" stroke-width="1.7"/>
                  </svg>
                </span>
              </div>

              <p v-if="errors.password" class="mt-2 text-sm text-red-600">{{ errors.password }}</p>

              <div v-if="form.password" class="mt-3 space-y-1 text-xs">
                <p :class="form.password.length >= 8 ? 'text-[#0C86C6]' : 'text-slate-400'">
                  ✓ Au moins 8 caractères
                </p>
                <p :class="/[a-zA-Z]/.test(form.password) ? 'text-[#0C86C6]' : 'text-slate-400'">
                  ✓ Au moins une lettre
                </p>
                <p :class="/[0-9]/.test(form.password) ? 'text-[#0C86C6]' : 'text-slate-400'">
                  ✓ Au moins un chiffre
                </p>
              </div>
            </div>

            <!-- Confirm Password -->
            <div>
              <label class="block text-sm font-semibold text-[#0B2B4B] mb-2">
                Confirmer le mot de passe
              </label>
              <input
                v-model="form.password_confirmation"
                type="password"
                placeholder="Répétez le mot de passe"
                autocomplete="new-password"
                class="w-full rounded-xl bg-white border px-4 py-3 text-[#0B2B4B] placeholder:text-slate-400
                       outline-none focus:ring-4 focus:ring-[#0C86C6]/15 focus:border-[#0C86C6]"
                :class="errors.password ? 'border-red-300' : 'border-slate-200'"
              />
            </div>

            <!-- CTA -->
            <button
              type="submit"
              :disabled="loading"
              class="w-full rounded-xl py-3 font-bold text-white
                     bg-[#0C86C6] hover:bg-[#0A76AE] active:bg-[#086997]
                     shadow-sm transition disabled:opacity-60 disabled:cursor-not-allowed"
            >
              <span v-if="!loading">Créer un compte</span>
              <span v-else>Création…</span>
            </button>

            <!-- Message -->
            <div
              v-if="serverMessage"
              class="rounded-xl border px-4 py-3 text-sm"
              :class="messageType === 'success'
                ? 'border-[#0C86C6]/25 bg-[#0C86C6]/10 text-[#0B2B4B]'
                : 'border-red-200 bg-red-50 text-red-800'"
            >
              {{ serverMessage }}
            </div>

            <p class="pt-2 text-xs text-[#335A78] text-center">
              Expérience moderne et sécurisée.
            </p>
          </form>
        </div>

        <!-- bottom accent jaune -->
        <div class="h-2 bg-[#F5C400]"></div>
      </div>

      <p class="text-center text-xs text-[#335A78] mt-6">
        © {{ new Date().getFullYear() }} Assistant Santé
      </p>
    </div>
  </div>
</template>

<script setup>
import axios from "axios";
import { reactive, ref } from "vue";

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

function clearErrors() {
  errors.name = "";
  errors.email = "";
  errors.date_of_birth = "";
  errors.password = "";
}

function validatePassword(password) {
  if (password.length < 8) return "Le mot de passe doit contenir au minimum 8 caractères";
  if (!/[a-zA-Z]/.test(password)) return "Le mot de passe doit contenir au moins une lettre";
  if (!/[0-9]/.test(password)) return "Le mot de passe doit contenir au moins un chiffre";
  return "";
}

function validatePasswordRequirements() {
  const passwordError = validatePassword(form.password);
  if (form.password && !passwordError) errors.password = "";
}

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
    errors.date_of_birth = "La date de naissance ne peut pas être dans le futur";
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

function convertDateFormat(dateStr) {
  const dateRegex = /^(\d{2})\/(\d{2})\/(\d{4})$/;
  const match = dateStr.match(dateRegex);
  if (match) {
    return `${match[3]}-${match[2]}-${match[1]}`;
  }
  return dateStr;
}

async function submit() {
  serverMessage.value = "";
  messageType.value = "success";
  clearErrors();

  if (!form.name) errors.name = "Nom d’utilisateur obligatoire";
  if (!form.email) errors.email = "Email obligatoire";
  if (!form.date_of_birth) errors.date_of_birth = "Date de naissance obligatoire";
  if (!form.password) errors.password = "Mot de passe obligatoire";

  if (form.password) {
    const passwordError = validatePassword(form.password);
    if (passwordError) errors.password = passwordError;
  }

  if (form.password !== form.password_confirmation) {
    errors.password = "Les mots de passe ne correspondent pas";
  }

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

    const res = await axios.post("/api/register", submitData, {
      headers: { "Content-Type": "application/json" },
    });

    const token = res.data.token;
    localStorage.setItem("auth_token", token);
    window.axios.defaults.headers.common["Authorization"] = `Bearer ${token}`;

    serverMessage.value = res.data.message || "Compte créé avec succès";
    messageType.value = "success";

    form.name = "";
    form.email = "";
    form.date_of_birth = "";
    form.password = "";
    form.password_confirmation = "";

    setTimeout(() => {
      window.location.href = "/profil-sante";
    }, 1500);
  } catch (err) {
    messageType.value = "error";
    const data = err?.response?.data;
    const status = err?.response?.status;

    console.error("Registration error:", { status, data, error: err.message });

    if (data?.errors) {
      errors.name = data.errors.name?.[0] ?? "";
      errors.email = data.errors.email?.[0] ?? "";
      errors.date_of_birth = data.errors.date_of_birth?.[0] ?? "";
      errors.password = data.errors.password?.[0] ?? "";
      serverMessage.value = "Veuillez corriger les erreurs du formulaire.";
    } else if (data?.message) {
      serverMessage.value = data.message;
    } else if (status === 500) {
      serverMessage.value = "Erreur serveur. Veuillez réessayer plus tard.";
    } else {
      serverMessage.value = "Erreur lors de la création du compte.";
    }
  } finally {
    loading.value = false;
  }
}
</script>