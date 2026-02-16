import "../css/app.css";
import "./bootstrap";
import ProfilSante from "./components/ProfilSante.vue";

import { createApp } from "vue";
import RegisterForm from "./components/RegisterForm.vue";

createApp({
  components: { RegisterForm, ProfilSante },
}).mount("#app");

