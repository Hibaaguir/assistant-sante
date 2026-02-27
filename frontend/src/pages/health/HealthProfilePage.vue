<template>
  <div class="mx-auto max-w-[1460px] px-4 py-4 sm:px-6 lg:px-8">
    <header class="mb-4 flex items-start gap-3 sm:gap-4">
      <div class="mt-1 flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-500 to-violet-500 shadow-md shadow-indigo-200/80 sm:h-12 sm:w-12">
        <svg viewBox="0 0 24 24" class="h-6 w-6 text-white" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M12 21s-6.5-4.5-9-8.5C.7 8.4 3 4 7.3 4c2 0 3.6 1 4.7 2.6C13.1 5 14.7 4 16.7 4 21 4 23.3 8.4 21 12.5 18.5 16.5 12 21 12 21z" />
        </svg>
      </div>
      <div>
        <h1 class="text-[30px] font-bold leading-none tracking-[-0.01em] text-slate-900 sm:text-[34px]">Profil Santé</h1>
        <p class="mt-1 text-[12px] font-medium leading-none text-slate-500 sm:text-[13px]">Gérez vos informations de santé</p>
      </div>
    </header>

    <div v-if="loading" class="rounded-3xl border border-slate-200 bg-white p-6 text-sm text-slate-600">
      Chargement du profil...
    </div>

    <div v-else-if="error" class="rounded-3xl border border-red-200 bg-red-50 p-6 text-sm text-red-700">
      {{ error }}
    </div>

    <div v-else class="grid gap-4 lg:grid-cols-2">
      <section class="min-h-[250px] rounded-[12px] border border-[#d8e9ff] bg-white p-4 shadow-[0_1px_3px_rgba(15,23,42,0.04)] sm:p-5">
        <div class="mb-5 flex items-center justify-between">
          <div class="flex items-center gap-3">
            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#ecf4ff] text-[#3b82f6]">
              <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4" /><path d="M4 20c0-3.3 3.6-6 8-6s8 2.7 8 6" /></svg>
            </span>
            <h2 class="text-[20px] font-medium leading-none text-slate-900 sm:text-[23px]">Informations de base</h2>
          </div>
          <button v-if="!editing.base" type="button" class="text-slate-800 transition-colors hover:text-blue-600" @click="startEdit('base')">
            <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2"><path d="m16 3 5 5-11 11H5v-5L16 3z" /></svg>
          </button>
        </div>

        <div v-if="!editing.base" class="space-y-2">
          <FieldRow label="Nom" :value="user.name || '-'" icon="user" />
          <FieldRow label="Âge" :value="computedAge || '-'" icon="calendar" />
          <FieldRow label="Sexe" :value="profil.sexe || '-'" icon="users" />
          <FieldRow label="Taille" :value="profil.taille ? `${profil.taille} cm` : '-'" icon="ruler" />
          <FieldRow label="Poids" :value="profil.poids ? `${profil.poids} kg` : '-'" icon="weight" />
        </div>

        <form v-else class="space-y-4" @submit.prevent="saveSection('base')">
          <div>
            <label class="mb-1 block text-sm font-semibold text-slate-900">Nom</label>
            <input :value="user.name || ''" disabled class="h-11 w-full rounded-xl border border-slate-200 bg-slate-100 px-4 text-base text-slate-700" />
          </div>
          <div class="grid gap-3 md:grid-cols-2">
            <div>
              <label class="mb-1 block text-sm font-semibold text-slate-900">Sexe</label>
              <select v-model="draft.sexe" class="h-11 w-full rounded-xl border border-slate-200 bg-slate-100 px-4 text-base">
                <option value="femme">Femme</option>
                <option value="homme">Homme</option>
              </select>
            </div>
            <div>
              <label class="mb-1 block text-sm font-semibold text-slate-900">Taille (cm)</label>
              <input v-model="draft.taille" type="number" min="0" class="h-11 w-full rounded-xl border border-slate-200 bg-slate-100 px-4 text-base" />
            </div>
          </div>
          <div>
            <label class="mb-1 block text-sm font-semibold text-slate-900">Poids (kg)</label>
            <input v-model="draft.poids" type="number" min="0" class="h-11 w-full rounded-xl border border-slate-200 bg-slate-100 px-4 text-base" />
          </div>
          <div class="grid gap-3 md:grid-cols-2">
            <button type="submit" :disabled="savingSection==='base'" class="h-11 rounded-xl bg-[#2563eb] px-5 text-sm font-bold text-white disabled:opacity-60">Enregistrer</button>
            <button type="button" class="h-11 rounded-xl border border-slate-300 bg-white px-5 text-sm font-semibold text-slate-900" @click="cancelEdit('base')">Annuler</button>
          </div>
        </form>
      </section>

      <section class="min-h-[250px] rounded-[12px] border border-[#f2d9e4] bg-white p-4 shadow-[0_1px_3px_rgba(15,23,42,0.04)] sm:p-5">
        <div class="mb-5 flex items-center justify-between">
          <div class="flex items-center gap-3">
            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#ffeef2] text-[#ef4566]">
              <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 21s-6.5-4.5-9-8.5C.7 8.4 3 4 7.3 4c2 0 3.6 1 4.7 2.6C13.1 5 14.7 4 16.7 4 21 4 23.3 8.4 21 12.5 18.5 16.5 12 21 12 21z" /></svg>
            </span>
            <h2 class="text-[20px] font-medium leading-none text-slate-900 sm:text-[23px]">Santé</h2>
          </div>
          <button v-if="!editing.health" type="button" class="text-slate-800 transition-colors hover:text-blue-600" @click="startEdit('health')">
            <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2"><path d="m16 3 5 5-11 11H5v-5L16 3z" /></svg>
          </button>
        </div>

        <div v-if="!editing.health" class="space-y-2">
          <FieldRow label="Groupe sanguin" :value="profil.groupe_sanguin || '-'" icon="droplet" />
          <FieldRow label="Objectifs" :value="joinList(profil.objectifs)" icon="target" />
          <FieldRow label="Allergies" :value="joinList(profil.allergies)" icon="alert" />
          <FieldRow label="Maladies chroniques" :value="joinList(profil.maladies_chroniques)" icon="shield" />
        </div>

        <form v-else class="space-y-4" @submit.prevent="saveSection('health')">
          <div>
            <label class="mb-1 block text-sm font-semibold text-slate-900">Groupe sanguin</label>
            <select v-model="draft.groupe_sanguin" class="h-11 w-full rounded-xl border border-slate-200 bg-slate-100 px-4 text-base">
              <option value="">-</option><option value="A+">A+</option><option value="A-">A-</option><option value="B+">B+</option><option value="B-">B-</option><option value="AB+">AB+</option><option value="AB-">AB-</option><option value="O+">O+</option><option value="O-">O-</option>
            </select>
          </div>
          <div>
            <label class="mb-1 block text-sm font-semibold text-slate-900">Objectifs</label>
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
              <div class="flex flex-wrap gap-2">
                <button
                  v-for="goal in goalOptions"
                  :key="goal"
                  type="button"
                  class="rounded-lg border px-3 py-1.5 text-sm"
                  :class="isSelected('objectifs', goal) ? 'border-blue-300 bg-blue-50 text-blue-800' : 'border-slate-200 bg-white text-slate-700'"
                  @click="toggleSelected('objectifs', goal)"
                >
                  {{ goal }}
                </button>
              </div>
            </div>
          </div>
          <div>
            <label class="mb-1 block text-sm font-semibold text-slate-900">Allergies</label>
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
              <div class="flex gap-2">
                <select
                  v-model="selectedAllergyOption"
                  class="h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm"
                  @change="addSelectedOption('allergies', selectedAllergyOption, 'allergy')"
                >
                  <option value="">Selectionner une allergie</option>
                  <option v-for="item in allergyOptions" :key="item" :value="item">{{ item }}</option>
                </select>
              </div>
              <div v-if="draft.allergies.length" class="mt-3 flex flex-wrap gap-2">
                <span v-for="item in draft.allergies" :key="`allergy-selected-${item}`" class="inline-flex items-center gap-2 rounded-lg border border-red-200 bg-red-50 px-2.5 py-1 text-xs text-red-800">
                  {{ item }}
                  <button type="button" @click="toggleSelected('allergies', item)">x</button>
                </span>
              </div>
              <div class="mt-3 flex gap-2">
                <input v-model="customInputs.allergies" class="h-10 flex-1 rounded-lg border border-slate-200 bg-white px-3 text-sm" placeholder="Ajouter une allergie si absente..." />
                <button type="button" class="h-10 rounded-lg bg-red-500 px-3 text-sm font-medium text-white" @click="addCustom('allergies', customInputs.allergies)">Ajouter</button>
              </div>
            </div>
          </div>
          <div>
            <label class="mb-1 block text-sm font-semibold text-slate-900">Maladies chroniques</label>
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
              <div class="flex gap-2">
                <select
                  v-model="selectedDiseaseOption"
                  class="h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm"
                  @change="addSelectedOption('maladies_chroniques', selectedDiseaseOption, 'disease')"
                >
                  <option value="">Selectionner une maladie</option>
                  <option v-for="item in diseaseOptions" :key="item" :value="item">{{ item }}</option>
                </select>
              </div>
              <div v-if="draft.maladies_chroniques.length" class="mt-3 flex flex-wrap gap-2">
                <span v-for="item in draft.maladies_chroniques" :key="`disease-selected-${item}`" class="inline-flex items-center gap-2 rounded-lg border border-indigo-200 bg-indigo-50 px-2.5 py-1 text-xs text-indigo-800">
                  {{ item }}
                  <button type="button" @click="toggleSelected('maladies_chroniques', item)">x</button>
                </span>
              </div>
              <div class="mt-3 flex gap-2">
                <input v-model="customInputs.maladies_chroniques" class="h-10 flex-1 rounded-lg border border-slate-200 bg-white px-3 text-sm" placeholder="Ajouter une maladie si absente..." />
                <button type="button" class="h-10 rounded-lg bg-indigo-500 px-3 text-sm font-medium text-white" @click="addCustom('maladies_chroniques', customInputs.maladies_chroniques)">Ajouter</button>
              </div>
            </div>
          </div>
          <div class="grid gap-3 md:grid-cols-2">
            <button type="submit" :disabled="savingSection==='health'" class="h-11 rounded-xl bg-[#ef4444] px-5 text-sm font-bold text-white disabled:opacity-60">Enregistrer</button>
            <button type="button" class="h-11 rounded-xl border border-slate-300 bg-white px-5 text-sm font-semibold text-slate-900" @click="cancelEdit('health')">Annuler</button>
          </div>
        </form>
      </section>

      <section class="min-h-[250px] rounded-[12px] border border-[#d4f3df] bg-white p-4 shadow-[0_1px_3px_rgba(15,23,42,0.04)] sm:p-5">
        <div class="mb-5 flex items-center justify-between">
          <div class="flex items-center gap-3">
            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#e9fff0] text-[#18b05b]">
              <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12h4l2-6 4 12 2-6h6" /></svg>
            </span>
            <h2 class="text-[20px] font-medium leading-none text-slate-900 sm:text-[23px]">Habitudes</h2>
          </div>
          <button v-if="!editing.habits" type="button" class="text-slate-800 transition-colors hover:text-blue-600" @click="startEdit('habits')">
            <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2"><path d="m16 3 5 5-11 11H5v-5L16 3z" /></svg>
          </button>
        </div>

        <div v-if="!editing.habits" class="space-y-2">
          <FieldRow label="Fumeur" :value="yesNo(profil.fumeur)" icon="smoke" />
          <FieldRow label="Alcool" :value="yesNo(profil.alcool)" icon="wine" />
          <FieldRow label="Traitements" :value="treatmentsSummary(profil.traitements)" icon="pill" />
        </div>

        <form v-else class="space-y-4" @submit.prevent="saveSection('habits')">
          <div class="grid grid-cols-2 gap-3">
            <div class="flex h-14 items-center justify-between rounded-xl border border-slate-200 bg-slate-50 px-3">
              <label class="text-sm font-semibold text-slate-900">Fumeur</label>
              <button
                type="button"
                :aria-pressed="draft.fumeur"
                class="relative h-8 w-14 rounded-full bg-[#c7d2e0] transition-colors"
                @click="draft.fumeur = !draft.fumeur"
              >
                <span
                  class="absolute left-1 top-1 h-6 w-6 rounded-full bg-white transition-transform"
                  :class="draft.fumeur ? 'translate-x-6' : 'translate-x-0'"
                />
              </button>
            </div>
            <div class="flex h-14 items-center justify-between rounded-xl border border-slate-200 bg-slate-50 px-3">
              <label class="text-sm font-semibold text-slate-900">Alcool</label>
              <button
                type="button"
                :aria-pressed="draft.alcool"
                class="relative h-8 w-14 rounded-full bg-[#c7d2e0] transition-colors"
                @click="draft.alcool = !draft.alcool"
              >
                <span
                  class="absolute left-1 top-1 h-6 w-6 rounded-full bg-white transition-transform"
                  :class="draft.alcool ? 'translate-x-6' : 'translate-x-0'"
                />
              </button>
            </div>
          </div>
          <div class="rounded-xl border border-slate-200 bg-slate-50 p-3 sm:p-4">
            <div class="mb-3 flex items-center justify-between">
              <p class="text-sm font-semibold text-slate-900">Traitements</p>
              <button type="button" class="h-9 rounded-lg bg-emerald-600 px-3 text-sm font-medium text-white" @click="openTreatmentEditor()">Ajouter</button>
            </div>

            <div v-if="showTreatmentEditor" class="space-y-3 rounded-lg border border-slate-200 bg-white p-3">
              <div class="grid gap-3 md:grid-cols-2">
                <div>
                  <label class="mb-1 block text-xs font-medium text-slate-600">Type</label>
                  <input v-model="treatmentDraft.type" list="treatment-types" class="h-10 w-full rounded-lg border border-slate-200 bg-slate-50 px-3 text-sm" placeholder="Type de traitement" />
                  <datalist id="treatment-types">
                    <option v-for="type in treatmentTypes" :key="type" :value="type" />
                  </datalist>
                </div>
                <div>
                  <label class="mb-1 block text-xs font-medium text-slate-600">Nom</label>
                  <input v-model="treatmentDraft.name" list="treatment-names" class="h-10 w-full rounded-lg border border-slate-200 bg-slate-50 px-3 text-sm" placeholder="Nom du traitement" />
                  <datalist id="treatment-names">
                    <option v-for="name in treatmentNames" :key="name" :value="name" />
                  </datalist>
                </div>
              </div>
              <div class="grid gap-3 md:grid-cols-2">
                <div>
                  <label class="mb-1 block text-xs font-medium text-slate-600">Dose</label>
                  <input v-model="treatmentDraft.dose" class="h-10 w-full rounded-lg border border-slate-200 bg-slate-50 px-3 text-sm" placeholder="Ex: 500mg" />
                </div>
                <div>
                  <label class="mb-1 block text-xs font-medium text-slate-600">Fréquence</label>
                  <div class="grid grid-cols-2 gap-2">
                    <select v-model="treatmentDraft.frequency_unit" class="h-10 rounded-lg border border-slate-200 bg-slate-50 px-2 text-sm">
                      <option value="jour">jour</option>
                      <option value="semaine">semaine</option>
                      <option value="mois">mois</option>
                    </select>
                    <input
                      v-model.number="treatmentDraft.frequency_count"
                      type="number"
                      min="1"
                      class="h-10 rounded-lg border border-slate-200 bg-slate-50 px-2 text-sm"
                      placeholder="Nb de prises"
                    />
                  </div>
                </div>
              </div>
              <div class="grid gap-3 md:grid-cols-2">
                <div>
                  <label class="mb-1 block text-xs font-medium text-slate-600">Début (JJ/MM/AAAA)</label>
                  <input :value="treatmentDraft.start_date" class="h-10 w-full rounded-lg border border-slate-200 bg-slate-50 px-3 text-sm" maxlength="10" placeholder="01/03/2026" @input="(event) => handleTreatmentDateInput(event, 'start_date')" />
                </div>
                <div>
                  <label class="mb-1 block text-xs font-medium text-slate-600">Fin (JJ/MM/AAAA)</label>
                  <input :value="treatmentDraft.end_date" class="h-10 w-full rounded-lg border border-slate-200 bg-slate-50 px-3 text-sm" maxlength="10" placeholder="30/03/2026" @input="(event) => handleTreatmentDateInput(event, 'end_date')" />
                </div>
              </div>
              <div class="grid grid-cols-2 gap-2">
                <button type="button" class="h-10 rounded-lg bg-emerald-600 text-sm font-medium text-white" @click="saveTreatmentDraft">
                  {{ editingTreatmentIndex > -1 ? "Mettre à jour" : "Ajouter" }}
                </button>
                <button type="button" class="h-10 rounded-lg border border-slate-300 bg-white text-sm font-medium text-slate-800" @click="cancelTreatmentEdit">Annuler</button>
              </div>
            </div>

            <div v-if="draft.traitements.length" class="mt-3 space-y-2">
              <div v-for="(item, index) in draft.traitements" :key="`draft-treatment-${index}-${item.type}-${item.name}`" class="flex items-start justify-between rounded-lg border border-slate-200 bg-white px-3 py-2.5">
                <div class="min-w-0">
                  <p class="truncate text-sm font-medium text-slate-900">{{ item.name || 'Traitement' }}</p>
                  <p class="mt-0.5 text-xs text-slate-500">
                    {{ item.type || '-' }}<span v-if="item.dose"> | {{ item.dose }}</span><span v-if="item.frequency_count && item.frequency_unit"> | {{ item.frequency_count }}/{{ item.frequency_unit }}</span>
                  </p>
                </div>
                <div class="ml-3 flex items-center gap-3">
                  <button type="button" class="text-xs font-medium text-blue-700" @click="openTreatmentEditor(index)">Modifier</button>
                  <button type="button" class="text-xs font-medium text-red-600" @click="removeTreatment(index)">Retirer</button>
                </div>
              </div>
            </div>
            <p v-else class="text-xs text-slate-500">Aucun traitement ajouté.</p>
          </div>
          <div class="grid gap-3 md:grid-cols-2">
            <button type="submit" :disabled="savingSection==='habits'" class="h-11 rounded-xl bg-[#16a34a] px-5 text-sm font-bold text-white disabled:opacity-60">Enregistrer</button>
            <button type="button" class="h-11 rounded-xl border border-slate-300 bg-white px-5 text-sm font-semibold text-slate-900" @click="cancelEdit('habits')">Annuler</button>
          </div>
        </form>
      </section>

      <section class="min-h-[250px] rounded-[12px] border border-[#e7dcff] bg-white p-4 shadow-[0_1px_3px_rgba(15,23,42,0.04)] sm:p-5">
        <div class="mb-5 flex items-center justify-between">
          <div class="flex items-center gap-3">
            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#f3edff] text-[#9333ea]">
              <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 4v5a4 4 0 1 0 8 0V4M12 13v3a4 4 0 0 0 8 0v-1a2 2 0 1 0-4 0v1" /></svg>
            </span>
            <h2 class="text-[20px] font-medium leading-none text-slate-900 sm:text-[23px]">Suivi médecin</h2>
          </div>
          <button v-if="!editing.doctor" type="button" class="text-slate-800 transition-colors hover:text-blue-600" @click="startEdit('doctor')">
            <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2"><path d="m16 3 5 5-11 11H5v-5L16 3z" /></svg>
          </button>
        </div>

        <div v-if="!editing.doctor" class="space-y-2">
          <FieldRow label="Consulte médecin" :value="yesNo(profil.consulte_medecin)" icon="stetho" />
          <FieldRow label="Autorise accès médecin" :value="yesNo(profil.medecin_peut_consulter)" icon="shield" />
          <FieldRow label="Email médecin" :value="profil.medecin_email || '-'" icon="stetho" />
        </div>

        <form v-else class="space-y-4" @submit.prevent="saveSection('doctor')">
          <div>
            <label class="mb-1 block text-sm font-semibold text-slate-900">Consulte médecin</label>
            <select v-model="draft.consulte_medecin" class="h-11 w-full rounded-xl border border-slate-200 bg-slate-100 px-4 text-base" @change="validateDoctorEmail">
              <option :value="true">Oui</option><option :value="false">Non</option>
            </select>
          </div>
          <div>
            <label class="mb-1 block text-sm font-semibold text-slate-900">Autorise accès médecin</label>
            <select v-model="draft.medecin_peut_consulter" :disabled="!draft.consulte_medecin" class="h-11 w-full rounded-xl border border-slate-200 bg-slate-100 px-4 text-base disabled:opacity-60" @change="validateDoctorEmail">
              <option :value="true">Oui</option><option :value="false">Non</option>
            </select>
          </div>
          <div>
            <label class="mb-1 block text-sm font-semibold text-slate-900">Email médecin</label>
            <input
              v-model.trim="draft.medecin_email"
              :disabled="!(draft.consulte_medecin && draft.medecin_peut_consulter)"
              class="h-11 w-full rounded-xl border bg-slate-100 px-4 text-base disabled:opacity-60"
              :class="doctorEmailError ? 'border-red-400 focus:border-red-500' : 'border-slate-200'"
              @input="validateDoctorEmail"
              @blur="validateDoctorEmail"
            />
            <p v-if="doctorEmailError" class="mt-1 text-xs font-medium text-red-600">
              {{ doctorEmailError }}
            </p>
          </div>
          <div class="grid gap-3 md:grid-cols-2">
            <button type="submit" :disabled="savingSection==='doctor'" class="h-11 rounded-xl bg-[#a21caf] px-5 text-sm font-bold text-white disabled:opacity-60">Enregistrer</button>
            <button type="button" class="h-11 rounded-xl border border-slate-300 bg-white px-5 text-sm font-semibold text-slate-900" @click="cancelEdit('doctor')">Annuler</button>
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
const doctorEmailError = ref("");
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

const goalOptions = [
  "Maintenir mon poids",
  "Perdre du poids",
  "Avoir plus d'energie",
  "Mieux dormir",
  "Reduire mon stress",
  "Suivre ma sante regulierement",
];
const allergyOptions = [
  "Pollen",
  "Acariens",
  "Poils d'animaux",
  "Poussiere",
  "Arachides",
  "Fruits de mer",
  "Lait (lactose)",
  "Oeufs",
  "Gluten",
  "Penicilline",
  "Aspirine",
  "Piqures d'insectes",
  "Moisissures",
];
const diseaseOptions = [
  "Diabete",
  "Hypertension arterielle",
  "Asthme",
  "Maladie cardiaque",
  "Maladie renale chronique",
  "Maladie thyroidienne",
  "Arthrite",
  "Epilepsie",
  "Migraine chronique",
  "Maladie pulmonaire chronique",
  "Cholesterol eleve",
  "Depression",
  "Anemie",
];
const treatmentTypes = ref([
  "Anti-inflammatoire",
  "Antibiotique",
  "Antidouleur",
  "Antihypertenseur",
  "Antidiabetique",
  "Anticoagulant",
  "Antiallergique",
  "Antidepresseur",
  "Corticoide",
  "Traitement hormonal",
  "Supplement vitaminique",
  "Inhalateur respiratoire",
]);
const treatmentNames = ref(["Paracetamol", "Ibuprofene", "Insuline", "Metformine", "Amlodipine", "Ventoline"]);

const customInputs = reactive({
  allergies: "",
  maladies_chroniques: "",
});
const selectedAllergyOption = ref("");
const selectedDiseaseOption = ref("");

const showTreatmentEditor = ref(false);
const editingTreatmentIndex = ref(-1);
const treatmentDraft = reactive({
  type: "",
  name: "",
  dose: "",
  frequency_unit: "jour",
  frequency_count: 1,
  start_date: "",
  end_date: "",
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

function normalizeList(value) {
  if (!Array.isArray(value)) return [];
  return value.map((item) => String(item || "").trim()).filter(Boolean);
}

function isSelected(key, value) {
  return Array.isArray(draft[key]) && draft[key].includes(value);
}

function toggleSelected(key, value) {
  if (!Array.isArray(draft[key])) draft[key] = [];
  if (draft[key].includes(value)) {
    draft[key] = draft[key].filter((item) => item !== value);
    return;
  }
  draft[key] = [...draft[key], value];
}

function addCustom(key, value) {
  const normalized = String(value || "").trim();
  if (!normalized) return;
  if (!Array.isArray(draft[key])) draft[key] = [];
  if (!draft[key].includes(normalized)) draft[key] = [...draft[key], normalized];
  if (key === "allergies") customInputs.allergies = "";
  if (key === "maladies_chroniques") customInputs.maladies_chroniques = "";
}

function addSelectedOption(key, value, kind) {
  const normalized = String(value || "").trim();
  if (!normalized) return;
  if (!Array.isArray(draft[key])) draft[key] = [];
  if (!draft[key].includes(normalized)) draft[key] = [...draft[key], normalized];
  if (kind === "allergy") selectedAllergyOption.value = "";
  if (kind === "disease") selectedDiseaseOption.value = "";
}

function treatmentsSummary(value) {
  if (!Array.isArray(value) || value.length === 0) return "-";
  const labels = value
    .map((item) => (item && typeof item === "object" ? item.name || item.type || "" : ""))
    .filter(Boolean);
  return labels.length ? labels.join(", ") : `${value.length} traitement(s)`;
}

function formatDateWithSlashes(value) {
  const digits = String(value || "").replace(/\D/g, "").slice(0, 8);
  if (digits.length <= 2) return digits;
  if (digits.length <= 4) return `${digits.slice(0, 2)}/${digits.slice(2)}`;
  return `${digits.slice(0, 2)}/${digits.slice(2, 4)}/${digits.slice(4, 8)}`;
}

function handleTreatmentDateInput(event, key) {
  const raw = event?.target?.value ?? "";
  treatmentDraft[key] = formatDateWithSlashes(raw);
}

function validateDoctorEmail() {
  const canUseDoctorEmail = Boolean(draft.consulte_medecin && draft.medecin_peut_consulter);
  if (!canUseDoctorEmail) {
    doctorEmailError.value = "";
    return true;
  }

  const email = String(draft.medecin_email || "").trim();
  if (!email) {
    doctorEmailError.value = "Veuillez renseigner l'email du medecin.";
    return false;
  }

  const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!emailPattern.test(email)) {
    doctorEmailError.value = "Format invalide: utilisez un email de type nom@domaine.com.";
    return false;
  }

  doctorEmailError.value = "";
  return true;
}

function resetTreatmentDraft() {
  treatmentDraft.type = "";
  treatmentDraft.name = "";
  treatmentDraft.dose = "";
  treatmentDraft.frequency_unit = "jour";
  treatmentDraft.frequency_count = 1;
  treatmentDraft.start_date = "";
  treatmentDraft.end_date = "";
}

function openTreatmentEditor(index = -1) {
  showTreatmentEditor.value = true;
  editingTreatmentIndex.value = index;

  if (index < 0) {
    resetTreatmentDraft();
    return;
  }

  const item = draft.traitements[index];
  if (!item || typeof item !== "object") {
    resetTreatmentDraft();
    return;
  }

  treatmentDraft.type = item.type || "";
  treatmentDraft.name = item.name || "";
  treatmentDraft.dose = item.dose || "";
  treatmentDraft.frequency_unit = item.frequency_unit || "jour";
  treatmentDraft.frequency_count = Number(item.frequency_count || 1);
  treatmentDraft.start_date = item.start_date || "";
  treatmentDraft.end_date = item.end_date || "";
}

function cancelTreatmentEdit() {
  showTreatmentEditor.value = false;
  editingTreatmentIndex.value = -1;
  resetTreatmentDraft();
}

function saveTreatmentDraft() {
  if (!treatmentDraft.type || !treatmentDraft.name) return;

  const nextTreatment = {
    type: treatmentDraft.type,
    name: treatmentDraft.name,
    dose: treatmentDraft.dose || null,
    frequency_unit: treatmentDraft.frequency_unit || "jour",
    frequency_count: Number(treatmentDraft.frequency_count || 1),
    start_date: treatmentDraft.start_date || null,
    end_date: treatmentDraft.end_date || null,
    duration:
      treatmentDraft.start_date && treatmentDraft.end_date
        ? `${treatmentDraft.start_date} - ${treatmentDraft.end_date}`
        : null,
  };

  if (!Array.isArray(draft.traitements)) draft.traitements = [];

  if (editingTreatmentIndex.value > -1) {
    draft.traitements.splice(editingTreatmentIndex.value, 1, nextTreatment);
  } else {
    draft.traitements.push(nextTreatment);
  }

  if (nextTreatment.type && !treatmentTypes.value.includes(nextTreatment.type)) {
    treatmentTypes.value = [...treatmentTypes.value, nextTreatment.type];
  }
  if (nextTreatment.name && !treatmentNames.value.includes(nextTreatment.name)) {
    treatmentNames.value = [...treatmentNames.value, nextTreatment.name];
  }

  cancelTreatmentEdit();
}

function removeTreatment(index) {
  if (!Array.isArray(draft.traitements)) return;
  draft.traitements.splice(index, 1);
}

function syncDraftFromProfil() {
  draft.sexe = profil.sexe || "";
  draft.taille = profil.taille ?? "";
  draft.poids = profil.poids ?? "";
  draft.groupe_sanguin = profil.groupe_sanguin || "";
  draft.objectifs = normalizeList(profil.objectifs);
  draft.allergies = normalizeList(profil.allergies);
  draft.maladies_chroniques = normalizeList(profil.maladies_chroniques);
  draft.traitements = Array.isArray(profil.traitements) ? [...profil.traitements] : [];
  draft.fumeur = Boolean(profil.fumeur);
  draft.alcool = Boolean(profil.alcool);
  draft.consulte_medecin = Boolean(profil.consulte_medecin);
  draft.medecin_peut_consulter = Boolean(profil.medecin_peut_consulter);
  draft.medecin_email = profil.medecin_email || "";
  doctorEmailError.value = "";
  customInputs.allergies = "";
  customInputs.maladies_chroniques = "";
  selectedAllergyOption.value = "";
  selectedDiseaseOption.value = "";
  cancelTreatmentEdit();
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
  const objectifs = normalizeList(draft.objectifs);
  const allergies = normalizeList(draft.allergies);
  const maladiesChroniques = normalizeList(draft.maladies_chroniques);
  const traitements = Array.isArray(draft.traitements) ? draft.traitements : [];
  const prendMedicament = traitements.length > 0;
  const consulteMedecin = Boolean(draft.consulte_medecin);
  const medecinPeutConsulter = Boolean(consulteMedecin && draft.medecin_peut_consulter);

  return {
    sexe: draft.sexe || null,
    taille: draft.taille === "" ? null : Number(draft.taille),
    poids: draft.poids === "" ? null : Number(draft.poids),
    groupe_sanguin: draft.groupe_sanguin || null,
    objectif: objectifs[0] || null,
    objectifs,
    allergies,
    maladies_chroniques: maladiesChroniques,
    traitements,
    prend_medicament: prendMedicament,
    nom_medicament: prendMedicament ? (traitements[0]?.name || null) : null,
    fumeur: Boolean(draft.fumeur),
    alcool: Boolean(draft.alcool),
    consulte_medecin: consulteMedecin,
    medecin_peut_consulter: medecinPeutConsulter,
    medecin_email: medecinPeutConsulter ? draft.medecin_email || null : null,
  };
}

async function saveSection(section) {
  if (section === "doctor" && !validateDoctorEmail()) {
    error.value = "";
    return;
  }

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
      if (section === "doctor" && e.response.data.errors?.medecin_email) {
        const doctorFieldError = e.response.data.errors.medecin_email;
        doctorEmailError.value = Array.isArray(doctorFieldError)
          ? doctorFieldError[0]
          : "Email medecin invalide.";
        return;
      }
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
      h("div", { class: "flex items-start gap-3 rounded-xl px-2.5 py-2 transition-colors hover:bg-slate-100/70 sm:px-3 sm:py-2.5" }, [
        h("div", { class: "mt-1" }, [h(Icon, { name: props.icon })]),
        h("div", null, [
          h("dt", { class: "text-[13px] font-normal text-slate-500 sm:text-[14px]" }, props.label),
          h("dd", { class: "text-[18px] font-medium leading-none text-slate-900 sm:text-[20px]" }, props.value),
        ]),
      ]);
  },
});
</script>
