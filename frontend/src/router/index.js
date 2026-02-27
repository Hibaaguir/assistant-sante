import { createRouter, createWebHistory } from "vue-router";
import RegisterForm from "@/components/register/RegisterForm.vue";
import ProfilSante from "@/components/profil-sante/ProfilSante.vue";
import MainLayout from "@/layouts/MainLayout.vue";
import JournalHome from "@/pages/journal/JournalHome.vue";
import JournalAssistant from "@/pages/journal/JournalAssistant.vue";
import JournalHistory from "@/pages/journal/JournalHistory.vue";
import HealthProfilePage from "@/pages/health/HealthProfilePage.vue";
import HealthDataPage from "@/pages/health/HealthDataPage.vue";
import Placeholder from "@/pages/Placeholder.vue";
import api from "@/services/api";

const routes = [
  { path: "/", redirect: "/register" },
  { path: "/register", name: "register", component: RegisterForm },
  { path: "/profil-sante", name: "profil-sante", component: ProfilSante, meta: { requiresAuth: true } },
  {
    path: "/main",
    component: MainLayout,
    meta: { requiresAuth: true },
    children: [
      { path: "", name: "main", redirect: { name: "health" } },
      { path: "dashboard", name: "dashboard", component: Placeholder, props: { title: "Dashboard" } },
      { path: "journal", name: "journal-home", component: JournalHome },
      { path: "health-data", name: "health-data", component: HealthDataPage },
      { path: "journal/new", name: "journal-wizard", component: JournalAssistant },
      { path: "journal/history", name: "journal-history", component: JournalHistory },
      { path: "health", name: "health", component: HealthProfilePage },
      { path: "ai", name: "ai", component: Placeholder, props: { title: "Recommandations IA" } },
      { path: "doctor", name: "doctor", component: Placeholder, props: { title: "Vue medecin" } },
    ],
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

let authCheckInFlight = null;

async function getAuthState() {
  const token = localStorage.getItem("auth_token");
  if (!token) return { isAuth: false, hasProfil: false };

  if (!authCheckInFlight) {
    authCheckInFlight = api
      .get("/auth/me")
      .then((res) => ({
        isAuth: true,
        hasProfil: Boolean(res?.data?.has_profil_sante),
      }))
      .catch(() => {
        localStorage.removeItem("auth_token");
        return { isAuth: false, hasProfil: false };
      })
      .finally(() => {
        authCheckInFlight = null;
      });
  }

  return authCheckInFlight;
}

router.beforeEach(async (to) => {
  // Keep register page always reachable as landing page.
  if (to.name === "register") {
    return true;
  }

  const state = await getAuthState();

  if (to.meta.requiresAuth && !state.isAuth) {
    return { name: "register" };
  }

  if (to.name === "profil-sante" && state.isAuth && state.hasProfil) {
    return { name: "health" };
  }

  if (to.path.startsWith("/main") && state.isAuth && !state.hasProfil) {
    return { name: "profil-sante" };
  }

  return true;
});

export default router;
