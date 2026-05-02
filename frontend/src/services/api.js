// Import Axios for HTTP requests to the API
import axios from "axios";

// Create an Axios instance configured with the API base URL and default headers
const api = axios.create({
    baseURL: import.meta.env.VITE_API_URL || "http://localhost:8000/api",
    headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
        "X-Requested-With": "XMLHttpRequest",
    },
});

// Request interceptor: attach the auth token to every outgoing request
api.interceptors.request.use((config) => {
    const token = localStorage.getItem("auth_token");
    if (token) config.headers.Authorization = `Bearer ${token}`;
    return config;//ce qui va etre envoyé au backend
});

// Response interceptor: clear token on 401 to avoid infinite redirect loops
api.interceptors.response.use(
    (response) => response,
    (error) => {
        const status = error?.response?.status;
        const requestUrl = String(error?.config?.url || "");
        const isLoginAttempt =
            /\/auth\/(login|connexion|register|inscription)/.test(requestUrl);

        if (status === 401 && !isLoginAttempt) {
            localStorage.removeItem("auth_token");
        }

        return Promise.reject(error);
    },
);

// Export the configured Axios instance
export default api;
