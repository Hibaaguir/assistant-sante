<template>
  <Teleport to="body">
    <div v-if="ouvert" class="fixed inset-0 z-[1400] flex items-center justify-center bg-slate-900/45 p-4">
      <div class="w-full max-w-[500px] rounded-2xl bg-white p-6 shadow-2xl">
        <div class="mb-5 flex items-start justify-between">
          <div>
            <h3 class="text-[32px] font-semibold leading-none text-slate-900">Modifier un utilisateur</h3>
            <p class="mt-2 text-sm text-slate-500">Modifiez les informations d'un utilisateur existant.</p>
          </div>
          <button type="button" class="text-2xl leading-none text-slate-500 hover:text-slate-700" @click="$emit('fermer')">×</button>
        </div>

        <form class="space-y-4" @submit.prevent="$emit('enregistrer')">
          <label class="grid grid-cols-[80px_1fr] items-center gap-3">
            <span class="text-base font-semibold text-slate-700">Nom</span>
            <input
              :value="formulaire.nom"
              type="text"
              class="h-11 rounded-2xl border border-orange-500 px-4 text-base outline-none"
              @input="$emit('mettre-a-jour', { champ: 'nom', valeur: $event.target.value })"
            >
          </label>

          <label class="grid grid-cols-[80px_1fr] items-center gap-3">
            <span class="text-base font-semibold text-slate-700">Email</span>
            <input
              :value="formulaire.email"
              type="email"
              class="h-11 rounded-2xl border border-slate-300 px-4 text-base outline-none focus:border-slate-400"
              @input="$emit('mettre-a-jour', { champ: 'email', valeur: $event.target.value })"
            >
          </label>

          <label class="grid grid-cols-[80px_1fr] items-center gap-3">
            <span class="text-base font-semibold text-slate-700">Type</span>
            <select
              :value="formulaire.type"
              class="h-11 rounded-2xl border border-slate-300 px-4 text-base outline-none focus:border-slate-400"
              @change="$emit('mettre-a-jour', { champ: 'type', valeur: $event.target.value })"
            >
              <option value="Patient">Patient</option>
              <option value="Médecin">Médecin</option>
            </select>
          </label>

          <label v-if="formulaire.type === 'Médecin'" class="grid grid-cols-[80px_1fr] items-center gap-3">
            <span class="text-base font-semibold text-slate-700">Spécialité</span>
            <input
              :value="formulaire.specialite || ''"
              type="text"
              placeholder="Ex: Cardiologie"
              class="h-11 rounded-2xl border border-slate-300 px-4 text-base outline-none focus:border-slate-400"
              @input="$emit('mettre-a-jour', { champ: 'specialite', valeur: $event.target.value })"
            >
          </label>

          <label class="grid grid-cols-[80px_1fr] items-center gap-3">
            <span class="text-base font-semibold text-slate-700">Statut</span>
            <select
              :value="formulaire.statut"
              class="h-11 rounded-2xl border border-slate-300 px-4 text-base outline-none focus:border-slate-400"
              @change="$emit('mettre-a-jour', { champ: 'statut', valeur: $event.target.value })"
            >
              <option value="Actif">Actif</option>
              <option value="Inactif">Inactif</option>
            </select>
          </label>

          <div class="flex items-center justify-end gap-3 pt-3">
            <button type="button" class="rounded-2xl bg-slate-100 px-6 py-2.5 text-xl font-semibold text-slate-600" @click="$emit('fermer')">
              Annuler
            </button>
            <button type="submit" class="rounded-2xl bg-gradient-to-r from-orange-500 to-red-500 px-6 py-2.5 text-xl font-semibold text-white shadow-lg">
              Enregistrer
            </button>
          </div>
        </form>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
defineProps({
  ouvert: { type: Boolean, default: false },
  formulaire: {
    type: Object,
    default: () => ({ nom: '', email: '', type: 'Patient', statut: 'Actif' })
  }
})

defineEmits(['fermer', 'enregistrer', 'mettre-a-jour'])
</script>
