<template>
  <button
    type="submit"
    :disabled="chargement || disabled"
    class="h-11 w-full rounded-xl bg-gradient-to-r from-blue-600 to-purple-600 font-semibold text-white transition hover:shadow-lg disabled:cursor-not-allowed disabled:opacity-50"
  >
    <slot />
  </button>
</template>

<script setup>
defineProps({
  chargement: { type: Boolean, default: false },
  disabled:   { type: Boolean, default: false },
})
</script>