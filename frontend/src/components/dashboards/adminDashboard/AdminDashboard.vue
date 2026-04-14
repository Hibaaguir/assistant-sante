<template>
    <div class="w-full px-4 py-6 sm:px-6 lg:px-8 lg:py-8 bg-white">
        <!-- Header -->
        <header
            class="rounded-2xl border border-slate-200 bg-white px-5 py-5 shadow-sm"
        >
            <div class="flex items-start gap-4">
                <div
                    class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-700 text-white"
                >
                    <svg
                        viewBox="0 0 24 24"
                        class="h-6 w-6"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path
                            d="M12 2l7 4v6c0 5-3.4 8.7-7 10-3.6-1.3-7-5-7-10V6l7-4z"
                        />
                    </svg>
                </div>
                <div>
                    <h1 class="text-4xl font-bold text-blue-600">
                        Espace Administrateur
                    </h1>
                    <p class="mt-2 text-base font-medium text-slate-700">
                        Gestion des comptes utilisateurs
                    </p>
                </div>
            </div>
        </header>

        <!-- Status messages — uses AlertMessage to avoid repeating the same colored-box code -->
        <AlertMessage :message="errorMessage" type="error" />
        <AlertMessage :message="successMessage" type="success" />
        <AlertMessage :message="deletionMessage" type="warning" />

        <!-- Stats -->
        <section
            class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4"
        >
            <StatisticCard
                v-for="card in statsCards"
                :key="card.label"
                v-bind="card"
            />
        </section>

        <!-- Search & Filters -->
        <section
            class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
        >
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center">
                <label class="relative block w-full">
                    <svg
                        viewBox="0 0 24 24"
                        class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <circle cx="11" cy="11" r="7" />
                        <path d="m20 20-3-3" />
                    </svg>
                    <input
                        v-model="searchText"
                        type="text"
                        placeholder="Rechercher par nom ou email..."
                        class="h-12 w-full rounded-2xl border border-slate-300 bg-white pl-12 pr-4 text-base outline-none focus:border-slate-400"
                    />
                </label>

                <button
                    type="button"
                    class="inline-flex h-12 items-center justify-center gap-2 rounded-2xl bg-blue-300 px-6 text-xl font-semibold text-white"
                    @click="showFilters = !showFilters"
                >
                    <svg
                        viewBox="0 0 24 24"
                        class="h-5 w-5"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path d="M3 4h18l-7 8v6l-4 2v-8z" />
                    </svg>
                    Filtres
                </button>
            </div>

            <div
                v-if="showFilters"
                class="mt-4 grid grid-cols-1 gap-4 border-t border-slate-200 pt-4 md:grid-cols-2"
            >
                <label class="space-y-1">
                    <span class="text-sm font-semibold text-slate-700"
                        >Type d'utilisateur</span
                    >
                    <select
                        v-model="filterType"
                        class="h-11 w-full rounded-xl border border-slate-300 px-3 text-base outline-none focus:border-slate-400"
                    >
                        <option value="Tous">Tous</option>
                        <option value="Patient">Usager</option>
                        <option value="Doctor">Médecin</option>
                    </select>
                </label>

                <label class="space-y-1">
                    <span class="text-sm font-semibold text-slate-700"
                        >Statut</span
                    >
                    <select
                        v-model="filterStatus"
                        class="h-11 w-full rounded-xl border border-slate-300 px-3 text-base outline-none focus:border-slate-400"
                    >
                        <option value="Tous">Tous</option>
                        <option value="Active">Actif</option>
                        <option value="Inactive">Inactif</option>
                    </select>
                </label>
            </div>
        </section>

        <!-- User table -->
        <section
            class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
        >
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr
                            class="text-left text-sm font-semibold text-slate-700"
                        >
                            <th class="px-6 py-4">Utilisateur</th>
                            <th class="px-6 py-4">Type</th>
                            <th class="px-6 py-4">Statut</th>
                            <th class="px-6 py-4">Inscription</th>
                            <th class="px-6 py-4">Dernière activité</th>
                            <th class="px-6 py-4 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        <tr v-if="loadingList">
                            <td
                                colspan="6"
                                class="px-6 py-8 text-center text-base text-slate-500"
                            >
                                Chargement des utilisateurs...
                            </td>
                        </tr>

                        <tr v-else-if="filteredUsers.length === 0">
                            <td
                                colspan="6"
                                class="px-6 py-8 text-center text-base text-slate-500"
                            >
                                Aucun utilisateur ne correspond à votre
                                recherche.
                            </td>
                        </tr>

                        <tr v-for="u in filteredUsers" v-else :key="u.id">
                            <!-- Name & email -->
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="inline-flex h-10 w-10 items-center justify-center rounded-full text-sm font-bold text-white"
                                        :class="
                                            isDoctor(u)
                                                ? 'bg-indigo-500'
                                                : 'bg-emerald-500'
                                        "
                                    >
                                        {{ initials(u.name) }}
                                    </div>
                                    <div>
                                        <p
                                            class="text-base font-semibold text-slate-900"
                                        >
                                            {{ u.name }}
                                        </p>
                                        <p class="text-sm text-slate-500">
                                            {{ u.email }}
                                        </p>
                                    </div>
                                </div>
                            </td>

                            <!-- Type -->
                            <td class="px-6 py-4">
                                <span
                                    class="inline-flex items-center rounded-full px-3 py-1 text-sm font-semibold"
                                    :class="
                                        isDoctor(u)
                                            ? 'bg-indigo-100 text-indigo-700'
                                            : 'bg-emerald-100 text-emerald-700'
                                    "
                                >
                                    {{ isDoctor(u) ? "Médecin" : "Usager" }}
                                </span>
                                <p
                                    v-if="u.specialty"
                                    class="mt-1 text-sm text-slate-500"
                                >
                                    {{ u.specialty }}
                                </p>
                            </td>

                            <!-- Status -->
                            <td class="px-6 py-4">
                                <span
                                    class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-sm font-semibold"
                                    :class="
                                        isActive(u)
                                            ? 'bg-blue-100 text-blue-700'
                                            : 'bg-slate-200 text-slate-700'
                                    "
                                >
                                    <svg
                                        viewBox="0 0 24 24"
                                        class="h-4 w-4"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                    >
                                        <circle cx="12" cy="12" r="9" />
                                        <path
                                            v-if="isActive(u)"
                                            d="m8 12 2.5 2.5L16 9"
                                        />
                                        <path v-else d="m9 9 6 6M15 9l-6 6" />
                                    </svg>
                                    {{ isActive(u) ? "Actif" : "Inactif" }}
                                </span>
                            </td>

                            <td class="px-6 py-4 text-sm text-slate-700">
                                {{ u.created_at }}
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-700">
                                {{ u.last_activity }}
                            </td>

                            <!-- Actions -->
                            <td class="px-6 py-4">
                                <div
                                    class="flex items-center justify-center gap-3"
                                >
                                    <button
                                        type="button"
                                        class="text-orange-500 hover:text-orange-700"
                                        :title="
                                            isActive(u)
                                                ? 'Désactiver le compte'
                                                : 'Activer le compte'
                                        "
                                        @click="toggleStatus(u)"
                                    >
                                        <svg
                                            viewBox="0 0 24 24"
                                            class="h-5 w-5"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                        >
                                            <circle cx="12" cy="8" r="4" />
                                            <path d="M6 20a6 6 0 0 1 12 0" />
                                        </svg>
                                    </button>

                                    <button
                                        type="button"
                                        class="text-red-500 hover:text-red-700"
                                        @click="openDeleteModal(u)"
                                    >
                                        <svg
                                            viewBox="0 0 24 24"
                                            class="h-5 w-5"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                        >
                                            <path
                                                d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6"
                                            />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <DeleteUserModal
            :open="deleteModalOpen"
            @cancel="closeDeleteModal"
            @confirm="confirmDeletion"
        />
    </div>
</template>

<script setup>
import { computed, onMounted, ref } from "vue";
import DeleteUserModal from "@/components/dashboards/adminDashboard/DeleteUserModal.vue";
import StatisticCard from "@/components/dashboards/adminDashboard/StatisticCard.vue";
import AlertMessage from "@/components/ui/AlertMessage.vue";
import {
    listAdminUsers,
    deleteAdminUser,
    toggleUserStatus,
} from "@/services/admin";

// ─── State ────────────────────────────────────────────────────────────────────
const users         = ref([]);
const loadingList   = ref(false);
const errorMessage  = ref("");
const successMessage= ref("");
const deletionMessage = ref("");
const searchText    = ref("");
const showFilters   = ref(true);
const filterType    = ref("Tous");
const filterStatus  = ref("Tous");
const deleteModalOpen  = ref(false);
const userIdToDelete   = ref(null);

// ─── Helpers ──────────────────────────────────────────────────────────────────

// Check if a user is a doctor
const isDoctor = (u) => u.type === "Doctor";

// Check if a user account is active
const isActive = (u) => u.status === "Active";

// Get the first two initials from a full name (e.g. "Marie Dupont" → "MD")
function initials(fullName) {
    return String(fullName || "")
        .split(" ")
        .filter(Boolean)
        .map((word) => word[0]?.toUpperCase() ?? "")
        .slice(0, 2)
        .join("");
}

// Extract a readable error message from an API error
function errorMessageFrom(err) {
    return err?.response?.data?.message ?? null;
}

// ─── Computed ─────────────────────────────────────────────────────────────────

// Count users by category for the stats cards at the top
const statistics = computed(() => ({
    total:    users.value.length,
    patients: users.value.filter((u) => u.type === "Patient").length,
    doctors:  users.value.filter((u) => isDoctor(u)).length,
    active:   users.value.filter((u) => isActive(u)).length,
}));

const statsCards = computed(() => [
    {
        label: "Total utilisateurs",
        value: statistics.value.total,
        color: "blue",
        icon: {
            viewBox: "0 0 24 24",
            path: '<circle cx="12" cy="8" r="4"/><path d="M6 20a6 6 0 0 1 12 0"/>',
        },
    },
    {
        label: "Usagers",
        value: statistics.value.patients,
        color: "emerald",
        icon: {
            viewBox: "0 0 24 24",
            path: '<path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>',
        },
    },
    {
        label: "Médecins",
        value: statistics.value.doctors,
        color: "indigo",
        icon: {
            viewBox: "0 0 24 24",
            path: '<path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/>',
        },
    },
    {
        label: "Actifs",
        value: statistics.value.active,
        color: "orange",
        icon: {
            viewBox: "0 0 24 24",
            path: '<circle cx="12" cy="8" r="4"/><path d="M6 20a6 6 0 0 1 12 0"/>',
        },
    },
]);

// Filter users based on the search text and dropdown filters
const filteredUsers = computed(() => {
    const search = searchText.value.trim().toLowerCase();

    return users.value.filter((u) => {
        // Search box: match by name or email (ignore case)
        const matchesSearch =
            search === "" ||
            u.name.toLowerCase().includes(search) ||
            u.email.toLowerCase().includes(search);

        // Type filter: "Tous" means show everyone
        const matchesType =
            filterType.value === "Tous" || u.type === filterType.value;

        // Status filter: "Tous" means show everyone
        const matchesStatus =
            filterStatus.value === "Tous" || u.status === filterStatus.value;

        return matchesSearch && matchesType && matchesStatus;
    });
});

// ─── Actions ──────────────────────────────────────────────────────────────────

// Load the full user list from the API.
// Returns true on success, false on failure (does NOT clear the list on failure).
async function loadUsers() {
    loadingList.value  = true;
    errorMessage.value = "";
    try {
        users.value = await listAdminUsers();
        return true;
    } catch (err) {
        // Do NOT clear users.value here — keep the current list visible so
        // users don't appear to vanish because of a background refresh failure.
        errorMessage.value =
            errorMessageFrom(err) ??
            "Une erreur est survenue lors du chargement des utilisateurs.";
        return false;
    } finally {
        loadingList.value = false;
    }
}

// Toggle a user between Active and Inactive
async function toggleStatus(u) {
    const newStatus = isActive(u) ? "Inactive" : "Active";
    const oldStatus = u.status; // keep the old status in case we need to undo
    const userName  = u.name;

    errorMessage.value  = "";
    successMessage.value = "";

    // Immediately update the UI before the API responds (optimistic update)
    u.status = newStatus;

    try {
        await toggleUserStatus(u.id, newStatus);

        // Show a success message, then hide it after 4 seconds
        successMessage.value = newStatus === "Active"
            ? `${userName} a été activé avec succès.`
            : `${userName} a été désactivé avec succès.`;

        setTimeout(() => { successMessage.value = ""; }, 4000);

        // Refresh the list silently to sync with the server.
        // If the refresh fails, revert the optimistic status change.
        const refreshed = await loadUsers();
        if (!refreshed) {
            u.status = oldStatus;
        }
    } catch (err) {
        // API call failed — revert the status back to what it was
        u.status = oldStatus;
        errorMessage.value =
            errorMessageFrom(err) ??
            "La mise à jour du statut a échoué. Veuillez réessayer.";
    }
}

// Open the delete confirmation modal for a user
function openDeleteModal(u) {
    if (!u?.id) {
        errorMessage.value = "Erreur : impossible d'identifier cet utilisateur.";
        return;
    }
    userIdToDelete.value  = u.id;
    deleteModalOpen.value = true;
}

// Close the delete modal and reset related state (called on cancel)
function closeDeleteModal() {
    deleteModalOpen.value = false;
    userIdToDelete.value  = null;
    errorMessage.value    = "";
    successMessage.value  = "Suppression annulée.";
    setTimeout(() => { successMessage.value = ""; }, 3000);
}

// Reset modal state without showing the "cancelled" message (used during actual deletion)
function resetDeleteModal() {
    deleteModalOpen.value = false;
    userIdToDelete.value  = null;
}

// Delete the selected user after confirmation
async function confirmDeletion() {
    errorMessage.value   = "";
    successMessage.value = "";
    deletionMessage.value = "";

    if (!userIdToDelete.value) {
        errorMessage.value = "Erreur : ID utilisateur invalide.";
        resetDeleteModal();
        return;
    }

    const idToDelete  = userIdToDelete.value;
    const index       = users.value.findIndex((u) => u.id === idToDelete);
    const deletedUser = users.value[index] ?? null;
    const userName    = deletedUser?.name || "Utilisateur";

    // Close the modal immediately — do NOT call closeDeleteModal() here as it
    // shows a misleading "Suppression annulée." message during an actual deletion.
    resetDeleteModal();

    // Remove from the UI immediately (optimistic delete)
    if (index !== -1) users.value.splice(index, 1);

    try {
        await deleteAdminUser(idToDelete);

        // Show success message, then hide it after 4 seconds
        deletionMessage.value = `${userName} a été supprimé avec succès.`;
        setTimeout(() => { deletionMessage.value = ""; }, 4000);

        // Refresh the list silently; restore the user if the refresh itself fails
        loadUsers().catch(() => {
            if (deletedUser && index !== -1) users.value.splice(index, 0, deletedUser);
        });
    } catch (err) {
        // API call failed — put the user back in the list
        if (deletedUser && index !== -1) users.value.splice(index, 0, deletedUser);
        errorMessage.value =
            errorMessageFrom(err) ??
            "La suppression a échoué. Veuillez réessayer.";
    }
}

onMounted(loadUsers);
</script>
