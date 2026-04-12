<template>
    <div class="min-h-screen bg-white relative overflow-hidden">
        <!-- En-tête sticky avec stepper -->
        <header
            class="sticky top-0 z-10 border-b border-slate-200 bg-white/80 backdrop-blur-sm"
        >
            <div class="mx-auto max-w-5xl px-4 py-6 sm:px-6">
                <div class="mb-6 text-center">
                    <h1
                        class="mb-2 text-3xl font-extrabold bg-gradient-to-r from-purple-600 to-purple-700 bg-clip-text text-transparent"
                    >
                        Création de ton profil santé
                    </h1>
                    <p class="text-sm text-gray-500">
                        Quelques minutes pour personnaliser ton expérience
                    </p>
                </div>

                <!-- Stepper -->
                <div
                    class="mx-auto mb-4 flex max-w-2xl items-center justify-between"
                >
                    <div
                        v-for="(step, i) in STEPS"
                        :key="step.number"
                        class="flex flex-1 items-center"
                    >
                        <div class="flex flex-1 flex-col items-center">
                            <div
                                class="mb-2 flex h-10 w-10 items-center justify-center rounded-full text-sm font-semibold transition-all"
                                :class="stepClass(step.number)"
                            >
                                <svg
                                    v-if="currentStep > step.number"
                                    class="h-5 w-5"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    aria-hidden="true"
                                >
                                    <path
                                        d="M20 6L9 17l-5-5"
                                        stroke="currentColor"
                                        stroke-width="2.5"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />
                                </svg>
                                <span v-else>{{ step.number }}</span>
                            </div>
                            <span
                                class="text-center text-xs font-medium"
                                :class="
                                    currentStep >= step.number
                                        ? 'text-purple-600'
                                        : 'text-gray-400'
                                "
                            >
                                {{ step.label }}
                            </span>
                        </div>
                        <div
                            v-if="i < STEPS.length - 1"
                            class="-mt-6 mx-2 h-0.5 flex-1 transition-all"
                            :class="
                                currentStep > step.number
                                    ? 'bg-purple-500'
                                    : 'bg-slate-200'
                            "
                        />
                    </div>
                </div>

                <!-- Barre de progression -->
                <div
                    class="mx-auto h-1.5 max-w-2xl overflow-hidden rounded-full bg-slate-200"
                >
                    <div
                        class="h-full bg-gradient-to-r from-purple-400 to-purple-500 transition-all duration-300"
                        :style="{ width: progress + '%' }"
                    />
                </div>
                <p class="mt-3 text-center text-xs text-gray-400">
                    Tu pourras modifier ces informations plus tard
                </p>
            </div>
        </header>

        <!-- Contenu principal -->
        <main class="mx-auto max-w-4xl px-4 py-8 sm:px-6 sm:py-12">
            <!-- État de chargement -->
            <div
                v-if="loading"
                class="rounded-3xl border border-slate-200 bg-white p-8 text-center shadow-sm"
            >
                <div
                    class="mb-3 inline-flex h-12 w-12 items-center justify-center rounded-full bg-purple-50 text-purple-600"
                >
                    <svg
                        class="h-5 w-5 animate-spin"
                        viewBox="0 0 24 24"
                        fill="none"
                    >
                        <path
                            d="M12 3a9 9 0 1 0 9 9"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                        />
                    </svg>
                </div>
                <p class="font-semibold text-slate-900">
                    Chargement du profil...
                </p>
            </div>

            <template v-else>
                <div
                    class="mb-8 rounded-3xl border border-gray-100 bg-white p-8 shadow-sm md:p-12"
                >
                    <!-- Error / success / warning alert messages -->
                    <p
                        v-if="saveError"
                        class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
                    >
                        {{ saveError }}
                    </p>
                    <p
                        v-if="saveSuccess"
                        class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"
                    >
                        {{ saveSuccess }}
                    </p>
                    <p
                        v-if="stepError && currentStep !== 1"
                        class="mb-5 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700"
                    >
                        {{ stepError }}
                    </p>

                    <!-- Étapes -->
                    <Etape1
                        v-if="currentStep === 1"
                        :form="form"
                        :computed-age="computedAge"
                        :errors="step1Errors"
                        :show-errors="step1Tried"
                    />
                    <Etape2 v-else-if="currentStep === 2" :form="form" />
                    <Etape3 v-else :form="form" />
                </div>

                <!-- Navigation -->
                <div class="flex justify-end">
                    <button
                        v-if="currentStep < TOTAL_STEPS"
                        type="button"
                        class="h-12 rounded-xl bg-purple-500 px-8 text-white hover:bg-purple-600 disabled:cursor-not-allowed disabled:opacity-50"
                        :disabled="saving"
                        @click="goNext"
                    >
                        Continuer
                    </button>
                    <button
                        v-else
                        type="button"
                        class="h-12 rounded-xl bg-gradient-to-r from-purple-500 to-purple-600 px-8 text-white hover:from-purple-600 hover:to-purple-700 disabled:opacity-50"
                        :disabled="saving"
                        @click="enregistrer"
                    >
                        {{ saving ? "Enregistrement..." : "Terminer" }}
                    </button>
                </div>
            </template>
        </main>
    </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref, watch } from "vue";
import api from "@/services/api";
import { useRouter } from "vue-router";
import { useAuthStore } from "@/stores/auth";
import Etape1 from "./HealthProfileStep1.vue";
import Etape2 from "./HealthProfileStep2.vue";
import Etape3 from "./HealthProfileStep3.vue";

// ─── Constantes ───────────────────────────────────────────────────────────────
const TOTAL_STEPS = 3;
const STEPS = [
    { number: 1, label: "Informations de base" },
    { number: 2, label: "Santé" },
    { number: 3, label: "Médecin" },
];

// ─── Stores / Router ──────────────────────────────────────────────────────────
const router = useRouter();
const authStore = useAuthStore();

// ─── État ─────────────────────────────────────────────────────────────────────
const currentStep = ref(1);
const loading = ref(true);
const saving = ref(false);
const saveError = ref("");
const saveSuccess = ref("");
const stepError = ref("");
const step1Tried = ref(false);
const dateNaissance = ref("");

const step1Errors = reactive({
    sexe: "",
    taille: "",
    poids: "",
    objectifs: "",
});

const form = reactive({
    sexe: "",
    taille: "",
    poids: "",
    groupe_sanguin: "",
    objectifs: [],
    allergies: [],
    maladies_chroniques: [],
    traitements: [],
    fumeur: false,
    alcool: false,
    consulte_medecin: false,
    medecin_peut_consulter: false,
    medecin_email: "",
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
watch(
    () => form.consulte_medecin,
    (v) => {
        if (!v) {
            form.medecin_peut_consulter = false;
            form.medecin_email = "";
        }
    },
);
watch(
    () => form.medecin_peut_consulter,
    (v) => {
        if (!v) form.medecin_email = "";
    },
);

// Return the CSS classes for a step circle (completed / active / inactive)
function stepClass(n) {
    if (currentStep.value > n)  return "bg-purple-500 text-white";
    if (currentStep.value === n) return "bg-purple-500 text-white ring-4 ring-purple-100";
    return "bg-slate-200 text-slate-500";
}

// Keep only non-empty strings from an array
function normalizeArray(v) {
    if (!Array.isArray(v)) return [];
    return v.filter((s) => typeof s === "string" && s.trim());
}

// Convert a frequency value to a positive integer — returns null if invalid
function normalizeFrequency(v) {
    if (v === null || v === undefined || v === "") return null;
    const number = Number(v);
    if (!Number.isFinite(number)) return null;
    return Math.max(1, Math.trunc(number));
}

// Get the first readable error message from API validation errors
function extractApiError(errors) {
    if (!errors || typeof errors !== "object") return "Validation invalide.";

    // Flatten all error arrays into a single list of messages
    const messages = Object.values(errors)
        .flat()
        .filter(Boolean);

    return messages.join(" ") || "Validation invalide.";
}

// Clear all step 1 error messages
function clearStep1Errors() {
    step1Errors.sexe      = "";
    step1Errors.taille    = "";
    step1Errors.poids     = "";
    step1Errors.objectifs = "";
}

// Log out and redirect to the registration page
function redirectLogin() {
    authStore.removeToken();
    router.replace({ name: "register" });
}

// ─── Validation ───────────────────────────────────────────────────────────────

// Validate step 1: gender, height, weight, goals
function validateStep1() {
    clearStep1Errors();

    // Gender is required
    if (!form.sexe) {
        step1Errors.sexe = "Veuillez sélectionner votre genre.";
    }

    // Height: required and must be between 80 and 250 cm
    if (!form.taille) {
        step1Errors.taille = "La taille est obligatoire.";
    } else {
        const height = Number(form.taille);
        if (height < 80 || height > 250) {
            step1Errors.taille = "La taille doit être entre 80 et 250 cm.";
        }
    }

    // Weight: required and must be between 35 and 250 kg
    if (!form.poids) {
        step1Errors.poids = "Le poids est obligatoire.";
    } else {
        const weight = Number(form.poids);
        if (weight < 35 || weight > 250) {
            step1Errors.poids = "Le poids doit être entre 35 et 250 kg.";
        }
    }

    // At least one goal must be selected
    if (!form.objectifs.length) {
        step1Errors.objectifs = "Veuillez sélectionner au moins un objectif.";
    }

    // Return true only if no errors were found
    return !Object.values(step1Errors).some(Boolean);
}

// Validate step 2: blood type is required
function validateStep2() {
    if (!form.groupe_sanguin) {
        stepError.value = "Le groupe sanguin est obligatoire.";
        return false;
    }
    return true;
}

// Validate step 3: if doctor sharing is enabled, email is required and must be valid
function validateStep3() {
    const doctorSharingEnabled = form.consulte_medecin && form.medecin_peut_consulter;

    if (!doctorSharingEnabled) return true;

    if (!form.medecin_email) {
        stepError.value = "Veuillez renseigner l'email du médecin.";
        return false;
    }

    const emailIsValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.medecin_email);
    if (!emailIsValid) {
        stepError.value = "L'email du médecin est invalide.";
        return false;
    }

    return true;
}

// ─── Navigation ───────────────────────────────────────────────────────────────
function goNext() {
    saveSuccess.value = "";
    stepError.value = "";
    clearStep1Errors();

    if (currentStep.value === 1) {
        step1Tried.value = true;
        if (!validateStep1()) return;
    }
    if (currentStep.value === 2 && !validateStep2()) return;

    step1Tried.value = false;
    currentStep.value++;
}

// Convert a date from DD/MM/YYYY format to YYYY-MM-DD (the format the API expects)
// Returns null if the date string is missing or invalid
function toIsoDate(frDate) {
    const text = String(frDate || "");

    // Must match exactly DD/MM/YYYY
    const match = text.match(/^(\d{2})\/(\d{2})\/(\d{4})$/);
    if (!match) return null;

    const day   = Number(match[1]);
    const month = Number(match[2]);
    const year  = Number(match[3]);

    // Double-check the date is real (e.g. rejects 31/02/2024)
    const date = new Date(year, month - 1, day);
    const isReal =
        date.getFullYear() === year &&
        date.getMonth() === month - 1 &&
        date.getDate() === day;

    if (!isReal) return null;

    return `${match[3]}-${match[2]}-${match[1]}`;
}

// Convert a single treatment object into the format expected by the API
function buildTreatment(t) {
    return {
        type:            t?.type            ?? null,
        name:            t?.name            ?? null,
        dose:            t?.dose            ?? null,
        frequency_unit:  t?.frequency_unit  ?? null,
        frequency_count: normalizeFrequency(t?.frequency_count),
        start_date:      toIsoDate(t?.start_date) ?? null,
        end_date:        toIsoDate(t?.end_date)   ?? null,
        duration:        t?.duration        ?? null,
    };
}

// Build the complete data object to send to the API
function buildPayload() {
    // Clean up treatments: convert each one and remove those without a type
    const treatments = Array.isArray(form.traitements)
        ? form.traitements.map(buildTreatment).filter((t) => t.type)
        : [];

    return {
        gender:          String(form.sexe || "").toLowerCase().trim(),
        height:          form.taille,
        weight:          form.poids,
        blood_type:      form.groupe_sanguin,
        goals:           normalizeArray(form.objectifs),
        allergies:       normalizeArray(form.allergies),
        chronic_diseases: normalizeArray(form.maladies_chroniques),
        treatments,
        smoker:          form.fumeur,
        alcoholic:       form.alcool,
        doctor_invited:  form.medecin_peut_consulter,
        doctor_email:    form.medecin_peut_consulter ? form.medecin_email : null,
    };
}

// Save the completed health profile to the API
async function enregistrer() {
    if (!validateStep3()) return;

    // Clear previous messages
    stepError.value   = "";
    saveError.value   = "";
    saveSuccess.value = "";
    saving.value      = true;

    try {
        await api.post("/health-profile", buildPayload());
        authStore.setHealthProfile(true);
        saveSuccess.value = "Profil enregistré avec succès.";
        router.push({ name: "health-settings" });
    } catch (err) {
        const status = err.response?.status;

        if (status === 401) {
            // Not logged in — redirect to registration
            redirectLogin();
            return;
        }

        if (status === 422) {
            // Server validation failed — show the first error message
            const data = err.response.data;
            saveError.value = data?.errors
                ? extractApiError(data.errors)
                : data?.message?.trim() || "Erreur de validation (422).";
        } else {
            saveError.value = "Erreur lors de l'enregistrement du profil.";
        }
    } finally {
        saving.value = false;
    }
}

// Load the existing health profile when the page opens
onMounted(async () => {
    // Redirect to registration if the user is not logged in
    if (!authStore.isAuthenticated) {
        router.replace({ name: "register" });
        return;
    }

    try {
        const { data } = await api.get("/health-profile");

        // Save the user's birth date (used to compute age)
        dateNaissance.value = data?.user?.date_of_birth
            ? String(data.user.date_of_birth)
            : "";

        authStore.setHealthProfile(Boolean(data?.data));

        const profile = data?.data;

        // No profile yet — stay on the creation form
        if (!profile) return;

        // Profile is already complete — go straight to the settings page
        const isComplete = profile.gender && profile.height && profile.weight && profile.blood_type;
        if (isComplete) {
            router.replace({ name: "health-settings" });
            return;
        }

        // Pre-fill the form with whatever was already saved
        form.sexe                = profile.gender        || "";
        form.taille              = profile.height        || "";
        form.poids               = profile.weight        || "";
        form.groupe_sanguin      = profile.blood_type    || "";
        form.objectifs           = profile.goals         || [];
        form.allergies           = profile.allergies     || [];
        form.maladies_chroniques = profile.chronic_diseases || [];
        form.traitements         = profile.treatments    || [];
        form.fumeur              = profile.smoker        || false;
        form.alcool              = profile.alcoholic     || false;
        form.consulte_medecin    = profile.doctor_invited || false;
        form.medecin_peut_consulter = profile.doctor_invited || false;
        form.medecin_email       = profile.doctor_email  || "";
    } catch (err) {
        // Session expired — redirect to login
        if (err.response?.status === 401) {
            redirectLogin();
            return;
        }
        saveError.value =
            "Impossible de charger le profil santé: " +
            (err.response?.data?.message || err.message);
    } finally {
        loading.value = false;
    }
});
</script>
