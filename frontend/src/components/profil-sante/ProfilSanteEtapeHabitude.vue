<template>
  <div class="space-y-8">
    <div class="text-center max-w-2xl mx-auto">
      <h2 class="text-3xl font-semibold text-gray-900 mb-2">Habitudes de vie</h2>
      <p class="text-sm text-gray-500">Quelques informations de mode de vie pour mieux personnaliser ton profil</p>
    </div>

    <div class="max-w-2xl mx-auto grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div class="rounded-2xl border-2 border-slate-200 bg-white px-4 py-4 flex items-center justify-between shadow-[0_4px_10px_rgba(15,23,42,0.04)]">
        <span class="text-sm font-semibold text-slate-800">🚬 Fumeur</span>
        <label class="relative inline-flex cursor-pointer items-center">
          <input v-model="form.fumeur" type="checkbox" class="peer sr-only" />
          <div class="h-8 w-14 rounded-full bg-gray-300 transition-colors peer-checked:bg-emerald-500" />
          <div class="absolute left-1 h-6 w-6 rounded-full bg-white shadow transition-transform peer-checked:translate-x-6" />
        </label>
      </div>

      <div class="rounded-2xl border-2 border-slate-200 bg-white px-4 py-4 flex items-center justify-between shadow-[0_4px_10px_rgba(15,23,42,0.04)]">
        <span class="text-sm font-semibold text-slate-800">🍷 Alcool</span>
        <label class="relative inline-flex cursor-pointer items-center">
          <input v-model="form.alcool" type="checkbox" class="peer sr-only" />
          <div class="h-8 w-14 rounded-full bg-gray-300 transition-colors peer-checked:bg-emerald-500" />
          <div class="absolute left-1 h-6 w-6 rounded-full bg-white shadow transition-transform peer-checked:translate-x-6" />
        </label>
      </div>
    </div>

    <section class="max-w-2xl mx-auto rounded-[26px] border border-[#cde5ff] bg-[linear-gradient(180deg,#eef6ff_0%,#ffffff_70%)] p-4 sm:p-5 shadow-[0_8px_20px_rgba(37,99,235,0.10)] space-y-4">
      <p class="text-base font-bold text-slate-900 text-center">🏃‍♀️ Fais-tu une activité sportive ?</p>

      <div class="grid grid-cols-2 gap-3">
        <button
          type="button"
          class="h-12 rounded-2xl text-base font-bold transition shadow-sm"
          :class="form.activite_physique ? 'bg-gradient-to-r from-emerald-500 to-teal-500 text-white shadow-[0_8px_16px_rgba(16,185,129,0.25)]' : 'bg-slate-200 text-slate-700'"
          @click="form.activite_physique = true"
        >
          ✅ Oui
        </button>
        <button
          type="button"
          class="h-12 rounded-2xl text-base font-bold transition shadow-sm"
          :class="!form.activite_physique ? 'bg-slate-700 text-white shadow-[0_8px_16px_rgba(15,23,42,0.20)]' : 'bg-slate-200 text-slate-700'"
          @click="form.activite_physique = false"
        >
          ❌ Non
        </button>
      </div>

      <div v-if="form.activite_physique" class="space-y-4">
        <div class="rounded-2xl border border-[#cde7ff] bg-white p-3.5 space-y-3 shadow-[0_4px_12px_rgba(37,99,235,0.08)]">
          <p class="text-sm font-bold text-slate-900 text-center">💪 Quelle activité pratiques-tu ?</p>
          <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
            <button
              v-for="activity in activityOptions"
              :key="activity.value"
              type="button"
              class="rounded-xl border px-2 py-2.5 text-xs sm:text-sm font-semibold transition"
              :class="isSelectedActivity(activity.value)
                ? 'border-emerald-400 bg-emerald-50 text-emerald-700 shadow-[0_4px_10px_rgba(16,185,129,0.15)]'
                : 'border-slate-200 bg-white text-slate-700 hover:border-blue-300'"
              @click="toggleActivity(activity.value)"
            >
              <span class="mr-1">{{ activity.emoji }}</span>{{ activity.label }}
            </button>
          </div>

          <div class="flex gap-2">
            <input
              v-model="customActivity"
              type="text"
              class="h-11 flex-1 rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm"
              placeholder="✨ Autre activité..."
              @keydown.enter.prevent="addCustomActivity"
            />
            <button type="button" class="h-11 rounded-xl bg-emerald-600 px-3.5 text-sm font-semibold text-white" @click="addCustomActivity">
              ➕ Ajouter
            </button>
          </div>
        </div>

        <div v-if="form.activites_physiques.length > 0" class="rounded-2xl border border-[#d8f5df] bg-[linear-gradient(180deg,#f4fff7_0%,#ffffff_100%)] p-3.5 space-y-2.5 shadow-[0_4px_12px_rgba(16,185,129,0.10)]">
          <p class="text-sm font-bold text-slate-900 text-center">📆 Combien de fois par semaine ?</p>
          <div class="grid grid-cols-3 gap-2">
            <button
              v-for="item in frequencyOptions"
              :key="item.value"
              type="button"
              class="h-10 rounded-xl text-xs sm:text-sm font-bold transition"
              :class="form.frequence_activite_physique === item.value ? 'bg-gradient-to-r from-emerald-500 to-teal-500 text-white shadow-[0_6px_14px_rgba(16,185,129,0.25)]' : 'bg-white border border-slate-200 text-slate-700 hover:border-emerald-300'"
              @click="form.frequence_activite_physique = item.value"
            >
              {{ item.label }}
            </button>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, watch } from "vue";

const props = defineProps({
  form: { type: Object, required: true },
});

const form = props.form;
if (typeof form.fumeur !== "boolean") form.fumeur = false;
if (typeof form.alcool !== "boolean") form.alcool = false;
if (typeof form.activite_physique !== "boolean") form.activite_physique = false;
if (!Array.isArray(form.activites_physiques)) form.activites_physiques = [];
if (typeof form.frequence_activite_physique !== "string") form.frequence_activite_physique = "";

const customActivity = ref("");
const activityOptions = [
  { value: "Course à pied", label: "Course", emoji: "🏃" },
  { value: "Vélo", label: "Vélo", emoji: "🚴" },
  { value: "Natation", label: "Natation", emoji: "🏊" },
  { value: "Salle de sport", label: "Gym", emoji: "🏋️" },
  { value: "Yoga", label: "Yoga", emoji: "🧘" },
  { value: "Football", label: "Foot", emoji: "⚽" },
  { value: "Basketball", label: "Basket", emoji: "🏀" },
  { value: "Arts martiaux", label: "Arts martiaux", emoji: "🥋" },
];
const frequencyOptions = [
  { value: "1-2 fois", label: "🔥 1-2 fois" },
  { value: "3-4 fois", label: "💪 3-4 fois" },
  { value: "5+ fois", label: "🏆 5+ fois" },
];

function isSelectedActivity(activity) {
  return form.activites_physiques.includes(activity);
}

function toggleActivity(activity) {
  if (isSelectedActivity(activity)) {
    form.activites_physiques = form.activites_physiques.filter((item) => item !== activity);
    return;
  }
  form.activites_physiques = [...form.activites_physiques, activity];
}

function addCustomActivity() {
  const value = String(customActivity.value || "").trim();
  if (!value) return;
  if (!form.activites_physiques.includes(value)) {
    form.activites_physiques = [...form.activites_physiques, value];
  }
  customActivity.value = "";
}

watch(
  () => form.activite_physique,
  (value) => {
    if (!value) {
      form.activites_physiques = [];
      form.frequence_activite_physique = "";
    }
  }
);

watch(
  () => form.activites_physiques.length,
  (length) => {
    if (length === 0) {
      form.frequence_activite_physique = "";
    }
  }
);
</script>
