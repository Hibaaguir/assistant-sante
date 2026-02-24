import { createRouter, createWebHistory } from "vue-router";
import RegisterForm from "@/components/register/RegisterForm.vue";
import ProfilSante from "@/components/profil-sante/ProfilSante.vue";

const routes = [
  {
    path: "/",
    redirect: "/register",
  },
  {
    path: "/register",
    component: RegisterForm,
  },
  {
    path: "/profil-sante",
    component: ProfilSante,
  },
];

export default createRouter({
  history: createWebHistory(),
  routes,
});

