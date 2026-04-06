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
  modelValue: { type: String, default: '' },
})
defineEmits(['update:modelValue'])
</script>