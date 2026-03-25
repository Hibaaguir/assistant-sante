<template>
  <div class="flex min-h-screen items-center justify-center bg-gray-50 px-4 py-12 sm:px-6 lg:px-8">
    <div class="w-full max-w-lg space-y-6">
      <!-- Button Retour -->
      <button
        type="button"
        class="flex items-center gap-2 text-sm font-semibold text-slate-600 transition hover:text-slate-900"
        @click="$router.push({ name: 'connexion' })"
      >
        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5">
          <path d="m15 18-6-6 6-6" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        Retour à la connexion
      </button>

      <!-- Header -->
      <div class="flex items-center gap-2">
        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-gradient-to-br from-blue-600 to-purple-600">
          <svg viewBox="0 0 24 24" class="h-6 w-6 stroke-white" fill="none" stroke-width="2">
            <circle cx="12" cy="12" r="10" />
            <path d="M12 6v6l4 2" />
          </svg>
        </div>
        <div>
          <h1 class="text-2xl font-bold text-gray-900">HealthFlow</h1>
        </div>
      </div>

      <!-- Content -->
      <div v-if="!reinitOk">
        <h2 class="text-xl font-semibold text-gray-900">Réinitialiser votre mot de passe</h2>
        <p class="mt-2 text-sm text-gray-600">
          Entrez votre nouveau mot de passe pour réinitialiser l'accès à votre compte.
        </p>
      </div>

      <!-- Form -->
      <form v-if="!reinitOk" class="space-y-4" @submit.prevent="reinitialiserMotDePasse">
        <div>
          <label for="email" class="block text-sm font-semibold text-gray-700">Adresse email</label>
          <input
            id="email"
            v-model="formulaire.email"
            type="email"
            placeholder="votre.email@exemple.com"
            disabled
            class="mt-2 h-12 w-full rounded-xl border border-gray-200 bg-gray-100 px-4 text-sm text-gray-500 placeholder:text-gray-400 focus:outline-none"
          />
        </div>

        <div>
          <label for="mot-de-passe" class="block text-sm font-semibold text-gray-700">Nouveau mot de passe</label>
          <div class="relative mt-2">
            <input
              id="mot-de-passe"
              v-model="formulaire.motDePasse"
              :type="afficherMotDePasse.nouveau ? 'text' : 'password'"
              placeholder="••••••••"
              class="h-12 w-full rounded-xl border border-gray-200 bg-gray-50 px-4 text-sm placeholder:text-gray-400 focus:border-blue-500 focus:bg-white focus:outline-none"
            />
            <button
              type="button"
              class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
              @click="afficherMotDePasse.nouveau = !afficherMotDePasse.nouveau"
            >
              <svg v-if="afficherMotDePasse.nouveau" viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                <circle cx="12" cy="12" r="3" />
              </svg>
              <svg v-else viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24" />
                <line x1="1" y1="1" x2="23" y2="23" />
              </svg>
            </button>
          </div>
          <p v-if="erreurs.motDePasse" class="mt-2 text-xs text-rose-600">{{ erreurs.motDePasse }}</p>
        </div>

        <div>
          <label for="confirmation" class="block text-sm font-semibold text-gray-700">Confirmer le mot de passe</label>
          <div class="relative mt-2">
            <input
              id="confirmation"
              v-model="formulaire.confirmationMotDePasse"
              :type="afficherMotDePasse.confirmation ? 'text' : 'password'"
              placeholder="••••••••"
              class="h-12 w-full rounded-xl border border-gray-200 bg-gray-50 px-4 text-sm placeholder:text-gray-400 focus:border-blue-500 focus:bg-white focus:outline-none"
            />
            <button
              type="button"
              class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
              @click="afficherMotDePasse.confirmation = !afficherMotDePasse.confirmation"
            >
              <svg v-if="afficherMotDePasse.confirmation" viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                <circle cx="12" cy="12" r="3" />
              </svg>
              <svg v-else viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24" />
                <line x1="1" y1="1" x2="23" y2="23" />
              </svg>
            </button>
          </div>
          <p v-if="erreurs.confirmationMotDePasse" class="mt-2 text-xs text-rose-600">{{ erreurs.confirmationMotDePasse }}</p>
        </div>

        <button
          type="submit"
          :disabled="chargement"
          class="h-12 w-full rounded-2xl bg-gradient-to-r from-blue-600 to-purple-600 font-semibold text-white transition hover:shadow-lg disabled:cursor-not-allowed disabled:opacity-50"
        >
          {{ chargement ? 'Réinitialisation...' : 'Réinitialiser mon mot de passe' }}
        </button>
      </form>

      <!-- Message de succès -->
      <Transition name="fade">
        <div v-if="reinitOk" class="space-y-4">
          <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4">
            <div class="flex gap-3">
              <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-emerald-100">
                <svg viewBox="0 0 24 24" class="h-5 w-5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2">
                  <polyline points="20 6 9 17 4 12" />
                </svg>
              </div>
              <div>
                <p class="font-semibold text-emerald-900">Succès !</p>
                <p class="mt-1 text-sm text-emerald-700">Votre mot de passe a été réinitialisé avec succès.</p>
                <p class="mt-2 text-xs text-emerald-600">Vous allez être redirigé vers la page de connexion dans quelques secondes.</p>
              </div>
            </div>
          </div>
          <button
            type="button"
            class="h-12 w-full rounded-2xl bg-gradient-to-r from-blue-600 to-purple-600 font-semibold text-white transition hover:shadow-lg"
            @click="$router.push({ name: 'connexion' })"
          >
            Aller à la connexion
          </button>
        </div>
      </Transition>

      <!-- Message d'erreur -->
      <Transition name="fade">
        <div v-if="messageErreur" class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
          {{ messageErreur }}
        </div>
      </Transition>

      <!-- Info box -->
      <div class="rounded-xl bg-blue-50 p-4 text-sm text-blue-800">
        <p class="font-semibold">🔒 Sécurité</p>
        <p class="mt-1">Nous ne vous demanderons jamais votre mot de passe par email. Gardez-le secret.</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import api from '@/services/api'
import { useNotificationsStore } from '@/stores/notifications'

const router = useRouter()
const route = useRoute()
const notifications = useNotificationsStore()

const formulaire = reactive({
  email: '',
  motDePasse: '',
  confirmationMotDePasse: '',
  token: ''
})

const afficherMotDePasse = reactive({
  nouveau: false,
  confirmation: false
})

const erreurs = reactive({
  motDePasse: '',
  confirmationMotDePasse: ''
})

const chargement = ref(false)
const messageErreur = ref('')
const reinitOk = ref(false)
const roleUtilisateur = ref(null)

onMounted(() => {
  const email = route.query.email
  const token = route.query.token

  if (!email || !token) {
    messageErreur.value = 'Lien de réinitialisation invalide ou expiré.'
    setTimeout(() => {
      router.push({ name: 'connexion' })
    }, 2000)
    return
  }

  formulaire.email = email
  formulaire.token = token
})

async function reinitialiserMotDePasse() {
  erreurs.motDePasse = ''
  erreurs.confirmationMotDePasse = ''
  messageErreur.value = ''

  if (!formulaire.motDePasse) {
    erreurs.motDePasse = 'Le mot de passe est requis.'
    return
  }

  if (formulaire.motDePasse.length < 8) {
    erreurs.motDePasse = 'Le mot de passe doit contenir au moins 8 caractères.'
    return
  }

  if (formulaire.motDePasse !== formulaire.confirmationMotDePasse) {
    erreurs.confirmationMotDePasse = 'Les mots de passe ne correspondent pas.'
    return
  }

  chargement.value = true
  try {
    const response = await api.post('/auth/reinitialiser-mot-de-passe', {
      email: formulaire.email,
      token: formulaire.token,
      mot_de_passe: formulaire.motDePasse,
      mot_de_passe_confirmation: formulaire.confirmationMotDePasse
    })

    if (response?.data) {
      const role = response?.data?.data?.role
      roleUtilisateur.value = role
      reinitOk.value = true
      notifications.succes('Votre mot de passe a été réinitialisé avec succès.')
      
      // Tous les utilisateurs vont à la même page de connexion
      setTimeout(() => {
        router.push({ name: 'connexion' })
      }, 3000)
    }
  } catch (error) {
    if (error?.response?.data?.message) {
      messageErreur.value = error.response.data.message
    } else if (error?.response?.data?.errors) {
      const apiErreurs = error.response.data.errors
      if (apiErreurs.mot_de_passe) {
        erreurs.motDePasse = apiErreurs.mot_de_passe[0]
      }
      if (apiErreurs.mot_de_passe_confirmation) {
        erreurs.confirmationMotDePasse = apiErreurs.mot_de_passe_confirmation[0]
      }
    } else {
      messageErreur.value = 'Une erreur est survenue. Veuillez réessayer.'
    }
    notifications.erreur(messageErreur.value || 'Erreur lors de la réinitialisation.')
  } finally {
    chargement.value = false
  }
}
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
