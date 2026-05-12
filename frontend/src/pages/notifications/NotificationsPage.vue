<template>
    <div class="w-full px-4 py-8 sm:px-6 lg:px-8">
        <!-- Header: titre principal + sous-titre de la page -->
        <Typography tag="h1" variant="h1-style"> Notifications </Typography>
        <Typography tag="h4" variant="h5-style">
            Historique de vos rappels et prises manquées
        </Typography>

        <!-- Zone des filtres (type + période) -->
        <div class="mb-6 flex flex-wrap items-center gap-3">
            <!-- Filtre type: change filterType pour recalculer filtered -->
            <div class="flex gap-2">
                <BaseButton
                    v-for="f in TYPE_FILTERS"
                    :key="f.value"
                    type="button"
                    :variant="filterType === f.value ? 'primary' : 'outline'"
                    size="md"
                    @click="filterType = f.value"
                >
                    {{ f.label }}
                </BaseButton>
            </div>

            <!-- Filtre date: change filterDays (7, 14, 30 jours) -->
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

        <!-- Contenu: état de chargement -->
        <Typography
            tag="h3"
            variant="secondary"
            v-if="loading"
            class="py-16 text-center text-sm text-slate-400"
        >
            Chargement...
        </Typography>

        <!-- Contenu: état d'erreur API -->
        <div
            v-else-if="error"
            class="rounded-2xl border border-red-200 bg-red-50 py-16 text-center text-sm text-red-700"
        >
            {{ error }}
        </div>

        <!-- Contenu: aucun résultat après application des filtres -->
        <div
            v-else-if="!filtered.length"
            class="rounded-2xl border border-slate-200 bg-white py-16 text-center text-sm text-slate-400"
        >
            Aucune notification pour ces filtres.
        </div>

        <!-- Contenu: liste des notifications filtrées -->
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
                <!-- Icône dynamique selon le type (missed/reminder) -->
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
                        <!-- Cercle + point d'exclamation alerte -->
                        <circle cx="12" cy="12" r="10" />
                        <path d="M12 8v4m0 4h.01" />
                    </svg>
                    <!-- Sinon, afficher l'icône cloche pour un rappel -->
                    <svg
                        v-else
                        viewBox="0 0 24 24"
                        class="h-5 w-5"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <!-- Forme de la cloche -->
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9" />
                        <!-- Base de la cloche -->
                        <path d="M13.73 21a2 2 0 0 1-3.46 0" />
                    </svg>
                </span>

                <!-- Texte de la notification: message + date formatée -->
                <div class="flex-1 min-w-0">
                    <Typography tag="h3" variant="h5-style">
                        {{ notif.message }}
                    </Typography>
                    <p class="mt-1 text-sm text-slate-900">
                        {{ formatLongDate(notif.target_date) }}
                    </p>
                </div>

                <!-- Badge type: libellé et style visuel selon notif.kind -->
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
import { formatLongDate } from "@/components/doctors/doctorUtilities.js";

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
// Filtrer les notifications selon le type et la date
const filtered = computed(() => {
    const days = Number(filterDays.value);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const cutoff = new Date(today);
    cutoff.setDate(today.getDate() - days); //aujourdui - nombre de jours sélectionné

    return all.value
        .filter((n) => {
            const matchType =
                filterType.value === "all" || n.kind === filterType.value;
            const notifDate = new Date(n.target_date + "T00:00:00");
            return matchType && notifDate >= cutoff;
        }) //pour trier les notifications du plus récent au plus ancien
        .sort((a, b) => new Date(b.target_date) - new Date(a.target_date));
});

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
