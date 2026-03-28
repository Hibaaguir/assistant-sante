<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50/50 via-white to-teal-50/50">
    <!-- En-tête sticky avec stepper -->
    <header class="sticky top-0 z-10 border-b border-slate-200 bg-white/80 backdrop-blur-sm">
      <div class="mx-auto max-w-5xl px-4 py-6 sm:px-6">
        <div class="mb-6 text-center">
          <h1 class="mb-1 text-2xl font-bold text-gray-900">Création de ton profil santé</h1>
          <p class="text-sm text-gray-500">Quelques minutes pour personnaliser ton expérience</p>
        </div>

        <!-- Stepper -->
        <div class="mx-auto mb-4 flex max-w-2xl items-center justify-between">
          <div v-for="(step, i) in STEPS" :key="step.number" class="flex flex-1 items-center">
            <div class="flex flex-1 flex-col items-center">
              <div class="mb-2 flex h-10 w-10 items-center justify-center rounded-full text-sm font-semibold transition-all" :class="stepClass(step.number)">
                <svg v-if="currentStep > step.number" class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                  <path d="M20 6L9 17l-5-5" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <span v-else>{{ step.number }}</span>
              </div>
              <span class="text-center text-xs font-medium" :class="currentStep >= step.number ? 'text-teal-600' : 'text-gray-400'">
                {{ step.label }}
              </span>
            </div>
            <div v-if="i < STEPS.length - 1" class="-mt-6 mx-2 h-0.5 flex-1 transition-all" :class="currentStep > step.number ? 'bg-teal-500' : 'bg-slate-200'" />
          </div>
        </div>

        <!-- Barre de progression -->
        <div class="mx-auto h-1.5 max-w-2xl overflow-hidden rounded-full bg-slate-200">
          <div class="h-full bg-gradient-to-r from-teal-500 to-blue-500 transition-all duration-300" :style="{ width: progress + '%' }" />
        </div>
        <p class="mt-3 text-center text-xs text-gray-400">Tu pourras modifier ces informations plus tard</p>
      </div>
    </header>

    <!-- Contenu principal -->
    <main class="mx-auto max-w-4xl px-4 py-8 sm:px-6 sm:py-12">
      <!-- État de chargement -->
      <div v-if="loading" class="rounded-3xl border border-slate-200 bg-white p-8 text-center shadow-sm">
        <div class="mb-3 inline-flex h-12 w-12 items-center justify-center rounded-full bg-teal-50 text-teal-600">
          <svg class="h-5 w-5 animate-spin" viewBox="0 0 24 24" fill="none">
            <path d="M12 3a9 9 0 1 0 9 9" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
          </svg>
        </div>
        <p class="font-semibold text-slate-900">Chargement du profil...</p>
      </div>

      <template v-else>
        <div class="mb-8 rounded-3xl border border-gray-100 bg-white p-8 shadow-sm md:p-12">
          <!-- Alertes -->
          <AlertBanner v-if="saveError"   color="red"     :message="saveError" />
          <AlertBanner v-if="saveSuccess" color="emerald" :message="saveSuccess" />
          <AlertBanner v-if="stepError && currentStep !== 1" color="amber" :message="stepError" />

          <!-- Étapes -->
          <Etape1 v-if="currentStep === 1" :form="form" :computed-age="computedAge" :errors="step1Errors" :show-errors="step1Tried" />
          <Etape2 v-else-if="currentStep === 2" :form="form" />
          <Etape3 v-else :form="form" />
        </div>

        <!-- Navigation -->
        <div class="flex justify-end">
          <button
            v-if="currentStep < TOTAL_STEPS"
            type="button"
            class="h-12 rounded-xl bg-teal-600 px-8 text-white hover:bg-teal-700 disabled:cursor-not-allowed disabled:opacity-50"
            :disabled="saving"
            @click="goNext"
          >Continuer</button>
          <button
            v-else
            type="button"
            class="h-12 rounded-xl bg-gradient-to-r from-teal-600 to-blue-600 px-8 text-white hover:from-teal-700 hover:to-blue-700 disabled:opacity-50"
            :disabled="saving"
            @click="enregistrer"
          >{{ saving ? "Enregistrement..." : "Terminer" }}</button>
        </div>
      </template>
    </main>
  </div>
</template>

<script setup>
import { computed, defineComponent, h, onMounted, reactive, ref, watch } from "vue";
import api from "@/services/api";
import { useRouter } from "vue-router";
import { useAuthStore } from "@/stores/auth";
import Etape1 from "./ProfilSanteEtape1.vue";
import Etape2 from "./ProfilSanteEtape2.vue";
import Etape3 from "./ProfilSanteEtape3.vue";

// ─── Mini-composant alerte ────────────────────────────────────────────────────
const COLOR_MAP = {
  red:     { border: "border-red-200",     bg: "bg-red-50",     text: "text-red-700" },
  emerald: { border: "border-emerald-200", bg: "bg-emerald-50", text: "text-emerald-700" },
  amber:   { border: "border-amber-200",   bg: "bg-amber-50",   text: "text-amber-700" },
};
const AlertBanner = defineComponent({
  props: { color: String, message: String },
  setup: (p) => () => {
    const c = COLOR_MAP[p.color] ?? COLOR_MAP.red;
    return h("p", { class: `mb-5 rounded-xl border ${c.border} ${c.bg} px-4 py-3 text-sm ${c.text}` }, p.message);
  },
});

// ─── Constantes ───────────────────────────────────────────────────────────────
const TOTAL_STEPS = 3;
const STEPS = [
  { number: 1, label: "Informations de base" },
  { number: 2, label: "Santé" },
  { number: 3, label: "Médecin" },
];

// ─── Stores / Router ──────────────────────────────────────────────────────────
const router    = useRouter();
const authStore = useAuthStore();

// ─── État ─────────────────────────────────────────────────────────────────────
const currentStep  = ref(1);
const loading      = ref(true);
const saving       = ref(false);
const saveError    = ref("");
const saveSuccess  = ref("");
const stepError    = ref("");
const step1Tried   = ref(false);
const dateNaissance = ref("");

const step1Errors = reactive({ sexe: "", taille: "", poids: "", objectifs: "" });

const form = reactive({
  sexe: "", taille: "", poids: "", groupe_sanguin: "",
  objectifs: [], allergies: [], maladies_chroniques: [], traitements: [],
  fumeur: false, alcool: false,
  consulte_medecin: false, medecin_peut_consulter: false, medecin_email: "",
});

// ─── Computed ─────────────────────────────────────────────────────────────────
const progress = computed(() => (currentStep.value / TOTAL_STEPS) * 100);

const computedAge = computed(() => {
  if (!dateNaissance.value) return "";
  const dob = new Date(dateNaissance.value);
  if (isNaN(dob)) return "";
  const today = new Date();
  let age = today.getFullYear() - dob.getFullYear();
  const m = today.getMonth() - dob.getMonth();
  if (m < 0 || (m === 0 && today.getDate() < dob.getDate())) age--;
  return age >= 0 ? age : "";
});

// ─── Watchers (effets de bord sur le formulaire) ──────────────────────────────
watch(() => form.consulte_medecin,      (v) => { if (!v) { form.medecin_peut_consulter = false; form.medecin_email = ""; } });
watch(() => form.medecin_peut_consulter,(v) => { if (!v) form.medecin_email = ""; });

// ─── Helpers ──────────────────────────────────────────────────────────────────
function stepClass(n) {
  if (currentStep.value > n)  return "bg-teal-500 text-white";
  if (currentStep.value === n) return "bg-teal-500 text-white ring-4 ring-teal-100";
  return "bg-slate-200 text-slate-500";
}

function normalizeArray(v) {
  return Array.isArray(v) ? v.filter((s) => typeof s === "string" && s.trim()) : [];
}

function normalizeFrequency(v) {
  if (v === null || v === undefined || v === "") return null;
  const n = Number(v);
  return Number.isFinite(n) ? Math.max(1, Math.trunc(n)) : null;
}

function extractApiError(errors) {
  if (!errors || typeof errors !== "object") return "Validation invalide.";
  return Object.values(errors).flatMap((e) => (Array.isArray(e) ? e : [e])).filter(Boolean).join(" ") || "Validation invalide.";
}

function clearStep1Errors() {
  Object.assign(step1Errors, { sexe: "", taille: "", poids: "", objectifs: "" });
}

function redirectLogin() {
  authStore.supprimerToken();
  router.replace({ name: "inscription" });
}

// ─── Validation ───────────────────────────────────────────────────────────────
function validateStep1() {
  clearStep1Errors();
  if (!form.sexe) step1Errors.sexe = "Veuillez sélectionner votre genre.";
  if (!form.taille) {
    step1Errors.taille = "La taille est obligatoire.";
  } else {
    const t = Number(form.taille);
    if (!Number.isFinite(t) || t < 80 || t > 250) step1Errors.taille = "La taille doit être entre 80 et 250 cm.";
  }
  if (!form.poids) {
    step1Errors.poids = "Le poids est obligatoire.";
  } else {
    const p = Number(form.poids);
    if (!Number.isFinite(p) || p < 35 || p > 250) step1Errors.poids = "Le poids doit être entre 35 et 250 kg.";
  }
  if (!Array.isArray(form.objectifs) || !form.objectifs.length) step1Errors.objectifs = "Veuillez sélectionner au moins un objectif.";
  return !Object.values(step1Errors).some(Boolean);
}

function validateStep2() {
  if (!form.groupe_sanguin) { stepError.value = "Le groupe sanguin est obligatoire."; return false; }
  return true;
}

function validateStep3() {
  if (form.consulte_medecin && form.medecin_peut_consulter) {
    if (!form.medecin_email) { stepError.value = "Veuillez renseigner l'email du médecin."; return false; }
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.medecin_email)) { stepError.value = "L'email du médecin est invalide."; return false; }
  }
  return true;
}

// ─── Navigation ───────────────────────────────────────────────────────────────
function goNext() {
  saveSuccess.value = "";
  stepError.value   = "";
  clearStep1Errors();

  if (currentStep.value === 1) {
    step1Tried.value = true;
    if (!validateStep1()) return;
  }
  if (currentStep.value === 2 && !validateStep2()) return;

  step1Tried.value = false;
  currentStep.value++;
}

// ─── Construction du payload ──────────────────────────────────────────────────
function buildPayload() {
  return {
    sexe: typeof form.sexe === "string" ? form.sexe.toLowerCase().trim() : form.sexe,
    taille: form.taille,
    poids: form.poids,
    groupe_sanguin: form.groupe_sanguin,
    objectifs: normalizeArray(form.objectifs),
    allergies: normalizeArray(form.allergies),
    maladies_chroniques: normalizeArray(form.maladies_chroniques),
    traitements: Array.isArray(form.traitements)
      ? form.traitements
          .map((t) => ({
            type: t?.type ?? null, name: t?.name ?? null,
            dose: t?.dose ?? null, frequency_unit: t?.frequency_unit ?? null,
            frequency_count: normalizeFrequency(t?.frequency_count),
            duration: t?.duration ?? null,
          }))
          .filter((t) => t.type)
      : [],
    fumeur: form.fumeur,
    alcool: form.alcool,
    consulte_medecin: form.consulte_medecin,
    medecin_peut_consulter: form.consulte_medecin && form.medecin_peut_consulter,
    medecin_email: form.consulte_medecin && form.medecin_peut_consulter ? form.medecin_email : null,
  };
}

// ─── Sauvegarde finale ────────────────────────────────────────────────────────
async function enregistrer() {
  if (!validateStep3()) return;
  stepError.value  = "";
  saveError.value  = "";
  saveSuccess.value = "";
  saving.value     = true;

  const payload = buildPayload();
  try {
    await api.post("/profil-sante", payload);
    authStore.definirPresenceProfilSante(true);
    saveSuccess.value = "Profil enregistré avec succès.";
    router.push({ name: "mon-profil-sante" });
  } catch (err) {
    if (err.response?.status === 401) { redirectLogin(); return; }
    if (err.response?.status === 422) {
      const d = err.response.data;
      saveError.value = d?.errors ? extractApiError(d.errors) : (d?.message?.trim() || "Erreur de validation (422).");
    } else {
      saveError.value = "Erreur lors de l'enregistrement du profil.";
    }
  } finally {
    saving.value = false;
  }
}

// ─── Chargement initial ───────────────────────────────────────────────────────
onMounted(async () => {
  if (!authStore.estConnecte) { router.replace({ name: "inscription" }); return; }
  try {
    const { data } = await api.get("/profil-sante");
    dateNaissance.value = data?.user?.date_of_birth ? String(data.user.date_of_birth) : "";
    authStore.definirPresenceProfilSante(Boolean(data?.data));
    if (data?.data) { router.replace({ name: "mon-profil-sante" }); return; }
  } catch (err) {
    if (err.response?.status === 401) { redirectLogin(); return; }
    saveError.value = "Impossible de charger le profil santé.";
  } finally {
    loading.value = false;
  }
});
</script>