<template>
  <span
    class="inline-flex h-4 w-4 items-center justify-center rounded-full border"
    :class="active ? 'border-violet-600' : 'border-slate-400'"
    aria-hidden="true"
  >
    <span v-if="active" class="h-2 w-2 rounded-full bg-violet-600" />
  </span>
</template>

<script setup>
defineProps({ active: { type: Boolean, default: false } })
</script>