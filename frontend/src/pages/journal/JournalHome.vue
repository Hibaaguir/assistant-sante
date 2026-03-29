<template>
    <div class="mx-auto max-w-[1320px] p-4 sm:p-6 lg:p-8">
        <section
            class="relative overflow-hidden rounded-3xl border border-slate-200 bg-gradient-to-br from-[#eef4ff] via-[#f7f4ff] to-[#f4fbf6] p-6 shadow-sm sm:p-8 lg:p-10"
        >
            <div
                class="pointer-events-none absolute -right-12 -top-12 h-40 w-40 rounded-full bg-[#8b5cf6]/20 blur-2xl"
            ></div>
            <div
                class="pointer-events-none absolute -bottom-12 left-10 h-36 w-36 rounded-full bg-[#22c55e]/20 blur-2xl"
            ></div>

            <div class="grid items-center gap-8 lg:grid-cols-[1.1fr_0.9fr]">
                <div>
                    <p
                        class="inline-flex items-center rounded-full border border-[#c7d9ff] bg-white/70 px-4 py-1.5 text-xs font-semibold text-[#3256c6]"
                    >
                        Journal Quotidien HealthFlow
                    </p>
                    <h2
                        class="mt-6 text-5xl font-extrabold tracking-tight text-slate-900 sm:text-6xl"
                    >
                        Suivé votre journée en
                        <span
                            class="bg-gradient-to-r from-purple-600 to-purple-700 bg-clip-text text-transparent"
                            >toute simplicité</span
                        >
                    </h2>
                    <p
                        class="mt-4 max-w-xl text-base text-slate-600 sm:text-lg"
                    >
                        Une interface structuree pour consigner vos indicateurs
                        quotidiens: sommeil, nutrition, hydratation, activite
                        physique et habitudes de vie.
                    </p>

                    <div class="mt-6 flex flex-wrap items-center gap-3">
                        <button
                            type="button"
                            class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-[#2563eb] to-[#7c3aed] px-5 py-3 text-sm font-semibold text-white shadow-md shadow-indigo-500/25 transition hover:-translate-y-0.5"
                            @click="router.push({ name: 'assistant-journal' })"
                        >
                            <svg
                                viewBox="0 0 24 24"
                                class="h-4 w-4"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                aria-hidden="true"
                            >
                                <path d="M12 5v14" />
                                <path d="M5 12h14" />
                            </svg>
                            Ajouter une entree
                        </button>
                        <button
                            type="button"
                            class="rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                            @click="router.push({ name: 'historique-journal' })"
                        >
                            Voir l'historique
                        </button>
                    </div>
                </div>

                <div
                    class="rounded-3xl border border-white/70 bg-white/80 p-3 shadow-xl shadow-slate-300/40"
                >
                    <img
                        src="https://images.unsplash.com/photo-1631815589968-fdb09a223b1e?auto=format&fit=crop&w=1000&q=80"
                        alt="Illustration sante"
                        class="h-[260px] w-full rounded-2xl object-cover"
                    />
                </div>
            </div>
        </section>

        <section class="mt-6 grid gap-6 md:grid-cols-2 xl:grid-cols-3">
            <div
                class="rounded-[28px] border border-[#d0d9f5] bg-gradient-to-br from-[#ede9fa] via-white to-[#f5f0fb] p-8 shadow-sm shadow-purple-200/50 transition hover:-translate-y-0.5 hover:shadow-md"
            >
                <h3 class="text-4xl font-extrabold text-slate-900">
                    🗓️ Derniere entree
                </h3>
                <div
                    v-if="latest"
                    class="mt-6 space-y-4 text-base text-slate-700"
                >
                    <div
                        class="flex items-center justify-between border-b border-slate-200 pb-3"
                    >
                        <span>Date</span>
                        <span class="font-semibold text-slate-900">{{
                            latest.dateLabel
                        }}</span>
                    </div>
                    <div
                        class="flex items-center justify-between border-b border-slate-200 pb-3"
                    >
                        <span>Sommeil</span>
                        <span class="font-semibold text-slate-900">{{
                            libelleSommeil(latest.sleep)
                        }}</span>
                    </div>
                    <div
                        class="flex items-center justify-between border-b border-slate-200 pb-3"
                    >
                        <span>Stress</span>
                        <span class="font-semibold text-slate-900">{{
                            libelleStress(latest.stress)
                        }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span>Energie</span>
                        <span class="font-semibold text-slate-900">{{
                            libelleEnergie(latest.energy)
                        }}</span>
                    </div>
                </div>
                <div
                    v-else
                    class="mt-6 rounded-xl border border-dashed border-slate-300 bg-slate-50 p-4 text-sm text-slate-500"
                >
                    Aucune donnee
                </div>
            </div>

            <CarteInfosDerniereEntree :derniere-entree="latest" />

            <div
                class="rounded-[28px] border border-[#d9ccff] bg-gradient-to-br from-[#f1eaff] via-[#f8f3ff] to-[#ecf2ff] p-8 shadow-sm shadow-violet-200/50"
            >
                <h3 class="text-4xl font-extrabold text-slate-900">
                    💡 Conseil du jour
                </h3>
                <p class="mt-3 text-sm text-slate-600">
                    Maintenez une saisie reguliere de vos donnees pour obtenir
                    un suivi clair, utile et exploitable dans le temps.
                </p>
                <div
                    class="mt-6 rounded-2xl border border-[#d9e5ff] bg-white/90 p-4"
                >
                    <p class="text-sm font-semibold text-slate-700">
                        🎯 Objectif quotidien
                    </p>
                    <p class="mt-1 text-xs text-slate-500">
                        Atteindre au moins 2.0 L d'eau et 30 min d'activite
                        legere.
                    </p>
                </div>
            </div>
        </section>
    </div>
</template>

<script setup>
/*
  Page d'accueil du module Journal.
  Elle propose la creation d'une nouvelle entree et resume la derniere.
  Les informations affichees proviennent du store journal.
*/

import { onMounted } from "vue";
import { storeToRefs } from "pinia";
import { useRouter } from "vue-router";
import CarteInfosDerniereEntree from "@/components/journal/CarteInfosDerniereEntree.vue";
import { useJournalStore } from "@/stores/journal";

const router = useRouter();
const store = useJournalStore();
const { derniereEntree: latest } = storeToRefs(store);

onMounted(async () => {
    await store.initialiser();
});

const libelleSommeil = (hours) => {
    const h = Math.floor(hours);
    const m = Math.round((hours - h) * 60);
    return m ? `${h}h ${m}min` : `${h}h`;
};

const libelleStress = (value) => {
    if (value >= 8) return "Eleve";
    if (value <= 3) return "Faible";
    return "Modere";
};

const libelleEnergie = (value) => {
    if (value >= 8) return "Excellente";
    if (value <= 4) return "Faible";
    return "Bonne";
};
</script>
