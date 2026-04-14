// Vue Router configuration: application routes, component imports, and access guards
import { createRouter, createWebHistory } from "vue-router";
import { useAuthStore } from "@/stores/auth";
import RegistrationForm from "@/components/registration/RegistrationForm.vue";
import LoginForm from "@/components/login/LoginForm.vue";
import ForgotPasswordPage from "@/pages/auth/ForgotPasswordPage.vue";
import ResetPasswordPage from "@/pages/auth/ResetPasswordPage.vue";
import HealthProfile from "@/components/health-profile/HealthProfile.vue";
import AppLayout from "@/layouts/AppLayout.vue";
import JournalHome from "@/pages/journal/JournalHome.vue";
import JournalAssistant from "@/pages/journal/JournalAssistant.vue";
import JournalHistory from "@/pages/journal/JournalHistory.vue";
import HealthProfilePage from "@/pages/health/HealthProfilePage.vue";
import HealthDataPage from "@/pages/health/HealthDataPage.vue";
import DashboardPage from "@/pages/userDashboard/DashboardPage.vue";
import DoctorRegistrationPage from "@/pages/doctor/DoctorRegistrationPage.vue";
import PlaceholderPage from "@/pages/PlaceholderPage.vue";
import PublicHomePage from "@/pages/home/PublicHomePage.vue";
import NotificationsPage from "@/pages/notifications/NotificationsPage.vue";

// All application routes with their components and access guards
const routes = [
    { path: "/", name: "public-home", component: PublicHomePage },
    { path: "/login", name: "login", component: LoginForm },
    {
        path: "/register",
        name: "register",
        component: RegistrationForm,
    },
    {
        path: "/forgot-password",
        name: "forgot-password",
        component: ForgotPasswordPage,
    },
    {
        path: "/reset-password",
        name: "reset-password",
        component: ResetPasswordPage,
    },
    // Legacy URL redirects
    { path: "/register/user", redirect: "/register" },
    { path: "/oublier-mot-de-passe", redirect: "/forgot-password" },
    { path: "/reinitialiser-mot-de-passe", redirect: "/reset-password" },
    { path: "/health-profile", component: HealthProfile, meta: { requiresAuth: true } },
    {
        path: "/doctor-register",
        name: "doctor-register",
        component: DoctorRegistrationPage,
    },
    { path: "/doctor-login", redirect: "/login" },
    {
        path: "/profil-sante",
        name: "health-profile",
        component: HealthProfile,
        meta: { requiresAuth: true },
    },
    {
        path: "/main",
        component: AppLayout,
        meta: { requiresAuth: true },
        children: [
            {
                path: "",
                name: "home",
                redirect: { name: "dashboard" },
            },
            {
                path: "dashboard",
                name: "dashboard",
                component: DashboardPage,
            },
            { path: "journal", name: "journal", component: JournalHome },
            {
                path: "health-data",
                name: "health-data",
                component: HealthDataPage,
            },
            {
                path: "journal/new",
                name: "journal-assistant",
                component: JournalAssistant,
            },
            {
                path: "journal/history",
                name: "journal-history",
                component: JournalHistory,
            },
            {
                path: "health",
                name: "health-settings",
                component: HealthProfilePage,
            },
            {
                path: "ai",
                name: "ai-recommendations",
                component: PlaceholderPage,
                props: { title: "Recommandations IA" },
            },
            {
                path: "notifications",
                name: "notifications",
                component: NotificationsPage,
            },
        ],
    },
];

// Create the router instance with HTML5 history mode
const router = createRouter({
    history: createWebHistory(),
    routes,
});

// Global navigation guard: handles authentication and route access control
router.beforeEach(async (to) => {
    const authStore = useAuthStore();
    const user = await authStore.loadUser();

    const defaultAuthenticatedRoute = () => {
        if (authStore.isAdmin) {
            return { name: "dashboard" };
        }
        if (authStore.isInDoctorSpace) {
            return { name: "dashboard" };
        }
        return authStore.hasHealthProfile
            ? { name: "dashboard" }
            : { name: "health-profile" };
    };

    const routeName = String(to.name || "");
    const isPublicAuthPage = [
        "register",
        "login",
        "public-home",
    ].includes(routeName);

    if (isPublicAuthPage && user) {
        return defaultAuthenticatedRoute();
    }

    // Doctor registration: always accessible unless visitor is already a registered doctor
    if (routeName === "doctor-register" && user && authStore.isInDoctorSpace) {
        return { name: "dashboard" };
    }

    if (to.meta.requiresAuth && !user) {
        return { name: "login" };
    }

    if (
        user &&
        authStore.isInDoctorSpace &&
        to.name !== "dashboard"
    ) {
        return { name: "dashboard" };
    }

    if (
        to.meta.requiresAuth &&
        user &&
        authStore.isInUserSpace &&
        !authStore.isAdmin &&
        !authStore.hasHealthProfile &&
        to.name !== "health-profile"
    ) {
        return { name: "health-profile" };
    }

    if (to.name === "health-profile" && user && authStore.isAdmin) {
        return { name: "dashboard" };
    }

    if (
        to.name === "health-profile" &&
        user &&
        authStore.hasHealthProfile &&
        authStore.isInUserSpace
    ) {
        return { name: "dashboard" };
    }

    return true;
});

export default router;
