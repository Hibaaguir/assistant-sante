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

  const estConnecte = computed(() => Boolean(localStorage.getItem('auth_token')))
  const nomUtilisateur = computed(() => user.value?.name || '')
  const roleUtilisateur = computed(() => user.value?.role?.toLowerCase() || null)
  const estMedecin = computed(() => roleUtilisateur.value === 'medecin' || roleUtilisateur.value === 'doctor')
  const aProfilSante = computed(() => Boolean(user.value?.has_profil_sante))
  const espaceCourant = computed(() => (estMedecin.value ? espaceActif.value : 'personnel'))
  const estDansEspaceMedecin = computed(() => estMedecin.value && espaceCourant.value === 'medecin')
  const estDansEspacePersonnel = computed(() => !estMedecin.value || espaceCourant.value === 'personnel')

  function synchroniserEspaceActif() {
    if (!estMedecin.value) {
      espaceActif.value = 'personnel'
      localStorage.setItem(ACTIVE_SPACE_KEY, 'personnel')
      return
    }

    const memorise = localStorage.getItem(ACTIVE_SPACE_KEY)
    espaceActif.value = memorise === 'personnel' ? 'personnel' : 'medecin'
    localStorage.setItem(ACTIVE_SPACE_KEY, espaceActif.value)
  }

  function definirEspaceActif(space) {
    const prochainEspace = space === 'medecin' && estMedecin.value ? 'medecin' : 'personnel'
    espaceActif.value = prochainEspace
    localStorage.setItem(ACTIVE_SPACE_KEY, prochainEspace)
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

  async function deconnexion() {
    try {
      await api.post('/auth/logout')
    } catch (_) {
      // La déconnexion locale doit toujours fonctionner même si l'API échoue.
    } finally {
      supprimerToken()
      user.value = null
      resolved.value = false
      espaceActif.value = 'personnel'
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
    estMedecin,
    estDansEspaceMedecin,
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
