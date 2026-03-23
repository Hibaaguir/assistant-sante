<template>
  <div class="relative">
    <!-- Modal modification profil -->
    <ModificationProfilModal :est-ouvert="modalProfilOuvert" @fermer="modalProfilOuvert = false" />

    <!-- Bouton circulaire de profil -->
    <button
      @click="menuOpen = !menuOpen"
      class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-blue-500 to-purple-600 text-white shadow-lg hover:shadow-xl transition-shadow"
      :class="menuOpen ? 'ring-2 ring-purple-400' : ''"
    >
      <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="12" cy="8" r="4" />
        <path d="M6 20a6 6 0 0 1 12 0" />
      </svg>
    </button>

    <!-- Menu dropdown -->
    <transition
      enter-active-class="transition ease-out duration-100"
      enter-from-class="transform opacity-0 scale-95"
      enter-to-class="transform opacity-100 scale-100"
      leave-active-class="transition ease-in duration-75"
      leave-from-class="transform opacity-100 scale-100"
      leave-to-class="transform opacity-0 scale-95"
    >
      <div
        v-if="menuOpen"
        class="absolute right-0 mt-2 w-56 rounded-2xl bg-white shadow-xl border border-gray-100 overflow-hidden z-50"
      >
        <!-- Profil utilisateur -->
        <div class="bg-gradient-to-r from-blue-50 to-purple-50 px-6 py-5 border-b border-gray-100">
          <div class="flex items-center gap-3">
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gradient-to-br from-blue-500 to-purple-600 text-white flex-shrink-0">
              <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="8" r="4" />
                <path d="M6 20a6 6 0 0 1 12 0" />
              </svg>
            </div>
            <div class="min-w-0 flex-1">
              <p class="text-sm font-semibold text-gray-900 truncate">{{ authStore.nomUtilisateur }}</p>
              <p class="text-xs text-gray-600">{{ libelleRoleUtilisateur }}</p>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="py-2">
          <button
            @click="ouvrirModalProfil"
            type="button"
            class="w-full px-6 py-3 text-left text-sm text-gray-700 hover:bg-gray-50 transition-colors flex items-center gap-2.5"
          >
            <svg viewBox="0 0 24 24" class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="m16 3 5 5-11 11H5v-5L16 3z" />
            </svg>
            Modifier mon profil
          </button>
        </div>
      </div>
    </transition>

    <!-- Overlay pour fermer le menu -->
    <div
      v-if="menuOpen"
      @click="menuOpen = false"
      class="fixed inset-0 z-40"
    />
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import ModificationProfilModal from '@/components/profil/ModificationProfilModal.vue'

const router = useRouter()
const authStore = useAuthStore()
const menuOpen = ref(false)
const modalProfilOuvert = ref(false)

const libelleRoleUtilisateur = computed(() => {
  const role = authStore.roleUtilisateur
  if (role === 'user') return 'Patient'
  if (role === 'admin' || role === 'administrateur') return 'Administrateur'
  return ''
})

function ouvrirModalProfil() {
  modalProfilOuvert.value = true
  menuOpen.value = false
}

async function deconnexion() {
  await authStore.deconnexion()
  router.push({ name: 'accueil-publique' })
}
</script>
