<template>
  <div class="mx-auto max-w-[1460px] rounded-3xl border border-slate-200 bg-gradient-to-br from-[#edf3ff] via-[#f6f2ff] to-[#f1faf5] px-4 py-4 sm:px-6 lg:px-8">
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

    <NotificationsEnLigne />

    <div v-if="loading" class="rounded-3xl border border-[#d8e6ff] bg-white/85 p-6 text-sm text-slate-600">
      Chargement du profil...
    </div>

    <div v-else-if="loadError" class="rounded-3xl border border-red-200 bg-red-50/90 p-6 text-sm text-red-700">
      {{ loadError }}
    </div>

    <div v-else class="grid gap-4 lg:grid-cols-2">
      <section class="min-h-[250px] rounded-[14px] border border-[#cfe0ff] bg-gradient-to-br from-[#edf4ff] via-white to-[#f7fbff] p-4 shadow-[0_2px_8px_rgba(59,130,246,0.08)] sm:p-5">
        <div class="mb-5 flex items-center justify-between">
          <div class="flex items-center gap-3">
            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#ecf4ff] text-[#3b82f6]">
              <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4" /><path d="M4 20c0-3.3 3.6-6 8-6s8 2.7 8 6" /></svg>
            </span>
            <h2 class="text-[20px] font-medium leading-none text-slate-900 sm:text-[23px]">Informations de base</h2>
          </div>
          <button v-if="!editing.base" type="button" class="text-slate-800 transition-colors hover:text-[#2563eb]" @click="startEdit('base')">
            <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2"><path d="m16 3 5 5-11 11H5v-5L16 3z" /></svg>
          </button>
        </div>

        <div v-if="!editing.base" class="space-y-2">
          <LigneChampSante label="Nom" :value="user.name || '-'" icon="user" />
          <LigneChampSante label="Âge" :value="computedAge || '-'" icon="calendar" />
          <LigneChampSante label="Sexe" :value="profil.sexe || '-'" icon="users" />
          <LigneChampSante label="Taille" :value="profil.taille ? `${profil.taille} cm` : '-'" icon="ruler" />
          <LigneChampSante label="Poids" :value="profil.poids ? `${profil.poids} kg` : '-'" icon="weight" />
        </div>

        <form v-else class="space-y-4" novalidate @submit.prevent="enregistrerSection('base')">
          <div v-if="sectionErrors.base.form.length" class="rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-700">
            <p v-for="(message, idx) in sectionErrors.base.form" :key="`base-form-error-${idx}`">
              {{ message }}
            </p>
          </div>
          <div>
            <label class="mb-1 block text-sm font-semibold text-slate-900">Nom</label>
            <input :value="user.name || ''" disabled class="h-11 w-full rounded-xl border border-slate-200 bg-slate-100 px-4 text-base text-slate-700" />
          </div>
          <div class="grid gap-3 md:grid-cols-2">
            <div>
              <label class="mb-1 block text-sm font-semibold text-slate-900">Sexe</label>
              <select
                v-model="draft.sexe"
                class="h-11 w-full rounded-xl border bg-slate-100 px-4 text-base"
                :class="sectionErrors.base.sexe ? 'border-red-400 focus:border-red-500' : 'border-slate-200'"
              >
                <option value="femme">Femme</option>
                <option value="homme">Homme</option>
              </select>
              <p v-if="sectionErrors.base.sexe" class="mt-1 text-xs font-medium text-red-600">{{ sectionErrors.base.sexe }}</p>
            </div>
            <div>
              <label class="mb-1 block text-sm font-semibold text-slate-900">Taille (cm)</label>
              <input
                v-model="draft.taille"
                type="number"
                min="80"
                max="250"
                class="h-11 w-full rounded-xl border bg-slate-100 px-4 text-base"
                :class="sectionErrors.base.taille ? 'border-red-400 focus:border-red-500' : 'border-slate-200'"
              />
              <p v-if="sectionErrors.base.taille" class="mt-1 text-xs font-medium text-red-600">{{ sectionErrors.base.taille }}</p>
            </div>
          </div>
          <div>
            <label class="mb-1 block text-sm font-semibold text-slate-900">Poids (kg)</label>
            <input
              v-model="draft.poids"
              type="number"
              min="35"
              max="250"
              class="h-11 w-full rounded-xl border bg-slate-100 px-4 text-base"
              :class="sectionErrors.base.poids ? 'border-red-400 focus:border-red-500' : 'border-slate-200'"
            />
            <p v-if="sectionErrors.base.poids" class="mt-1 text-xs font-medium text-red-600">{{ sectionErrors.base.poids }}</p>
          </div>
          <div class="grid gap-3 md:grid-cols-2">
            <button type="submit" :disabled="savingSection==='base'" class="h-11 rounded-xl bg-[#2563eb] px-5 text-sm font-bold text-white disabled:opacity-60">Enregistrer</button>
            <button type="button" class="h-11 rounded-xl border border-slate-300 bg-white px-5 text-sm font-semibold text-slate-900" @click="cancelEdit('base')">Annuler</button>
          </div>
        </form>
      </section>

      <section class="min-h-[250px] rounded-[14px] border border-[#f2d9e4] bg-gradient-to-br from-[#fff1f6] via-white to-[#fff7fa] p-4 shadow-[0_2px_8px_rgba(244,114,182,0.08)] sm:p-5">
        <div class="mb-5 flex items-center justify-between">
          <div class="flex items-center gap-3">
            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#ffeef2] text-[#ef4566]">
              <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 21s-6.5-4.5-9-8.5C.7 8.4 3 4 7.3 4c2 0 3.6 1 4.7 2.6C13.1 5 14.7 4 16.7 4 21 4 23.3 8.4 21 12.5 18.5 16.5 12 21 12 21z" /></svg>
            </span>
            <h2 class="text-[20px] font-medium leading-none text-slate-900 sm:text-[23px]">Santé</h2>
          </div>
          <button v-if="!editing.health" type="button" class="text-slate-800 transition-colors hover:text-[#ef4566]" @click="startEdit('health')">
            <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2"><path d="m16 3 5 5-11 11H5v-5L16 3z" /></svg>
          </button>
        </div>

        <div v-if="!editing.health" class="space-y-2">
          <LigneChampSante label="Groupe sanguin" :value="profil.groupe_sanguin || '-'" icon="droplet" />
          <LigneChampSante label="Objectifs" :value="joindreListe(profil.objectifs)" icon="target" />
          <LigneChampSante label="Allergies" :value="joindreListe(profil.allergies)" icon="alert" />
          <LigneChampSante label="Maladies chroniques" :value="joindreListe(profil.maladies_chroniques)" icon="shield" />
        </div>

        <form v-else class="space-y-4" novalidate @submit.prevent="enregistrerSection('health')">
          <div v-if="sectionErrors.health.form.length" class="rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-700">
            <p v-for="(message, idx) in sectionErrors.health.form" :key="`health-form-error-${idx}`">
              {{ message }}
            </p>
          </div>
          <div>
            <label class="mb-1 block text-sm font-semibold text-slate-900">Groupe sanguin</label>
            <select v-model="draft.groupe_sanguin" class="h-11 w-full rounded-xl border border-slate-200 bg-slate-100 px-4 text-base">
              <option value="">-</option><option value="A+">A+</option><option value="A-">A-</option><option value="B+">B+</option><option value="B-">B-</option><option value="AB+">AB+</option><option value="AB-">AB-</option><option value="O+">O+</option><option value="O-">O-</option>
            </select>
          </div>
          <div>
            <label class="mb-1 block text-sm font-semibold text-slate-900">Objectifs</label>
            <div class="rounded-xl border bg-slate-50 p-3" :class="sectionErrors.health.objectifs ? 'border-red-300' : 'border-slate-200'">
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
            <p v-if="sectionErrors.health.objectifs" class="mt-1 text-xs font-medium text-red-600">{{ sectionErrors.health.objectifs }}</p>
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

      <section class="min-h-[250px] rounded-[14px] border border-[#d4f3df] bg-gradient-to-br from-[#effcf4] via-white to-[#f7fff9] p-4 shadow-[0_2px_8px_rgba(34,197,94,0.08)] sm:p-5">
        <div class="mb-5 flex items-center justify-between">
          <div class="flex items-center gap-3">
            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#e9fff0] text-[#18b05b]">
              <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12h4l2-6 4 12 2-6h6" /></svg>
            </span>
            <h2 class="text-[20px] font-medium leading-none text-slate-900 sm:text-[23px]">Habitudes</h2>
          </div>
          <button v-if="!editing.habits" type="button" class="text-slate-800 transition-colors hover:text-[#18b05b]" @click="startEdit('habits')">
            <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2"><path d="m16 3 5 5-11 11H5v-5L16 3z" /></svg>
          </button>
        </div>

        <div v-if="!editing.habits" class="space-y-2">
          <LigneChampSante label="Fumeur" :value="ouiNon(profil.fumeur)" icon="smoke" />
          <LigneChampSante label="Alcool" :value="ouiNon(profil.alcool)" icon="wine" />
          <LigneChampSante label="Traitements" :value="treatmentsSummary(profil.traitements)" icon="pill" />
        </div>

        <form v-else class="space-y-4" novalidate @submit.prevent="enregistrerSection('habits')">
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
                  <input
                    v-model="treatmentDraft.type"
                    list="treatment-types"
                    class="h-10 w-full rounded-lg border border-slate-200 bg-slate-50 px-3 text-sm"
                    placeholder="Type de traitement"
                    @input="handleTreatmentTypeInput"
                  />
                  <datalist id="treatment-types">
                    <option v-for="type in treatmentTypes" :key="type" :value="type" />
                  </datalist>
                </div>
                <div>
                  <label class="mb-1 block text-xs font-medium text-slate-600">Nom</label>
                  <input
                    v-model="treatmentDraft.name"
                    list="treatment-names"
                    :disabled="!treatmentDraft.type"
                    class="h-10 w-full rounded-lg border border-slate-200 bg-slate-50 px-3 text-sm disabled:cursor-not-allowed disabled:bg-slate-100"
                    :placeholder="treatmentDraft.type ? 'Nom du traitement' : 'Sélectionnez d\'abord un type'"
                  />
                  <datalist id="treatment-names">
                    <option v-for="name in treatmentNamesForSelectedType" :key="name" :value="name" />
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
                <button type="button" class="h-10 rounded-lg border border-slate-300 bg-white text-sm font-medium text-slate-800" @click="cancelTreatmentEditWithNotice">Annuler</button>
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
                  <button type="button" class="text-xs font-medium text-red-600" @click="requestRemoveTreatment(index)">Retirer</button>
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

      <section class="min-h-[250px] rounded-[14px] border border-[#e7dcff] bg-gradient-to-br from-[#f5efff] via-white to-[#faf7ff] p-4 shadow-[0_2px_8px_rgba(147,51,234,0.08)] sm:p-5">
        <div class="mb-5 flex items-center justify-between">
          <div class="flex items-center gap-3">
            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#f3edff] text-[#9333ea]">
              <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 4v5a4 4 0 1 0 8 0V4M12 13v3a4 4 0 0 0 8 0v-1a2 2 0 1 0-4 0v1" /></svg>
            </span>
            <h2 class="text-[20px] font-medium leading-none text-slate-900 sm:text-[23px]">Suivi médecin</h2>
          </div>
          <button v-if="!editing.doctor" type="button" class="text-slate-800 transition-colors hover:text-[#9333ea]" @click="startEdit('doctor')">
            <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2"><path d="m16 3 5 5-11 11H5v-5L16 3z" /></svg>
          </button>
        </div>

        <div v-if="!editing.doctor" class="space-y-2">
          <LigneChampSante label="Consulte médecin" :value="ouiNon(profil.consulte_medecin)" icon="stetho" />
          <LigneChampSante label="Autorise accès médecin" :value="ouiNon(profil.medecin_peut_consulter)" icon="shield" />
          <LigneChampSante label="Email médecin" :value="profil.medecin_email || '-'" icon="stetho" />
        </div>

        <form v-else class="space-y-4" novalidate @submit.prevent="enregistrerSection('doctor')">
          <div>
            <label class="mb-1 block text-sm font-semibold text-slate-900">Consulte médecin</label>
            <select v-model="draft.consulte_medecin" class="h-11 w-full rounded-xl border border-slate-200 bg-slate-100 px-4 text-base" @change="validerEmailMedecin">
              <option :value="true">Oui</option><option :value="false">Non</option>
            </select>
          </div>
          <div>
            <label class="mb-1 block text-sm font-semibold text-slate-900">Autorise accès médecin</label>
            <select v-model="draft.medecin_peut_consulter" :disabled="!draft.consulte_medecin" class="h-11 w-full rounded-xl border border-slate-200 bg-slate-100 px-4 text-base disabled:opacity-60" @change="validerEmailMedecin">
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
              @input="validerEmailMedecin"
              @blur="validerEmailMedecin"
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

    <DialogueConfirmation
      :open="confirmDeleteTreatmentOpen"
      title="Confirmer la suppression"
      message="Voulez-vous supprimer ce traitement ?"
      confirm-label="Supprimer"
      cancel-label="Annuler"
      @confirm="confirmRemoveTreatment"
      @cancel="cancelRemoveTreatment"
    />
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from "vue";
import { useRouter } from "vue-router";
import { useAuthStore } from "@/stores/auth";
import api from "@/services/api";
import LigneChampSante from "@/components/health/LigneChampSante.vue";
import { useNotificationsStore } from "@/stores/notifications";
import NotificationsEnLigne from "@/components/ui/NotificationsEnLigne.vue";
import DialogueConfirmation from "@/components/ui/DialogueConfirmation.vue";

/*
  Cette page affiche et edite le profil sante utilisateur.
  Elle charge le profil, ouvre des sections d'edition, puis sauvegarde via l'API.
  La logique garde un draft local pour eviter de modifier l'affichage tant que l'utilisateur ne valide pas.
  Les helpers ci-dessous servent surtout a normaliser les listes et les traitements.
*/

// State principal de la page.
const router = useRouter();
const authStore = useAuthStore();
const notifications = useNotificationsStore();
const loading = ref(true);
const loadError = ref("");
const doctorEmailError = ref("");
const savingSection = ref("");
const profil = reactive({});
const user = reactive({});
const sectionErrors = reactive({
  base: {
    sexe: "",
    taille: "",
    poids: "",
    form: [],
  },
  health: {
    objectifs: "",
    form: [],
  },
});

const editing = reactive({
  base: false,
  health: false,
  habits: false,
  doctor: false,
});

// Draft edite dans les formulaires avant sauvegarde.
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
const allergyOptions = ref([
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
]);
const diseaseOptions = ref([
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
]);
const treatmentTypes = ref([]);
const treatmentNamesByType = reactive({});

const customInputs = reactive({
  allergies: "",
  maladies_chroniques: "",
});
const selectedAllergyOption = ref("");
const selectedDiseaseOption = ref("");

const showTreatmentEditor = ref(false);
const editingTreatmentIndex = ref(-1);
const confirmDeleteTreatmentOpen = ref(false);
const pendingDeleteTreatmentIndex = ref(-1);
const treatmentDraft = reactive({
  type: "",
  name: "",
  dose: "",
  frequency_unit: "jour",
  frequency_count: 1,
  start_date: "",
  end_date: "",
});

function normalizeTreatmentText(value) {
  return String(value || "").trim().replace(/\s+/g, " ");
}

function appendUniqueCatalogOption(targetRef, value) {
  const normalizedValue = normalizeTreatmentText(value);
  if (!normalizedValue) return;

  const exists = targetRef.value.some((item) =>
    item.localeCompare(normalizedValue, "fr", { sensitivity: "base" }) === 0
  );

  if (exists) return;

  targetRef.value = [...targetRef.value, normalizedValue].sort((a, b) =>
    a.localeCompare(b, "fr", { sensitivity: "base" })
  );
}

function ensureTreatmentType(type) {
  const normalizedType = normalizeTreatmentText(type);
  if (!normalizedType) return "";

  if (!treatmentTypes.value.includes(normalizedType)) {
    treatmentTypes.value = [...treatmentTypes.value, normalizedType].sort((a, b) =>
      a.localeCompare(b, "fr", { sensitivity: "base" })
    );
  }

  if (!Array.isArray(treatmentNamesByType[normalizedType])) {
    treatmentNamesByType[normalizedType] = [];
  }

  return normalizedType;
}

function mergeTreatmentCatalogEntry(type, name = "") {
  const normalizedType = ensureTreatmentType(type);
  if (!normalizedType) return;

  const normalizedName = normalizeTreatmentText(name);
  if (!normalizedName) return;

  const namesForType = treatmentNamesByType[normalizedType];
  if (!namesForType.includes(normalizedName)) {
    namesForType.push(normalizedName);
    namesForType.sort((a, b) => a.localeCompare(b, "fr", { sensitivity: "base" }));
  }
}

function applyTreatmentCatalog(catalog) {
  const types = Array.isArray(catalog?.types) ? catalog.types : [];
  types.forEach((type) => ensureTreatmentType(type));

  const namesByType =
    catalog?.names_by_type && typeof catalog.names_by_type === "object"
      ? catalog.names_by_type
      : {};

  Object.entries(namesByType).forEach(([type, names]) => {
    ensureTreatmentType(type);
    (Array.isArray(names) ? names : []).forEach((name) => mergeTreatmentCatalogEntry(type, name));
  });
}

async function loadTreatmentCatalog() {
  try {
    const response = await api.get("/treatment-catalog");
    applyTreatmentCatalog(response?.data?.data || {});
  } catch (_) {
    // Le profil reste editable meme si le catalogue n'est pas charge.
  }
}

function applyAllergyCatalog(catalog) {
  const items = Array.isArray(catalog?.items) ? catalog.items : [];
  items.forEach((item) => appendUniqueCatalogOption(allergyOptions, item));
}

function applyChronicDiseaseCatalog(catalog) {
  const items = Array.isArray(catalog?.items) ? catalog.items : [];
  items.forEach((item) => appendUniqueCatalogOption(diseaseOptions, item));
}

async function loadAllergyCatalog() {
  try {
    const response = await api.get("/allergy-catalog");
    applyAllergyCatalog(response?.data?.data || {});
  } catch (_) {
    // Le profil reste editable meme si le catalogue n'est pas charge.
  }
}

async function loadChronicDiseaseCatalog() {
  try {
    const response = await api.get("/chronic-disease-catalog");
    applyChronicDiseaseCatalog(response?.data?.data || {});
  } catch (_) {
    // Le profil reste editable meme si le catalogue n'est pas charge.
  }
}

async function persistAllergyCatalogEntry(name) {
  const normalizedName = normalizeTreatmentText(name);
  if (!normalizedName) return;

  await api.post("/allergy-catalog", {
    name: normalizedName,
  });
}

async function persistChronicDiseaseCatalogEntry(name) {
  const normalizedName = normalizeTreatmentText(name);
  if (!normalizedName) return;

  await api.post("/chronic-disease-catalog", {
    name: normalizedName,
  });
}

async function persistTreatmentCatalogEntry(type, name = "") {
  const normalizedType = normalizeTreatmentText(type);
  const normalizedName = normalizeTreatmentText(name);
  if (!normalizedType) return;

  await api.post("/treatment-catalog", {
    type: normalizedType,
    name: normalizedName || null,
  });
}

function getTreatmentNamesByType(type) {
  const normalizedType = ensureTreatmentType(type);
  if (!normalizedType) return [];
  return treatmentNamesByType[normalizedType];
}

const treatmentNamesForSelectedType = computed(() => getTreatmentNamesByType(treatmentDraft.type));

// Age affiche dans la section "Informations de base".
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

// Helpers d'affichage simples.
function ouiNon(value) {
  return value ? "Oui" : "Non";
}

function joindreListe(value) {
  if (!Array.isArray(value) || value.length === 0) return "-";
  return value.filter(Boolean).join(", ");
}

function normaliserListe(value) {
  if (!Array.isArray(value)) return [];
  return value.map((item) => String(item || "").trim()).filter(Boolean);
}

// Helpers de selection multiple (objectifs, allergies, maladies).
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

async function addCustom(key, value) {
  const normalized = String(value || "").trim();
  if (!normalized) return;
  if (!Array.isArray(draft[key])) draft[key] = [];
  if (!draft[key].includes(normalized)) draft[key] = [...draft[key], normalized];

  if (key === "allergies") {
    appendUniqueCatalogOption(allergyOptions, normalized);
    try {
      await persistAllergyCatalogEntry(normalized);
    } catch {
      notifications.avertissement("Allergie ajoutee au profil local, mais la mise a jour du catalogue partage a echoue.");
    }
  }

  if (key === "maladies_chroniques") {
    appendUniqueCatalogOption(diseaseOptions, normalized);
    try {
      await persistChronicDiseaseCatalogEntry(normalized);
    } catch {
      notifications.avertissement("Maladie chronique ajoutee au profil local, mais la mise a jour du catalogue partage a echoue.");
    }
  }

  customInputs[key] = "";
}

function addSelectedOption(key, value, kind) {
  const normalized = String(value || "").trim();
  if (!normalized) return;
  if (!Array.isArray(draft[key])) draft[key] = [];
  if (!draft[key].includes(normalized)) draft[key] = [...draft[key], normalized];
  if (kind === "allergy") selectedAllergyOption.value = "";
  if (kind === "disease") selectedDiseaseOption.value = "";
}

// Resume lisible des traitements pour le mode lecture.
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

function handleTreatmentTypeInput() {
  const normalizedType = normalizeTreatmentText(treatmentDraft.type);
  if (!normalizedType) {
    treatmentDraft.name = "";
    return;
  }

  treatmentDraft.type = ensureTreatmentType(normalizedType);

  const availableNames = getTreatmentNamesByType(treatmentDraft.type);
  if (treatmentDraft.name && !availableNames.includes(treatmentDraft.name)) {
    treatmentDraft.name = "";
  }
}

// Validation locale de l'email medecin avant envoi serveur.
function validerEmailMedecin() {
  if (!(draft.consulte_medecin && draft.medecin_peut_consulter)) {
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

// Gestion du mini-editeur de traitements.
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
  handleTreatmentTypeInput();
}

function cancelTreatmentEdit() {
  showTreatmentEditor.value = false;
  editingTreatmentIndex.value = -1;
  resetTreatmentDraft();
}

function cancelTreatmentEditWithNotice() {
  cancelTreatmentEdit();
  notifications.actionAnnulee();
}

async function saveTreatmentDraft() {
  const normalizedType = normalizeTreatmentText(treatmentDraft.type);
  const normalizedName = normalizeTreatmentText(treatmentDraft.name);
  if (!normalizedType || !normalizedName) return;

  treatmentDraft.type = normalizedType;
  treatmentDraft.name = normalizedName;
  const isUpdate = editingTreatmentIndex.value > -1;

  const nextTreatment = {
    type: normalizedType,
    name: normalizedName,
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

  ensureTreatmentType(nextTreatment.type);
  mergeTreatmentCatalogEntry(nextTreatment.type, nextTreatment.name);

  try {
    await persistTreatmentCatalogEntry(nextTreatment.type, nextTreatment.name);
  } catch {
    notifications.avertissement("Traitement ajoute au profil local, mais la mise a jour du catalogue partage a echoue.");
  }

  cancelTreatmentEdit();
  if (isUpdate) notifications.actionModifiee();
  else notifications.actionAjoutee();
}

function removeTreatment(index) {
  if (!Array.isArray(draft.traitements)) return;
  draft.traitements.splice(index, 1);
  notifications.actionSupprimee();
}

function requestRemoveTreatment(index) {
  pendingDeleteTreatmentIndex.value = index;
  confirmDeleteTreatmentOpen.value = true;
}

function cancelRemoveTreatment() {
  confirmDeleteTreatmentOpen.value = false;
  pendingDeleteTreatmentIndex.value = -1;
  notifications.actionAnnulee();
}

function confirmRemoveTreatment() {
  const index = pendingDeleteTreatmentIndex.value;
  confirmDeleteTreatmentOpen.value = false;
  pendingDeleteTreatmentIndex.value = -1;
  if (index < 0 || !Array.isArray(draft.traitements) || index >= draft.traitements.length) return;
  removeTreatment(index);
}

// Synchronise les donnees du profil vers le draft editable.
function syncDraftFromProfil() {
  draft.sexe = profil.sexe || "";
  draft.taille = profil.taille ?? "";
  draft.poids = profil.poids ?? "";
  draft.groupe_sanguin = profil.groupe_sanguin || "";
  draft.objectifs = normaliserListe(profil.objectifs);
  draft.allergies = normaliserListe(profil.allergies);
  draft.maladies_chroniques = normaliserListe(profil.maladies_chroniques);
  draft.allergies.forEach((item) => appendUniqueCatalogOption(allergyOptions, item));
  draft.maladies_chroniques.forEach((item) => appendUniqueCatalogOption(diseaseOptions, item));
  draft.traitements = Array.isArray(profil.traitements) ? [...profil.traitements] : [];
  draft.traitements.forEach((item) => {
    if (!item || typeof item !== "object") return;

    const type = normalizeTreatmentText(item.type || "");
    const name = normalizeTreatmentText(item.name || "");

    if (type) ensureTreatmentType(type);
    if (type && name) mergeTreatmentCatalogEntry(type, name);
  });
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
  clearSectionErrors();
  resetEditFlags();
  editing[section] = true;
}

function cancelEdit(section) {
  syncDraftFromProfil();
  clearSectionErrors(section);
  editing[section] = false;
  notifications.actionAnnulee();
}

function clearSectionErrors(section = null) {
  const clearBase = section === null || section === "base";
  const clearHealth = section === null || section === "health";

  if (clearBase) {
    sectionErrors.base.sexe = "";
    sectionErrors.base.taille = "";
    sectionErrors.base.poids = "";
    sectionErrors.base.form = [];
  }

  if (clearHealth) {
    sectionErrors.health.objectifs = "";
    sectionErrors.health.form = [];
  }
}

function validateBaseSection() {
  clearSectionErrors("base");

  if (!draft.sexe) {
    sectionErrors.base.sexe = "Veuillez selectionner le sexe.";
  }

  if (draft.taille === "" || draft.taille === null) {
    sectionErrors.base.taille = "La taille est obligatoire.";
  }

  if (draft.poids === "" || draft.poids === null) {
    sectionErrors.base.poids = "Le poids est obligatoire.";
  }

  const taille = Number(draft.taille);
  if (sectionErrors.base.taille === "" && (!Number.isFinite(taille) || taille < 80 || taille > 250)) {
    sectionErrors.base.taille = "La taille doit etre une valeur entre 80 et 250 cm.";
  }

  const poids = Number(draft.poids);
  if (sectionErrors.base.poids === "" && (!Number.isFinite(poids) || poids < 35 || poids > 250)) {
    sectionErrors.base.poids = "Le poids doit etre une valeur entre 35 et 250 kg.";
  }

  return !sectionErrors.base.sexe && !sectionErrors.base.taille && !sectionErrors.base.poids;
}

function validateHealthSection() {
  clearSectionErrors("health");
  if (!Array.isArray(draft.objectifs) || draft.objectifs.length === 0) {
    sectionErrors.health.objectifs = "Veuillez selectionner au moins un objectif.";
  }
  return !sectionErrors.health.objectifs;
}

// Construit le payload API final avec normalisation des champs.
function construireChargeUtile() {
  const objectifs = normaliserListe(draft.objectifs);
  const allergies = normaliserListe(draft.allergies);
  const maladiesChroniques = normaliserListe(draft.maladies_chroniques);
  const traitements = Array.isArray(draft.traitements) ? draft.traitements : [];
  const consulteMedecin = Boolean(draft.consulte_medecin);
  const medecinPeutConsulter = Boolean(consulteMedecin && draft.medecin_peut_consulter);

  return {
    sexe: draft.sexe || null,
    taille: draft.taille === "" ? null : Number(draft.taille),
    poids: draft.poids === "" ? null : Number(draft.poids),
    groupe_sanguin: draft.groupe_sanguin || null,
    objectifs,
    allergies,
    maladies_chroniques: maladiesChroniques,
    traitements,
    fumeur: Boolean(draft.fumeur),
    alcool: Boolean(draft.alcool),
    consulte_medecin: consulteMedecin,
    medecin_peut_consulter: medecinPeutConsulter,
    medecin_email: medecinPeutConsulter ? draft.medecin_email || null : null,
  };
}

// Sauvegarde d'une section avec gestion des cas d'erreur API.
async function enregistrerSection(section) {
  if (section === "base" && !validateBaseSection()) {
    notifications.avertissement("Veuillez corriger les champs en erreur.");
    return;
  }

  if (section === "health" && !validateHealthSection()) {
    notifications.avertissement("Veuillez corriger les champs en erreur.");
    return;
  }

  if (section === "doctor" && !validerEmailMedecin()) {
    return;
  }

  savingSection.value = section;
  clearSectionErrors(section);
  try {
    const response = await api.post("/profil-sante", construireChargeUtile());
    Object.assign(profil, response?.data?.data || {});
    Object.assign(user, response?.data?.user || user);
    authStore.definirPresenceProfilSante(true);
    syncDraftFromProfil();
    editing[section] = false;
    notifications.actionModifiee();
  } catch (e) {
    if (e?.response?.status === 401) {
      authStore.supprimerToken();
      router.replace({ name: "inscription" });
      return;
    }
    if (e?.response?.status === 422 && e?.response?.data?.errors) {
      if (section === "doctor" && e.response.data.errors?.medecin_email) {
        const doctorFieldError = e.response.data.errors.medecin_email;
        doctorEmailError.value = Array.isArray(doctorFieldError)
          ? doctorFieldError[0]
          : "Email medecin invalide.";
        notifications.avertissement(doctorEmailError.value);
        return;
      }

      clearSectionErrors();
      const backendErrors = e.response.data.errors || {};
      const mappedMessages = [];

      if (backendErrors.sexe) {
        sectionErrors.base.sexe = "Veuillez selectionner le sexe.";
        mappedMessages.push(sectionErrors.base.sexe);
      }
      if (backendErrors.taille) {
        sectionErrors.base.taille = "La taille doit etre une valeur entre 80 et 250 cm.";
        mappedMessages.push(sectionErrors.base.taille);
      }
      if (backendErrors.poids) {
        sectionErrors.base.poids = "Le poids doit etre une valeur entre 35 et 250 kg.";
        mappedMessages.push(sectionErrors.base.poids);
      }
      if (backendErrors.objectifs) {
        sectionErrors.health.objectifs = "Veuillez selectionner au moins un objectif.";
        mappedMessages.push(sectionErrors.health.objectifs);
      }

      const fallbackMessages = Object.values(backendErrors)
        .flatMap((entry) => (Array.isArray(entry) ? entry : [entry]))
        .filter(Boolean)
        .map((entry) => String(entry));
      const finalMessages = mappedMessages.length ? [...new Set(mappedMessages)] : fallbackMessages;

      if (sectionErrors[section]) {
        sectionErrors[section].form = finalMessages.length ? finalMessages : ["Validation invalide."];
      }

      const hasBaseErrors = Boolean(sectionErrors.base.sexe || sectionErrors.base.taille || sectionErrors.base.poids);
      const hasHealthErrors = Boolean(sectionErrors.health.objectifs);

      if (hasBaseErrors && !editing.base) {
        resetEditFlags();
        editing.base = true;
      } else if (hasHealthErrors && !editing.health) {
        resetEditFlags();
        editing.health = true;
      }

      notifications.avertissement("Veuillez corriger les champs en erreur.");
    } else {
      notifications.erreur("Erreur lors de la sauvegarde du profil.");
    }
  } finally {
    savingSection.value = "";
  }
}

// Chargement initial du profil.
onMounted(async () => {
  try {
    await Promise.all([
      loadTreatmentCatalog(),
      loadAllergyCatalog(),
      loadChronicDiseaseCatalog(),
    ]);

    const response = await api.get("/profil-sante");
    Object.assign(profil, response?.data?.data || {});
    Object.assign(user, response?.data?.user || {});
    authStore.definirPresenceProfilSante(Boolean(response?.data?.data));
    syncDraftFromProfil();
  } catch (e) {
    if (e?.response?.status === 401) {
      authStore.supprimerToken();
      router.replace({ name: "inscription" });
      return;
    }
    loadError.value = "Impossible de charger les données du profil.";
  } finally {
    loading.value = false;
  }
});
</script>
