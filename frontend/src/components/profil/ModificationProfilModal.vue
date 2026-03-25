<template>
  <Teleport to="body">
    <Transition name="modal-fade">
      <div v-if="estOuvert" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/20 backdrop-blur-sm">
        <div class="relative max-h-[90vh] w-full max-w-md overflow-y-auto rounded-2xl bg-white p-6 shadow-xl">
          <!-- Bouton fermer -->
          <button
            type="button"
            class="absolute right-4 top-4 text-slate-400 transition hover:text-slate-600"
            @click="fermer"
            aria-label="Fermer le modal"
          >
            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.2">
              <path d="m6 6 12 12M18 6 6 18" stroke-linecap="round" />
            </svg>
          </button>

          <!-- Header -->
          <div class="mb-6">
            <h2 class="text-[24px] font-semibold leading-none text-slate-900">Modification du profil</h2>
            <p class="mt-2 text-sm text-slate-600">Mettez à jour vos informations personnelles</p>
          </div>

          <!-- Tabs -->
          <div class="mb-6 flex gap-2 border-b border-slate-200">
            <button
              type="button"
              class="pb-3 font-semibold transition"
              :class="ongletActif === 'nom' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-slate-600 hover:text-slate-900'"
              @click="ongletActif = 'nom'"
            >
              Informations
            </button>
            <button
              type="button"
              class="pb-3 font-semibold transition"
              :class="ongletActif === 'mdp' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-slate-600 hover:text-slate-900'"
              @click="ongletActif = 'mdp'"
            >
              Sécurité
            </button>
          </div>

          <!-- Contenu -->
          <form class="space-y-4" @submit.prevent="traiterFormulaire">
            <!-- Tab Informations -->
            <div v-if="ongletActif === 'nom'" class="space-y-4">
              <div>
                <p class="block text-sm font-semibold text-slate-700">Photo de profil</p>
                <div class="mt-2 flex items-center gap-4">
                  <div class="flex h-16 w-16 items-center justify-center overflow-hidden rounded-full bg-gradient-to-br from-blue-500 to-purple-600 text-white">
                    <img v-if="photoApercu" :src="photoApercu" alt="Photo de profil" class="h-16 w-16 rounded-full object-cover" />
                    <svg v-else viewBox="0 0 24 24" class="h-8 w-8" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <circle cx="12" cy="8" r="4" />
                      <path d="M6 20a6 6 0 0 1 12 0" />
                    </svg>
                  </div>

                  <div class="flex flex-wrap gap-2">
                    <input
                      ref="inputPhoto"
                      type="file"
                      accept="image/png,image/jpeg,image/webp"
                      class="hidden"
                      @change="selectionnerPhoto"
                    />

                    <button
                      type="button"
                      class="inline-flex h-10 items-center justify-center rounded-xl border border-slate-200 px-3 text-xs font-semibold text-slate-700 transition hover:bg-slate-50"
                      :disabled="chargement.photo"
                      @click="ouvrirSelecteurPhoto"
                    >
                      {{ photoApercu ? 'Modifier la photo' : 'Ajouter une photo' }}
                    </button>

                    <button
                      v-if="photoApercu"
                      type="button"
                      class="inline-flex h-10 items-center justify-center rounded-xl border border-rose-200 px-3 text-xs font-semibold text-rose-600 transition hover:bg-rose-50"
                      :disabled="chargement.photo"
                      @click="supprimerPhoto"
                    >
                      Supprimer
                    </button>
                  </div>
                </div>
                <p v-if="erreurs.photo" class="mt-2 text-xs text-rose-600">{{ erreurs.photo }}</p>
              </div>

              <div>
                <label for="nom" class="block text-sm font-semibold text-slate-700">Nom d'utilisateur</label>
                <input
                  id="nom"
                  v-model="formulaire.nom"
                  type="text"
                  placeholder="Votre nom complet"
                  class="mt-2 h-12 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:outline-none"
                />
                <p v-if="erreurs.nom" class="mt-2 text-xs text-rose-600">{{ erreurs.nom }}</p>
              </div>

              <button
                type="submit"
                :disabled="chargement.nom || formulaire.nom === nomOriginal"
                class="h-11 w-full rounded-xl bg-gradient-to-r from-blue-600 to-purple-600 font-semibold text-white transition hover:shadow-lg disabled:cursor-not-allowed disabled:opacity-50"
              >
                {{ chargement.nom ? 'Enregistrement...' : 'Enregistrer les modifications' }}
              </button>
            </div>

            <!-- Tab Sécurité -->
            <div v-if="ongletActif === 'mdp'" class="space-y-4">
              <div>
                <label for="mdp-actuel" class="block text-sm font-semibold text-slate-700">Mot de passe actuel</label>
                <div class="relative mt-2">
                  <input
                    id="mdp-actuel"
                    v-model="formulaire.motDePasseActuel"
                    :type="afficherMotDePasse.actuel ? 'text' : 'password'"
                    placeholder="••••••••"
                    class="h-12 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:outline-none"
                  />
                  <button
                    type="button"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600"
                    @click="afficherMotDePasse.actuel = !afficherMotDePasse.actuel"
                  >
                    <svg v-if="afficherMotDePasse.actuel" viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                      <circle cx="12" cy="12" r="3" />
                    </svg>
                    <svg v-else viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24" />
                      <line x1="1" y1="1" x2="23" y2="23" />
                    </svg>
                  </button>
                </div>
                <p v-if="erreurs.motDePasseActuel" class="mt-2 text-xs text-rose-600">{{ erreurs.motDePasseActuel }}</p>
              </div>

              <div>
                <label for="nouveau-mdp" class="block text-sm font-semibold text-slate-700">Nouveau mot de passe</label>
                <div class="relative mt-2">
                  <input
                    id="nouveau-mdp"
                    v-model="formulaire.nouveauMotDePasse"
                    :type="afficherMotDePasse.nouveau ? 'text' : 'password'"
                    placeholder="••••••••"
                    class="h-12 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:outline-none"
                  />
                  <button
                    type="button"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600"
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
                <p v-if="erreurs.nouveauMotDePasse" class="mt-2 text-xs text-rose-600">{{ erreurs.nouveauMotDePasse }}</p>
              </div>

              <div>
                <label for="confirm-mdp" class="block text-sm font-semibold text-slate-700">Confirmez le mot de passe</label>
                <div class="relative mt-2">
                  <input
                    id="confirm-mdp"
                    v-model="formulaire.confirmationMotDePasse"
                    :type="afficherMotDePasse.confirmation ? 'text' : 'password'"
                    placeholder="••••••••"
                    class="h-12 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:outline-none"
                  />
                  <button
                    type="button"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600"
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
                :disabled="chargement.mdp"
                class="h-11 w-full rounded-xl bg-gradient-to-r from-blue-600 to-purple-600 font-semibold text-white transition hover:shadow-lg disabled:cursor-not-allowed disabled:opacity-50"
              >
                {{ chargement.mdp ? 'Mise à jour...' : 'Changer le mot de passe' }}
              </button>
            </div>
          </form>

          <!-- Message de succès -->
          <Transition name="fade">
            <div v-if="messageSucces" class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
              {{ messageSucces }}
            </div>
          </Transition>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, reactive, watch } from 'vue'
import api from '@/services/api'
import { useNotificationsStore } from '@/stores/notifications'
import { useAuthStore } from '@/stores/auth'

defineProps({
  estOuvert: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['fermer', 'profil-mis-a-jour'])

const authStore = useAuthStore()
const notifications = useNotificationsStore()

const ongletActif = ref('nom')
const nomOriginal = ref(authStore.nomUtilisateur)
const messageSucces = ref('')
const inputPhoto = ref(null)
const photoApercu = ref(authStore.photoProfil || '')

const formulaire = reactive({
  nom: authStore.nomUtilisateur,
  motDePasseActuel: '',
  nouveauMotDePasse: '',
  confirmationMotDePasse: ''
})

const afficherMotDePasse = reactive({
  actuel: false,
  nouveau: false,
  confirmation: false
})

const erreurs = reactive({
  nom: '',
  photo: '',
  motDePasseActuel: '',
  nouveauMotDePasse: '',
  confirmationMotDePasse: ''
})

const chargement = reactive({
  nom: false,
  photo: false,
  mdp: false
})

function fermer() {
  messageSucces.value = ''
  Object.keys(erreurs).forEach(key => erreurs[key] = '')
  emit('fermer')
}

async function traiterFormulaire() {
  Object.keys(erreurs).forEach(key => erreurs[key] = '')
  messageSucces.value = ''

  if (ongletActif.value === 'nom') {
    await mettreAJourNom()
  } else if (ongletActif.value === 'mdp') {
    await changerMotDePasse()
  }
}

async function mettreAJourNom() {
  if (!formulaire.nom.trim()) {
    erreurs.nom = 'Le nom est requis.'
    return
  }

  if (formulaire.nom.length < 2) {
    erreurs.nom = 'Le nom doit contenir au moins 2 caractères.'
    return
  }

  if (formulaire.nom.length > 120) {
    erreurs.nom = 'Le nom ne peut pas dépasser 120 caractères.'
    return
  }

  chargement.nom = true
  try {
    const response = await api.put('/profil-utilisateur/nom', {
      nom: formulaire.nom
    })

    if (response?.data) {
      authStore.mettreAJourUtilisateur({ name: formulaire.nom })
      nomOriginal.value = formulaire.nom
      messageSucces.value = 'Nom mis à jour avec succès!'
      notifications.succes('Votre profil a été mis à jour.')
      emit('profil-mis-a-jour')
      setTimeout(() => fermer(), 1500)
    }
  } catch (error) {
    if (error?.response?.data?.errors?.nom) {
      erreurs.nom = error.response.data.errors.nom[0]
    } else if (error?.response?.data?.message) {
      erreurs.nom = error.response.data.message
    } else {
      erreurs.nom = 'Une erreur est survenue lors de la mise à jour.'
    }
    notifications.erreur(erreurs.nom)
  } finally {
    chargement.nom = false
  }
}

function ouvrirSelecteurPhoto() {
  erreurs.photo = ''
  inputPhoto.value?.click()
}

function convertirFichierEnBase64(fichier) {
  return new Promise((resolve, reject) => {
    const reader = new FileReader()
    reader.onload = () => resolve(String(reader.result || ''))
    reader.onerror = () => reject(new Error('Impossible de lire le fichier.'))
    reader.readAsDataURL(fichier)
  })
}

async function selectionnerPhoto(event) {
  const fichier = event?.target?.files?.[0]
  if (!fichier) return

  erreurs.photo = ''

  if (!['image/png', 'image/jpeg', 'image/webp'].includes(fichier.type)) {
    erreurs.photo = 'Format non supporté. Utilisez PNG, JPG ou WEBP.'
    return
  }

  if (fichier.size > 2 * 1024 * 1024) {
    erreurs.photo = 'La photo ne doit pas dépasser 2 Mo.'
    return
  }

  chargement.photo = true
  try {
    const base64 = await convertirFichierEnBase64(fichier)
    const response = await api.put('/profil-utilisateur/photo', {
      photo: base64,
    })

    const photo = response?.data?.data?.photo_profil || base64
    photoApercu.value = photo
    authStore.mettreAJourUtilisateur({ profile_photo: photo })
    messageSucces.value = 'Photo de profil mise à jour avec succès!'
    notifications.succes('Photo de profil mise à jour.')
    emit('profil-mis-a-jour')
  } catch (error) {
    if (error?.response?.data?.errors?.photo) {
      erreurs.photo = error.response.data.errors.photo[0]
    } else if (error?.response?.data?.message) {
      erreurs.photo = error.response.data.message
    } else {
      erreurs.photo = 'Une erreur est survenue lors de la mise à jour de la photo.'
    }
    notifications.erreur(erreurs.photo)
  } finally {
    chargement.photo = false
    if (event?.target) event.target.value = ''
  }
}

async function supprimerPhoto() {
  erreurs.photo = ''
  chargement.photo = true

  try {
    await api.delete('/profil-utilisateur/photo')
    photoApercu.value = ''
    authStore.mettreAJourUtilisateur({ profile_photo: null })
    messageSucces.value = 'Photo de profil supprimée avec succès!'
    notifications.succes('Photo de profil supprimée.')
    emit('profil-mis-a-jour')
  } catch (error) {
    if (error?.response?.data?.message) {
      erreurs.photo = error.response.data.message
    } else {
      erreurs.photo = 'Une erreur est survenue lors de la suppression de la photo.'
    }
    notifications.erreur(erreurs.photo)
  } finally {
    chargement.photo = false
  }
}

watch(
  () => authStore.photoProfil,
  (valeur) => {
    photoApercu.value = valeur || ''
  },
  { immediate: true }
)

async function changerMotDePasse() {
  if (!formulaire.motDePasseActuel) {
    erreurs.motDePasseActuel = 'Le mot de passe actuel est requis.'
    return
  }

  if (!formulaire.nouveauMotDePasse) {
    erreurs.nouveauMotDePasse = 'Le nouveau mot de passe est requis.'
    return
  }

  if (formulaire.nouveauMotDePasse.length < 8) {
    erreurs.nouveauMotDePasse = 'Le mot de passe doit contenir au moins 8 caractères.'
    return
  }

  if (formulaire.nouveauMotDePasse !== formulaire.confirmationMotDePasse) {
    erreurs.confirmationMotDePasse = 'Les mots de passe ne correspondent pas.'
    return
  }

  chargement.mdp = true
  try {
    await api.post('/profil-utilisateur/changer-mot-de-passe', {
      mot_de_passe_actuel: formulaire.motDePasseActuel,
      nouveau_mot_de_passe: formulaire.nouveauMotDePasse,
      nouveau_mot_de_passe_confirmation: formulaire.confirmationMotDePasse
    })

    messageSucces.value = 'Mot de passe changé avec succès!'
    formulaire.motDePasseActuel = ''
    formulaire.nouveauMotDePasse = ''
    formulaire.confirmationMotDePasse = ''
    notifications.succes('Votre mot de passe a été mis à jour.')
    setTimeout(() => fermer(), 1500)
  } catch (error) {
    if (error?.response?.status === 422) {
      if (error?.response?.data?.message) {
        erreurs.motDePasseActuel = error.response.data.message
      } else {
        erreurs.motDePasseActuel = 'Le mot de passe actuel est incorrect.'
      }
    } else if (error?.response?.data?.errors) {
      const apiErreurs = error.response.data.errors
      if (apiErreurs.mot_de_passe_actuel) {
        erreurs.motDePasseActuel = apiErreurs.mot_de_passe_actuel[0]
      }
      if (apiErreurs.nouveau_mot_de_passe) {
        erreurs.nouveauMotDePasse = apiErreurs.nouveau_mot_de_passe[0]
      }
    } else {
      erreurs.motDePasseActuel = 'Une erreur est survenue lors du changement du mot de passe.'
    }
    notifications.erreur(erreurs.motDePasseActuel || 'Erreur lors du changement du mot de passe.')
  } finally {
    chargement.mdp = false
  }
}
</script>

<style scoped>
.modal-fade-enter-active,
.modal-fade-leave-active {
  transition: opacity 0.3s ease;
}

.modal-fade-enter-from,
.modal-fade-leave-to {
  opacity: 0;
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
