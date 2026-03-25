// Importation de la bibliothèque Axios pour effectuer des requêtes HTTP vers l'API
import axios from "axios";

// Création d'une instance Axios configurée avec l'URL de base de l'API et les headers par défaut
const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || "http://localhost:8000/api",
  headers: {
    "Content-Type": "application/json",
    Accept: "application/json",
    "X-Requested-With": "XMLHttpRequest",
  },
});

// Intercepteur de requête permettant d'ajouter automatiquement le token d'authentification dans le header Authorization
api.interceptors.request.use((config) => {
  const token = localStorage.getItem("auth_token");
  if (token) config.headers.Authorization = `Bearer ${token}`;
  return config;
});

// Intercepteur de reponse pour eviter les boucles de requetes 401.
api.interceptors.response.use(
  (response) => response,
  (error) => {
    const status = error?.response?.status;
    const requeteUrl = String(error?.config?.url || "");
    const estTentativeConnexion = /\/auth\/(login|connexion|register|inscription)/.test(requeteUrl);

    if (status === 401 && !estTentativeConnexion) {
      localStorage.removeItem("auth_token");
    }

    return Promise.reject(error);
  }
);

// Export de l'instance Axios configurée pour être utilisée dans toute l'application
export default api;