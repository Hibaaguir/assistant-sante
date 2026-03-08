/*
  Point d'entree principal du frontend Vue.
  Pinia est instancie avant le routeur pour que le guard d'auth
  puisse acceder au store des le premier appel de navigation.
*/

import { createApp } from "vue";
import { createPinia } from "pinia";
import App from "./App.vue";
import router from "./router";
import "./index.css";

const pinia = createPinia();
const app = createApp(App);
app.use(pinia);
app.use(router);
app.mount("#app");
