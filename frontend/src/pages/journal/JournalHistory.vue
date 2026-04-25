<template>
    <div class="w-full p-4 sm:p-6 lg:p-8 bg-white">
        <div
            class="relative mb-4 overflow-hidden rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6"
        >
            <div
                class="pointer-events-none absolute -right-10 -top-10 h-28 w-28 rounded-full bg-transparent blur-2xl"
            ></div>
            <div
                class="pointer-events-none absolute -bottom-10 left-8 h-24 w-24 rounded-full bg-transparent blur-2xl"
            ></div>
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <p
                        class="inline-flex items-center rounded-full border border-[#d2ddff] bg-white/85 px-3 py-1 text-xs font-semibold text-[#3b5ac8]"
                    >
                        HealthFlow Journal
                    </p>
                    <Typography tag="h1" variant="h3-style" class="mt-4">
                        🗂️ Historique du journal
                    </Typography>
                    <Typography tag="h4" variant="h3-style" class="mt-1">
                        Consultez, filtrez et mettez a jour vos entrees
                        precedentes en toute clarte.
                    </Typography>
                </div>
                <div class="flex gap-2">
                    <!-- Use BaseButton to avoid repeating the same button style everywhere -->
                    <BaseButton variant="secondary" @click="showFilter = true">
                        <svg
                            viewBox="0 0 24 24"
                            class="h-3.5 w-3.5"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            aria-hidden="true"
                        >
                            <path d="M3 5h18l-7 8v5l-4 2v-7z" />
                        </svg>
                        Filtrer
                    </BaseButton>
                    <BaseButton
                        variant="primary"
                        @click="router.push({ name: 'journal' })"
                    >
                        Retour
                    </BaseButton>
                </div>
            </div>
        </div>
        <!-- AlertMessage picks the right color automatically based on tone -->
        <AlertMessage
            :message="noticeMessage"
            :type="noticeTone === 'success' ? 'success' : 'warning'"
        />

        <div
            v-if="store.filter.type !== 'all'"
            class="mb-4 flex items-center gap-2 text-base"
        >
            <span class="text-slate-700 font-semibold">Filtre actif :</span>
            <span
                class="rounded-full bg-gradient-to-r from-[#149bd7] to-[#7c3aed] px-3 py-1 font-semibold text-white"
                >{{ activeFilterLabel }}</span
            >
            <BaseButton
                type="button"
                variant="outline"
                @click="store.resetFilter()"
            >
                Réinitialiser filtre
            </BaseButton>
        </div>

        <div class="space-y-3">
            <CarteEntreeHistorique
                v-for="entry in store.filteredEntries"
                :key="entry.id"
                :entree="entry"
                :editing="false"
                :filter-type="store.filter.type"
                @edit="
                    router.push({
                        name: 'journal-assistant',
                        query: { edit: entry.id },
                    })
                "
                @request-delete="requestDeletion(entry.id)"
            />
        </div>

        <div
            v-if="showNoResults"
            class="mt-4 rounded-2xl border border-slate-200 bg-white p-6 text-center shadow-sm"
        >
            <p class="text-base font-semibold text-slate-800">
                Aucune entrée trouvée avec ce filtre.
            </p>
            <p class="mt-1 text-base text-slate-600">
                Réinitialise le filtre pour afficher tout l'historique.
            </p>
            <BaseButton class="mt-4" @click="resetFilter">
                Réinitialiser le filtre
            </BaseButton>
        </div>

        <div
            v-else-if="!hasEntries"
            class="mt-4 rounded-2xl border border-slate-200 bg-white p-6 text-center shadow-sm"
        >
            <p class="text-base font-semibold text-slate-800">
                Aucune entrée enregistrée pour le moment.
            </p>
            <BaseButton
                class="mt-4"
                @click="router.push({ name: 'journal-assistant' })"
            >
                Ajouter une entrée
            </BaseButton>
        </div>

        <FilterModal
            :open="showFilter"
            :filter="store.filter"
            @close="showFilter = false"
            @apply="applyFilter"
            @reset="resetFilter"
        />

        <ConfirmationDialog
            :open="showDeleteConfirm"
            title="Supprimer l'entree"
            message="Cette action est definitive. Voulez-vous continuer ?"
            confirm-label="Supprimer"
            cancel-label="Annuler"
            @cancel="cancelDeletion"
            @confirm="confirmDeletion"
        />
    </div>
</template>

<script setup>
import { computed, onMounted, ref } from "vue";
import Typography from "@/components/ui/Typography.vue";
import { useRoute, useRouter } from "vue-router";
import FilterModal from "@/components/journal-entries/FilterModal.vue";
import CarteEntreeHistorique from "@/components/journal-entries/EntryHistoryCard.vue";
import ConfirmationDialog from "@/components/ui/ConfirmationDialog.vue";
import AlertMessage from "@/components/ui/AlertMessage.vue";
import BaseButton from "@/components/ui/BaseButton.vue";
import { useJournalStore } from "@/stores/journal";
import { useNotificationsStore } from "@/stores/notifications";

const route = useRoute();
const router = useRouter();
const store = useJournalStore();
const notifications = useNotificationsStore();
const showFilter = ref(false);
const showDeleteConfirm = ref(false);
const pendingDeleteId = ref(null);

onMounted(async () => {
    await store.initialize();
});

const activeFilterLabel = computed(() => {
    const map = {
        all: "Toutes les données",
        date: "Par date",
        month: "Par mois",
        nutrition: "Nutrition",
        hydration: "Hydratation",
        sleep: "Sommeil",
        stress: "Stress",
        energy: "Énergie",
        activity: "Activités",
    };
    return map[store.filter.type] ?? "Toutes les données";
});

const hasEntries = computed(() => store.entries.length > 0);
const hasFilteredEntries = computed(() => store.filteredEntries.length > 0);
const showNoResults = computed(
    () => hasEntries.value && !hasFilteredEntries.value,
);

// Decide which color to use for the notice banner (from the URL query)
const noticeTone = computed(() => {
    if (route.query.notice === "saved") return "success";
    if (route.query.notice === "canceled") return "warning";
    return "";
});

// Get the notice banner text based on the URL query
const noticeMessage = computed(() => {
    if (route.query.notice === "saved")
        return "Modifications enregistrées avec succès.";
    if (route.query.notice === "canceled") return "Modifications annulées.";
    return "";
});

// Apply the selected filter and close the filter modal
const applyFilter = (nextFilter) => {
    store.setFilter(nextFilter);
    showFilter.value = false;
};

// Reset the filter and close the filter modal
const resetFilter = () => {
    store.resetFilter();
    showFilter.value = false;
};

// Start a deletion request — store the ID and open the confirmation dialog
const requestDeletion = (id) => {
    pendingDeleteId.value = id;
    showDeleteConfirm.value = true;
};

// Cancel the deletion and close the dialog
const cancelDeletion = () => {
    pendingDeleteId.value = null;
    showDeleteConfirm.value = false;
    notifications.actionCancelled();
};

//confir
const confirmDeletion = async () => {
    if (!pendingDeleteId.value) return;

    try {
        await store.deleteEntry(pendingDeleteId.value);
        notifications.itemDeleted();
    } catch (error) {
        const message =
            error?.response?.data?.message || "Erreur lors de la suppression.";
        notifications.error(message);
    } finally {
        // Quel que soit le résultat, on réinitialise l’état de suppression
        pendingDeleteId.value = null;
        showDeleteConfirm.value = false;
    }
};
</script>
