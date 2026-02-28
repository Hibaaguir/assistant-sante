<template>
  <div v-if="open" class="fixed inset-0 z-50 bg-black/35 p-4" @click.self="$emit('close')">
    <div class="mx-auto mt-6 w-full max-w-[540px] rounded-2xl bg-white p-4 shadow-[0_25px_50px_rgba(15,23,42,0.25)] sm:mt-10 sm:p-5">
      <div class="mb-4 flex items-center justify-between">
        <h3 class="text-3xl font-bold leading-none text-slate-900">Filtrer l'historique</h3>
        <button class="text-slate-500" type="button" @click="$emit('close')">×</button>
      </div>

      <div class="grid gap-2 sm:grid-cols-2">
        <button
          v-for="option in options"
          :key="option.id"
          type="button"
          class="flex items-center gap-2 rounded-xl border px-3 py-3 text-left text-sm font-semibold"
          :class="model.type === option.id ? 'border-blue-500 bg-blue-50 text-slate-900' : 'border-slate-300 bg-white text-slate-700'"
          @click="model.type = option.id"
        >
          <span class="text-xs">{{ model.type === option.id ? '?' : '?' }}</span>
          <span>{{ option.emoji }}</span>
          <span>{{ option.label }}</span>
        </button>
      </div>

      <div v-if="model.type === 'month'" class="mt-3 rounded-xl border border-blue-200 bg-blue-50 p-3">
        <p class="mb-2 text-sm font-semibold text-slate-700">Sélectionnez un mois</p>
        <input v-model="model.month" type="month" class="w-full rounded-lg border border-blue-300 bg-white px-3 py-2 text-sm" />
      </div>

      <div v-if="model.type === 'date'" class="mt-3 rounded-xl border border-blue-200 bg-blue-50 p-3">
        <p class="mb-2 text-sm font-semibold text-slate-700">Sélectionnez une date</p>
        <input v-model="model.date" type="date" class="w-full rounded-lg border border-blue-300 bg-white px-3 py-2 text-sm" />
      </div>

      <div class="mt-4 flex gap-2">
        <button type="button" class="flex-1 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 py-2 text-sm font-semibold text-white" @click="$emit('apply', model)">Appliquer</button>
        <button type="button" class="rounded-xl bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-500" @click="$emit('reset')">Réinitialiser</button>
      </div>
    </div>
  </div>
</template>

<script setup>
/*
  Modal de filtre pour l'historique du journal.
  Le modele local permet de preparer le filtre avant confirmation.
  Les actions sont renvoyees au parent via emits.
*/

import { reactive, watch } from 'vue'

const props = defineProps({
  open: {
    type: Boolean,
    default: false
  },
  filter: {
    type: Object,
    required: true
  }
})

const model = reactive({
  type: 'all',
  month: '',
  date: ''
})

watch(
  () => props.filter,
  (next) => {
    model.type = next.type
    model.month = next.month
    model.date = next.date
  },
  { immediate: true, deep: true }
)

const options = [
  { id: 'all', label: 'Toutes les données', emoji: '???' },
  { id: 'date', label: 'Par date', emoji: '??' },
  { id: 'month', label: 'Par mois', emoji: '???' },
  { id: 'nutrition', label: 'Nutrition', emoji: '??' },
  { id: 'hydration', label: 'Hydratation', emoji: '??' },
  { id: 'activity', label: 'Activités', emoji: '??' },
  { id: 'sleep', label: 'Sommeil', emoji: '??' },
  { id: 'stress', label: 'Stress', emoji: '??' },
  { id: 'energy', label: 'Énergie', emoji: '?' }
]

defineEmits(['close', 'apply', 'reset'])
</script>



