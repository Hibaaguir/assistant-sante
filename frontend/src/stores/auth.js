/*
  Store d'authentification centralisé.
  Source unique de vérité pour l'utilisateur connecté, son rôle, et la gestion du token.
  Expose chargerUtilisateur() avec déduplication des appels simultanés.
*/

import { defineStore } from 'pinia'
import { computed, ref } from 'vue'
import api from '@/services/api'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const resolved = ref(false)

  // Promesse partagée pour éviter plusieurs appels /auth/me simultanés.
  let _fetchInFlight = null

  const estConnecte = computed(() => Boolean(localStorage.getItem('auth_token')))
  const nomUtilisateur = computed(() => user.value?.name || '')
  const roleUtilisateur = computed(() => user.value?.role?.toLowerCase() || null)
  const estMedecin = computed(() => roleUtilisateur.value === 'medecin' || roleUtilisateur.value === 'doctor')
  const aProfilSante = computed(() => Boolean(user.value?.has_profil_sante))

  async function chargerUtilisateur() {
    if (!localStorage.getItem('auth_token')) {
      user.value = null
      resolved.value = true
      return null
    }

    if (!_fetchInFlight) {
      _fetchInFlight = api
        .get('/auth/me')
        .then((res) => {
          user.value = res?.data?.user ?? null
          if (user.value) {
            // has_profil_sante vient du niveau racine de la réponse
            user.value.has_profil_sante = Boolean(res?.data?.has_profil_sante)
          }
          resolved.value = true
          return user.value
        })
        .catch(() => {
          supprimerToken()
          user.value = null
          resolved.value = true
          return null
        })
        .finally(() => {
          _fetchInFlight = null
        })
    }

    return _fetchInFlight
  }

  async function deconnexion() {
    try {
      await api.post('/auth/logout')
    } catch (_) {
      // La déconnexion locale doit toujours fonctionner même si l'API échoue.
    } finally {
      supprimerToken()
      user.value = null
      resolved.value = false
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
    estConnecte,
    estMedecin,
    nomUtilisateur,
    roleUtilisateur,
    aProfilSante,
    chargerUtilisateur,
    deconnexion,
    supprimerToken,
    definirToken,
  }
})
