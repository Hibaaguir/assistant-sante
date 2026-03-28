<template>
  <div class="space-y-2">
    <label class="text-sm font-medium text-gray-900">
      {{ label }} <span class="text-gray-400 font-normal">(JJ/MM/AAAA)</span>
    </label>
    <input
      :value="value"
      type="text"
      placeholder="Ex: 01/03/2026"
      maxlength="10"
      class="h-12 rounded-lg w-full border bg-slate-50 px-4 outline-none focus:border-teal-500"
      :class="error ? 'border-red-300' : 'border-gray-200'"
      @input="$emit('input', $event)"
    />
    <p v-if="error" class="text-sm text-red-600">{{ error }}</p>
  </div>
</template>

<script setup>
defineProps({
  label: { type: String, required: true },
  value: { type: String, default: '' },
  error: { type: String, default: '' },
})
defineEmits(['input'])
</script>