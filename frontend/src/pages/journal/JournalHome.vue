<template>
    <div class="flex flex-col gap-4 p-4 lg:p-5" style="min-height: calc(100vh - 61px)">

        <!-- Hero -->
        <section class="relative overflow-hidden rounded-3xl border border-slate-200 bg-gradient-to-br from-white to-indigo-50 p-8 lg:p-10 text-slate-900 shadow-md">

            <div class="relative grid items-center gap-8 lg:grid-cols-[1fr_1fr]">
                <div class="flex flex-col justify-center">
                    <span class="inline-flex items-center rounded-full border border-slate-300 bg-slate-100 px-4 py-1.5 text-xs font-semibold text-slate-600 w-fit mb-4">
                        Journal Quotidien HealthFlow
                    </span>
                    <h2 class="text-5xl lg:text-6xl font-extrabold tracking-tight leading-tight mb-4">
                        Suivez votre journée en<br/>
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600">toute simplicité</span>
                    </h2>
                    <p class="text-base lg:text-lg text-slate-600 max-w-lg leading-relaxed mb-6">
                        Une interface structurée pour consigner vos indicateurs quotidiens : sommeil, nutrition, hydratation, activité physique et habitudes de vie.
                    </p>
                    <div class="flex flex-wrap gap-4 items-center">
                        <button
                            class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-[#2563eb] to-[#7c3aed] px-6 py-3 text-base font-semibold text-white shadow-lg shadow-indigo-300 transition hover:brightness-110 hover:shadow-xl"
                            @click="router.push({ name: 'journal-assistant' })"
                        >
                            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
                            Ajouter une entrée
                        </button>
                        <button
                            class="inline-flex items-center gap-2 rounded-xl border-2 border-slate-300 bg-white px-6 py-3 text-base font-semibold text-slate-700 hover:bg-slate-100 transition hover:border-slate-400"
                            @click="router.push({ name: 'journal-history' })"
                        >
                            Voir l'historique
                        </button>
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
            <div class="flex flex-col rounded-2xl border border-slate-200 bg-white p-4 shadow-xs">
                <div class="flex items-center gap-2 mb-3">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-violet-100">
                        <span class="text-base">🗓️</span>
                    </div>
                    <h3 class="text-base font-bold text-slate-900">Dernière entrée</h3>
                </div>
                <div v-if="latestEntry" class="flex-1 space-y-2.5 text-sm text-slate-700">
                    <div class="flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2">
                        <span class="text-slate-500">Date</span>
                        <span class="font-semibold text-slate-900">{{ latestEntry.dateLabel }}</span>
                    </div>
                    <div class="flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2">
                        <span class="text-slate-500">Sommeil</span>
                        <span class="font-semibold text-indigo-600">{{ sleepLabel(latestEntry.sleep) }}</span>
                    </div>
                    <div class="flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2">
                        <span class="text-slate-500">Stress</span>
                        <span class="font-semibold text-rose-500">{{ stressLabel(latestEntry.stress) }}</span>
                    </div>
                    <div class="flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2">
                        <span class="text-slate-500">Énergie</span>
                        <span class="font-semibold text-emerald-600">{{ energyLabel(latestEntry.energy) }}</span>
                    </div>
                </div>
                <div v-else class="flex items-center justify-center rounded-lg border border-dashed border-slate-300 bg-slate-50 px-4 py-6 text-sm text-slate-500">
                    Aucune donnée
                </div>
            </div>

            <!-- Autres infos -->
            <CarteInfosDerniereEntree :derniere-entree="latestEntry" />

            <!-- Conseil + Objectif -->
            <div class="rounded-2xl border border-slate-200 bg-gradient-to-br from-violet-50 to-indigo-50 p-4 shadow-xs">
                <div class="flex items-center gap-2 mb-3">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-violet-100">
                        <span class="text-base">💡</span>
                    </div>
                    <h3 class="text-base font-bold text-slate-900">Conseil du jour</h3>
                </div>
                <p class="text-sm text-slate-600 leading-relaxed">
                    Maintenez une saisie régulière de vos données pour obtenir un suivi
                    clair, utile et exploitable dans le temps.
                </p>

                <div class="mt-3 rounded-lg border border-slate-200 bg-white px-3 py-2">
                    <div class="flex items-center gap-2 text-sm font-semibold text-slate-800">
                        <span class="text-base">🎯</span>
                        Objectif quotidien
                    </div>
                    <p class="mt-1 text-sm text-slate-600">
                        Atteindre au moins 2.0 L d'eau et 30 min d'activité légère.
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { onMounted } from "vue";
import { storeToRefs } from "pinia";
import { useRouter } from "vue-router";
import CarteInfosDerniereEntree from "@/components/journal-entries/LastEntryInfoCard.vue";
import { useJournalStore } from "@/stores/journal";

const router = useRouter();
const store = useJournalStore();
const { derniereEntree: latestEntry } = storeToRefs(store);

onMounted(async () => {
    await store.initialiser();
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
</script>
