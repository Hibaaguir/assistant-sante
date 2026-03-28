<template>
  <label class="text-base font-medium text-gray-900 flex items-center gap-2">
    <svg v-if="icon" class="h-5 w-5" :class="color" viewBox="0 0 24 24" fill="none" aria-hidden="true">
      <path :d="icon" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
      <circle v-if="withCircle" cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2" />
    </svg>
    <slot />
  </label>
</template>

<script setup>
defineProps({
  icon:       { type: String,  default: null },
  color:      { type: String,  default: 'text-gray-400' },
  withCircle: { type: Boolean, default: false },
})
</script>