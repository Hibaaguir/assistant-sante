<template>
  <div class="mx-auto max-w-[1320px] px-4 py-6 sm:px-6 lg:px-8 lg:py-8">

    <!-- En-tête -->
    <header class="rounded-2xl border border-slate-200 bg-white px-5 py-5 shadow-sm">
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
    </header>

    <!-- Erreur -->
    <div
      v-if="messageErreur"
      class="mt-4 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700"
    >
      {{ messageErreur }}
    </div>

    <!-- Statistiques -->
    <section class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
      <CarteStatistique
        v-for="stat in cartesStatistiques"
        :key="stat.label"
        v-bind="stat"
      />
    </section>

    <!-- Recherche & filtres -->
    <section class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
      <div class="flex flex-col gap-3 lg:flex-row lg:items-center">
        <label class="relative block w-full">
          <svg viewBox="0 0 24 24" class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="7" /><path d="m20 20-3-3" />
          </svg>
          <input
            v-model="texteRecherche"
            type="text"
            placeholder="Rechercher par nom ou email..."
            class="h-12 w-full rounded-2xl border border-slate-300 bg-white pl-12 pr-4 text-base outline-none focus:border-slate-400"
          />
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
            <option value="Médecin">Médecin</option>
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

    <!-- Tableau utilisateurs -->
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

            <tr v-if="chargementListe">
              <td colspan="6" class="px-6 py-8 text-center text-base text-slate-500">
                Chargement des utilisateurs...
              </td>
            </tr>

            <tr v-else-if="utilisateursFiltres.length === 0">
              <td colspan="6" class="px-6 py-8 text-center text-base text-slate-500">
                Aucun utilisateur ne correspond à votre recherche.
              </td>
            </tr>

            <tr v-for="utilisateur in utilisateursFiltres" :key="utilisateur.id" v-else>
              <!-- Nom & email -->
              <td class="px-6 py-4">
                <div class="flex items-center gap-3">
                  <div
                    class="inline-flex h-10 w-10 items-center justify-center rounded-full text-sm font-bold text-white"
                    :class="estMedecin(utilisateur) ? 'bg-indigo-500' : 'bg-emerald-500'"
                  >
                    {{ initialesNom(utilisateur.nom) }}
                  </div>
                  <div>
                    <p class="text-base font-semibold text-slate-900">{{ utilisateur.nom }}</p>
                    <p class="text-sm text-slate-500">{{ utilisateur.email }}</p>
                  </div>
                </div>
              </td>

              <!-- Type -->
              <td class="px-6 py-4">
                <span
                  class="inline-flex items-center rounded-full px-3 py-1 text-sm font-semibold"
                  :class="estMedecin(utilisateur) ? 'bg-indigo-100 text-indigo-700' : 'bg-emerald-100 text-emerald-700'"
                >
                  {{ utilisateur.type }}
                </span>
                <p v-if="utilisateur.specialite" class="mt-1 text-sm text-slate-500">
                  {{ utilisateur.specialite }}
                </p>
              </td>

              <!-- Statut -->
              <td class="px-6 py-4">
                <span
                  class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-sm font-semibold"
                  :class="estActif(utilisateur) ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-700'"
                >
                  <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="9" />
                    <path v-if="estActif(utilisateur)" d="m8 12 2.5 2.5L16 9" />
                    <path v-else d="m9 9 6 6M15 9l-6 6" />
                  </svg>
                  {{ utilisateur.statut }}
                </span>
              </td>

              <td class="px-6 py-4 text-sm text-slate-700">{{ utilisateur.inscription }}</td>
              <td class="px-6 py-4 text-sm text-slate-700">{{ utilisateur.derniere_activite }}</td>

              <!-- Actions -->
              <td class="px-6 py-4">
                <div class="flex items-center justify-center gap-3">
                  <button
                    type="button"
                    class="text-orange-500 hover:text-orange-700"
                    :title="estActif(utilisateur) ? 'Désactiver le compte' : 'Activer le compte'"
                    @click="basculerStatut(utilisateur)"
                  >
                    <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2">
                      <circle cx="12" cy="8" r="4" /><path d="M6 20a6 6 0 0 1 12 0" />
                    </svg>
                  </button>

                  <button type="button" class="text-red-500 hover:text-red-700" @click="ouvrirSuppression(utilisateur)">
                    <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6" />
                    </svg>
                  </button>

                  <button type="button" class="text-slate-500 hover:text-slate-700">
                    <svg viewBox="0 0 24 24" class="h-5 w-5" fill="currentColor">
                      <circle cx="12" cy="5" r="2" /><circle cx="12" cy="12" r="2" /><circle cx="12" cy="19" r="2" />
                    </svg>
                  </button>
                </div>
              </td>
            </tr>

          </tbody>
        </table>
      </div>
    </section>

    <ModalSuppressionUtilisateur
      :ouvert="modalSuppressionOuvert"
      @annuler="fermerSuppression"
      @confirmer="confirmerSuppression"
    />
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from "vue";
import ModalSuppressionUtilisateur from "@/components/dashboards/adminDashboard/ModalSuppressionUtilisateur.vue";
import {
  listerUtilisateursAdministrateur,
  supprimerUtilisateurAdministrateur,
  basculerStatutUtilisateur,
} from "@/services/administrateur";

// --- État ---
const utilisateurs        = ref([]);
const chargementListe     = ref(false);
const messageErreur       = ref("");
const texteRecherche      = ref("");
const afficherFiltres     = ref(true);
const filtreType          = ref("Tous");
const filtreStatut        = ref("Tous");
const modalSuppressionOuvert      = ref(false);
const idUtilisateurSuppression    = ref(null);

// --- Helpers ---
const estMedecin = (u) => u.type === "Médecin";
const estActif   = (u) => u.statut === "Actif";

function initialesNom(nomComplet) {
  return String(nomComplet || "")
    .split(" ")
    .filter(Boolean)
    .map((p) => p[0]?.toUpperCase() ?? "")
    .slice(0, 2)
    .join("");
}

function messageErreurDepuis(error) {
  return error?.response?.data?.message ?? null;
}

// --- Computed ---
const statistiques = computed(() => ({
  total:    utilisateurs.value.length,
  patients: utilisateurs.value.filter((u) => u.type === "Patient").length,
  medecins: utilisateurs.value.filter((u) => estMedecin(u)).length,
  actifs:   utilisateurs.value.filter((u) => estActif(u)).length,
}));

const cartesStatistiques = computed(() => [
  { label: "Total utilisateurs", valeur: statistiques.value.total,    couleur: "blue"   },
  { label: "Patients",           valeur: statistiques.value.patients,  couleur: "emerald" },
  { label: "Médecins",           valeur: statistiques.value.medecins,  couleur: "indigo"  },
  { label: "Actifs",             valeur: statistiques.value.actifs,    couleur: "orange"  },
]);

const utilisateursFiltres = computed(() => {
  const recherche = texteRecherche.value.trim().toLowerCase();

  return utilisateurs.value.filter((u) => {
    const correspondRecherche =
      recherche === "" ||
      u.nom.toLowerCase().includes(recherche) ||
      u.email.toLowerCase().includes(recherche);

    const correspondType   = filtreType.value   === "Tous" || u.type   === filtreType.value;
    const correspondStatut = filtreStatut.value === "Tous" || u.statut === filtreStatut.value;

    return correspondRecherche && correspondType && correspondStatut;
  });
});

// --- Actions ---
async function chargerUtilisateurs() {
  chargementListe.value = true;
  messageErreur.value   = "";
  try {
    utilisateurs.value = await listerUtilisateursAdministrateur();
  } catch (error) {
    utilisateurs.value = [];
    messageErreur.value = messageErreurDepuis(error) ?? "Impossible de charger les utilisateurs.";
  } finally {
    chargementListe.value = false;
  }
}

async function basculerStatut(utilisateur) {
  const nouveauStatut = estActif(utilisateur) ? "Inactif" : "Actif";
  messageErreur.value = "";
  try {
    await basculerStatutUtilisateur(utilisateur.id, nouveauStatut);
    await chargerUtilisateurs();
  } catch (error) {
    messageErreur.value = messageErreurDepuis(error) ?? "Impossible de changer le statut de cet utilisateur.";
  }
}

function ouvrirSuppression(utilisateur) {
  idUtilisateurSuppression.value = utilisateur.id;
  modalSuppressionOuvert.value   = true;
}

function fermerSuppression() {
  modalSuppressionOuvert.value   = false;
  idUtilisateurSuppression.value = null;
}

async function confirmerSuppression() {
  messageErreur.value = "";
  try {
    await supprimerUtilisateurAdministrateur(idUtilisateurSuppression.value);
    await chargerUtilisateurs();
    fermerSuppression();
  } catch (error) {
    messageErreur.value = messageErreurDepuis(error) ?? "Impossible de supprimer cet utilisateur.";
  }
}

onMounted(chargerUtilisateurs);
</script>