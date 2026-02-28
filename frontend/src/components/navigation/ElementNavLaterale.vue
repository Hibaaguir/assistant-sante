<template>
  <RouterLink :to="to" class="block">
    <div
      class="flex items-center gap-3 rounded-[14px] px-4 py-3 text-[15px] font-semibold transition"
      :class="active
        ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-[0_10px_24px_rgba(59,130,246,0.35)]'
        : 'text-slate-600 hover:bg-white'"
    >
      <span class="inline-flex h-5 w-5 items-center justify-center" v-html="icon" />
      <span>{{ label }}</span>
    </div>
  </RouterLink>
</template>

<script setup>
/*
  Element unitaire de la navigation laterale.
  Il recoit son etat (actif/inactif) et ses donnees via props.
  Son role est uniquement visuel et reutilisable.
*/

defineProps({
  to: {
    type: Object,
    required: true
  },
  label: {
    type: String,
    required: true
  },
  icon: {
    type: String,
    required: true
  },
  active: {
    type: Boolean,
    default: false
  }
})
</script>

