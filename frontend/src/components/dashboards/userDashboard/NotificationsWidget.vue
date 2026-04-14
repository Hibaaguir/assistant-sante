<!--
  NotificationsWidget.vue
  Treatment notification reminders: loading, auto-refresh, mark as read.
-->
<template>
    <section
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

        <template v-else-if="unread.length">
            <!-- Header -->
            <div
                class="mb-4 flex items-center justify-between gap-3 border-b border-slate-100 px-4 py-4"
            >
                <div>
                    <h2
                        class="text-[24px] font-bold leading-none text-slate-900"
                    >
                        Rappels de médicaments
                    </h2>
                    <p class="mt-1.5 text-base font-medium text-slate-600">
                        Rappels quotidiens et doses oubliées détectées
                    </p>
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

            <!-- List -->
            <ul class="space-y-3 p-4">
                <li
                    v-for="n in unread"
                    :key="n.id"
                    class="rounded-xl border px-4 py-3"
                    :class="
                        n.kind === 'missed'
                            ? 'border-red-200 bg-red-50'
                            : 'border-purple-200 bg-purple-50'
                    "
                >
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex-1">
                            <!-- Icon + title -->
                            <div class="flex items-center gap-2">
                                <span class="text-base">
                                    {{ n.kind === "missed" ? "⚠️" : "💊" }}
                                </span>
                                <p
                                    class="text-sm font-bold"
                                    :class="
                                        n.kind === 'missed'
                                            ? 'text-red-800'
                                            : 'text-purple-900'
                                    "
                                >
                                    {{ n.kind === "missed" ? "Traitement oublié" : "Rappel de traitement" }}
                                </p>
                            </div>
                            <!-- Message -->
                            <p class="mt-1 text-xs text-slate-600">
                                {{ n.message }}
                            </p>
                            <p class="mt-2 text-[11px] text-slate-400">
                                {{ formatDate(n.created_at) }}
                            </p>
                        </div>
                        <button
                            type="button"
                            class="mt-1 shrink-0 rounded-md border bg-white px-2 py-1 text-xs font-semibold transition"
                            :class="
                                n.kind === 'missed'
                                    ? 'border-red-200 text-red-600 hover:bg-red-50'
                                    : 'border-purple-200 text-purple-700 hover:bg-purple-100'
                            "
                            @click="markRead(n.id)"
                        >
                            ✓
                        </button>
                    </div>
                </li>
            </ul>
        </template>

        <template v-else>
            <!-- Empty state -->
            <div class="px-4 py-8 text-center">
                <p class="text-sm font-semibold text-slate-800">
                    Aucune notification pour le moment
                </p>
                <p class="mt-1 text-xs text-slate-500">
                    Toutes vos notifications ont été lues
                </p>
            </div>
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

// ─── State ────────────────────────────────────────────────────────────────────
const notifications = ref([]);
const loading = ref(false);
const error = ref("");
let timer = null;

const unread = computed(() => notifications.value.filter((n) => !n.read_at));

// ─── Loading ──────────────────────────────────────────────────────────────────
async function load(silent = false) {
    if (!silent) loading.value = true;
    error.value = "";
    try {
        const { data } = await api.get("/notifications");
        notifications.value = Array.isArray(data?.data) ? data.data : [];
    } catch (e) {
        if (e?.response?.status === 401) {
            await authStore.logout({ callApi: false });
            await router.replace({ name: "login" });
            return;
        }
        error.value = "Impossible de charger les notifications pour le moment."; //
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

// ─── Utility ──────────────────────────────────────────────────────────────────
function formatDate(val) {
    if (!val) return "";
    const d = new Date(val);
    return isNaN(d)
        ? ""
        : d.toLocaleString("en-US", {
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
