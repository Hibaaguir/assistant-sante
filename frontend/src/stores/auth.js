/*
  Store d'authentification centralisé.
  Source unique de vérité pour l'utilisateur connecté, son rôle, et la gestion du token.
  Expose fetchUser() avec déduplication des appels simultanés.
*/

import { defineStore } from 'pinia'
import { computed, ref } from 'vue'
import api from '@/services/api'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const resolved = ref(false)

  // Promesse partagée pour éviter plusieurs appels /auth/me simultanés.
  let _fetchInFlight = null

  const isLoggedIn = computed(() => Boolean(localStorage.getItem('auth_token')))
  const userName = computed(() => user.value?.name || '')
  const userRole = computed(() => user.value?.role?.toLowerCase() || null)
  const isDoctor = computed(() => userRole.value === 'medecin' || userRole.value === 'doctor')
  const hasProfil = computed(() => Boolean(user.value?.has_profil_sante))

  async function fetchUser() {
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
          clearToken()
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

  async function logout() {
    try {
      await api.post('/auth/logout')
    } catch (_) {
      // La déconnexion locale doit toujours fonctionner même si l'API échoue.
    } finally {
      clearToken()
      user.value = null
      resolved.value = false
    }
  }

  function clearToken() {
    localStorage.removeItem('auth_token')
    delete api.defaults.headers.common.Authorization
  }

  function setToken(token) {
    localStorage.setItem('auth_token', token)
  }

  return {
    user,
    resolved,
    isLoggedIn,
    isDoctor,
    userName,
    userRole,
    hasProfil,
    fetchUser,
    logout,
    clearToken,
    setToken,
  }
})
