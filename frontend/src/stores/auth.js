/*
  Store d'authentification centralisé.
  Source unique de vérité pour l'utilisateur connecté, son rôle, son
  espace actif, et la gestion du token.
*/

import { defineStore } from 'pinia'
import { computed, ref } from 'vue'
import api from '@/services/api'

const ACTIVE_SPACE_KEY = 'active_space'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const resolved = ref(false)
  const espaceActif = ref(localStorage.getItem(ACTIVE_SPACE_KEY) || 'personnel')

  let fetchInFlight = null
  let deconnexionInFlight = null

  const estConnecte = computed(() => Boolean(localStorage.getItem('auth_token')))
  const nomUtilisateur = computed(() => user.value?.name || '')
  const roleUtilisateur = computed(() => user.value?.role?.toLowerCase() || null)
  const estAdministrateur = computed(() => roleUtilisateur.value === 'administrateur' || roleUtilisateur.value === 'admin')
  const aProfilSante = computed(() => Boolean(user.value?.has_profil_sante))
  const espaceCourant = computed(() => (estAdministrateur.value ? 'administrateur' : 'personnel'))
  const estDansEspacePersonnel = computed(() => !estAdministrateur.value || espaceCourant.value === 'personnel')

  function synchroniserEspaceActif() {
    espaceActif.value = 'personnel'
    localStorage.setItem(ACTIVE_SPACE_KEY, 'personnel')
  }

  function definirEspaceActif(space) {
    espaceActif.value = 'personnel'
    localStorage.setItem(ACTIVE_SPACE_KEY, 'personnel')
  }

  function definirPresenceProfilSante(hasProfile) {
    if (!user.value) return
    user.value.has_profil_sante = Boolean(hasProfile)
  }

  function appliquerAuthentification(data, preferredSpace = null) {
    if (data?.token) {
      definirToken(data.token)
    }

    user.value = data?.user ?? null
    if (user.value) {
      user.value.has_profil_sante = Boolean(data?.has_profil_sante)
    }

    resolved.value = true

    if (preferredSpace) {
      definirEspaceActif(preferredSpace)
    } else {
      synchroniserEspaceActif()
    }

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
      espaceActif.value = 'personnel'
    })()

    try {
      await deconnexionInFlight
    } finally {
      deconnexionInFlight = null
    }
  }

  function supprimerToken() {
    localStorage.removeItem('auth_token')
    localStorage.removeItem(ACTIVE_SPACE_KEY)
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
    estDansEspacePersonnel,
    nomUtilisateur,
    roleUtilisateur,
    aProfilSante,
    appliquerAuthentification,
    chargerUtilisateur,
    deconnexion,
    definirEspaceActif,
    definirPresenceProfilSante,
    supprimerToken,
    definirToken,
  }
})
