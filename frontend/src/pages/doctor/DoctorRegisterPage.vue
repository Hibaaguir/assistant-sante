<template>
  <div class="min-h-screen bg-[#f5f7fb] px-4 py-10">
    <div class="mx-auto max-w-md">
      <div class="rounded-[24px] border border-slate-200 bg-white p-8 shadow-[0_18px_45px_rgba(15,23,42,0.06)]">
        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-sky-700">Compte medecin</p>
        <h1 class="mt-3 text-3xl font-semibold text-slate-950">Creer un compte</h1>
        <p class="mt-2 text-sm leading-6 text-slate-600">Un compte medecin cree ici sera enregistre avec le role <strong>medecin</strong>.</p>

        <form @submit.prevent="submit" class="mt-8 space-y-4">
          <div>
            <label class="mb-2 block text-sm font-medium text-slate-700">Email</label>
            <input v-model.trim="form.email" type="email" placeholder="medecin@exemple.com" autocomplete="email" class="h-12 w-full rounded-2xl border px-4 text-slate-900 outline-none transition" :class="errors.email ? 'border-red-300 focus:border-red-400' : 'border-slate-200 focus:border-sky-500'" />
            <p v-if="errors.email" class="mt-2 text-sm text-red-600">{{ errors.email }}</p>
          </div>

          <div>
            <label class="mb-2 block text-sm font-medium text-slate-700">Mot de passe</label>
            <input v-model="form.password" type="password" placeholder="Minimum 8 caracteres" autocomplete="new-password" class="h-12 w-full rounded-2xl border px-4 text-slate-900 outline-none transition" :class="errors.password ? 'border-red-300 focus:border-red-400' : 'border-slate-200 focus:border-sky-500'" />
          </div>

          <div>
            <label class="mb-2 block text-sm font-medium text-slate-700">Confirmation</label>
            <input v-model="form.password_confirmation" type="password" placeholder="Repetez le mot de passe" autocomplete="new-password" class="h-12 w-full rounded-2xl border px-4 text-slate-900 outline-none transition" :class="errors.password ? 'border-red-300 focus:border-red-400' : 'border-slate-200 focus:border-sky-500'" />
            <p v-if="errors.password" class="mt-2 text-sm text-red-600">{{ errors.password }}</p>
          </div>

          <div>
            <label class="mb-2 block text-sm font-medium text-slate-700">Specialite</label>
            <input v-model.trim="form.specialite" type="text" placeholder="Cardiologie" autocomplete="organization-title" class="h-12 w-full rounded-2xl border px-4 text-slate-900 outline-none transition" :class="errors.specialite ? 'border-red-300 focus:border-red-400' : 'border-slate-200 focus:border-sky-500'" />
            <p v-if="errors.specialite" class="mt-2 text-sm text-red-600">{{ errors.specialite }}</p>
          </div>

          <button type="submit" :disabled="loading" class="h-12 w-full rounded-2xl bg-slate-950 text-sm font-semibold text-white transition hover:bg-slate-800 disabled:opacity-50">
            <span v-if="!loading">Creer le compte medecin</span>
            <span v-else>Creation...</span>
          </button>

          <div v-if="serverMessage" class="rounded-2xl border px-4 py-3 text-sm" :class="messageType === 'success' ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-red-200 bg-red-50 text-red-700'">
            {{ serverMessage }}
          </div>
        </form>

        <p class="mt-6 text-sm text-slate-600">
          Vous avez deja un compte ?
          <RouterLink :to="loginLink" class="font-semibold text-sky-700 hover:underline">Se connecter</RouterLink>
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
  email: String(route.query.email || "").trim(),
  password: "",
  password_confirmation: "",
  specialite: "",
});

const errors = reactive({
  email: "",
  password: "",
  specialite: "",
});

const loading = ref(false);
const serverMessage = ref("");
const messageType = ref("success");

const loginLink = computed(() => ({ name: "doctor-login", query: { email: form.email } }));

function clearErrors() {
  errors.email = "";
  errors.password = "";
  errors.specialite = "";
}

async function submit() {
  serverMessage.value = "";
  messageType.value = "success";
  clearErrors();

  if (!form.email || !form.password || !form.password_confirmation || !form.specialite) {
    if (!form.email) errors.email = "L'adresse email est obligatoire.";
    if (!form.password) errors.password = "Le mot de passe est obligatoire.";
    if (!form.specialite) errors.specialite = "La specialite est obligatoire.";
    serverMessage.value = "Veuillez remplir les champs obligatoires.";
    return;
  }

  if (form.password !== form.password_confirmation) {
    errors.password = "Les mots de passe ne correspondent pas.";
  }
  if (errors.password) return;

  loading.value = true;

  try {
    const res = await api.post("/auth/doctor/register", {
      email: form.email,
      password: form.password,
      password_confirmation: form.password_confirmation,
      specialite: form.specialite,
    });

    if (res?.data?.token) authStore.setToken(res.data.token);

    serverMessage.value = res?.data?.message || "Compte medecin cree avec succes.";
    messageType.value = "success";
    setTimeout(() => router.push("/main/dashboard"), 300);
  } catch (err) {
    messageType.value = "error";
    const data = err?.response?.data;
    const status = err?.response?.status;
    if (status === 422 && data?.errors) {
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
