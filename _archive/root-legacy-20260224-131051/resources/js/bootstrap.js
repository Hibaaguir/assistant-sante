import axios from "axios";
window.axios = axios;

window.axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";

const token = document
  .querySelector('meta[name="csrf-token"]')
  ?.getAttribute("content");

if (token) {
  window.axios.defaults.headers.common["X-CSRF-TOKEN"] = token;
}

// Configurer le token d'authentification Sanctum depuis localStorage
const authToken = localStorage.getItem("auth_token");
if (authToken) {
  window.axios.defaults.headers.common["Authorization"] = `Bearer ${authToken}`;
}
