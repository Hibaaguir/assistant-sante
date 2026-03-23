<!--
  TableauDeBordPatient.vue
  Dashboard principal du patient : affiche un graphique SVG interactif
  montrant l'évolution des signes vitaux sur les 7 derniers jours.
  Chaque courbe (rythme cardiaque, tension, saturation O₂) est rendue
  par son propre composant dédié. Les données sont récupérées depuis
  l'API /health-data/overview.
-->
<template>
    <div class="mx-auto max-w-[1320px] p-4 sm:p-6 lg:p-8">
        <header class="flex items-center justify-between">
            <div>
                <h1
                    class="text-[34px] font-semibold leading-none text-slate-900"
                >
                    Dashboard
                </h1>
                <p class="mt-2 text-sm text-slate-600">
                    Vue d'ensemble de votre santé
                </p>
            </div>
        </header>
        <NotificationsEnLigne />

        <ModificationProfilModal
            :est-ouvert="modalProfilOuvert"
            @fermer="modalProfilOuvert = false"
            @profil-mis-a-jour="chargerDonneesSante"
        />

        <section
            class="mt-5 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm"
        >
            <div
                class="mb-4 flex items-center justify-between gap-3 border-b border-slate-100 pb-4"
            >
                <div>
                    <h2
                        class="text-[22px] font-semibold leading-none text-slate-900"
                    >
                        Notifications traitements
                    </h2>
                    <p class="mt-1 text-sm text-slate-600">
                        Rappels journaliers et oublis detectes
                    </p>
                </div>
                <button
                    type="button"
                    class="rounded-xl border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50"
                    :disabled="nombreNonLues === 0 || chargementNotifications"
                    @click="marquerToutLu"
                >
                    Tout marquer lu
                </button>
            </div>

            <div
                v-if="chargementNotifications"
                class="rounded-xl border border-slate-100 bg-slate-50 px-4 py-3 text-sm text-slate-600"
            >
                Chargement des notifications...
            </div>

            <div
                v-else-if="erreurNotification"
                class="rounded-xl border border-red-100 bg-red-50 px-4 py-3 text-sm text-red-700"
            >
                {{ erreurNotification }}
            </div>

            <ul v-else-if="notificationsVisibles.length > 0" class="space-y-3">
                <li
                    v-for="notification in notificationsVisibles"
                    :key="notification.id"
                    class="rounded-xl border px-4 py-3"
                    :class="'border-blue-200 bg-blue-50'"
                >
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold text-slate-900">
                                {{ notification.data?.title || "Notification" }}
                            </p>
                            <p class="mt-1 text-sm text-slate-700">
                                {{ notification.data?.message || "" }}
                            </p>
                            <p class="mt-2 text-xs text-slate-500">
                                {{
                                    formaterDateHeureNotification(
                                        notification.created_at,
                                    )
                                }}
                            </p>
                        </div>

                        <button
                            type="button"
                            class="rounded-lg border border-blue-200 bg-white px-2.5 py-1 text-xs font-semibold text-blue-700 transition hover:bg-blue-100"
                            @click="marquerNotificationLue(notification.id)"
                        >
                            Marquer lue
                        </button>
                    </div>
                </li>
            </ul>

            <div
                v-else
                class="rounded-xl border border-slate-100 bg-slate-50 px-4 py-3 text-sm text-slate-600"
            >
                Aucune notification pour le moment.
            </div>
        </section>

        <section
            class="mt-5 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm"
        >
            <div class="mb-3 flex items-center justify-between">
                <h2
                    class="text-[34px] font-semibold leading-none text-slate-900"
                >
                    Évolution - 7 derniers jours
                </h2>
                <div class="flex items-center gap-2 text-[12px] font-medium">
                    <button
                        type="button"
                        class="rounded-xl border px-3 py-1.5 transition"
                        :class="
                            selectedSeries.rhythm
                                ? 'border-rose-500 bg-white text-rose-600'
                                : 'border-slate-200 bg-slate-100 text-slate-500'
                        "
                        @click="basculerSerie('rhythm')"
                    >
                        Rythme
                    </button>
                    <button
                        type="button"
                        class="rounded-xl border px-3 py-1.5 transition"
                        :class="
                            selectedSeries.tension
                                ? 'border-blue-500 bg-white text-blue-600'
                                : 'border-slate-200 bg-slate-100 text-slate-500'
                        "
                        @click="basculerSerie('tension')"
                    >
                        Tension
                    </button>
                    <button
                        type="button"
                        class="rounded-xl border px-3 py-1.5 transition"
                        :class="
                            selectedSeries.saturation
                                ? 'border-violet-500 bg-white text-violet-600'
                                : 'border-slate-200 bg-slate-100 text-slate-500'
                        "
                        @click="basculerSerie('saturation')"
                    >
                        Saturation
                    </button>
                </div>
            </div>

            <div
                ref="chartRef"
                class="relative overflow-x-auto"
                @mousemove="gererMouvementGraphique"
                @mouseleave="gererSortieGraphique"
            >
                <svg
                    :viewBox="`0 0 ${chart.width} ${chart.height}`"
                    class="h-[300px] w-full min-w-[980px]"
                >
                    <!-- Grille -->
                    <g stroke="#e2e8f0" stroke-dasharray="4 4">
                        <line
                            v-for="tick in yTicks"
                            :key="`h-${tick}`"
                            :x1="chart.left"
                            :y1="convertirYEnPx(tick)"
                            :x2="chart.width - chart.right"
                            :y2="convertirYEnPx(tick)"
                        />
                        <line
                            v-for="(label, i) in labels"
                            :key="`v-${label}-${i}`"
                            :x1="convertirXEnPx(i)"
                            :y1="chart.top"
                            :x2="convertirXEnPx(i)"
                            :y2="chart.height - chart.bottom"
                        />
                    </g>

                    <!-- Ligne de survol verticale -->
                    <line
                        v-if="hoverIndex !== null"
                        :x1="convertirXEnPx(hoverIndex)"
                        :y1="chart.top"
                        :x2="convertirXEnPx(hoverIndex)"
                        :y2="chart.height - chart.bottom"
                        stroke="#cbd5e1"
                        stroke-width="1.5"
                    />

                    <!-- Courbes des signes vitaux -->
                    <CourbeRythmeCardiaque
                        v-if="selectedSeries.rhythm"
                        :values="heartRateValues"
                        :hover-index="hoverIndex"
                        :chart="chart"
                    />
                    <CourbeTensionArterielle
                        v-if="selectedSeries.tension"
                        :values="systolicValues"
                        :hover-index="hoverIndex"
                        :chart="chart"
                    />
                    <CourbeSaturationO2
                        v-if="selectedSeries.saturation"
                        :values="saturationValues"
                        :hover-index="hoverIndex"
                        :chart="chart"
                    />

                    <!-- Axe Y -->
                    <g fill="#94a3b8" font-size="13">
                        <text
                            v-for="tick in yTicks"
                            :key="`y-${tick}`"
                            :x="chart.left - 22"
                            :y="convertirYEnPx(tick) + 4"
                        >
                            {{ tick }}
                        </text>
                    </g>
                    <!-- Axe X -->
                    <g fill="#94a3b8" font-size="14">
                        <text
                            v-for="(label, i) in labels"
                            :key="`x-${label}-${i}`"
                            :x="convertirXEnPx(i) - 24"
                            :y="chart.height - 8"
                        >
                            {{ label }}
                        </text>
                    </g>
                </svg>

                <!-- Tooltip au survol -->
                <div
                    v-if="hoverIndex !== null"
                    class="pointer-events-none absolute rounded-2xl border border-slate-200 bg-white/95 px-4 py-3 text-[12px] shadow-lg"
                    :style="{
                        left: `${tooltipLeft}px`,
                        top: `${TOOLTIP_TOP}px`,
                    }"
                >
                    <p class="text-slate-900">{{ labels[hoverIndex] }}</p>
                    <p v-if="selectedSeries.rhythm" class="mt-1 text-rose-500">
                        Rythme cardiaque (bpm) :
                        {{ heartRateValues[hoverIndex] }}
                    </p>
                    <p v-if="selectedSeries.tension" class="mt-1 text-blue-600">
                        Tension systolique (mmHg) :
                        {{ systolicValues[hoverIndex] }}
                    </p>
                    <p
                        v-if="selectedSeries.saturation"
                        class="mt-1 text-violet-600"
                    >
                        Saturation O₂ (%) : {{ saturationValues[hoverIndex] }}
                    </p>
                </div>
            </div>

            <!-- Légende -->
            <div
                class="mt-1.5 flex items-center justify-center gap-3 text-[12px] font-medium"
            >
                <span
                    v-if="selectedSeries.rhythm"
                    class="inline-flex items-center gap-1 text-rose-500"
                    ><span class="h-1.5 w-1.5 rounded-full bg-rose-500" />Rythme
                    cardiaque (bpm)</span
                >
                <span
                    v-if="selectedSeries.tension"
                    class="inline-flex items-center gap-1 text-blue-500"
                    ><span
                        class="h-1.5 w-1.5 rounded-full bg-blue-500"
                    />Tension systolique (mmHg)</span
                >
                <span
                    v-if="selectedSeries.saturation"
                    class="inline-flex items-center gap-1 text-violet-500"
                    ><span
                        class="h-1.5 w-1.5 rounded-full bg-violet-500"
                    />Saturation O₂ (%)</span
                >
            </div>
        </section>
    </div>
</template>

<script setup>
import { computed, onMounted, onUnmounted, reactive, ref } from "vue";
import { useRouter } from "vue-router";
import api from "@/services/api";
import NotificationsEnLigne from "@/components/ui/NotificationsEnLigne.vue";
import ModificationProfilModal from "@/components/profil/ModificationProfilModal.vue";
import CourbeRythmeCardiaque from "@/components/dashboards/userDashboard/CourbeRythmeCardiaque.vue";
import CourbeTensionArterielle from "@/components/dashboards/userDashboard/CourbeTensionArterielle.vue";
import CourbeSaturationO2 from "@/components/dashboards/userDashboard/CourbeSaturationO2.vue";
import { useAuthStore } from "@/stores/auth";

const chartRef = ref(null);
const router = useRouter();
const authStore = useAuthStore();
const modalProfilOuvert = ref(false);
const labels = ref([]);
const heartRateValues = ref([]);
const systolicValues = ref([]);
const saturationValues = ref([]);
const hoverIndex = ref(null);
const listeNotifications = ref([]);
const chargementNotifications = ref(false);
const erreurNotification = ref("");
let minuterieRafraichissementNotifications = null;
const selectedSeries = reactive({
    rhythm: true,
    tension: true,
    saturation: true,
});

const chart = {
    width: 980,
    height: 300,
    left: 70,
    right: 28,
    top: 24,
    bottom: 38,
    minY: 0,
    maxY: 140,
};
const yTicks = [0, 35, 70, 105, 140];

const tooltipLeft = computed(() =>
    Math.max(
        8,
        Math.min(convertirXEnPx(hoverIndex.value) + 12, chart.width - 230),
    ),
);
const nombreNonLues = computed(
    () =>
        listeNotifications.value.filter((notification) => !notification.read_at)
            .length,
);
const notificationsVisibles = computed(() =>
    listeNotifications.value.filter((notification) => !notification.read_at),
);
const TOOLTIP_TOP = chart.top + 10;

async function gererErreurAuthentification(error) {
    if (error?.response?.status !== 401) {
        throw error;
    }

    // Le backend a deja repondu 401, on nettoie localement sans rappeler /auth/logout.
    await authStore.deconnexion({ appelerApi: false });
    await router.replace({ name: "connexion" });
}

function normaliserSerie(values, fallback = 0) {
    let last = fallback;
    return (Array.isArray(values) ? values : []).map((v) => {
        const n = Number(v);
        if (Number.isFinite(n)) {
            last = n;
            return n;
        }
        return last;
    });
}

function formaterLibelle(label) {
    if (!label) return "";
    const date = new Date(`${label}T00:00:00`);
    if (Number.isNaN(date.getTime())) return String(label);
    return date
        .toLocaleDateString("fr-FR", { day: "2-digit", month: "short" })
        .replace(".", "");
}

async function chargerDonneesSante() {
    try {
        const res = await api.get("/health-data/overview", {
            params: { days: 7 },
        });
        const chartData = res?.data?.data?.vitals_chart ?? {};
        const labelSource = Array.isArray(chartData.labels)
            ? chartData.labels
            : [];
        labels.value = labelSource.map(formaterLibelle);
        heartRateValues.value = normaliserSerie(chartData.heart_rate, 70);
        systolicValues.value = normaliserSerie(
            chartData.systolic_pressure,
            120,
        );
        saturationValues.value = normaliserSerie(
            chartData.oxygen_saturation,
            98,
        );
    } catch (error) {
        await gererErreurAuthentification(error);
    }
}

function formaterDateHeureNotification(value) {
    if (!value) return "";
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return "";
    return date.toLocaleString("fr-FR", {
        day: "2-digit",
        month: "short",
        hour: "2-digit",
        minute: "2-digit",
    });
}

async function chargerListeNotifications({ silencieux = false } = {}) {
    if (!silencieux) chargementNotifications.value = true;
    erreurNotification.value = "";

    try {
        const res = await api.get("/notifications");
        listeNotifications.value = Array.isArray(res?.data?.data)
            ? res.data.data
            : [];
    } catch (error) {
        if (error?.response?.status === 401) {
            await gererErreurAuthentification(error);
            return;
        }

        erreurNotification.value =
            "Impossible de charger les notifications pour le moment.";
    } finally {
        chargementNotifications.value = false;
    }
}

async function marquerNotificationLue(idNotification) {
    try {
        await api.post(`/notifications/${idNotification}/read`);
        listeNotifications.value = listeNotifications.value.map(
            (notification) =>
                notification.id === idNotification
                    ? {
                          ...notification,
                          read_at:
                              notification.read_at || new Date().toISOString(),
                      }
                    : notification,
        );
    } catch (_) {
        erreurNotification.value =
            "Impossible de marquer cette notification comme lue.";
    }
}

async function marquerToutLu() {
    try {
        await api.post("/notifications/read-all");
        const maintenant = new Date().toISOString();
        listeNotifications.value = listeNotifications.value.map(
            (notification) => ({
                ...notification,
                read_at: notification.read_at || maintenant,
            }),
        );
    } catch (_) {
        erreurNotification.value =
            "Impossible de marquer toutes les notifications comme lues.";
    }
}

function convertirXEnPx(index) {
    if (labels.value.length <= 1) return chart.left;
    const usable = chart.width - chart.left - chart.right;
    const step = usable / (labels.value.length - 1);
    return chart.left + index * step;
}

function convertirYEnPx(value) {
    const n = Number(value);
    if (!Number.isFinite(n)) return chart.height - chart.bottom;
    const usable = chart.height - chart.top - chart.bottom;
    const ratio = (n - chart.minY) / (chart.maxY - chart.minY);
    return chart.height - chart.bottom - ratio * usable;
}

function gererMouvementGraphique(event) {
    if (!chartRef.value || labels.value.length === 0) return;
    const rect = chartRef.value.getBoundingClientRect();
    const localX = ((event.clientX - rect.left) / rect.width) * chart.width;
    const usable = chart.width - chart.left - chart.right;
    const step =
        labels.value.length > 1 ? usable / (labels.value.length - 1) : usable;
    const nearest = Math.round((localX - chart.left) / step);
    hoverIndex.value = Math.min(Math.max(nearest, 0), labels.value.length - 1);
}

function gererSortieGraphique() {
    hoverIndex.value = null;
}

function basculerSerie(key) {
    if (
        selectedSeries[key] &&
        Object.values(selectedSeries).filter(Boolean).length === 1
    )
        return;
    selectedSeries[key] = !selectedSeries[key];
}

onMounted(async () => {
    await Promise.all([chargerDonneesSante(), chargerListeNotifications()]);

    minuterieRafraichissementNotifications = window.setInterval(() => {
        chargerListeNotifications({ silencieux: true });
    }, 60000);
});

onUnmounted(() => {
    if (minuterieRafraichissementNotifications !== null) {
        window.clearInterval(minuterieRafraichissementNotifications);
        minuterieRafraichissementNotifications = null;
    }
});
</script>
