<!--
  Dashboard administrateur.
  Ce composant reprend le prototype: cartes statistiques, recherche,
  filtres, tableau utilisateurs, edition et suppression.
-->
<template>
  <div class="mx-auto max-w-[1320px] px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
    <header class="rounded-2xl border border-slate-200 bg-white px-5 py-5 shadow-sm">
      <div class="flex items-start justify-between gap-4">
        <div class="flex items-start gap-4">
          <div class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-orange-600 text-white">
            <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M12 2l7 4v6c0 5-3.4 8.7-7 10-3.6-1.3-7-5-7-10V6l7-4z" />
            </svg>
          </div>
          <div>
            <h1 class="text-3xl font-bold text-slate-900">Tableau de bord Administrateur</h1>
            <p class="mt-1 text-sm text-slate-600">Gestion des comptes utilisateurs</p>
          </div>
        </div>
      </div>
    </header>

    <div v-if="messageErreur" class="mt-4 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
      {{ messageErreur }}
    </div>

    <section class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
      <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex items-center justify-between">
          <div class="inline-flex h-11 w-11 items-center justify-center rounded-xl bg-blue-100 text-blue-600">
            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
              <circle cx="8.5" cy="7" r="3" />
              <path d="M20 8v6M23 11h-6" />
            </svg>
          </div>
          <svg viewBox="0 0 24 24" class="h-5 w-5 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2">
            <path d="m7 17 10-10" />
            <path d="M10 7h7v7" />
          </svg>
        </div>
        <p class="mt-4 text-4xl font-bold text-slate-900">{{ statistiques.total }}</p>
        <p class="mt-1 text-lg text-slate-600">Total utilisateurs</p>
      </article>

      <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex items-center justify-between">
          <div class="inline-flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600">
            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M3 12h4l2-6 4 12 2-6h6" />
            </svg>
          </div>
          <svg viewBox="0 0 24 24" class="h-5 w-5 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2">
            <path d="m7 17 10-10" />
            <path d="M10 7h7v7" />
          </svg>
        </div>
        <p class="mt-4 text-4xl font-bold text-slate-900">{{ statistiques.patients }}</p>
        <p class="mt-1 text-lg text-slate-600">Patients</p>
      </article>

      <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex items-center justify-between">
          <div class="inline-flex h-11 w-11 items-center justify-center rounded-xl bg-indigo-100 text-indigo-600">
            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M4 11a8 8 0 0 1 16 0" />
              <path d="M12 19a3 3 0 0 0 3-3v-5" />
              <path d="M9 20h6" />
            </svg>
          </div>
          <svg viewBox="0 0 24 24" class="h-5 w-5 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2">
            <path d="m7 17 10-10" />
            <path d="M10 7h7v7" />
          </svg>
        </div>
        <p class="mt-4 text-4xl font-bold text-slate-900">{{ statistiques.actifs }}</p>
        <p class="mt-1 text-lg text-slate-600">Actifs</p>
      </article>
    </section>

    <section class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
      <div class="flex flex-col gap-3 lg:flex-row lg:items-center">
        <label class="relative block w-full">
          <svg viewBox="0 0 24 24" class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="7" />
            <path d="m20 20-3-3" />
          </svg>
          <input
            v-model="texteRecherche"
            type="text"
            placeholder="Rechercher par nom ou email..."
            class="h-12 w-full rounded-2xl border border-slate-300 bg-white pl-12 pr-4 text-base outline-none focus:border-slate-400"
          >
        </label>

        <button
          type="button"
          class="inline-flex h-12 items-center justify-center gap-2 rounded-2xl bg-orange-500 px-6 text-xl font-semibold text-white"
          @click="afficherFiltres = !afficherFiltres"
        >
          <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M3 4h18l-7 8v6l-4 2v-8z" />
          </svg>
          Filtres
        </button>
      </div>

      <div v-if="afficherFiltres" class="mt-4 grid grid-cols-1 gap-4 border-t border-slate-200 pt-4 md:grid-cols-2">
        <label class="space-y-1">
          <span class="text-sm font-semibold text-slate-700">Type d'utilisateur</span>
          <select v-model="filtreType" class="h-11 w-full rounded-xl border border-slate-300 px-3 text-base outline-none focus:border-slate-400">
            <option value="Tous">Tous</option>
            <option value="Patient">Patient</option>
          </select>
        </label>

        <label class="space-y-1">
          <span class="text-sm font-semibold text-slate-700">Statut</span>
          <select v-model="filtreStatut" class="h-11 w-full rounded-xl border border-slate-300 px-3 text-base outline-none focus:border-slate-400">
            <option value="Tous">Tous</option>
            <option value="Actif">Actif</option>
            <option value="Inactif">Inactif</option>
          </select>
        </label>
      </div>
    </section>

    <section class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200">
          <thead class="bg-slate-50">
            <tr class="text-left text-sm font-semibold text-slate-700">
              <th class="px-6 py-4">Utilisateur</th>
              <th class="px-6 py-4">Type</th>
              <th class="px-6 py-4">Statut</th>
              <th class="px-6 py-4">Inscription</th>
              <th class="px-6 py-4">Dernière activité</th>
              <th class="px-6 py-4 text-center">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-200 bg-white">
            <tr v-for="utilisateur in utilisateursFiltres" :key="utilisateur.id">
              <td class="px-6 py-4">
                <div class="flex items-center gap-3">
                  <div class="inline-flex h-10 w-10 items-center justify-center rounded-full text-sm font-bold text-white bg-emerald-500">
                    {{ initialesNom(utilisateur.nom) }}
                  </div>
                  <div>
                    <p class="text-base font-semibold text-slate-900">{{ utilisateur.nom }}</p>
                    <p class="text-sm text-slate-500">{{ utilisateur.email }}</p>
                  </div>
                </div>
              </td>

              <td class="px-6 py-4">
                <div>
                  <span class="inline-flex items-center rounded-full px-3 py-1 text-sm font-semibold bg-emerald-100 text-emerald-700">
                    {{ utilisateur.type }}
                  </span>
                </div>
              </td>

              <td class="px-6 py-4">
                <span class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-sm font-semibold" :class="utilisateur.statut === 'Actif' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-700'">
                  <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="9" />
                    <path v-if="utilisateur.statut === 'Actif'" d="m8 12 2.5 2.5L16 9" />
                    <path v-else d="m9 9 6 6M15 9l-6 6" />
                  </svg>
                  {{ utilisateur.statut }}
                </span>
              </td>

              <td class="px-6 py-4 text-sm text-slate-700">{{ utilisateur.inscription }}</td>
              <td class="px-6 py-4 text-sm text-slate-700">{{ utilisateur.derniere_activite }}</td>

              <td class="px-6 py-4">
                <div class="flex items-center justify-center gap-3">
                  <button type="button" class="text-orange-500 hover:text-orange-700" @click="basculerStatutUtilisateur(utilisateur)">
                    <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2">
                      <circle cx="12" cy="8" r="4" />
                      <path d="M6 20a6 6 0 0 1 12 0" />
                    </svg>
                  </button>

                  <button type="button" class="text-blue-500 hover:text-blue-700" @click="ouvrirEdition(utilisateur)">
                    <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M12 20h9" />
                      <path d="m16.5 3.5 4 4L8 20l-5 1 1-5z" />
                    </svg>
                  </button>

                  <button type="button" class="text-red-500 hover:text-red-700" @click="ouvrirSuppression(utilisateur)">
                    <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M3 6h18" />
                      <path d="M8 6V4h8v2" />
                      <path d="M19 6l-1 14H6L5 6" />
                    </svg>
                  </button>

                  <button type="button" class="text-slate-500 hover:text-slate-700">
                    <svg viewBox="0 0 24 24" class="h-5 w-5" fill="currentColor">
                      <circle cx="12" cy="5" r="2" />
                      <circle cx="12" cy="12" r="2" />
                      <circle cx="12" cy="19" r="2" />
                    </svg>
                  </button>
                </div>
              </td>
            </tr>

            <tr v-if="!chargementListe && utilisateursFiltres.length === 0">
              <td colspan="6" class="px-6 py-8 text-center text-base text-slate-500">
                Aucun utilisateur ne correspond à votre recherche.
              </td>
            </tr>

            <tr v-if="chargementListe">
              <td colspan="6" class="px-6 py-8 text-center text-base text-slate-500">
                Chargement des utilisateurs...
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>

    <ModalEditionUtilisateur
      :ouvert="modalEditionOuvert"
      :formulaire="formulaireEdition"
      @fermer="fermerEdition"
      @enregistrer="enregistrerEdition"
      @mettre-a-jour="mettreAJourFormulaireEdition"
    />

    <ModalSuppressionUtilisateur
      :ouvert="modalSuppressionOuvert"
      @annuler="fermerSuppression"
      @confirmer="confirmerSuppression"
    />
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import ModalEditionUtilisateur from '@/components/dashboards/adminDashboard/ModalEditionUtilisateur.vue'
import ModalSuppressionUtilisateur from '@/components/dashboards/adminDashboard/ModalSuppressionUtilisateur.vue'
import {
  listerUtilisateursAdministrateur,
  modifierUtilisateurAdministrateur,
  supprimerUtilisateurAdministrateur
} from '@/services/administrateur'

const utilisateurs = ref([])
const chargementListe = ref(false)
const messageErreur = ref('')
const texteRecherche = ref('')
const afficherFiltres = ref(true)
const filtreType = ref('Tous')
const filtreStatut = ref('Tous')

const modalEditionOuvert = ref(false)
const modalSuppressionOuvert = ref(false)
const idUtilisateurEdition = ref(null)
const idUtilisateurSuppression = ref(null)

const formulaireEdition = reactive({
  nom: '',
  email: '',
  type: 'Patient',
  statut: 'Actif'
})

const statistiques = computed(() => {
  const total = utilisateurs.value.length
  const patients = utilisateurs.value.filter((item) => item.type === 'Patient').length
  const actifs = utilisateurs.value.filter((item) => item.statut === 'Actif').length

  return { total, patients, actifs }
})

const utilisateursFiltres = computed(() => {
  const recherche = texteRecherche.value.trim().toLowerCase()

  return utilisateurs.value.filter((item) => {
    const correspondRecherche = recherche === ''
      || item.nom.toLowerCase().includes(recherche)
      || item.email.toLowerCase().includes(recherche)

    const correspondType = filtreType.value === 'Tous' || item.type === filtreType.value
    const correspondStatut = filtreStatut.value === 'Tous' || item.statut === filtreStatut.value

    return correspondRecherche && correspondType && correspondStatut
  })
})

function initialesNom(nomComplet) {
  return String(nomComplet || '')
    .split(' ')
    .filter(Boolean)
    .slice(0, 1)
    .map((partie) => partie[0]?.toUpperCase() || '')
    .join('')
}

function ouvrirEdition(utilisateur) {
  idUtilisateurEdition.value = utilisateur.id
  formulaireEdition.nom = utilisateur.nom
  formulaireEdition.email = utilisateur.email
  formulaireEdition.type = utilisateur.type
  formulaireEdition.statut = utilisateur.statut
  modalEditionOuvert.value = true
}

function fermerEdition() {
  modalEditionOuvert.value = false
  idUtilisateurEdition.value = null
}

function mettreAJourFormulaireEdition({ champ, valeur }) {
  formulaireEdition[champ] = valeur
}

async function enregistrerEdition() {
  try {
    messageErreur.value = ''
    await modifierUtilisateurAdministrateur(idUtilisateurEdition.value, {
      nom: formulaireEdition.nom,
      email: formulaireEdition.email,
      type: formulaireEdition.type,
      statut: formulaireEdition.statut,
      specialite: formulaireEdition.specialite || ''
    })
    await chargerUtilisateursAdministrateur()
    fermerEdition()
  } catch (error) {
    console.error('Erreur modification utilisateur:', error?.response?.data || error?.message || error)
    messageErreur.value = error?.response?.data?.message || 'Impossible de modifier cet utilisateur pour le moment.'
  }
}

function ouvrirSuppression(utilisateur) {
  idUtilisateurSuppression.value = utilisateur.id
  modalSuppressionOuvert.value = true
}

function fermerSuppression() {
  modalSuppressionOuvert.value = false
  idUtilisateurSuppression.value = null
}

async function confirmerSuppression() {
  try {
    messageErreur.value = ''
    await supprimerUtilisateurAdministrateur(idUtilisateurSuppression.value)
    await chargerUtilisateursAdministrateur()
    fermerSuppression()
  } catch (error) {
    console.error('Erreur suppression utilisateur:', error?.response?.data || error?.message || error)
    messageErreur.value = error?.response?.data?.message || 'Impossible de supprimer cet utilisateur pour le moment.'
  }
}

async function basculerStatutUtilisateur(utilisateur) {
  const nouveauStatut = utilisateur.statut === 'Actif' ? 'Inactif' : 'Actif'

  try {
    messageErreur.value = ''
    await modifierUtilisateurAdministrateur(utilisateur.id, {
      nom: utilisateur.nom,
      email: utilisateur.email,
      type: utilisateur.type,
      statut: nouveauStatut,
      specialite: utilisateur.specialite || ''
    })
    await chargerUtilisateursAdministrateur()
  } catch (error) {
    console.error('Erreur changement statut:', error?.response?.data || error?.message || error)
    messageErreur.value = error?.response?.data?.message || 'Impossible de changer le statut de cet utilisateur pour le moment.'
  }
}

async function chargerUtilisateursAdministrateur() {
  chargementListe.value = true
  messageErreur.value = ''

  try {
    utilisateurs.value = await listerUtilisateursAdministrateur()
  } catch (error) {
    console.error('Erreur chargement utilisateurs:', error?.response?.data || error?.message || error)
    utilisateurs.value = []
    messageErreur.value = error?.response?.data?.message || 'Impossible de charger les utilisateurs administrateur.'
  } finally {
    chargementListe.value = false
  }
}

onMounted(() => {
  chargerUtilisateursAdministrateur()
})
</script>
