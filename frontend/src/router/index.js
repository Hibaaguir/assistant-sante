/*
  Configuration centrale des routes de l'application.
  Le guard global gere l'acces selon l'etat d'authentification et du profil sante.
  Un cache court ("authCheckInFlight") evite les appels /auth/me en doublon.
*/

import { createRouter, createWebHistory } from "vue-router";
import RegisterForm from "@/components/register/RegisterForm.vue";
import LoginForm from "@/components/login/LoginForm.vue";
import RegisterRolePage from "@/pages/auth/RegisterRolePage.vue";
import ProfilSante from "@/components/profil-sante/ProfilSante.vue";
import MainLayout from "@/layouts/MainLayout.vue";
import JournalHome from "@/pages/journal/JournalHome.vue";
import JournalAssistant from "@/pages/journal/JournalAssistant.vue";
import JournalHistory from "@/pages/journal/JournalHistory.vue";
import HealthProfilePage from "@/pages/health/HealthProfilePage.vue";
import HealthDataPage from "@/pages/health/HealthDataPage.vue";
import DashboardPage from "@/pages/DashboardPage.vue";
import DoctorLoginPage from "@/pages/doctor/DoctorLoginPage.vue";
import DoctorRegisterPage from "@/pages/doctor/DoctorRegisterPage.vue";
import Placeholder from "@/pages/Placeholder.vue";
import api from "@/services/api";

const routes = [
  { path: "/", redirect: "/login" },
  { path: "/login", name: "login", component: LoginForm },
  { path: "/register", name: "register", component: RegisterRolePage },
  { path: "/register/user", name: "user-register", component: RegisterForm },
  { path: "/doctor-login", name: "doctor-login", component: DoctorLoginPage },
  { path: "/doctor-register", name: "doctor-register", component: DoctorRegisterPage },
  { path: "/profil-sante", name: "profil-sante", component: ProfilSante, meta: { requiresAuth: true } },
  {
    path: "/main",
    component: MainLayout,
    meta: { requiresAuth: true },
    children: [
      { path: "", name: "main", redirect: { name: "dashboard" } },
      { path: "dashboard", name: "dashboard", component: DashboardPage },
      { path: "journal", name: "journal-home", component: JournalHome },
      { path: "health-data", name: "health-data", component: HealthDataPage },
      { path: "journal/new", name: "journal-wizard", component: JournalAssistant },
      { path: "journal/history", name: "journal-history", component: JournalHistory },
      { path: "health", name: "health", component: HealthProfilePage },
      { path: "ai", name: "ai", component: Placeholder, props: { title: "Recommandations IA" } },
    ],
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

// Promesse partagee pour eviter plusieurs verifications auth simultanees.
let authCheckInFlight = null;

async function getAuthState() {
  const token = localStorage.getItem("auth_token");
  if (!token) {
    return {
      isAuth: false,
      hasProfil: false,
      role: null,
    };
  }

  if (!authCheckInFlight) {
    authCheckInFlight = api
      .get("/auth/me")
      .then((res) => ({
        isAuth: true,
        hasProfil: Boolean(res?.data?.has_profil_sante),
        role: String(res?.data?.user?.role || "").toLowerCase() || null,
      }))
      .catch(() => {
        localStorage.removeItem("auth_token");
        return {
          isAuth: false,
          hasProfil: false,
          role: null,
        };
      })
      .finally(() => {
        authCheckInFlight = null;
      });
  }

  return authCheckInFlight;
}

function clearLocalAuth() {
  localStorage.removeItem("auth_token");
  if (api.defaults.headers.common.Authorization) {
    delete api.defaults.headers.common.Authorization;
  }
}

router.beforeEach(async (to) => {
  const state = await getAuthState();
  const routeName = String(to.name || "");
  const isDoctorAuthPage = ["doctor-login", "doctor-register"].includes(routeName);
  const isUserAuthPage = ["register", "user-register", "login"].includes(routeName);
  const isAuthPage = isDoctorAuthPage || isUserAuthPage;

  if (isAuthPage && state.isAuth) {
    if ((isDoctorAuthPage && state.role === "medecin") || (isUserAuthPage && state.role === "user")) {
      return { name: "dashboard" };
    }

    // Switching between patient and doctor entry paths must not reuse the wrong session.
    clearLocalAuth();
    return true;
  }

  if (to.meta.requiresAuth && !state.isAuth) {
    return { name: "login" };
  }

  if (to.name === "profil-sante" && state.isAuth && state.hasProfil) {
    return { name: "dashboard" };
  }

  return true;
});

export default router;
