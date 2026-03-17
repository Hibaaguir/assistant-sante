// Configuration centrale du routeur Vue : définition des routes de l'application et import des composants nécessaires
import { createRouter, createWebHistory } from "vue-router";
import { useAuthStore } from "@/stores/auth";
import FormulaireInscription from "@/components/register/FormulaireInscription.vue";
import FormulaireConnexion from "@/components/login/FormulaireConnexion.vue";
import OublierMotDePassePage from "@/pages/auth/OublierMotDePassePage.vue";
import ReinitialiserMotDePassePage from "@/pages/auth/ReinitialiserMotDePassePage.vue";
import ProfilSante from "@/components/profil-sante/ProfilSante.vue";
import MiseEnPagePrincipale from "@/layouts/MiseEnPagePrincipale.vue";
import JournalHome from "@/pages/journal/JournalHome.vue";
import JournalAssistant from "@/pages/journal/JournalAssistant.vue";
import JournalHistory from "@/pages/journal/JournalHistory.vue";
import ProfilSantePage from "@/pages/health/ProfilSantePage.vue";
import DonneesSantePage from "@/pages/health/DonneesSantePage.vue";
import TableauDeBordPage from "@/pages/TableauDeBordPage.vue";
import InscriptionMedecinPage from "@/pages/doctor/InscriptionMedecinPage.vue";
import ChoixEspacePage from "@/pages/doctor/ChoixEspacePage.vue";
import PageTemporaire from "@/pages/PageTemporaire.vue";
import PageAccueilPublique from "@/pages/accueil/PageAccueilPublique.vue";

// Déclaration de toutes les routes de l'application avec leurs composants associés et certaines protections d'accès
const routes = [
  { path: "/", name: "accueil-publique", component: PageAccueilPublique },
  { path: "/login", name: "connexion", component: FormulaireConnexion },
  { path: "/register", name: "inscription", component: FormulaireInscription },
  { path: "/oublier-mot-de-passe", name: "oublier-mot-de-passe", component: OublierMotDePassePage },
  { path: "/reinitialiser-mot-de-passe", name: "reinitialiser-mot-de-passe", component: ReinitialiserMotDePassePage },
  { path: "/register/user", redirect: "/register" },
  { path: "/doctor-register", name: "inscription-medecin", component: InscriptionMedecinPage },
  { path: "/doctor-login", redirect: "/login" },
  { path: "/choix-espace", name: "choix-espace", component: ChoixEspacePage, meta: { requiresAuth: true } },
  { path: "/profil-sante", name: "profil-sante", component: ProfilSante, meta: { requiresAuth: true } },
  {
    path: "/main",
    component: MiseEnPagePrincipale,
    meta: { requiresAuth: true },
    children: [
      { path: "", name: "accueil", redirect: { name: "tableau-de-bord" } },
      { path: "dashboard", name: "tableau-de-bord", component: TableauDeBordPage },
      { path: "journal", name: "journal", component: JournalHome },
      { path: "health-data", name: "donnees-sante", component: DonneesSantePage },
      { path: "journal/new", name: "assistant-journal", component: JournalAssistant },
      { path: "journal/history", name: "historique-journal", component: JournalHistory },
      { path: "health", name: "mon-profil-sante", component: ProfilSantePage },
      { path: "ai", name: "recommandations-ia", component: PageTemporaire, props: { title: "Recommandations IA" } },
    ],
  },
];

// Création de l'instance du routeur Vue avec l'historique HTML5 et les routes définies
const router = createRouter({
  history: createWebHistory(),
  routes,
});

// Guard global exécuté avant chaque navigation pour gérer l'authentification et l'accès aux routes protégées
router.beforeEach(async (to) => {
  const authStore = useAuthStore();
  const user = await authStore.chargerUtilisateur();
  const routeParDefautAuthentifie = () => {
    if (authStore.estAdministrateur) {
      return { name: "tableau-de-bord" };
    }

    if (authStore.estDansEspaceMedecin) {
      return { name: "tableau-de-bord" };
    }

    return authStore.aProfilSante
      ? { name: "tableau-de-bord" }
      : { name: "profil-sante" };
  };

  const routeName = String(to.name || "");
  const estPageAuthMedecin = ["connexion-medecin", "inscription-medecin"].includes(routeName);
  const estPageAuthUtilisateur = ["inscription", "connexion", "accueil-publique"].includes(routeName);

  if ((estPageAuthMedecin || estPageAuthUtilisateur) && user) {
    return routeParDefautAuthentifie();
  }

  if (to.meta.requiresAuth && !user) {
    return { name: "connexion" };
  }

  if (
    to.meta.requiresAuth &&
    user &&
    authStore.estDansEspacePersonnel &&
    !authStore.estAdministrateur &&
    !authStore.aProfilSante &&
    to.name !== "profil-sante" &&
    to.name !== "choix-espace"
  ) {
    return { name: "profil-sante" };
  }

  if (to.name === "profil-sante" && user && authStore.estAdministrateur) {
    return { name: "tableau-de-bord" };
  }

  if (to.name === "choix-espace" && user && !authStore.estMedecin) {
    return routeParDefautAuthentifie();
  }

  if (to.name === "profil-sante" && user && authStore.aProfilSante && authStore.estDansEspacePersonnel) {
    return { name: "tableau-de-bord" };
  }

  return true;
});

// Export du routeur pour l'utiliser dans l'application Vue
export default router;
