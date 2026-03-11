// Point d'entrée principal de l'application Vue : initialisation de l'application, du store Pinia et du routeur
import { createApp } from "vue";
import { createPinia } from "pinia";
import App from "./App.vue";
import router from "./router";
import "./index.css";

// Création de l'instance du store global Pinia pour la gestion de l'état de l'application
const pinia = createPinia();

// Création de l'application Vue à partir du composant racine App
const app = createApp(App);

// Enregistrement du store Pinia dans l'application
app.use(pinia);

// Enregistrement du routeur pour gérer la navigation entre les pages
app.use(router);

// Montage de l'application dans l'élément HTML ayant l'id "app"
app.mount("#app");