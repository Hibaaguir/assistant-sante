<template>
  <div class="space-y-10">
    <div class="text-center max-w-2xl mx-auto">
      <h2 class="text-3xl font-semibold text-gray-900 mb-3">Informations de base</h2>
      <p class="text-gray-500">Commencons par quelques informations essentielles pour personnaliser ton experience</p>
    </div>

    <div class="space-y-8">
      <div class="space-y-4">
        <p class="text-base font-medium text-gray-900">Sexe</p>

        <div class="grid grid-cols-2 gap-4 max-w-xl">
          <label
            class="relative cursor-pointer rounded-2xl border-2 p-6 transition-all hover:shadow-md"
            :class="form.sexe === 'homme' ? 'border-teal-500 bg-teal-50/50 shadow-sm' : 'border-gray-200 bg-white hover:border-gray-300'"
          >
            <input class="sr-only" type="radio" name="sex" :checked="form.sexe === 'homme'" @change="form.sexe = 'homme'" />
            <div class="flex flex-col items-center gap-3">
              <svg class="h-8 w-8" :class="form.sexe === 'homme' ? 'text-teal-600' : 'text-gray-400'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <circle cx="10" cy="14" r="5" stroke-width="2" />
                <path d="M14 10l6-6m0 0h-4m4 0v4" stroke-width="2" />
              </svg>
              <span class="font-medium" :class="form.sexe === 'homme' ? 'text-teal-900' : 'text-gray-700'">Homme</span>
            </div>
          </label>

          <label
            class="relative cursor-pointer rounded-2xl border-2 p-6 transition-all hover:shadow-md"
            :class="form.sexe === 'femme' ? 'border-teal-500 bg-teal-50/50 shadow-sm' : 'border-gray-200 bg-white hover:border-gray-300'"
          >
            <input class="sr-only" type="radio" name="sex" :checked="form.sexe === 'femme'" @change="form.sexe = 'femme'" />
            <div class="flex flex-col items-center gap-3">
              <svg class="h-8 w-8" :class="form.sexe === 'femme' ? 'text-teal-600' : 'text-gray-400'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <circle cx="12" cy="8" r="5" stroke-width="2" />
                <path d="M12 13v7m-3-3h6" stroke-width="2" />
              </svg>
              <span class="font-medium" :class="form.sexe === 'femme' ? 'text-teal-900' : 'text-gray-700'">Femme</span>
            </div>
          </label>
        </div>
      </div>

      <div class="space-y-4 max-w-md">
        <p class="text-base font-medium text-gray-900">Age</p>
        <div class="h-14 px-5 flex items-center bg-gray-50 rounded-xl border-2 border-gray-200 text-lg text-gray-900">
          {{ computedAge !== '' ? `${computedAge} ans` : 'Age indisponible' }}
        </div>
        <p class="text-xs text-gray-500 px-1">L'age est calcule automatiquement depuis la date de naissance</p>
      </div>

      <div class="space-y-4">
        <p class="text-base font-medium text-gray-900">
          Taille et poids <span class="text-gray-400 font-normal">(obligatoire)</span>
        </p>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 max-w-2xl">
          <div class="space-y-2">
            <input
              v-model="form.taille"
              type="number"
              placeholder="Taille en cm"
              class="h-14 w-full text-lg rounded-xl border-2 border-gray-200 px-4 outline-none focus:border-teal-500"
            />
            <p class="text-xs text-gray-500 px-1">Ex: 175</p>
          </div>
          <div class="space-y-2">
            <input
              v-model="form.poids"
              type="number"
              placeholder="Poids en kg"
              class="h-14 w-full text-lg rounded-xl border-2 border-gray-200 px-4 outline-none focus:border-teal-500"
            />
            <p class="text-xs text-gray-500 px-1">Ex: 70</p>
          </div>
        </div>
      </div>

      <div class="space-y-4">
        <p class="text-base font-medium text-gray-900">Habitudes de vie</p>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div class="rounded-xl border-2 border-gray-200 bg-white px-4 py-4 flex items-center justify-between">
            <span class="text-sm font-medium text-gray-800">Fumeur</span>
            <label class="relative inline-flex cursor-pointer items-center">
              <input v-model="form.fumeur" type="checkbox" class="peer sr-only" />
              <div class="h-8 w-14 rounded-full bg-gray-300 transition-colors peer-checked:bg-teal-500" />
              <div class="absolute left-1 h-6 w-6 rounded-full bg-white shadow transition-transform peer-checked:translate-x-6" />
            </label>
          </div>

          <div class="rounded-xl border-2 border-gray-200 bg-white px-4 py-4 flex items-center justify-between">
            <span class="text-sm font-medium text-gray-800">Consommation d'alcool</span>
            <label class="relative inline-flex cursor-pointer items-center">
              <input v-model="form.alcool" type="checkbox" class="peer sr-only" />
              <div class="h-8 w-14 rounded-full bg-gray-300 transition-colors peer-checked:bg-teal-500" />
              <div class="absolute left-1 h-6 w-6 rounded-full bg-white shadow transition-transform peer-checked:translate-x-6" />
            </label>
          </div>
        </div>
      </div>

      <div class="space-y-4">
        <p class="text-base font-medium text-gray-900">Objectifs</p>
        <p class="text-xs text-gray-500">Vous pouvez sélectionner plusieurs objectifs.</p>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
          <div
            v-for="goal in goals"
            :key="goal.value"
            class="cursor-pointer p-6 border-2 transition-all hover:shadow-md rounded-xl"
            :class="isGoalSelected(goal.value) ? 'border-teal-500 bg-teal-50/50 shadow-sm' : 'border-gray-200 bg-white hover:border-gray-300'"
            @click="toggleGoal(goal.value)"
          >
            <div class="flex items-center gap-4">
              <div class="h-12 w-12 rounded-2xl flex items-center justify-center border" :class="goal.color">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path :d="goal.icon" stroke-width="2" />
                </svg>
              </div>
              <div class="flex-1">
                <p class="font-medium text-gray-900">{{ goal.label }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  form: { type: Object, required: true },
  computedAge: { type: [Number, String], default: "" },
});

const form = props.form;

const goals = [
  { value: "Maintenir mon poids", label: "Bien-etre general", color: "bg-purple-50 text-purple-600 border-purple-200", icon: "M5 13a7 7 0 0 0 14 0M8 8h.01M16 8h.01" },
  { value: "Perdre du poids", label: "Suivi et evolution du poids", color: "bg-blue-50 text-blue-600 border-blue-200", icon: "M4 7h16M7 12h10M10 17h4" },
  { value: "Avoir plus d'energie", label: "Augmenter l'energie", color: "bg-orange-50 text-orange-600 border-orange-200", icon: "M13 2L3 14h7l-1 8 10-12h-7z" },
  { value: "Mieux dormir", label: "Ameliorer le sommeil", color: "bg-indigo-50 text-indigo-600 border-indigo-200", icon: "M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8z" },
  { value: "Reduire mon stress", label: "Reduire le stress", color: "bg-teal-50 text-teal-600 border-teal-200", icon: "M12 2a7 7 0 0 0-7 7c0 2.4 1.4 4.4 3.5 5.6V17a1 1 0 0 0 1 1h5a1 1 0 0 0 1-1v-2.4A6.5 6.5 0 0 0 19 9a7 7 0 0 0-7-7z" },
  { value: "Suivre ma sante regulierement", label: "Suivi de l'etat de sante", color: "bg-red-50 text-red-600 border-red-200", icon: "M9 3h6v4H9zM5 7h14v14H5zM9 12h6M9 16h4" },
];

if (!Array.isArray(form.objectifs)) form.objectifs = [];
if (!form.objectif && form.objectifs.length) form.objectif = form.objectifs[0];

function isGoalSelected(goal) {
  return Array.isArray(form.objectifs) && form.objectifs.includes(goal);
}

function toggleGoal(goal) {
  if (!Array.isArray(form.objectifs)) form.objectifs = [];

  if (form.objectifs.includes(goal)) {
    form.objectifs = form.objectifs.filter((item) => item !== goal);
  } else {
    form.objectifs = [...form.objectifs, goal];
  }

  form.objectif = form.objectifs[0] || "";
}
</script>
