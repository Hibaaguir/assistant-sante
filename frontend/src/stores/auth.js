/*
  Store d'authentification centralisé.
  Source unique de vérité pour l'utilisateur connecté, son rôle, son
  niveau d'accès, et la gestion du token.
*/

import { defineStore } from 'pinia'
import { computed, ref } from 'vue'
import api from '@/services/api'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const resolved = ref(false)

  let fetchInFlight = null
  let deconnexionInFlight = null

  const estConnecte = computed(() => Boolean(localStorage.getItem('auth_token')))
  const nomUtilisateur = computed(() => user.value?.name || '')
  const photoProfil = computed(() => user.value?.profile_photo || '')
  const roleUtilisateur = computed(() => user.value?.role?.toLowerCase() || null)
  const estAdministrateur = computed(() => roleUtilisateur.value === 'administrateur' || roleUtilisateur.value === 'admin')
  const estMedecin = computed(() => roleUtilisateur.value === 'medecin' || roleUtilisateur.value === 'doctor')
  const aProfilSante = computed(() => Boolean(user.value?.has_profil_sante))
  const espaceCourant = computed(() => (estMedecin.value ? 'medecin' : (estAdministrateur.value ? 'administrateur' : 'personnel')))
  const estDansEspaceMedecin = computed(() => estMedecin.value)
  const estDansEspacePersonnel = computed(() => !estMedecin.value && !estAdministrateur.value)

  function definirPresenceProfilSante(hasProfile) {
    if (!user.value) return
    user.value.has_profil_sante = Boolean(hasProfile)
  }

  function mettreAJourUtilisateur(changements = {}) {
    if (!user.value) return
    user.value = {
      ...user.value,
      ...changements,
    }
  }

  function appliquerAuthentification(data) {
    if (data?.token) {
      definirToken(data.token)
    }

    user.value = data?.user ?? null
    if (user.value) {
      user.value.has_profil_sante = Boolean(data?.has_profil_sante)
    }

    resolved.value = true

    return user.value
  }

  async function chargerUtilisateur() {
    if (!localStorage.getItem('auth_token')) {
      user.value = null
      resolved.value = true
      return null
    }

    if (!fetchInFlight) {
      fetchInFlight = api
        .get('/auth/me')
        .then((res) => appliquerAuthentification(res?.data))
        .catch(() => {
          supprimerToken()
          user.value = null
          resolved.value = true
          return null
        })
        .finally(() => {
          fetchInFlight = null
        })
    }

    return fetchInFlight
  }

  async function deconnexion(options = {}) {
    const { appelerApi = true } = options

    if (deconnexionInFlight) {
      return deconnexionInFlight
    }

    deconnexionInFlight = (async () => {
      const tokenPresent = Boolean(localStorage.getItem('auth_token'))

      if (appelerApi && tokenPresent) {
        try {
          await api.post('/auth/logout')
        } catch (_) {
          // La déconnexion locale doit toujours fonctionner même si l'API échoue.
        }
      }

      supprimerToken()
      user.value = null
      resolved.value = false
    })()

    try {
      await deconnexionInFlight
    } finally {
      deconnexionInFlight = null
    }
  }

  function supprimerToken() {
    localStorage.removeItem('auth_token')
    delete api.defaults.headers.common.Authorization
  }

  function definirToken(token) {
    localStorage.setItem('auth_token', token)
  }

  return {
    user,
    resolved,
    espaceCourant,
    estConnecte,
    estAdministrateur,
    estMedecin,
    estDansEspaceMedecin,
    estDansEspacePersonnel,
    nomUtilisateur,
    photoProfil,
    roleUtilisateur,
    aProfilSante,
    appliquerAuthentification,
    chargerUtilisateur,
    deconnexion,
    definirPresenceProfilSante,
    mettreAJourUtilisateur,
    supprimerToken,
    definirToken,
  }
})
