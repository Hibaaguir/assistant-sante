<template>
    <div class="min-h-screen bg-[#edf2f9] py-4">
        <div class="mx-auto max-w-[1160px] px-3 sm:px-4 lg:px-6">
            <NotificationsEnLigne mode="inline" />

            <header
                class="rounded-[16px] border border-[#c8d4e4] bg-[#edf2f9] p-4 shadow-[0_1px_4px_rgba(15,23,42,0.05)] sm:p-4"
            >
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h1
                            class="bg-gradient-to-r from-[#3d6df2] via-[#6f49e8] to-[#b832df] bg-clip-text text-[44px] font-bold leading-none tracking-[-0.015em] text-transparent sm:text-[47px]"
                        >
                            Mes Tâches Bien-être
                        </h1>
                        <p
                            class="mt-1.5 text-[15px] font-normal text-slate-600"
                        >
                            Gérez vos objectifs quotidiens de bien-être
                        </p>
                    </div>

                    <div
                        class="min-w-[146px] rounded-[18px] border border-[#c9d8ea] bg-white/95 px-3.5 py-2.5 shadow-[0_4px_12px_rgba(15,23,42,0.08)]"
                    >
                        <div class="flex items-center gap-2">
                            <span
                                class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-[#eef4ff] text-[#2563eb]"
                            >
                                <svg
                                    viewBox="0 0 24 24"
                                    class="h-4.5 w-4.5"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2.2"
                                >
                                    <path d="M9 11l3 3L22 4" />
                                    <path
                                        d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"
                                    />
                                </svg>
                            </span>
                            <div>
                                <p
                                    class="text-[34px] font-extrabold leading-none tracking-[-0.02em] text-slate-900"
                                >
                                    {{ totalTerminees }}/{{ totalTaches }}
                                </p>
                                <p
                                    class="mt-0.5 text-[11px] font-medium leading-none text-slate-500"
                                >
                                    Tâches complétées
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-3.5 flex flex-wrap items-center gap-2">
                    <div class="relative min-w-[340px] flex-1">
                        <span
                            class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"
                        >
                            <svg
                                viewBox="0 0 24 24"
                                class="h-6 w-6"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <circle cx="11" cy="11" r="7" />
                                <path d="m21 21-4.3-4.3" />
                            </svg>
                        </span>
                        <input
                            v-model="recherche"
                            type="text"
                            placeholder="Rechercher une tâche..."
                            class="h-[46px] w-full rounded-[14px] border border-slate-300 bg-white pl-14 pr-4 text-[14px] font-medium text-slate-700 shadow-[0_1px_6px_rgba(15,23,42,0.06)] outline-none transition focus:border-[#3b82f6]"
                        />
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <button
                            v-for="onglet in ongletsCategorie"
                            :key="onglet.valeur"
                            type="button"
                            class="h-[42px] rounded-[14px] border px-5 text-[15px] font-semibold transition-all duration-300"
                            :class="
                                categorieActive === onglet.valeur
                                    ? 'border-transparent bg-gradient-to-r from-[#2563eb] to-[#9333ea] text-white shadow-[0_6px_14px_rgba(79,70,229,0.24)]'
                                    : 'border-slate-300 bg-white text-slate-600 hover:bg-slate-50 hover:shadow-sm'
                            "
                            @click="categorieActive = onglet.valeur"
                        >
                            {{ onglet.libelle }}
                        </button>
                    </div>
                </div>
            </header>

            <section class="mt-5 grid gap-5 lg:grid-cols-[286px_1fr]">
                <aside class="space-y-5">
                    <div
                        class="min-h-[232px] rounded-[24px] border border-[#a9c8ff] bg-gradient-to-br from-[#dce8ff] to-[#eeddf8] p-5 shadow-[0_8px_16px_rgba(15,23,42,0.10)]"
                    >
                        <div class="flex items-center gap-3">
                            <span
                                class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-gradient-to-br from-[#60a5fa] to-[#8b5cf6] text-white"
                            >
                                <svg
                                    viewBox="0 0 24 24"
                                    class="h-5 w-5"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path d="M12 3v4" />
                                    <path d="M12 17v4" />
                                    <path d="M3 12h4" />
                                    <path d="M17 12h4" />
                                    <path d="m5.6 5.6 2.8 2.8" />
                                    <path d="m15.6 15.6 2.8 2.8" />
                                    <path d="m18.4 5.6-2.8 2.8" />
                                    <path d="m8.4 15.6-2.8 2.8" />
                                </svg>
                            </span>
                            <h3
                                class="text-[22px] font-bold leading-tight text-slate-800"
                            >
                                Message du jour
                            </h3>
                        </div>

                        <p
                            class="mt-3.5 text-[16px] font-medium leading-relaxed text-slate-700"
                        >
                            Vous avez commencé, c'est déjà une victoire !
                            Continuez d'avancer. ✨
                        </p>

                        <div class="my-4 h-px bg-[#9cc3ff]/75"></div>

                        <p
                            class="text-[12px] italic leading-relaxed text-slate-600"
                        >
                            "Prenez soin de votre corps, c'est le seul endroit
                            où vous devez vivre."
                        </p>
                    </div>

                    <div
                        class="rounded-[24px] border border-slate-300 bg-white p-5 shadow-[0_6px_14px_rgba(15,23,42,0.08)]"
                    >
                        <h3
                            class="flex items-center gap-2 text-[26px] font-bold text-slate-800"
                        >
                            <span class="text-[#9333ea]">◈</span>
                            Catégories
                        </h3>

                        <ul class="mt-4 space-y-3">
                            <li
                                v-for="categorie in categoriesAffichees"
                                :key="categorie.cle"
                                class="flex items-center justify-between text-[15px]"
                            >
                                <div
                                    class="flex items-center gap-3 text-slate-600"
                                >
                                    <span
                                        class="h-3.5 w-3.5 rounded-full"
                                        :style="{
                                            backgroundColor: categorie.couleur,
                                        }"
                                    ></span>
                                    <span>{{ categorie.libelle }}</span>
                                </div>
                                <span
                                    class="text-[15px] font-semibold text-slate-700"
                                    >{{
                                        compteParCategorie[categorie.cle] || 0
                                    }}</span
                                >
                            </li>
                        </ul>
                    </div>
                </aside>

                <div class="space-y-4">
                    <div
                        class="rounded-[22px] border border-slate-300 bg-white p-4 shadow-[0_8px_18px_rgba(15,23,42,0.08)]"
                    >
                        <div class="flex flex-wrap items-center gap-3">
                            <input
                                ref="champTache"
                                v-model="formulaireTache.titre"
                                type="text"
                                placeholder="Ajouter une nouvelle tâche bien-être..."
                                class="h-[52px] min-w-[300px] flex-1 rounded-[14px] border border-slate-300 bg-[#f9fafb] px-5 text-[15px] font-medium text-slate-700 outline-none transition focus:border-[#3b82f6]"
                                @keyup.enter="soumettreTache"
                            />

                            <button
                                type="button"
                                class="h-[52px] rounded-[14px] bg-gradient-to-r from-[#3b82f6] to-[#9333ea] px-8 text-[18px] font-bold text-white shadow-[0_12px_24px_rgba(79,70,229,0.32)] transition-all duration-300 hover:-translate-y-0.5"
                                @click="soumettreTache"
                            >
                                {{
                                    idTacheEnEdition
                                        ? "Mettre à jour"
                                        : "+ Ajouter"
                                }}
                            </button>
                        </div>
                    </div>

                    <div
                        v-if="chargementInitial"
                        class="rounded-[20px] border border-slate-300 bg-white p-6 text-base text-slate-500"
                    >
                        Chargement des tâches...
                    </div>

                    <TransitionGroup
                        v-else
                        name="liste-taches"
                        tag="div"
                        class="space-y-3.5"
                    >
                        <article
                            v-for="tache in tachesFiltrees"
                            :key="tache.id"
                            class="group min-h-[88px] rounded-[20px] border border-slate-300 bg-white px-5 py-4 shadow-[0_6px_14px_rgba(15,23,42,0.05)] transition-all duration-300 hover:-translate-y-0.5 hover:shadow-[0_10px_18px_rgba(15,23,42,0.08)]"
                        >
                            <div class="flex items-center gap-4">
                                <button
                                    type="button"
                                    class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-full border transition-all duration-300"
                                    :class="
                                        tache.est_complete
                                            ? `${classeCouleurCoche(tache.categorie)} text-white shadow-[0_6px_14px_rgba(15,23,42,0.15)]`
                                            : 'border-slate-300 bg-white text-transparent hover:border-[#3b82f6]'
                                    "
                                    @click="basculerEtatTache(tache.id)"
                                >
                                    <svg
                                        viewBox="0 0 24 24"
                                        class="h-5 w-5"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2.8"
                                    >
                                        <path d="M5 13l4 4L19 7" />
                                    </svg>
                                </button>

                                <div class="min-w-0 flex-1">
                                    <p
                                        class="truncate text-[22px] font-semibold leading-[1.22] tracking-[-0.01em] text-slate-800 sm:text-[23px]"
                                        :class="
                                            tache.est_complete
                                                ? 'line-through text-slate-500'
                                                : ''
                                        "
                                    >
                                        {{ tache.titre }}
                                    </p>

                                    <div
                                        class="mt-2 flex flex-wrap items-center gap-2"
                                    >
                                        <span
                                            class="rounded-full px-2.5 py-0.5 text-[12px] font-semibold text-white"
                                            :style="{
                                                backgroundColor:
                                                    couleurCategorie(
                                                        tache.categorie,
                                                    ),
                                            }"
                                        >
                                            {{
                                                libelleCategorie(
                                                    tache.categorie,
                                                )
                                            }}
                                        </span>

                                        <span
                                            v-if="tache.date_echeance"
                                            class="text-[13px] font-medium text-slate-500"
                                            >📅 {{ tache.date_echeance }}</span
                                        >
                                    </div>
                                </div>

                                <div class="flex items-center gap-2">
                                    <button
                                        type="button"
                                        class="h-9 w-9 rounded-full bg-slate-100 text-slate-600 transition hover:scale-105 hover:bg-slate-200"
                                        @click="editerTache(tache)"
                                    >
                                        ✎
                                    </button>
                                    <button
                                        type="button"
                                        class="h-9 w-9 rounded-full bg-red-50 text-red-500 transition hover:scale-105 hover:bg-red-100"
                                        @click="demanderSuppression(tache.id)"
                                    >
                                        🗑
                                    </button>
                                </div>
                            </div>
                        </article>

                        <div
                            v-if="!tachesFiltrees.length"
                            key="vide"
                            class="rounded-[20px] border border-dashed border-slate-300 bg-white px-5 py-10 text-center text-[18px] text-slate-500"
                        >
                            Aucune tâche pour ce filtre.
                        </div>
                    </TransitionGroup>
                </div>
            </section>

            <DialogueConfirmation
                :open="showDeleteConfirm"
                title="Supprimer la tâche"
                message="Cette action est définitive. Êtes-vous sûr de vouloir supprimer cette tâche ?"
                confirm-label="Supprimer"
                cancel-label="Annuler"
                @cancel="annulerSuppression"
                @confirm="confirmerSuppression"
            />

            <div class="mt-4 flex justify-end pb-1">
                <button
                    type="button"
                    class="gros-plus inline-flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-r from-[#3b82f6] to-[#d946ef] text-4xl font-bold text-white shadow-[0_12px_24px_rgba(79,70,229,0.35)] transition-all duration-300 hover:-translate-y-1"
                    @click="focusChampTache"
                >
                    +
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from "vue";
import { useRouter } from "vue-router";
import NotificationsEnLigne from "@/components/ui/NotificationsEnLigne.vue";
import DialogueConfirmation from "@/components/ui/DialogueConfirmation.vue";
import { useAuthStore } from "@/stores/auth";
import { useNotificationsStore } from "@/stores/notifications";
import { useTachesBienEtreStore } from "@/stores/tachesBienEtre";

const routeur = useRouter();
const authStore = useAuthStore();
const notifications = useNotificationsStore();
const storeTaches = useTachesBienEtreStore();

const champTache = ref(null);
const chargementInitial = ref(true);
const recherche = ref("");
const categorieActive = ref("toutes");
const idTacheEnEdition = ref(null);
const showDeleteConfirm = ref(false);
const pendingDeleteId = ref(null);

const formulaireTache = reactive({
    titre: "",
    categorie: "",
    date_echeance: "",
});

const ongletsCategorie = [
    { valeur: "toutes", libelle: "Toutes" },
    { valeur: "bien-etre", libelle: "Bien-Être" },
    { valeur: "sante", libelle: "Santé" },
    { valeur: "fitness", libelle: "Fitness" },
    { valeur: "nutrition", libelle: "Nutrition" },
];

const couleursCategorie = {
    "bien-etre": "#d946ef",
    sante: "#0ea5e9",
    fitness: "#10b981",
    nutrition: "#f97316",
};

const categoriesAffichees = [
    {
        cle: "bien-etre",
        libelle: "Bien-être",
        couleur: couleursCategorie["bien-etre"],
    },
    { cle: "sante", libelle: "Santé", couleur: couleursCategorie.sante },
    { cle: "fitness", libelle: "Fitness", couleur: couleursCategorie.fitness },
    {
        cle: "nutrition",
        libelle: "Nutrition",
        couleur: couleursCategorie.nutrition,
    },
];

const toutesLesTaches = computed(() => storeTaches.taches || []);
const totalTaches = computed(() => Number(storeTaches.resume?.total || 0));
const totalTerminees = computed(() =>
    Number(storeTaches.resume?.completes || 0),
);
const compteParCategorie = computed(() => storeTaches.resume?.categories || {});

const tachesFiltrees = computed(() => {
    const filtreTexte = recherche.value.trim().toLowerCase();

    return toutesLesTaches.value.filter((tache) => {
        const parCategorie =
            categorieActive.value === "toutes" ||
            tache.categorie === categorieActive.value;
        const parRecherche =
            !filtreTexte ||
            String(tache.titre || "")
                .toLowerCase()
                .includes(filtreTexte);
        return parCategorie && parRecherche;
    });
});

function couleurCategorie(categorie) {
    return couleursCategorie[categorie] || couleursCategorie["bien-etre"];
}

function libelleCategorie(categorie) {
    if (categorie === "sante") return "santé";
    if (categorie === "fitness") return "fitness";
    if (categorie === "nutrition") return "nutrition";
    return "bien-être";
}

function classeCouleurCoche(categorie) {
    if (categorie === "sante") return "border-[#0ea5e9] bg-[#0ea5e9]";
    if (categorie === "fitness") return "border-[#10b981] bg-[#10b981]";
    if (categorie === "nutrition") return "border-[#f97316] bg-[#f97316]";
    return "border-[#d946ef] bg-[#d946ef]";
}

function reinitialiserFormulaire() {
    formulaireTache.titre = "";
    formulaireTache.categorie = "";
    formulaireTache.date_echeance = "";
    idTacheEnEdition.value = null;
}

function focusChampTache() {
    champTache.value?.focus();
}

async function chargerTaches() {
    try {
        await storeTaches.charger();
    } catch (erreur) {
        if (erreur?.response?.status === 401) {
            authStore.supprimerToken();
            routeur.replace({ name: "connexion" });
            return;
        }

        notifications.erreur("Impossible de charger vos tâches.");
    } finally {
        chargementInitial.value = false;
    }
}

async function soumettreTache() {
    const titre = String(formulaireTache.titre || "").trim();
    if (titre.length < 2) {
        notifications.avertissement(
            "Veuillez renseigner un titre valide pour votre tâche (minimum 2 caractères).",
        );
        return;
    }

    const payload = {
        titre,
        categorie:
            formulaireTache.categorie ||
            (categorieActive.value !== "toutes"
                ? categorieActive.value
                : "bien-etre"),
        date_echeance: formulaireTache.date_echeance || null,
    };

    try {
        if (idTacheEnEdition.value) {
            await storeTaches.mettreAJour(idTacheEnEdition.value, payload);
            notifications.actionModifiee("Tâche mise à jour avec succès.");
        } else {
            await storeTaches.ajouter(payload);
            notifications.actionAjoutee("Tâche ajoutée avec succès.");
        }

        reinitialiserFormulaire();
        focusChampTache();
    } catch (erreur) {
        if (erreur?.response?.status === 422) {
            notifications.avertissement(
                "Veuillez vérifier les champs de la tâche.",
            );
            return;
        }
        notifications.erreur("Erreur lors de l'enregistrement de la tâche.");
    }
}

function editerTache(tache) {
    idTacheEnEdition.value = tache.id;
    formulaireTache.titre = tache.titre || "";
    formulaireTache.categorie = tache.categorie || "bien-etre";
    formulaireTache.date_echeance = tache.date_echeance || "";
    focusChampTache();
}

async function basculerEtatTache(idTache) {
    try {
        await storeTaches.basculer(idTache);
    } catch {
        notifications.erreur("Impossible de modifier le statut de la tâche.");
    }
}

function demanderSuppression(idTache) {
    pendingDeleteId.value = idTache;
    showDeleteConfirm.value = true;
}

function annulerSuppression() {
    pendingDeleteId.value = null;
    showDeleteConfirm.value = false;
    notifications.actionAnnulee();
}

async function confirmerSuppression() {
    if (!pendingDeleteId.value) return;

    try {
        await storeTaches.supprimer(pendingDeleteId.value);
        notifications.actionSupprimee("Tâche supprimée avec succès.");

        if (idTacheEnEdition.value === pendingDeleteId.value) {
            reinitialiserFormulaire();
        }

        pendingDeleteId.value = null;
        showDeleteConfirm.value = false;
    } catch {
        // Suppression échouée, fermer la modal silencieusement
        showDeleteConfirm.value = false;
        pendingDeleteId.value = null;
    }
}

onMounted(async () => {
    await chargerTaches();
});
</script>

<style scoped>
.liste-taches-enter-active,
.liste-taches-leave-active {
    transition: all 260ms ease;
}

.liste-taches-enter-from,
.liste-taches-leave-to {
    opacity: 0;
    transform: translateY(10px) scale(0.98);
}

.liste-taches-move {
    transition: transform 260ms ease;
}

.gros-plus {
    animation: pulse-doux 2.6s ease-in-out infinite;
}

@keyframes pulse-doux {
    0%,
    100% {
        box-shadow: 0 12px 24px rgba(79, 70, 229, 0.35);
    }
    50% {
        box-shadow: 0 16px 30px rgba(79, 70, 229, 0.5);
    }
}
</style>
