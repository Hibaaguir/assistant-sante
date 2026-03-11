// Configuration centrale du routeur Vue : définition des routes de l'application et import des composants nécessaires
import { createRouter, createWebHistory } from "vue-router";
import { useAuthStore } from "@/stores/auth";
import FormulaireInscription from "@/components/register/FormulaireInscription.vue";
import FormulaireConnexion from "@/components/login/FormulaireConnexion.vue";
import ChoixRoleInscriptionPage from "@/pages/auth/ChoixRoleInscriptionPage.vue";
import ProfilSante from "@/components/profil-sante/ProfilSante.vue";
import MiseEnPagePrincipale from "@/layouts/MiseEnPagePrincipale.vue";
import JournalHome from "@/pages/journal/JournalHome.vue";
import JournalAssistant from "@/pages/journal/JournalAssistant.vue";
import JournalHistory from "@/pages/journal/JournalHistory.vue";
import ProfilSantePage from "@/pages/health/ProfilSantePage.vue";
import DonneesSantePage from "@/pages/health/DonneesSantePage.vue";
import TableauDeBordPage from "@/pages/TableauDeBordPage.vue";
import ConnexionMedecinPage from "@/pages/doctor/ConnexionMedecinPage.vue";
import InscriptionMedecinPage from "@/pages/doctor/InscriptionMedecinPage.vue";
import PageTemporaire from "@/pages/PageTemporaire.vue";

// Déclaration de toutes les routes de l'application avec leurs composants associés et certaines protections d'accès
const routes = [
  { path: "/", redirect: "/login" },
  { path: "/login", name: "connexion", component: FormulaireConnexion },
  { path: "/register", name: "inscription", component: ChoixRoleInscriptionPage },
  { path: "/register/user", name: "inscription-utilisateur", component: FormulaireInscription },
  { path: "/doctor-login", name: "connexion-medecin", component: ConnexionMedecinPage },
  { path: "/doctor-register", name: "inscription-medecin", component: InscriptionMedecinPage },
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

  const role = authStore.roleUtilisateur;
  const routeName = String(to.name || "");
  const estPageAuthMedecin = ["connexion-medecin", "inscription-medecin"].includes(routeName);
  const estPageAuthUtilisateur = ["inscription", "inscription-utilisateur", "connexion"].includes(routeName);

  if ((estPageAuthMedecin || estPageAuthUtilisateur) && user) {
    if ((estPageAuthMedecin && role === "medecin") || (estPageAuthUtilisateur && role !== "medecin")) {
      return { name: "tableau-de-bord" };
    }
    authStore.supprimerToken();
    return true;
  }

  if (to.meta.requiresAuth && !user) {
    return { name: "connexion" };
  }

  if (to.name === "profil-sante" && user && authStore.aProfilSante) {
    return { name: "tableau-de-bord" };
  }

  return true;
});

// Export du routeur pour l'utiliser dans l'application Vue
export default router;
