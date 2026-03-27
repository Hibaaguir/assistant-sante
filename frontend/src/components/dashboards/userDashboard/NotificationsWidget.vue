<!--
  NotificationsWidget.vue
  Notifications de traitements persistantes : chargement, refresh auto, marquer comme lue.
-->
<template>
    <section
        v-if="loading || error || unread.length"
        class="mt-5 rounded-2xl border border-slate-200 bg-white shadow-sm"
    >
        <div v-if="loading" class="px-4 py-3 text-sm text-slate-600">
            Chargement des notifications...
        </div>

        <div
            v-else-if="error"
            class="rounded-2xl border border-red-100 bg-red-50 px-4 py-3 text-sm text-red-700"
        >
            {{ error }}
        </div>

        <template v-else>
            <!-- En-tête -->
            <div
                class="mb-4 flex items-center justify-between gap-3 border-b border-slate-100 px-4 py-4"
            >
                <div>
                    <h2
                        class="text-[22px] font-semibold leading-none text-slate-900"
                    >
                        Notifications traitements
                    </h2>
                    <p class="mt-1 text-sm text-slate-600">
                        Rappels journaliers et oublis détectés
                    </p>
                </div>
                <button
                    type="button"
                    class="rounded-xl border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50"
                    :disabled="!unread.length"
                    @click="markAllRead"
                >
                    Tout marquer lu
                </button>
            </div>

            <!-- Liste -->
            <ul class="space-y-2 p-4">
                <li
                    v-for="n in unread"
                    :key="n.id"
                    class="rounded-lg border border-blue-200 bg-blue-50 px-3 py-2"
                >
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-slate-900">
                                {{ n.data?.title || "Notification" }}
                            </p>
                            <p class="mt-0.5 text-xs text-slate-700">
                                {{ n.data?.message || "" }}
                            </p>
                            <p class="mt-1 text-xs text-slate-500">
                                {{ formatDate(n.created_at) }}
                            </p>
                        </div>
                        <button
                            type="button"
                            class="mt-1 rounded-md border border-blue-200 bg-white px-2 py-1 text-xs font-semibold text-blue-700 transition hover:bg-blue-100"
                            @click="markRead(n.id)"
                        >
                            ✓
                        </button>
                    </div>
                </li>
            </ul>
        </template>
    </section>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref } from "vue";
import { useRouter } from "vue-router";
import api from "@/services/api";
import { useAuthStore } from "@/stores/auth";

const router = useRouter();
const authStore = useAuthStore();

// ─── État ─────────────────────────────────────────────────────────────────────
const notifications = ref([]);
const loading = ref(false);
const error = ref("");
let timer = null;

const unread = computed(() => notifications.value.filter((n) => !n.read_at));

// ─── Chargement ───────────────────────────────────────────────────────────────
async function load(silent = false) {
    if (!silent) loading.value = true;
    error.value = "";
    try {
        const { data } = await api.get("/notifications");
        notifications.value = Array.isArray(data?.data) ? data.data : [];
    } catch (e) {
        if (e?.response?.status === 401) {
            await authStore.deconnexion({ appelerApi: false });
            await router.replace({ name: "connexion" });
            return;
        }
        error.value = "Impossible de charger les notifications pour le moment.";
    } finally {
        loading.value = false;
    }
}

// ─── Actions ──────────────────────────────────────────────────────────────────
async function markRead(id) {
    try {
        await api.post(`/notifications/${id}/read`);
        const now = new Date().toISOString();
        notifications.value = notifications.value.map((n) =>
            n.id === id ? { ...n, read_at: n.read_at || now } : n,
        );
    } catch {
        error.value = "Impossible de marquer cette notification comme lue.";
    }
}

async function markAllRead() {
    try {
        await api.post("/notifications/read-all");
        const now = new Date().toISOString();
        notifications.value = notifications.value.map((n) => ({
            ...n,
            read_at: n.read_at || now,
        }));
    } catch {
        error.value =
            "Impossible de marquer toutes les notifications comme lues.";
    }
}

// ─── Utilitaire ───────────────────────────────────────────────────────────────
function formatDate(val) {
    if (!val) return "";
    const d = new Date(val);
    return isNaN(d)
        ? ""
        : d.toLocaleString("fr-FR", {
              day: "2-digit",
              month: "short",
              hour: "2-digit",
              minute: "2-digit",
          });
}

// ─── Lifecycle ────────────────────────────────────────────────────────────────
onMounted(() => {
    load();
    timer = setInterval(() => load(true), 60_000);
});

onUnmounted(() => {
    clearInterval(timer);
    timer = null;
});
</script>
