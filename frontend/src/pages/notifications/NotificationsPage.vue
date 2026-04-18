<template>
    <div class="w-full px-4 py-8 sm:px-6 lg:px-8">
        <!-- Header -->
        <Typography tag="h1" variant="h1-style"> Notifications </Typography>
        <Typography tag="h4" variant="h5-style">
            Historique de vos rappels et prises manquées
        </Typography>

        <!-- Filtres -->
        <div class="mb-6 flex flex-wrap items-center gap-3">
            <!-- Filtre type -->
            <div class="flex gap-2">
                <BaseButton
                    v-for="f in TYPE_FILTERS"
                    :key="f.value"
                    type="button"
                    variant="outline"
                    size="md"
                    @click="filterType = f.value"
                >
                    {{ f.label }}
                </BaseButton>
            </div>

            <!-- Filtre date -->
            <select
                v-model="filterDays"
                class="h-11 rounded-lg border border-slate-200 bg-white px-5 text-base font-bold text-slate-600 outline-none"
            >
                <option
                    v-for="d in DATE_FILTERS"
                    :key="d.value"
                    :value="d.value"
                >
                    {{ d.label }}
                </option>
            </select>
        </div>

        <!-- Contenu -->
        <Typography
            tag="h3"
            variant="secondary"
            v-if="loading"
            class="py-16 text-center text-sm text-slate-400"
        >
            Chargement...
        </Typography>

        <div
            v-else-if="error"
            class="rounded-2xl border border-red-200 bg-red-50 py-16 text-center text-sm text-red-700"
        >
            {{ error }}
        </div>

        <div
            v-else-if="!filtered.length"
            class="rounded-2xl border border-slate-200 bg-white py-16 text-center text-sm text-slate-400"
        >
            Aucune notification pour ces filtres.
        </div>

        <div v-else class="space-y-3">
            <div
                v-for="notif in filtered"
                :key="notif.id"
                class="flex items-start gap-4 rounded-2xl border bg-white p-4 shadow-sm"
                :class="
                    notif.kind === 'missed'
                        ? 'border-red-100'
                        : 'border-blue-100'
                "
            >
                <!-- Icône -->
                <span
                    class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-full"
                    :class="
                        notif.kind === 'missed'
                            ? 'bg-red-100 text-red-500'
                            : 'bg-blue-100 text-blue-500'
                    "
                >
                    <svg
                        v-if="notif.kind === 'missed'"
                        viewBox="0 0 24 24"
                        class="h-5 w-5"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <circle cx="12" cy="12" r="10" />
                        <path d="M12 8v4m0 4h.01" />
                    </svg>
                    <svg
                        v-else
                        viewBox="0 0 24 24"
                        class="h-5 w-5"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9" />
                        <path d="M13.73 21a2 2 0 0 1-3.46 0" />
                    </svg>
                </span>

                <!-- Texte -->
                <div class="flex-1 min-w-0">
                    <Typography tag="h3" variant="h5-style">
                        {{ notif.message }}
                    </Typography>
                    <p class="mt-1 text-sm text-slate-900">
                        {{ formatDate(notif.target_date) }}
                    </p>
                </div>

                <!-- Badge type -->
                <span
                    class="shrink-0 rounded-full px-3 py-1 text-xs font-semibold"
                    :class="
                        notif.kind === 'missed'
                            ? 'bg-red-100 text-red-600'
                            : 'bg-blue-100 text-blue-600'
                    "
                >
                    {{ notif.kind === "missed" ? "Manquée" : "Rappel" }}
                </span>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { useRouter } from "vue-router";
import api from "@/services/api";
import Typography from "@/components/ui/Typography.vue";
import BaseButton from "@/components/ui/BaseButton.vue";
import { useAuthStore } from "@/stores/auth";

const router = useRouter();
const authStore = useAuthStore();

const TYPE_FILTERS = [
    { value: "all", label: "Toutes" },
    { value: "reminder", label: "Rappels" },
    { value: "missed", label: "Manquées" },
];

const DATE_FILTERS = [
    { value: 7, label: "7 derniers jours" },
    { value: 14, label: "14 derniers jours" },
    { value: 30, label: "30 derniers jours" },
];

const loading = ref(true);
const all = ref([]);
const error = ref("");
const filterType = ref("all");
const filterDays = ref(7);

const filtered = computed(() => {
    const days = Number(filterDays.value);
    const cutoff = new Date();
    cutoff.setDate(cutoff.getDate() - days);
    cutoff.setHours(0, 0, 0, 0);

    return all.value.filter((n) => {
        const matchType =
            filterType.value === "all" || n.kind === filterType.value;
        if (!n.target_date) return matchType;
        const notifDate = new Date(n.target_date + "T00:00:00");
        return matchType && !isNaN(notifDate) && notifDate >= cutoff;
    });
});

function formatDate(dateStr) {
    if (!dateStr) return "";
    return new Date(`${dateStr}T00:00:00`).toLocaleDateString("fr-FR", {
        weekday: "long",
        day: "numeric",
        month: "long",
    });
}

async function load() {
    loading.value = true;
    error.value = "";
    try {
        const { data: res } = await api.get("/notifications");
        all.value = Array.isArray(res?.data) ? res.data : [];
    } catch (e) {
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

onMounted(load);
</script>
