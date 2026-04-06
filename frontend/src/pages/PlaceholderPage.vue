<template>
  <div class="mx-auto max-w-[1320px] p-4 sm:p-6 lg:p-8">
    <div class="rounded-2xl border border-slate-300 bg-white p-8">
      <h2 class="text-3xl font-bold text-slate-900">{{ title }}</h2>
      <p class="mt-3 text-slate-600">Page placeholder pour garder la navigation conforme au design.</p>
    </div>
  </div>
</template>

<script setup>
/*
  Composant placeholder simple pour les pages non encore implementees.
  Il garde la navigation active avec un titre dynamique.
  Le but est de fournir un ecran temporaire propre sans logique metier.
*/

// Props minimales: un titre obligatoire affiche dans la carte.
defineProps({
  title: {
    type: String,
    required: true
  }
})
</script>
