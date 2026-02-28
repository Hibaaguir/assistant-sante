/*
  Point d'entree principal du frontend Vue.
  Ce fichier initialise l'application avec Pinia et le routeur.
  Le montage est volontairement minimal pour garder un demarrage clair.
*/

import { createApp } from "vue";
import { createPinia } from "pinia";
import App from "./App.vue";
import router from "./router";
import "./index.css";

// Bootstrapping de l'application.
createApp(App).use(createPinia()).use(router).mount("#app");
