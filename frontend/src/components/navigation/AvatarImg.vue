<template>
  <img :src="src" alt="Photo de profil" :class="`h-${size} w-${size} rounded-full object-cover`" />
</template>

<script setup>
defineProps({
  src:  { type: String, required: true },
  size: { type: String, default: '10' },  // correspond aux classes Tailwind h-* / w-*
})
</script>