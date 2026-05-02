<!-- Widget de notifications : rappels de médicaments pris ou oubliés -->
<template>
    <section class="mt-5 rounded-2xl border-2 border-blue-300 bg-white shadow-sm transition-all duration-300 hover:shadow-lg hover:border-blue-400">

        <!-- Message pendant le chargement -->
        <Typography v-if="loading" tag="h4" variant="secondary" class="px-4 py-3 text-sm text-slate-700">
            Chargement des notifications...
        </Typography>

        <!-- Message d'erreur en rouge -->
        <div v-else-if="error" class="rounded-2xl border border-red-100 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ error }}
        </div>

        <!-- Liste des notifications non lues -->
        <template v-else-if="unread.length">
            <!-- En-tête avec titre et bouton "tout marquer comme lu" -->
            <div class="mb-4 flex items-center justify-between gap-3 border-b border-slate-100 px-4 py-4">
                <div>
                    <Typography tag="h2" variant="h2-style">Rappels de médicaments</Typography>
                    <Typography tag="h3" variant="default" class="mt-1.5">Rappels quotidiens et doses oubliées détectées</Typography>
                </div>
                <button
                    type="button"
                    class="rounded-xl border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50"
                    :disabled="!unread.length"
                    @click="markAllRead"
                >
                    Tout marquer comme lu
                </button>
            </div>

            <!-- Chaque notification dans la liste -->
            <ul class="space-y-3 p-4">
                <li
                    v-for="n in unread"
                    :key="n.id"
                    class="rounded-xl border px-4 py-3"
                    :class="n.kind === 'missed' ? 'border-red-200 bg-red-50' : 'border-purple-200 bg-purple-50'"
                >
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex-1">
                            <!-- Icône + titre selon le type de notification -->
                            <div class="flex items-center gap-2">
                                <span class="text-base">{{ n.kind === "missed" ? "⚠️" : "💊" }}</span>
                                <Typography tag="h3" variant="mauve">
                                    {{ n.kind === "missed" ? "Traitement oublié" : "Rappel de traitement" }}
                                </Typography>
                            </div>
                            <!-- Message et date de la notification -->
                            <Typography tag="h4" variant="h4-style" class="mt-1.5">{{ n.message }}</Typography>
                            <Typography tag="h4" variant="default">{{ formatDate(n.target_date, n.kind) }}</Typography>
                        </div>
                    </div>
                </li>
            </ul>
        </template>

        <!-- Message si toutes les notifications sont lues -->
        <template v-else>
            <div class="px-4 py-8 text-center">
                <Typography tag="h2" variant="h2-style">Aucune notification pour le moment</Typography>
                <Typography tag="h3" variant="secondary">Toutes vos notifications ont été lues</Typography>
            </div>
        </template>
    </section>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref } from "vue";
import { useRouter } from "vue-router";
import api from "@/services/api";
import { useAuthStore } from "@/stores/auth";
import Typography from "@/components/ui/Typography.vue";

const router    = useRouter();
const authStore = useAuthStore();

// Données et état du composant
const notifications = ref([]);  // toutes les notifications reçues
const loading       = ref(false); // true pendant le chargement
const error         = ref("");  // message d'erreur si l'appel échoue
let timer           = null;     // timer pour le rafraîchissement automatique

// Filtrer uniquement les notifications non lues
const unread = computed(() => notifications.value.filter((n) => !n.read_at));

// Charge les notifications depuis l'API (silent = true pour ne pas afficher le spinner)
async function load(silent = false) {
    if (!silent) loading.value = true;
    error.value = "";
    try {
        const { data } = await api.get("/notifications");
        notifications.value = Array.isArray(data?.data) ? data.data : [];
    } catch (e) {
        // Si l'utilisateur n'est plus connecté, le rediriger vers la page de connexion
        if (e?.response?.status === 401) {
            await authStore.logout({ callApi: false });
            await router.replace({ name: "login" });
            return;
        }
        error.value = "Impossible de charger les notifications pour le moment.";
    } finally {
        loading.value = false;
    }
}

// Marque toutes les notifications comme lues
async function markAllRead() {
    try {
        await api.post("/notifications/read-all");
        const now = new Date().toISOString();
        // Mettre à jour localement sans recharger
        notifications.value = notifications.value.map((n) => ({
            ...n,
            read_at: n.read_at || now,
        }));
        router.push({ name: "notifications" });
    } catch {
        error.value = "Impossible de marquer toutes les notifications comme lues.";
    }
}

// Formate la date de la notification selon son type : reminder → 08:00, missed → 21:00
function formatDate(targetDate, kind) {
    if (!targetDate) return "";
    const hour = kind === "missed" ? 21 : 8;
    const d = new Date(`${targetDate}T${String(hour).padStart(2, "0")}:00:00`);
    return isNaN(d) ? "" : d.toLocaleString("fr-FR", {
        day: "2-digit", month: "short", hour: "2-digit", minute: "2-digit",
    });
}

// Charger au démarrage et rafraîchir automatiquement toutes les 60 secondes
onMounted(() => {
    load();
    timer = setInterval(() => load(true), 60_000);
});

// Annuler le timer quand le composant est retiré (évite les fuites mémoire)
onUnmounted(() => {
    clearInterval(timer);
    timer = null;
});
</script>
