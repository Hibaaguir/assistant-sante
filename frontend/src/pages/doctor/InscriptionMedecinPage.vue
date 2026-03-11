<template>
  <div class="min-h-screen bg-[#eef1f6] px-4 py-10 sm:px-6">
    <div class="mx-auto flex max-w-[700px] flex-col items-center">
      <div class="flex h-20 w-20 items-center justify-center rounded-[22px] bg-gradient-to-br from-[#574bff] to-[#2563ff] shadow-[0_20px_40px_rgba(53,77,255,0.28)]">
        <svg viewBox="0 0 24 24" class="h-10 w-10 text-white" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <path d="M9 3v4a3 3 0 0 0 6 0V3" />
          <path d="M6 6H5a2 2 0 0 0-2 2v1a5 5 0 0 0 5 5h1v3a3 3 0 1 0 6 0v-4" />
          <path d="M18 8a3 3 0 1 1 0 6" />
          <path d="M19.5 17.5h.01" />
        </svg>
      </div>

      <div class="mt-7 text-center">
        <h1 class="text-4xl font-extrabold tracking-[-0.03em] text-[#071b49] sm:text-5xl">Espace Medecin</h1>
        <p class="mt-3 text-lg text-[#50607d]">Creez votre compte professionnel</p>
      </div>

      <div class="mt-10 w-full max-w-[560px] rounded-[28px] border border-white/70 bg-[#f8f8f9] px-6 py-8 shadow-[0_18px_45px_rgba(15,23,42,0.12)] sm:px-8 sm:py-9">
        <div class="flex items-center justify-center gap-6 text-sm font-medium text-[#4c5b78]">
          <div class="flex items-center gap-2">
            <span class="flex h-5 w-5 items-center justify-center rounded-full text-[#5348ff]">
              <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="m12 3 7 3v5c0 4.5-3 7.5-7 9-4-1.5-7-4.5-7-9V6l7-3Z" />
                <path d="m9.5 11.8 1.7 1.7 3.4-3.8" />
              </svg>
            </span>
            <span>Securise</span>
          </div>

          <div class="flex items-center gap-2">
            <span class="flex h-5 w-5 items-center justify-center rounded-full text-[#16a34a]">
              <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <circle cx="12" cy="12" r="8.5" />
                <path d="m8.8 12.1 2.2 2.2 4.4-4.8" />
              </svg>
            </span>
            <span>Certifie</span>
          </div>
        </div>

        <div class="my-7 h-px bg-[#e2e6ee]" />

        <form class="space-y-5" @submit.prevent="soumettre">
          <div>
            <label class="mb-2 block text-base font-semibold text-[#162a55]">Nom complet</label>
            <input
              v-model.trim="form.name"
              type="text"
              placeholder="Dr. Jean Dupont"
              autocomplete="name"
              :class="classeChamp(errors.name)"
            />
            <p v-if="errors.name" class="mt-2 text-sm text-red-600">{{ errors.name }}</p>
          </div>

          <div>
            <label class="mb-2 block text-base font-semibold text-[#162a55]">Specialite</label>
            <input
              v-model.trim="form.specialite"
              type="text"
              placeholder="Entrez votre specialite"
              autocomplete="organization-title"
              :class="classeChamp(errors.specialite)"
            />
            <p v-if="errors.specialite" class="mt-2 text-sm text-red-600">{{ errors.specialite }}</p>
          </div>

          <div>
            <label class="mb-2 block text-base font-semibold text-[#162a55]">Email professionnel</label>
            <div class="relative">
              <span class="pointer-events-none absolute inset-y-0 left-4 flex items-center text-[#94a0b8]">
                <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                  <rect x="3" y="5" width="18" height="14" rx="2" />
                  <path d="m4 7 8 6 8-6" />
                </svg>
              </span>
              <input
                v-model.trim="form.email"
                type="email"
                placeholder="docteur@exemple.com"
                autocomplete="email"
                :class="[classeChamp(errors.email), 'pl-12']"
              />
            </div>
            <p v-if="errors.email" class="mt-2 text-sm text-red-600">{{ errors.email }}</p>
          </div>

          <div>
            <label class="mb-2 block text-base font-semibold text-[#162a55]">Mot de passe</label>
            <div class="relative">
              <span class="pointer-events-none absolute inset-y-0 left-4 flex items-center text-[#94a0b8]">
                <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                  <rect x="4" y="11" width="16" height="9" rx="2" />
                  <path d="M8 11V8a4 4 0 1 1 8 0v3" />
                </svg>
              </span>
              <input
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                placeholder="Minimum 8 caracteres"
                autocomplete="new-password"
                :class="[classeChamp(errors.password), 'pl-12 pr-14']"
              />
              <button
                type="button"
                class="absolute inset-y-0 right-4 flex items-center text-[#94a0b8] transition hover:text-[#4f46e5]"
                @click="showPassword = !showPassword"
              >
                <svg v-if="!showPassword" viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                  <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6-10-6-10-6Z" />
                  <circle cx="12" cy="12" r="3" />
                </svg>
                <svg v-else viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                  <path d="m3 3 18 18" />
                  <path d="M10.6 10.7A3 3 0 0 0 13.4 13.5" />
                  <path d="M9.9 5.1A11.4 11.4 0 0 1 12 5c6.5 0 10 7 10 7a15.4 15.4 0 0 1-4 4.7" />
                  <path d="M6.6 6.6C4 8.3 2 12 2 12s3.5 7 10 7a9.8 9.8 0 0 0 3-.5" />
                </svg>
              </button>
            </div>
            <p v-if="errors.password" class="mt-2 text-sm text-red-600">{{ errors.password }}</p>
          </div>

          <div v-if="serverMessage" class="rounded-[18px] border px-4 py-3 text-sm" :class="messageType === 'success' ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-red-200 bg-red-50 text-red-700'">
            {{ serverMessage }}
          </div>

          <button
            type="submit"
            :disabled="loading"
            class="mt-2 flex h-14 w-full items-center justify-center rounded-[18px] bg-gradient-to-r from-[#574bff] to-[#2563ff] text-lg font-bold text-white shadow-[0_18px_28px_rgba(53,77,255,0.28)] transition hover:brightness-105 disabled:cursor-not-allowed disabled:opacity-60"
          >
            <span v-if="!loading">Creer mon compte</span>
            <span v-else>Creation...</span>
          </button>
        </form>

        <p class="mt-8 text-center text-base text-[#50607d]">
          Vous avez deja un compte ?
          <RouterLink :to="lienConnexion" class="font-semibold text-[#4f46ff] transition hover:text-[#2f55ff] hover:underline">Se connecter</RouterLink>
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

const form = reactive({
  name: "",
  email: String(route.query.email || "").trim(),
  password: "",
  specialite: "",
});

const errors = reactive({
  name: "",
  email: "",
  password: "",
  specialite: "",
});

const loading = ref(false);
const showPassword = ref(false);
const serverMessage = ref("");
const messageType = ref("success");

const lienConnexion = computed(() => ({ name: "connexion-medecin", query: { email: form.email } }));

function effacerErreurs() {
  errors.name = "";
  errors.email = "";
  errors.password = "";
  errors.specialite = "";
}

function classeChamp(hasError) {
  return [
    "h-14 w-full rounded-[18px] border bg-white px-4 text-[17px] text-[#0f172a] outline-none transition placeholder:text-[#a0a8b8]",
    hasError ? "border-red-300 focus:border-red-400" : "border-[#d7dce5] focus:border-[#4f46ff]",
  ];
}

async function soumettre() {
  serverMessage.value = "";
  messageType.value = "success";
  effacerErreurs();

  if (!form.name || !form.email || !form.password || !form.specialite) {
    if (!form.name) errors.name = "Le nom complet est obligatoire.";
    if (!form.email) errors.email = "L'adresse email est obligatoire.";
    if (!form.password) errors.password = "Le mot de passe est obligatoire.";
    if (!form.specialite) errors.specialite = "La specialite est obligatoire.";
    serverMessage.value = "Veuillez remplir les champs obligatoires.";
    return;
  }

  loading.value = true;

  try {
    const res = await api.post("/auth/doctor/register", {
      name: form.name,
      email: form.email,
      password: form.password,
      password_confirmation: form.password,
      specialite: form.specialite,
    });

    if (res?.data?.token) authStore.definirToken(res.data.token);

    serverMessage.value = res?.data?.message || "Compte medecin cree avec succes.";
    messageType.value = "success";
    setTimeout(() => router.push("/main/dashboard"), 300);
  } catch (err) {
    messageType.value = "error";
    const data = err?.response?.data;
    const status = err?.response?.status;
    if (status === 422 && data?.errors) {
      errors.name = Array.isArray(data.errors.name) ? data.errors.name[0] : "";
      errors.email = Array.isArray(data.errors.email) ? data.errors.email[0] : "";
      errors.password = Array.isArray(data.errors.password) ? data.errors.password[0] : "";
      errors.specialite = Array.isArray(data.errors.specialite) ? data.errors.specialite[0] : "";
      serverMessage.value = "Veuillez corriger les erreurs du formulaire medecin.";
    } else {
      serverMessage.value = data?.message || "Erreur lors de la creation du compte medecin.";
    }
  } finally {
    loading.value = false;
  }
}
</script>
