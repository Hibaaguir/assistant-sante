
import "../css/app.css";

// Import du fichier bootstrap (configuration Axios, CSRF, etc.)
import "./bootstrap";

// Import du composant Vue ProfilSante
import ProfilSante from "./components/ProfilSante.vue";

// Import de la fonction createApp pour créer une application Vue
import { createApp } from "vue";

// Import du composant Vue RegisterForm
import RegisterForm from "./components/RegisterForm.vue";

// Création de l’application Vue
createApp({

  // Déclaration des composants utilisés dans l’application
  components: { RegisterForm, ProfilSante },

})

// Montage de l’application dans la div ayant l’id "app"
.mount("#app");


