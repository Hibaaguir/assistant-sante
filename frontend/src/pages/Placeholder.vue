<template>
  <div class="mx-auto max-w-[1320px] p-4 sm:p-6 lg:p-8">
    <div class="rounded-2xl border border-slate-300 bg-white p-8">
      <h2 class="text-3xl font-bold text-slate-900">{{ title }}</h2>
      <p class="mt-3 text-slate-600">Page placeholder pour garder la navigation conforme au design.</p>
    </div>
  </div>
</template>

<script setup>
defineProps({
  title: {
    type: String,
    required: true
  }
})
</script>
