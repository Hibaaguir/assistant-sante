<template>
  <!-- bloc interne (le parent gère déjà le fond + centrage) -->
  <div>
    <h2 class="text-white font-extrabold text-xl sm:text-2xl mb-6">
      Informations de base
    </h2>

    <!-- Card "Informations de base" -->
    <div class="rounded-3xl bg-white/10 border border-white/15 p-6 sm:p-8">
      <!-- Age (calculé automatiquement) -->
      <div class="mb-4">
        <div class="text-white/90 font-semibold mb-2">Âge</div>
        <div class="inline-block rounded-2xl bg-white/10 border border-white/15 px-4 py-3 text-white/90">
          {{ form.age ? form.age + ' ans' : 'Non renseigné' }}
        </div>
      </div>
      <!-- Sexe -->
      <div class="mb-6">
        <div class="text-white/90 font-semibold mb-3">Sexe</div>

        <div class="grid grid-cols-2 gap-4">
          <!-- Homme -->
          <button
            type="button"
            @click="form.sexe = 'homme'"
            :class="[
              baseSexBtn,
              form.sexe === 'homme' ? activeSexBtn : inactiveSexBtn
            ]"
          >
            <span class="text-lg">👨</span>
            <span class="font-semibold">Homme</span>
          </button>

          <!-- Femme -->
          <button
            type="button"
            @click="form.sexe = 'femme'"
            :class="[
              baseSexBtn,
              form.sexe === 'femme' ? activeSexBtn : inactiveSexBtn
            ]"
          >
            <span class="text-lg">👩</span>
            <span class="font-semibold">Femme</span>
          </button>
        </div>
      </div>

      <!-- Taille / Poids -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
        <!-- Taille -->
        <div>
          <label class="block text-white/80 text-sm font-medium mb-2">Taille</label>
          <div class="relative">
            <input
              v-model="form.taille"
              type="number"
              min="100"
              max="250"
              placeholder="170"
              class="w-full rounded-2xl bg-white/10 border border-white/15 text-white placeholder:text-white/35
                     px-4 py-4 pr-14 outline-none focus:ring-2 focus:ring-cyan-300/60 focus:border-transparent"
            />
            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-white/60 text-sm">
              cm
            </span>
          </div>
        </div>

        <!-- Poids -->
        <div>
          <label class="block text-white/80 text-sm font-medium mb-2">Poids</label>
          <div class="relative">
            <input
              v-model="form.poids"
              type="number"
              min="30"
              max="300"
              step="0.1"
              placeholder="70"
              class="w-full rounded-2xl bg-white/10 border border-white/15 text-white placeholder:text-white/35
                     px-4 py-4 pr-14 outline-none focus:ring-2 focus:ring-cyan-300/60 focus:border-transparent"
            />
            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-white/60 text-sm">
              kg
            </span>
          </div>
        </div>
      </div>

      <!-- Groupe sanguin -->
      <div class="mb-8">
        <label class="block text-white/80 text-sm font-medium mb-2">Groupe Sanguin</label>
        <div class="relative">
          <select
            v-model="form.groupe_sanguin"
            class="w-full appearance-none rounded-2xl bg-white/10 border border-white/15 text-white
                   px-4 py-4 pr-12 outline-none focus:ring-2 focus:ring-cyan-300/60 focus:border-transparent"
          >
            <option value="" class="text-gray-900">Sélectionner</option>
            <option class="text-gray-900">A+</option>
            <option class="text-gray-900">A-</option>
            <option class="text-gray-900">B+</option>
            <option class="text-gray-900">B-</option>
            <option class="text-gray-900">O+</option>
            <option class="text-gray-900">O-</option>
            <option class="text-gray-900">AB+</option>
            <option class="text-gray-900">AB-</option>
          </select>

          <!-- chevron -->
          <svg
            class="pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 w-5 h-5 text-white/70"
            viewBox="0 0 20 20"
            fill="currentColor"
          >
            <path
              fill-rule="evenodd"
              d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z"
              clip-rule="evenodd"
            />
          </svg>
        </div>
      </div>

      <!-- Objectifs (ajoutés: plusieurs choix possibles) -->
      <div class="mb-8">
        <label class="block text-white/80 text-sm font-medium mb-2">Objectifs</label>
        <p class="text-white/60 text-xs mb-3">Choisissez vos objectifs principaux (plusieurs choix possibles).</p>

        <div class="flex flex-wrap gap-2">
          <button
            v-for="item in OBJECTIFS"
            :key="item"
            type="button"
            @click="toggleMulti('objectifs', item)"
            :class="[chipBase, isSelected('objectifs', item) ? chipActive : chipInactive]"
          >
            {{ item }}
          </button>
        </div>
      </div>

      <!-- Actions -->
      <div class="flex items-center justify-end">
        <button
          type="button"
          @click="$emit('suivant')"
          class="group inline-flex items-center gap-3 rounded-full px-7 py-4 font-semibold text-white
                 bg-gradient-to-r from-emerald-300 to-cyan-300
                 shadow-[0_12px_30px_rgba(0,255,200,0.25)]
                 hover:shadow-[0_16px_40px_rgba(0,255,200,0.35)]
                 transition-all"
        >
          Continuer
          <svg
            class="w-5 h-5 translate-x-0 group-hover:translate-x-0.5 transition-transform"
            viewBox="0 0 24 24"
            fill="none"
          >
            <path
              d="M9 18l6-6-6-6"
              stroke="currentColor"
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round"
            />
          </svg>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
const { form } = defineProps({
  form: { type: Object, required: true },
});

const baseSexBtn =
  "w-full rounded-2xl px-5 py-4 border transition-all flex items-center justify-center gap-3";

const inactiveSexBtn =
  "bg-white/10 border-white/15 text-white/90 hover:bg-white/15";

const activeSexBtn =
  "bg-[#1B78D6] border-cyan-200/40 text-white shadow-[0_10px_30px_rgba(0,0,0,0.25)]";

// chips & objectifs
const chipBase = "px-3 py-2 rounded-md text-[11px] font-semibold border transition select-none";
const chipInactive = "bg-white/10 border-white/15 text-white/80 hover:bg-white/15";
const chipActive = "bg-[#1B78D6] border-cyan-200/40 text-white shadow-[0_10px_30px_rgba(0,0,0,0.25)]";

const OBJECTIFS = [
  "Maintenir mon poids",
  "Perdre du poids",
  "Améliorer mon alimentation",
  "Mieux dormir",
  "Réduire mon stress",
  "Avoir plus d’énergie",
  "Suivre ma santé régulièrement",
  "Suivre une maladie chronique",
  "Recevoir des conseils personnalisés",
];

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
