<template>
    <div
        class="flex flex-col gap-4 p-4 lg:p-5"
        style="min-height: calc(100vh - 61px)"
    >
        <!-- Hero -->
        <section
            class="relative overflow-hidden rounded-3xl border-2 border-blue-400 bg-white p-8 lg:p-10 text-slate-900 shadow-md transition-all duration-300 hover:border-blue-600 hover:shadow-lg"
        >
            <div
                class="relative grid items-center gap-8 lg:grid-cols-[1fr_1fr]"
            >
                <div class="flex flex-col justify-center">
                    <span
                        class="inline-flex items-center rounded-full border border-slate-300 bg-slate-100 px-4 py-1.5 text-xs font-semibold text-slate-600 w-fit mb-4"
                    >
                        Journal Quotidien HealthFlow
                    </span>
                    <Typography tag="h1" variant="h1-style" class="text-black">
                        Suivez votre journée en toute simplicité
                    </Typography>
                    <Typography tag="p" variant="paragraph">
                        Une interface structurée pour consigner vos indicateurs
                        quotidiens : sommeil, nutrition, hydratation, activité
                        physique et habitudes de vie.
                    </Typography>
                    <div class="flex flex-wrap gap-4 items-center">
                        <BaseButton
                            variant="add"
                            size="lg"
                            @click="router.push({ name: 'journal-assistant' })"
                        >
                            <svg
                                viewBox="0 0 24 24"
                                class="h-5 w-5"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2.5"
                                stroke-linecap="round"
                            >
                                <path d="M12 5v14M5 12h14" />
                            </svg>
                            Ajouter une entrée
                        </BaseButton>
                        <BaseButton
                            variant="secondary"
                            size="lg"
                            @click="router.push({ name: 'journal-history' })"
                        >
                            Voir l'historique
                        </BaseButton>
                    </div>
                </div>
                <img
                    src="https://img.freepik.com/vecteurs-libre/composition-mode-vie-actif-plat-raquettes-velo-homme-cours-execution-boules-horloge-alimentaire-saine-dans-illustration-du-cadre_1284-47378.jpg"
                    alt="Mode de vie actif et santé"
                    class="hidden lg:block h-[320px] w-full rounded-3xl border-2 border-slate-300 object-contain bg-slate-50 shadow-xl hover:shadow-2xl transition duration-300"
                />
            </div>
        </section>

        <!-- Cards principale -->
        <div class="grid flex-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
            <!-- Dernière entrée détail -->
            <div
                class="flex flex-col rounded-2xl border-2 border-blue-300 bg-white p-4 shadow-sm transition-all duration-300 hover:border-blue-500 hover:shadow-md"
            >
                <div class="flex items-center gap-2 mb-3">
                    <div
                        class="flex h-8 w-8 items-center justify-center rounded-lg bg-violet-100"
                    >
                        <span class="text-base">🗓️</span>
                    </div>
                    <h3 class="text-base font-bold text-slate-900">
                        Dernière entrée
                    </h3>
                </div>
                <div
                    v-if="latestEntry"
                    class="flex-1 space-y-2.5 text-sm text-slate-700"
                >
                    <div
                        class="flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2"
                    >
                        <span class="text-slate-500">Date</span>
                        <span class="font-semibold text-slate-900">{{
                            latestEntry.dateLabel
                        }}</span>
                    </div>
                    <div
                        class="flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2"
                    >
                        <span class="text-slate-500">Sommeil</span>
                        <span class="font-semibold text-indigo-600">{{
                            sleepLabel(latestEntry.sleep)
                        }}</span>
                    </div>
                    <div
                        class="flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2"
                    >
                        <span class="text-slate-500">Stress</span>
                        <span class="font-semibold text-rose-500">{{
                            stressLabel(latestEntry.stress)
                        }}</span>
                    </div>
                    <div
                        class="flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2"
                    >
                        <span class="text-slate-500">Énergie</span>
                        <span class="font-semibold text-emerald-600">{{
                            energyLabel(latestEntry.energy)
                        }}</span>
                    </div>
                </div>
                <div
                    v-else
                    class="flex items-center justify-center rounded-lg border border-dashed border-slate-300 bg-slate-50 px-4 py-6 text-sm text-slate-500"
                >
                    Aucune donnée
                </div>
            </div>

            <!-- Autres infos -->
            <CarteInfosDerniereEntree :last-entry="latestEntry" />

            <!-- Analyse IA -->
            <div
                class="rounded-2xl border-2 border-blue-300 bg-white p-4 shadow-sm transition-all duration-300 hover:border-blue-500 hover:shadow-md flex flex-col gap-3"
            >
                <div class="flex items-center gap-2">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-100">
                        <span class="text-base">🤖</span>
                    </div>
                    <h3 class="text-base font-bold text-slate-900">Analyse IA</h3>
                </div>

                <!-- Pas de données -->
                <div
                    v-if="!latestEntry || !latestEntry.energy"
                    class="flex flex-col items-center justify-center gap-2 rounded-lg border border-dashed border-slate-300 bg-slate-50 px-4 py-6 text-center text-sm text-slate-500"
                >
                    <span class="text-2xl">🍽️</span>
                    Ajoutez des repas à votre entrée pour que l'IA analyse votre énergie et apport en sucre.
                </div>

                <template v-else>
                    <!-- Énergie -->
                    <div class="flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2.5">
                        <div class="flex items-center gap-2 text-sm font-medium text-slate-700">
                            <span>⚡</span> Énergie
                        </div>
                        <span class="text-sm font-bold px-2.5 py-0.5 rounded-full border" :class="energyBadgeClass(latestEntry.energy)">
                            {{ latestEntry.energy }}/10 — {{ energyLabel(latestEntry.energy) }}
                        </span>
                    </div>

                    <!-- Apport en sucre -->
                    <div class="flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2.5">
                        <div class="flex items-center gap-2 text-sm font-medium text-slate-700">
                            <span>🍬</span> Apport en sucre
                        </div>
                        <span class="text-sm font-bold px-2.5 py-0.5 rounded-full border" :class="sugarBadgeClass(latestEntry.sugar)">
                            {{ sugarLabel(latestEntry.sugar) }}
                        </span>
                    </div>

                    <!-- Recommandations journal (depuis l'analyse IA) -->
                    <div v-if="journalRecs.length" class="space-y-2">
                        <div
                            v-for="rec in journalRecs"
                            :key="rec.priority"
                            class="rounded-lg border border-blue-100 bg-blue-50 px-3 py-2.5"
                        >
                            <div class="flex items-center gap-1.5 text-xs font-semibold text-blue-700 mb-1">
                                <span>💡</span> {{ domainLabel(rec.domain) }}
                            </div>
                            <p class="text-xs text-slate-600 leading-relaxed">{{ rec.action }}</p>
                        </div>
                    </div>
                    <div v-else-if="!aiLoading" class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs text-slate-400 text-center">
                        Aucune recommandation disponible
                    </div>
                </template>
            </div>
        </div>
    </div>
</template>

<script setup>
import { onMounted, ref, computed } from "vue";
import { storeToRefs } from "pinia";
import { useRouter } from "vue-router";
import CarteInfosDerniereEntree from "@/components/journal-entries/LastEntryInfoCard.vue";
import { useJournalStore } from "@/stores/journal";
import Typography from "@/components/ui/Typography.vue";
import BaseButton from "@/components/ui/BaseButton.vue";
import api from "@/services/api";

const router = useRouter();
const store = useJournalStore();
const { lastEntry: latestEntry } = storeToRefs(store);

const JOURNAL_DOMAINS = ["sleep", "nutrition", "activity", "smoking", "alcohol"];
const DOMAIN_LABELS = {
    sleep: "Sommeil", nutrition: "Nutrition", activity: "Activité physique",
    smoking: "Tabac", alcohol: "Alcool",
};
const domainLabel = (d) => DOMAIN_LABELS[d] ?? d;

const aiLoading = ref(false);
const journalRecs = ref([]);

async function loadAiRecs() {
    aiLoading.value = true;
    try {
        const { data } = await api.get("/ai/analysis");
        journalRecs.value = (data.global_recommendations ?? []).filter(
            (r) => JOURNAL_DOMAINS.includes(r.domain)
        );
    } catch {
        journalRecs.value = [];
    } finally {
        aiLoading.value = false;
    }
}

onMounted(async () => {
    await store.initialiser();
    loadAiRecs();
});

const sleepLabel = (hours) => {
    const h = Math.floor(hours);
    const m = Math.round((hours - h) * 60);
    return m ? `${h}h ${m}min` : `${h}h`;
};

const stressLabel = (value) => {
    if (value >= 8) return "Élevé";
    if (value <= 3) return "Faible";
    return "Modéré";
};

const energyLabel = (value) => {
    if (value >= 8) return "Excellente";
    if (value <= 4) return "Faible";
    return "Bonne";
};

const energyBadgeClass = (value) => {
    if (value >= 7) return "bg-emerald-100 text-emerald-700 border-emerald-300";
    if (value >= 4) return "bg-amber-100 text-amber-700 border-amber-300";
    return "bg-rose-100 text-rose-700 border-rose-300";
};

const sugarLabel = (sugar) => {
    const map = { high: "Élevé", medium: "Modéré", low: "Faible" };
    return map[sugar] || "Modéré";
};

const sugarBadgeClass = (sugar) => {
    const map = {
        high: "bg-rose-100 text-rose-700 border-rose-300",
        medium: "bg-amber-100 text-amber-700 border-amber-300",
        low: "bg-emerald-100 text-emerald-700 border-emerald-300",
    };
    return map[sugar] || map.medium;
};

</script>
