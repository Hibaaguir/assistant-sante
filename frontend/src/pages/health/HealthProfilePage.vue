<template>
  <div class="mx-auto max-w-[1380px] px-4 py-5 sm:px-6 lg:px-8">
    <header class="mb-6 flex items-start gap-4">
      <div class="mt-1 flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-500 to-violet-500 shadow-lg shadow-indigo-200">
        <svg viewBox="0 0 24 24" class="h-6 w-6 text-white" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M12 21s-6.5-4.5-9-8.5C.7 8.4 3 4 7.3 4c2 0 3.6 1 4.7 2.6C13.1 5 14.7 4 16.7 4 21 4 23.3 8.4 21 12.5 18.5 16.5 12 21 12 21z" />
        </svg>
      </div>
      <div>
        <h1 class="text-[46px] font-extrabold leading-none tracking-[-0.02em] text-slate-900">Profil Santé</h1>
        <p class="mt-1 text-[24px] leading-none text-slate-500">Gérez vos informations de santé</p>
      </div>
    </header>

    <div v-if="loading" class="rounded-3xl border border-slate-200 bg-white p-6 text-sm text-slate-600">
      Chargement du profil...
    </div>

    <div v-else-if="error" class="rounded-3xl border border-red-200 bg-red-50 p-6 text-sm text-red-700">
      {{ error }}
    </div>

    <div v-else class="grid gap-4 lg:grid-cols-2">
      <section class="rounded-[18px] border border-[#d8e9ff] bg-white p-6">
        <div class="mb-6 flex items-center justify-between">
          <div class="flex items-center gap-3">
            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#ecf4ff] text-[#3b82f6]">
              <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4" /><path d="M4 20c0-3.3 3.6-6 8-6s8 2.7 8 6" /></svg>
            </span>
            <h2 class="text-[30px] font-bold leading-none text-slate-900">Informations de base</h2>
          </div>
          <button v-if="!editing.base" type="button" class="text-slate-900 hover:text-blue-600" @click="startEdit('base')">
            <svg viewBox="0 0 24 24" class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="2"><path d="m16 3 5 5-11 11H5v-5L16 3z" /></svg>
          </button>
        </div>

        <div v-if="!editing.base" class="space-y-5">
          <FieldRow label="Nom" :value="user.name || '-'" icon="user" />
          <FieldRow label="Âge" :value="computedAge || '-'" icon="calendar" />
          <FieldRow label="Sexe" :value="profil.sexe || '-'" icon="users" />
          <FieldRow label="Taille" :value="profil.taille ? `${profil.taille} cm` : '-'" icon="ruler" />
          <FieldRow label="Poids" :value="profil.poids ? `${profil.poids} kg` : '-'" icon="weight" />
        </div>

        <form v-else class="space-y-4" @submit.prevent="saveSection('base')">
          <div>
            <label class="mb-1 block text-[32px] font-semibold text-slate-900">Nom</label>
            <input :value="user.name || ''" disabled class="h-14 w-full rounded-xl border border-slate-200 bg-slate-100 px-4 text-[32px] text-slate-700" />
          </div>
          <div class="grid gap-3 md:grid-cols-2">
            <div>
              <label class="mb-1 block text-[32px] font-semibold text-slate-900">Sexe</label>
              <select v-model="draft.sexe" class="h-14 w-full rounded-xl border border-slate-200 bg-slate-100 px-4 text-[32px]">
                <option value="">-</option>
                <option value="femme">Femme</option>
                <option value="homme">Homme</option>
              </select>
            </div>
            <div>
              <label class="mb-1 block text-[32px] font-semibold text-slate-900">Taille (cm)</label>
              <input v-model="draft.taille" type="number" min="0" class="h-14 w-full rounded-xl border border-slate-200 bg-slate-100 px-4 text-[32px]" />
            </div>
          </div>
          <div>
            <label class="mb-1 block text-[32px] font-semibold text-slate-900">Poids (kg)</label>
            <input v-model="draft.poids" type="number" min="0" class="h-14 w-full rounded-xl border border-slate-200 bg-slate-100 px-4 text-[32px]" />
          </div>
          <div class="grid gap-3 md:grid-cols-2">
            <button type="submit" :disabled="savingSection==='base'" class="h-14 rounded-xl bg-[#2563eb] px-5 text-[34px] font-bold text-white disabled:opacity-60">Enregistrer</button>
            <button type="button" class="h-14 rounded-xl border border-slate-300 bg-white px-5 text-[34px] font-semibold text-slate-900" @click="cancelEdit('base')">Annuler</button>
          </div>
        </form>
      </section>

      <section class="rounded-[18px] border border-[#f2d9e4] bg-white p-6">
        <div class="mb-6 flex items-center justify-between">
          <div class="flex items-center gap-3">
            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#ffeef2] text-[#ef4566]">
              <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 21s-6.5-4.5-9-8.5C.7 8.4 3 4 7.3 4c2 0 3.6 1 4.7 2.6C13.1 5 14.7 4 16.7 4 21 4 23.3 8.4 21 12.5 18.5 16.5 12 21 12 21z" /></svg>
            </span>
            <h2 class="text-[30px] font-bold leading-none text-slate-900">Santé</h2>
          </div>
          <button v-if="!editing.health" type="button" class="text-slate-900 hover:text-blue-600" @click="startEdit('health')">
            <svg viewBox="0 0 24 24" class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="2"><path d="m16 3 5 5-11 11H5v-5L16 3z" /></svg>
          </button>
        </div>

        <div v-if="!editing.health" class="space-y-5">
          <FieldRow label="Groupe sanguin" :value="profil.groupe_sanguin || '-'" icon="droplet" />
          <FieldRow label="Objectif principal" :value="profil.objectif || '-'" icon="target" />
          <FieldRow label="Objectifs" :value="joinList(profil.objectifs)" icon="target" />
          <FieldRow label="Allergies" :value="joinList(profil.allergies)" icon="alert" />
          <FieldRow label="Maladies chroniques" :value="joinList(profil.maladies_chroniques)" icon="shield" />
        </div>

        <form v-else class="space-y-4" @submit.prevent="saveSection('health')">
          <div>
            <label class="mb-1 block text-[32px] font-semibold text-slate-900">Groupe sanguin</label>
            <select v-model="draft.groupe_sanguin" class="h-14 w-full rounded-xl border border-slate-200 bg-slate-100 px-4 text-[32px]">
              <option value="">-</option><option value="A+">A+</option><option value="A-">A-</option><option value="B+">B+</option><option value="B-">B-</option><option value="AB+">AB+</option><option value="AB-">AB-</option><option value="O+">O+</option><option value="O-">O-</option>
            </select>
          </div>
          <div>
            <label class="mb-1 block text-[32px] font-semibold text-slate-900">Objectif principal</label>
            <input v-model="draft.objectif" class="h-14 w-full rounded-xl border border-slate-200 bg-slate-100 px-4 text-[32px]" />
          </div>
          <div>
            <label class="mb-1 block text-[32px] font-semibold text-slate-900">Objectifs</label>
            <textarea v-model="draft.objectifs_text" rows="2" class="w-full rounded-xl border border-slate-200 bg-slate-100 px-4 py-3 text-[32px]" />
          </div>
          <div>
            <label class="mb-1 block text-[32px] font-semibold text-slate-900">Allergies</label>
            <textarea v-model="draft.allergies_text" rows="2" class="w-full rounded-xl border border-slate-200 bg-slate-100 px-4 py-3 text-[32px]" />
          </div>
          <div>
            <label class="mb-1 block text-[32px] font-semibold text-slate-900">Maladies chroniques</label>
            <textarea v-model="draft.maladies_text" rows="2" class="w-full rounded-xl border border-slate-200 bg-slate-100 px-4 py-3 text-[32px]" />
          </div>
          <div class="grid gap-3 md:grid-cols-2">
            <button type="submit" :disabled="savingSection==='health'" class="h-14 rounded-xl bg-[#ef4444] px-5 text-[34px] font-bold text-white disabled:opacity-60">Enregistrer</button>
            <button type="button" class="h-14 rounded-xl border border-slate-300 bg-white px-5 text-[34px] font-semibold text-slate-900" @click="cancelEdit('health')">Annuler</button>
          </div>
        </form>
      </section>

      <section class="rounded-[18px] border border-[#d4f3df] bg-white p-6">
        <div class="mb-6 flex items-center justify-between">
          <div class="flex items-center gap-3">
            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#e9fff0] text-[#18b05b]">
              <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12h4l2-6 4 12 2-6h6" /></svg>
            </span>
            <h2 class="text-[30px] font-bold leading-none text-slate-900">Habitudes</h2>
          </div>
          <button v-if="!editing.habits" type="button" class="text-slate-900 hover:text-blue-600" @click="startEdit('habits')">
            <svg viewBox="0 0 24 24" class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="2"><path d="m16 3 5 5-11 11H5v-5L16 3z" /></svg>
          </button>
        </div>

        <div v-if="!editing.habits" class="space-y-5">
          <FieldRow label="Fumeur" :value="yesNo(profil.fumeur)" icon="smoke" />
          <FieldRow label="Alcool" :value="yesNo(profil.alcool)" icon="wine" />
          <FieldRow label="Prend médicament" :value="yesNo(profil.prend_medicament)" icon="pill" />
          <FieldRow label="Nom médicament" :value="profil.nom_medicament || '-'" icon="pill" />
        </div>

        <form v-else class="space-y-4" @submit.prevent="saveSection('habits')">
          <div>
            <label class="mb-1 block text-[32px] font-semibold text-slate-900">Fumeur</label>
            <select v-model="draft.fumeur" class="h-14 w-full rounded-xl border border-slate-200 bg-slate-100 px-4 text-[32px]">
              <option :value="true">Oui</option><option :value="false">Non</option>
            </select>
          </div>
          <div>
            <label class="mb-1 block text-[32px] font-semibold text-slate-900">Alcool</label>
            <select v-model="draft.alcool" class="h-14 w-full rounded-xl border border-slate-200 bg-slate-100 px-4 text-[32px]">
              <option :value="true">Oui</option><option :value="false">Non</option>
            </select>
          </div>
          <div>
            <label class="mb-1 block text-[32px] font-semibold text-slate-900">Prend médicament</label>
            <select v-model="draft.prend_medicament" class="h-14 w-full rounded-xl border border-slate-200 bg-slate-100 px-4 text-[32px]">
              <option :value="true">Oui</option><option :value="false">Non</option>
            </select>
          </div>
          <div>
            <label class="mb-1 block text-[32px] font-semibold text-slate-900">Nom médicament</label>
            <input v-model="draft.nom_medicament" :disabled="!draft.prend_medicament" class="h-14 w-full rounded-xl border border-slate-200 bg-slate-100 px-4 text-[32px] disabled:opacity-60" />
          </div>
          <div class="grid gap-3 md:grid-cols-2">
            <button type="submit" :disabled="savingSection==='habits'" class="h-14 rounded-xl bg-[#16a34a] px-5 text-[34px] font-bold text-white disabled:opacity-60">Enregistrer</button>
            <button type="button" class="h-14 rounded-xl border border-slate-300 bg-white px-5 text-[34px] font-semibold text-slate-900" @click="cancelEdit('habits')">Annuler</button>
          </div>
        </form>
      </section>

      <section class="rounded-[18px] border border-[#e7dcff] bg-white p-6">
        <div class="mb-6 flex items-center justify-between">
          <div class="flex items-center gap-3">
            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#f3edff] text-[#9333ea]">
              <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 4v5a4 4 0 1 0 8 0V4M12 13v3a4 4 0 0 0 8 0v-1a2 2 0 1 0-4 0v1" /></svg>
            </span>
            <h2 class="text-[30px] font-bold leading-none text-slate-900">Suivi médecin</h2>
          </div>
          <button v-if="!editing.doctor" type="button" class="text-slate-900 hover:text-blue-600" @click="startEdit('doctor')">
            <svg viewBox="0 0 24 24" class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="2"><path d="m16 3 5 5-11 11H5v-5L16 3z" /></svg>
          </button>
        </div>

        <div v-if="!editing.doctor" class="space-y-5">
          <FieldRow label="Consulte médecin" :value="yesNo(profil.consulte_medecin)" icon="stetho" />
          <FieldRow label="Autorise accès médecin" :value="yesNo(profil.medecin_peut_consulter)" icon="shield" />
          <FieldRow label="Email médecin" :value="profil.medecin_email || '-'" icon="stetho" />
        </div>

        <form v-else class="space-y-4" @submit.prevent="saveSection('doctor')">
          <div>
            <label class="mb-1 block text-[32px] font-semibold text-slate-900">Consulte médecin</label>
            <select v-model="draft.consulte_medecin" class="h-14 w-full rounded-xl border border-slate-200 bg-slate-100 px-4 text-[32px]">
              <option :value="true">Oui</option><option :value="false">Non</option>
            </select>
          </div>
          <div>
            <label class="mb-1 block text-[32px] font-semibold text-slate-900">Autorise accès médecin</label>
            <select v-model="draft.medecin_peut_consulter" :disabled="!draft.consulte_medecin" class="h-14 w-full rounded-xl border border-slate-200 bg-slate-100 px-4 text-[32px] disabled:opacity-60">
              <option :value="true">Oui</option><option :value="false">Non</option>
            </select>
          </div>
          <div>
            <label class="mb-1 block text-[32px] font-semibold text-slate-900">Email médecin</label>
            <input v-model="draft.medecin_email" :disabled="!(draft.consulte_medecin && draft.medecin_peut_consulter)" class="h-14 w-full rounded-xl border border-slate-200 bg-slate-100 px-4 text-[32px] disabled:opacity-60" />
          </div>
          <div class="grid gap-3 md:grid-cols-2">
            <button type="submit" :disabled="savingSection==='doctor'" class="h-14 rounded-xl bg-[#a21caf] px-5 text-[34px] font-bold text-white disabled:opacity-60">Enregistrer</button>
            <button type="button" class="h-14 rounded-xl border border-slate-300 bg-white px-5 text-[34px] font-semibold text-slate-900" @click="cancelEdit('doctor')">Annuler</button>
          </div>
        </form>
      </section>
    </div>
  </div>
</template>

<script setup>
import { computed, defineComponent, h, onMounted, reactive, ref } from "vue";
import { useRouter } from "vue-router";
import api from "@/services/api";

const router = useRouter();
const loading = ref(true);
const error = ref("");
const savingSection = ref("");
const profil = reactive({});
const user = reactive({});

const editing = reactive({
  base: false,
  health: false,
  habits: false,
  doctor: false,
});

const draft = reactive({
  sexe: "",
  taille: "",
  poids: "",
  groupe_sanguin: "",
  objectif: "",
  objectifs_text: "",
  allergies_text: "",
  maladies_text: "",
  fumeur: false,
  alcool: false,
  prend_medicament: false,
  nom_medicament: "",
  consulte_medecin: false,
  medecin_peut_consulter: false,
  medecin_email: "",
});

const computedAge = computed(() => {
  if (!user.date_of_birth) return "";
  const dob = new Date(user.date_of_birth);
  if (Number.isNaN(dob.getTime())) return "";
  const today = new Date();
  let age = today.getFullYear() - dob.getFullYear();
  const monthDiff = today.getMonth() - dob.getMonth();
  if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < dob.getDate())) age -= 1;
  return age >= 0 ? `${age} ans` : "";
});

function yesNo(value) {
  return value ? "Oui" : "Non";
}

function joinList(value) {
  if (!Array.isArray(value) || value.length === 0) return "-";
  return value.filter(Boolean).join(", ");
}

function listToText(value) {
  if (!Array.isArray(value) || value.length === 0) return "";
  return value.filter(Boolean).join(", ");
}

function textToList(value) {
  if (!value || !String(value).trim()) return [];
  return String(value)
    .split(",")
    .map((item) => item.trim())
    .filter(Boolean);
}

function syncDraftFromProfil() {
  draft.sexe = profil.sexe || "";
  draft.taille = profil.taille ?? "";
  draft.poids = profil.poids ?? "";
  draft.groupe_sanguin = profil.groupe_sanguin || "";
  draft.objectif = profil.objectif || "";
  draft.objectifs_text = listToText(profil.objectifs);
  draft.allergies_text = listToText(profil.allergies);
  draft.maladies_text = listToText(profil.maladies_chroniques);
  draft.fumeur = Boolean(profil.fumeur);
  draft.alcool = Boolean(profil.alcool);
  draft.prend_medicament = Boolean(profil.prend_medicament);
  draft.nom_medicament = profil.nom_medicament || "";
  draft.consulte_medecin = Boolean(profil.consulte_medecin);
  draft.medecin_peut_consulter = Boolean(profil.medecin_peut_consulter);
  draft.medecin_email = profil.medecin_email || "";
}

function resetEditFlags() {
  editing.base = false;
  editing.health = false;
  editing.habits = false;
  editing.doctor = false;
}

function startEdit(section) {
  syncDraftFromProfil();
  resetEditFlags();
  editing[section] = true;
}

function cancelEdit(section) {
  syncDraftFromProfil();
  editing[section] = false;
}

function buildPayload() {
  const objectifs = textToList(draft.objectifs_text);
  const objectifPrincipal = draft.objectif || objectifs[0] || null;
  const prendMedicament = Boolean(draft.prend_medicament);
  const consulteMedecin = Boolean(draft.consulte_medecin);
  const medecinPeutConsulter = Boolean(consulteMedecin && draft.medecin_peut_consulter);

  return {
    sexe: draft.sexe || null,
    taille: draft.taille === "" ? null : Number(draft.taille),
    poids: draft.poids === "" ? null : Number(draft.poids),
    groupe_sanguin: draft.groupe_sanguin || null,
    objectif: objectifPrincipal,
    objectifs,
    allergies: textToList(draft.allergies_text),
    maladies_chroniques: textToList(draft.maladies_text),
    traitements: Array.isArray(profil.traitements) ? profil.traitements : [],
    prend_medicament: prendMedicament,
    nom_medicament: prendMedicament ? draft.nom_medicament || null : null,
    fumeur: Boolean(draft.fumeur),
    alcool: Boolean(draft.alcool),
    consulte_medecin: consulteMedecin,
    medecin_peut_consulter: medecinPeutConsulter,
    medecin_email: medecinPeutConsulter ? draft.medecin_email || null : null,
  };
}

async function saveSection(section) {
  savingSection.value = section;
  error.value = "";
  try {
    const response = await api.post("/profil-sante", buildPayload());
    Object.assign(profil, response?.data?.data || {});
    Object.assign(user, response?.data?.user || user);
    syncDraftFromProfil();
    editing[section] = false;
  } catch (e) {
    if (e?.response?.status === 401) {
      localStorage.removeItem("auth_token");
      router.replace({ name: "register" });
      return;
    }
    if (e?.response?.status === 422 && e?.response?.data?.errors) {
      const firstError = Object.values(e.response.data.errors)[0];
      error.value = Array.isArray(firstError) ? firstError[0] : "Validation invalide.";
    } else {
      error.value = "Erreur lors de la sauvegarde du profil.";
    }
  } finally {
    savingSection.value = "";
  }
}

onMounted(async () => {
  try {
    const response = await api.get("/profil-sante");
    Object.assign(profil, response?.data?.data || {});
    Object.assign(user, response?.data?.user || {});
    syncDraftFromProfil();
  } catch (e) {
    if (e?.response?.status === 401) {
      localStorage.removeItem("auth_token");
      router.replace({ name: "register" });
      return;
    }
    error.value = "Impossible de charger les données du profil.";
  } finally {
    loading.value = false;
  }
});

const Icon = defineComponent({
  name: "HealthIcon",
  props: { name: { type: String, required: true } },
  setup(props) {
    const map = {
      user: () => [h("circle", { cx: "12", cy: "8", r: "4" }), h("path", { d: "M4 20c0-3.3 3.6-6 8-6s8 2.7 8 6" })],
      calendar: () => [h("rect", { x: "3", y: "4", width: "18", height: "17", rx: "2" }), h("path", { d: "M16 2v4M8 2v4M3 10h18" })],
      users: () => [h("circle", { cx: "9", cy: "8", r: "3" }), h("path", { d: "M3 20c0-2.8 2.7-5 6-5s6 2.2 6 5" }), h("circle", { cx: "18", cy: "9", r: "2" }), h("path", { d: "M15 20c.3-1.6 1.7-3 3.5-3.6" })],
      ruler: () => [h("path", { d: "M16 3 3 16l5 5L21 8l-5-5z" }), h("path", { d: "m12 7 5 5M9 10l2 2M6 13l2 2" })],
      weight: () => [h("path", { d: "M7 7a5 5 0 0 1 10 0" }), h("path", { d: "M4 9h16l2 11H2L4 9z" })],
      droplet: () => [h("path", { d: "M12 3s6 6.4 6 10a6 6 0 0 1-12 0c0-3.6 6-10 6-10z" })],
      target: () => [h("circle", { cx: "12", cy: "12", r: "8" }), h("circle", { cx: "12", cy: "12", r: "4" }), h("circle", { cx: "12", cy: "12", r: "1.5" })],
      alert: () => [h("circle", { cx: "12", cy: "12", r: "10" }), h("path", { d: "M12 8v5M12 16h.01" })],
      shield: () => [h("path", { d: "M12 3 5 6v6c0 5 3.4 8 7 9 3.6-1 7-4 7-9V6l-7-3z" })],
      smoke: () => [h("path", { d: "M3 14h10v4H3zM15 15h2v3h-2zM19 15h2v3h-2zM16 6c1 1 1 2 0 3M19 5c1.5 1.2 1.8 3 .8 4.5" })],
      wine: () => [h("path", { d: "M8 3h8v3a4 4 0 0 1-8 0V3zM12 10v8M9 21h6" })],
      pill: () => [h("path", { d: "M14.5 3.5a5 5 0 0 1 7 7l-6 6a5 5 0 0 1-7-7l6-6z" }), h("path", { d: "m9 9 6 6" })],
      stetho: () => [h("path", { d: "M8 4v5a4 4 0 1 0 8 0V4M12 13v3a4 4 0 0 0 8 0v-1a2 2 0 1 0-4 0v1" })],
    };

    return () =>
      h(
        "svg",
        { viewBox: "0 0 24 24", fill: "none", stroke: "currentColor", "stroke-width": "2", class: "h-5 w-5 text-slate-400" },
        map[props.name] ? map[props.name]() : map.user(),
      );
  },
});

const FieldRow = defineComponent({
  name: "FieldRow",
  props: {
    label: { type: String, required: true },
    value: { type: String, required: true },
    icon: { type: String, required: true },
  },
  setup(props) {
    return () =>
      h("div", { class: "flex items-start gap-4" }, [
        h("div", { class: "mt-1" }, [h(Icon, { name: props.icon })]),
        h("div", null, [h("dt", { class: "text-[24px] text-slate-500" }, props.label), h("dd", { class: "text-[34px] font-semibold leading-none text-slate-900" }, props.value)]),
      ]);
  },
});
</script>

