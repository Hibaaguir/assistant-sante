<template>
  <Teleport to="body">
    <div v-if="ouvert" class="fixed inset-0 z-[1400] flex items-center justify-center bg-slate-900/45 p-4">
      <div class="w-full max-w-[640px] rounded-2xl bg-white p-6 shadow-2xl">
        <h3 class="text-[40px] font-semibold leading-none text-slate-900">Êtes-vous sûr ?</h3>
        <p class="mt-4 text-2xl text-slate-500">
          Cette action est irréversible. Cela supprimera définitivement le compte de l'utilisateur.
        </p>

        <div class="mt-8 flex items-center justify-end gap-3">
          <button type="button" class="rounded-2xl border border-slate-300 bg-slate-100 px-7 py-2.5 text-2xl font-semibold text-slate-700" @click="$emit('annuler')">
            Annuler
          </button>
          <button type="button" class="rounded-2xl bg-gradient-to-r from-orange-500 to-red-500 px-7 py-2.5 text-2xl font-semibold text-white shadow-lg" @click="$emit('confirmer')">
            Supprimer
          </button>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
defineProps({
  ouvert: { type: Boolean, default: false }
})

defineEmits(['annuler', 'confirmer'])
</script>
