<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50/50 via-white to-teal-50/50">
    <div class="bg-white/80 border-b border-slate-200 backdrop-blur-sm sticky top-0 z-10">
      <div class="max-w-5xl mx-auto px-4 sm:px-6 py-6">
        <div class="text-center mb-6">
          <h1 class="text-2xl font-bold text-gray-900 mb-1">Creation de ton profil sante</h1>
          <p class="text-sm text-gray-500">Quelques minutes pour personnaliser ton experience</p>
        </div>

        <div class="flex items-center justify-between max-w-2xl mx-auto mb-4">
          <div v-for="(step, index) in steps" :key="step.number" class="flex items-center flex-1">
            <div class="flex flex-col items-center flex-1">
              <div
                class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-semibold mb-2 transition-all"
                :class="stepClass(step.number)"
              >
                <svg v-if="currentStep > step.number" class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                  <path d="M20 6L9 17l-5-5" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <span v-else>{{ step.number }}</span>
              </div>
              <span class="text-xs font-medium text-center" :class="currentStep >= step.number ? 'text-teal-600' : 'text-gray-400'">
                {{ step.label }}
              </span>
            </div>
            <div
              v-if="index < steps.length - 1"
              class="h-0.5 flex-1 mx-2 -mt-6 transition-all"
              :class="currentStep > step.number ? 'bg-teal-500' : 'bg-slate-200'"
            />
          </div>
        </div>

        <div class="max-w-2xl mx-auto h-1.5 rounded-full bg-slate-200 overflow-hidden">
          <div class="h-full bg-gradient-to-r from-teal-500 to-blue-500 transition-all duration-300" :style="{ width: progress + '%' }" />
        </div>
        <p class="text-xs text-gray-400 text-center mt-3">Tu pourras modifier ces informations plus tard</p>
      </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 py-8 sm:py-12">
      <div v-if="loading" class="bg-white rounded-3xl border border-slate-200 shadow-sm p-8 text-center">
        <div class="inline-flex items-center justify-center h-12 w-12 rounded-full bg-teal-50 text-teal-600 mb-3">
          <svg class="w-5 h-5 animate-spin" viewBox="0 0 24 24" fill="none">
            <path d="M12 3a9 9 0 1 0 9 9" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
          </svg>
        </div>
        <p class="text-slate-900 font-semibold">Chargement du profil...</p>
      </div>

      <div v-else class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8 md:p-12 mb-8">
        <p v-if="saveError" class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
          {{ saveError }}
        </p>
        <p v-if="saveSuccess" class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
          {{ saveSuccess }}
        </p>
        <p v-if="stepError && currentStep !== 1" class="mb-5 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700">
          {{ stepError }}
        </p>

        <Etape1 v-if="currentStep === 1" :form="form" :computed-age="computedAge" :errors="step1Errors" :show-errors="step1HasTriedContinue" />
        <Etape2 v-else-if="currentStep === 2" :form="form" />
        <Etape3 v-else :form="form" />
      </div>

      <div v-if="!loading" class="flex justify-end items-center">
        <button
          v-if="currentStep < totalSteps"
          type="button"
          class="gap-2 rounded-xl h-12 px-8 bg-teal-600 hover:bg-teal-700 text-white disabled:opacity-50 disabled:cursor-not-allowed"
          :disabled="saving"
          @click="goNext"
        >
          Continuer
        </button>

        <button
          v-else
          type="button"
          class="gap-2 rounded-xl h-12 px-8 bg-gradient-to-r from-teal-600 to-blue-600 hover:from-teal-700 hover:to-blue-700 text-white disabled:opacity-50"
          :disabled="saving"
          @click="enregistrer"
        >
          {{ saving ? "Enregistrement..." : "Terminer" }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
/*
  Conteneur principal du wizard Profil Sante.
  Il orchestre les 3 etapes, la navigation et la sauvegarde finale.
  Les donnees sont centralisees dans `form` puis envoyees a l'API.
*/

import { computed, onMounted, reactive, ref, watch } from "vue";
import api from "@/services/api";
import { useRouter } from "vue-router";
import { useAuthStore } from "@/stores/auth";
import Etape1 from "./ProfilSanteEtape1.vue";
import Etape2 from "./ProfilSanteEtape2.vue";
import Etape3 from "./ProfilSanteEtape3.vue";

const router = useRouter();
const authStore = useAuthStore();

const currentStep = ref(1);
const totalSteps = 3;
const loading = ref(true);
const saving = ref(false);
const saveError = ref("");
const saveSuccess = ref("");
const stepError = ref("");
const step1HasTriedContinue = ref(false);
const step1Errors = reactive({
  sexe: "",
  taille: "",
  poids: "",
  objectifs: "",
});
const userDateOfBirth = ref("");
const steps = [
  { number: 1, label: "Informations de base" },
  { number: 2, label: "Sante" },
  { number: 3, label: "Medecin" },
];

const form = reactive({
  sexe: "",
  taille: "",
  poids: "",
  groupe_sanguin: "",
  objectifs: [],
  allergies: [],
  maladies_chroniques: [],
  traitements: [],
  prend_medicament: false,
  nom_medicament: "",
  fumeur: false,
  alcool: false,
  consulte_medecin: false,
  medecin_peut_consulter: false,
  medecin_email: "",
});

const progress = computed(() => (currentStep.value / totalSteps) * 100);

const computedAge = computed(() => {
  if (!userDateOfBirth.value) return "";

  const dob = new Date(userDateOfBirth.value);
  if (Number.isNaN(dob.getTime())) return "";

  const today = new Date();
  let age = today.getFullYear() - dob.getFullYear();
  const monthDiff = today.getMonth() - dob.getMonth();

  if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < dob.getDate())) {
    age -= 1;
  }

  return age >= 0 ? age : "";
});

onMounted(async () => {
  if (!authStore.estConnecte) {
    router.replace({ name: "inscription" });
    return;
  }

  try {
    const response = await api.get("/profil-sante");
    const profil = response?.data?.data;
    const user = response?.data?.user;

    userDateOfBirth.value = user?.date_of_birth ? String(user.date_of_birth) : "";

    authStore.definirPresenceProfilSante(Boolean(profil));

    if (profil) {
      router.replace({ name: "mon-profil-sante" });
      return;
    }
  } catch (error) {
    if (error.response?.status === 401) {
      authStore.supprimerToken();
      router.replace({ name: "inscription" });
      return;
    }
    saveError.value = "Impossible de charger le profil sante.";
  } finally {
    loading.value = false;
  }
});

watch(
  () => form.prend_medicament,
  (value) => {
    if (!value) form.nom_medicament = "";
  }
);

watch(
  () => form.consulte_medecin,
  (value) => {
    if (!value) {
      form.medecin_peut_consulter = false;
      form.medecin_email = "";
    }
  }
);

watch(
  () => form.medecin_peut_consulter,
  (value) => {
    if (!value) form.medecin_email = "";
  }
);

function normalizeArray(value) {
  return Array.isArray(value) ? value.filter((item) => typeof item === "string" && item.trim() !== "") : [];
}

function normalizeFrequencyCount(value) {
  if (value === null || value === undefined || value === "") return null;
  const normalized = Number(value);
  if (!Number.isFinite(normalized)) return null;
  return Math.max(1, Math.trunc(normalized));
}

function extractValidationMessage(errors) {
  if (!errors || typeof errors !== "object") return "Validation invalide.";
  const messages = Object.values(errors).flatMap((entry) => (Array.isArray(entry) ? entry : [entry])).filter(Boolean);
  if (!messages.length) return "Validation invalide.";
  return messages.join(" ");
}

function construireChargeUtile() {
  const objectifs = normalizeArray(form.objectifs);

  return {
    sexe: typeof form.sexe === "string" ? form.sexe.toLowerCase().trim() : form.sexe,
    taille: form.taille,
    poids: form.poids,
    groupe_sanguin: form.groupe_sanguin,
    objectifs,
    allergies: normalizeArray(form.allergies),
    maladies_chroniques: normalizeArray(form.maladies_chroniques),
    traitements: Array.isArray(form.traitements)
      ? form.traitements
          .map((item) => ({
            type: item?.type ?? null,
            name: item?.name ?? null,
            dose: item?.dose ?? null,
            frequency_unit: item?.frequency_unit ?? null,
            frequency_count: normalizeFrequencyCount(item?.frequency_count),
            duration: item?.duration ?? null,
          }))
          .filter((item) => item.type)
      : [],
    prend_medicament: form.prend_medicament,
    nom_medicament: form.prend_medicament ? form.nom_medicament : null,
    fumeur: form.fumeur,
    alcool: form.alcool,
    consulte_medecin: form.consulte_medecin,
    medecin_peut_consulter: form.consulte_medecin && form.medecin_peut_consulter,
    medecin_email: form.consulte_medecin && form.medecin_peut_consulter ? form.medecin_email : null,
  };
}

async function enregistrer() {
  if (!validateStep3()) return;

  stepError.value = "";
  saveError.value = "";
  saveSuccess.value = "";
  saving.value = true;

  const payload = construireChargeUtile();

  try {
    await api.post("/profil-sante", payload);
    authStore.definirPresenceProfilSante(true);
    saveSuccess.value = "Profil enregistre avec succes.";
    router.push({ name: "mon-profil-sante" });
  } catch (error) {
    if (error.response?.status === 401) {
      authStore.supprimerToken();
      router.replace({ name: "inscription" });
      return;
    }

    if (error.response?.status === 422) {
      const responseData = error.response?.data;

      if (responseData?.errors) {
        saveError.value = extractValidationMessage(responseData.errors);
        console.error("Validation /api/profil-sante:", responseData.errors);
      } else if (typeof responseData?.message === "string" && responseData.message.trim()) {
        saveError.value = responseData.message;
        console.error("Validation /api/profil-sante message:", responseData.message);
      } else {
        saveError.value = "Erreur de validation (422).";
      }

      console.error("Payload /api/profil-sante:", payload);
    } else {
      saveError.value = "Erreur lors de l'enregistrement du profil.";
    }
  } finally {
    saving.value = false;
  }
}

function stepClass(stepNumber) {
  if (currentStep.value > stepNumber) return "bg-teal-500 text-white";
  if (currentStep.value === stepNumber) return "bg-teal-500 text-white ring-4 ring-teal-100";
  return "bg-slate-200 text-slate-500";
}

function goBack() {
  if (currentStep.value === 1) {
    router.back();
    return;
  }

  stepError.value = "";
  step1HasTriedContinue.value = false;
  clearStep1Errors();
  currentStep.value -= 1;
}

function goNext() {
  saveSuccess.value = "";
  stepError.value = "";
  clearStep1Errors();
  if (currentStep.value === 1) {
    step1HasTriedContinue.value = true;
    if (!validateStep1()) return;
  }
  if (currentStep.value === 2 && !validateStep2()) return;
  step1HasTriedContinue.value = false;
  currentStep.value += 1;
}

function clearStep1Errors() {
  step1Errors.sexe = "";
  step1Errors.taille = "";
  step1Errors.poids = "";
  step1Errors.objectifs = "";
}

function validateStep1() {
  clearStep1Errors();

  if (!form.sexe) {
    step1Errors.sexe = "Veuillez selectionner votre genre.";
  }

  if (!form.taille) {
    step1Errors.taille = "La taille est obligatoire.";
  }
  if (!form.poids) {
    step1Errors.poids = "Le poids est obligatoire.";
  }

  if (form.taille) {
    const taille = Number(form.taille);
    if (!Number.isFinite(taille) || taille < 80 || taille > 250) {
      step1Errors.taille = "La taille doit etre une valeur entre 80 et 250 cm.";
    }
  }

  if (form.poids) {
    const poids = Number(form.poids);
    if (!Number.isFinite(poids) || poids < 35 || poids > 250) {
      step1Errors.poids = "Le poids doit etre une valeur entre 35 et 250 kg.";
    }
  }

  if (!Array.isArray(form.objectifs) || !form.objectifs.length) {
    step1Errors.objectifs = "Veuillez selectionner au moins un objectif.";
  }

  return !step1Errors.sexe && !step1Errors.taille && !step1Errors.poids && !step1Errors.objectifs;
}

function validateStep2() {
  if (!form.groupe_sanguin) {
    stepError.value = "groupe sanguin est obligatoire pour enregistrer.";
    return false;
  }
  return true;
}

function validateStep3() {
  if (form.prend_medicament && !form.nom_medicament) {
    stepError.value = "Veuillez renseigner le nom du medicament.";
    return false;
  }
  if (form.consulte_medecin && form.medecin_peut_consulter && !form.medecin_email) {
    stepError.value = "Veuillez renseigner l'email du medecin.";
    return false;
  }
  if (form.medecin_email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.medecin_email)) {
    stepError.value = "L'email du medecin est invalide.";
    return false;
  }
  return true;
}
</script>
