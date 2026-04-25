<!-- Composant de sélection de date ou de mois pour filtrer les entrées du journal en fonction de la date -->
<template>
  <div class="mt-3 rounded-xl border border-violet-200 bg-violet-50 p-3">
    <p class="mb-2 text-sm font-semibold text-slate-700">{{ label }}</p>
    <input
      :type="type"
      :value="modelValue"
      class="w-full rounded-lg border border-violet-300 bg-white px-3 py-2 text-sm"
      @input="$emit('update:modelValue', $event.target.value)"
    />
  </div>
</template>

<script setup>
defineProps({
  label:      { type: String, required: true },
  type:       { type: String, required: true }, // 'date' | 'month'
  modelValue: { type: String, default: '' },// Valeur au format 'YYYY-MM-DD' pour date ou 'YYYY-MM' pour month  
})
// Émet un événement pour mettre à jour la valeur du modèle lorsque l'utilisateur sélectionne une date ou un mois
defineEmits(['update:modelValue'])
//$event.target.value contient la nouvelle valeur sélectionnée par l'utilisateur, qui est ensuite émise pour mettre à jour le modèle parent
//modelValue est lié à l'input pour refléter la valeur actuelle du champ de date ou de mois, permettant une liaison bidirectionnelle entre le composant et son parent
</script>