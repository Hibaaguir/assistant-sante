<template>
  <div class="space-y-6">
    <!-- Allergies -->
    <section>
      <h3 class="text-white/90 font-semibold mb-3">Allergies</h3>
      <div class="flex flex-wrap gap-2">
        <button
          v-for="item in ALLERGIES"
          :key="item"
          type="button"
          @click="toggleMulti('allergies', item)"
          :class="[chipBase, isSelected('allergies', item) ? chipActive : chipInactive]"
        >
          {{ item }}
        </button>
      </div>
    </section>

    <!-- Maladies chroniques -->
    <section>
      <h3 class="text-white/90 font-semibold mb-3">Maladies Chroniques</h3>
      <div class="flex flex-wrap gap-2">
        <button
          v-for="item in MALADIES"
          :key="item"
          type="button"
          @click="toggleMulti('maladies_chroniques', item)"
          :class="[chipBase, isSelected('maladies_chroniques', item) ? chipActive : chipInactive]"
        >
          {{ item }}
        </button>
      </div>
    </section>

    <!-- Traitements -->
    <section>
      <h3 class="text-white/90 font-semibold mb-3">Traitements</h3>
      <div class="flex flex-wrap gap-2">
        <button
          v-for="item in TRAITEMENTS"
          :key="item"
          type="button"
          @click="toggleMulti('traitements', item)"
          :class="[chipBase, isSelected('traitements', item) ? chipActive : chipInactive]"
        >
          {{ item }}
        </button>
      </div>
    </section>

    <!-- Bloc médicaments (comme la capture) -->
    <section class="rounded-2xl bg-white/10 border border-white/15 p-5">
      <div class="flex items-center justify-between mb-3">
        <div>
          <div class="text-white/90 font-semibold">Prenez-vous des médicaments ?</div>
          <div class="text-white/60 text-xs mt-1">Détails des médicaments</div>
        </div>

        <button
          type="button"
          @click="form.prend_medicament = !form.prend_medicament"
          class="relative inline-flex h-5 w-10 items-center rounded-full transition-colors"
          :class="form.prend_medicament ? 'bg-cyan-300/90' : 'bg-white/25'"
          aria-label="Toggle médicaments"
        >
          <span
            class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"
            :class="form.prend_medicament ? 'translate-x-5' : 'translate-x-1'"
          />
        </button>
      </div>

      <div class="rounded-2xl bg-white/10 border border-white/15 p-3">
        <textarea
          v-model="form.nom_medicament"
          :disabled="!form.prend_medicament"
          rows="3"
          class="w-full resize-none rounded-xl bg-transparent text-white placeholder:text-white/35
                 outline-none disabled:opacity-40 disabled:cursor-not-allowed"
          placeholder="Listez vos médicaments et leurs posologies..."
        />
      </div>
    </section>

    <!-- Fumeur / Alcool -->
    <section class="grid grid-cols-1 sm:grid-cols-2 gap-3">
      <div class="rounded-2xl bg-white/10 border border-white/15 p-4 flex items-center justify-between">
        <div class="flex items-center gap-2 text-white/90 font-semibold text-sm">
          <span class="opacity-80">←</span>
          <span>Fumeur</span>
        </div>
        <button
          type="button"
          @click="form.fumeur = !form.fumeur"
          class="relative inline-flex h-5 w-10 items-center rounded-full transition-colors"
          :class="form.fumeur ? 'bg-cyan-300/90' : 'bg-white/25'"
          aria-label="Toggle fumeur"
        >
          <span
            class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"
            :class="form.fumeur ? 'translate-x-5' : 'translate-x-1'"
          />
        </button>
      </div>

      <div class="rounded-2xl bg-white/10 border border-white/15 p-4 flex items-center justify-between">
        <div class="flex items-center gap-2 text-white/90 font-semibold text-sm">
          <span class="text-pink-200">🍷</span>
          <span>Alcool</span>
        </div>
        <button
          type="button"
          @click="form.alcool = !form.alcool"
          class="relative inline-flex h-5 w-10 items-center rounded-full transition-colors"
          :class="form.alcool ? 'bg-cyan-300/90' : 'bg-white/25'"
          aria-label="Toggle alcool"
        >
          <span
            class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"
            :class="form.alcool ? 'translate-x-5' : 'translate-x-1'"
          />
        </button>
      </div>
    </section>

    <!-- Objectifs -->
    <section>
      <h3 class="text-white/90 font-semibold mb-2 flex items-center gap-2">
        <span class="text-pink-200">📌</span>
        Vos Objectifs de Santé
      </h3>
      <p class="text-white/60 text-xs mb-3">
        Décrivez vos objectifs de santé (perte de poids, amélioration de la condition physique,
        gestion du stress, etc.).
      </p>

      <div class="rounded-2xl bg-white/10 border border-white/15 p-3">
        <textarea
          v-model="form.objectif"
          rows="4"
          class="w-full resize-none rounded-xl bg-transparent text-white placeholder:text-white/35 outline-none"
          placeholder="Ex: Perdre 5kg, marcher 30min/jour, réduire le stress..."
        />
      </div>
    </section>

    <!-- Boutons bas -->
    <div class="flex items-center justify-between pt-2">
      <button
        type="button"
        @click="$emit('retour')"
        class="inline-flex items-center gap-2 rounded-full px-6 py-3 text-white/90
               bg-white/10 border border-white/15 hover:bg-white/15 transition"
      >
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none">
          <path
            d="M15 18l-6-6 6-6"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
          />
        </svg>
        Retour
      </button>

      <button
        type="button"
        @click="$emit('enregistrer')"
        class="inline-flex items-center gap-2 rounded-full px-7 py-3 font-semibold text-white
               bg-gradient-to-r from-emerald-300 to-cyan-300
               shadow-[0_12px_30px_rgba(0,255,200,0.25)]
               hover:shadow-[0_16px_40px_rgba(0,255,200,0.35)]
               transition-all"
      >
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none">
          <path
            d="M5 13l4 4L19 7"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
          />
        </svg>
        Terminé
      </button>
    </div>
  </div>
</template>

<script setup>
const { form } = defineProps({
  form: { type: Object, required: true },
});

const ALLERGIES = [
  "Pollen",
  "Arachides",
  "Fruits de mer",
  "Lactose",
  "Gluten",
  "Pénicilline",
  "Aspirine",
  "Abeilles/Guêpes",
  "Poussière",
  "Animaux",
];

const MALADIES = [
  "Diabète",
  "Hypertension",
  "Asthme",
  "Arthrite",
  "Maladie cardiaque",
  "Cholestérol élevé",
  "Migraine chronique",
  "Thyroïde",
  "Anémie",
  "Aucune",
];

const TRAITEMENTS = [
  "Insuline",
  "Antihypertenseur",
  "Inhalateur",
  "Anti-inflammatoire",
  "Anticoagulant",
  "Statines",
  "Antidépresseur",
  "Anxiolytique",
  "Supplément vitaminique",
  "Aucun",
];

const chipBase = "px-3 py-2 rounded-md text-[11px] font-semibold border transition select-none";
const chipInactive = "bg-white/10 border-white/15 text-white/80 hover:bg-white/15";
const chipActive = "bg-[#1B78D6] border-cyan-200/40 text-white shadow-[0_10px_30px_rgba(0,0,0,0.25)]";

function isSelected(key, value) {
  return Array.isArray(form[key]) ? form[key].includes(value) : false;
}

function toggleMulti(key, value) {
  if (!Array.isArray(form[key])) form[key] = [];

  if (value === "Aucune" || value === "Aucun") {
    form[key] = [value];
    return;
  }

  form[key] = form[key].filter((v) => v !== "Aucune" && v !== "Aucun");

  const idx = form[key].indexOf(value);
  if (idx === -1) form[key].push(value);
  else form[key].splice(idx, 1);
}
</script>
