<!--
  TableauDeBordMedecin.vue
  Dashboard principal du médecin : liste des patients, détail interactif,
  gestion des invitations (accepter / refuser).
-->
<template>
    <div class="w-full px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
        <p
            v-if="errorMessage"
            class="mb-4 rounded-[16px] border border-[#f3b8bb] bg-[#fff5f5] px-4 py-3 text-[14px] font-medium text-[#c63a3f]"
        >
            {{ errorMessage }}
        </p>

        <header>
            <h1
                class="text-[38px] font-bold leading-tight tracking-[-0.03em] bg-gradient-to-r from-purple-600 to-purple-700 bg-clip-text text-transparent sm:text-[44px]"
            >
                Espace Médecin
            </h1>
            <p class="mt-4 text-[16px] font-medium text-slate-700">
                Suivi en temps réel de vos patients
            </p>
        </header>

        <NotificationsOnline />

        <!-- Onglets -->
        <nav class="mt-10 rounded-[18px] bg-[#eef0f3] p-[6px]">
            <div
                class="grid grid-cols-2 gap-3 md:flex md:items-center md:gap-0"
            >
                <button
                    v-for="tab in tabs"
                    :key="tab.key"
                    type="button"
                    class="inline-flex h-[48px] items-center justify-center gap-3 rounded-[14px] px-6 text-[16px] font-semibold transition"
                    :class="
                        activeTab === tab.key
                            ? 'bg-white text-[#0a244f] shadow-[0_3px_10px_rgba(15,23,42,0.10)]'
                            : 'text-[#4c5d7a]'
                    "
                    @click="activeTab = tab.key"
                >
                    <component :is="tab.icon" class="h-[18px] w-[18px]" />
                    <span>{{ tab.label }}</span>
                    <span
                        class="inline-flex h-[22px] min-w-[22px] items-center justify-center rounded-full px-2 text-[13px] font-semibold"
                        :class="
                            activeTab === tab.key
                                ? 'bg-[#dbe9ff] text-[#3f6ed8]'
                                : 'bg-[#dde2ea] text-[#6d7b93]'
                        "
                    >
                        {{ tab.count }}
                    </span>
                </button>
            </div>
        </nav>

        <!-- Contenu -->
        <template v-if="activeTab === 'patients'">
            <DoctorPatientList
                v-if="!selectedPatient"
                :patients="patients"
                @open-patient="openPatient"
            />
            <DoctorPatientDetail
                v-else
                :key="selectedPatient.id"
                :patient="selectedPatient"
                @back="selectedPatient = null"
            />
        </template>

        <DoctorInvitations
            v-else
            :invitations="invitations"
            :processed-invitations="processedInvitations"
            :action-invitation-id="actionInvitationId"
            @accept-invitation="acceptInvitation"
            @reject-invitation="rejectInvitation"
        />
    </div>
</template>

<script setup>
import { computed, onMounted, ref } from "vue";
import { useNotificationsStore } from "@/stores/notifications";
import api from "@/services/api";
import { IconAddUser, IconUsers } from "@/components/doctors/DoctorIcons.js";
import {
    mapInvitation,
    mapPatient,
    mapPatientDetail,
} from "@/components/doctors/doctorUtilities.js";
import NotificationsOnline from "@/components/ui/NotificationsOnline.vue";
import DoctorInvitations from "@/components/doctors/DoctorInvitations.vue";
import DoctorPatientDetail from "@/components/doctors/DoctorPatientDetail.vue";
import DoctorPatientList from "@/components/doctors/DoctorPatientList.vue";

const notifications = useNotificationsStore();

// ─── State ─────────────────────────────────────────────────────────────────────
const activeTab = ref("patients");
const errorMessage = ref("");
const patients = ref([]);
const invitations = ref([]);
const processedInvitations = ref([]);
const actionInvitationId = ref(null);
const selectedPatient = ref(null);

// ─── Tabs ──────────────────────────────────────────────────────────────────────
const tabs = computed(() => [
    {
        key: "patients",
        label: "Mes patients",
        count: patients.value.length,
        icon: IconUsers,
    },
    {
        key: "invitations",
        label: "Ajouter des invitations",
        count: invitations.value.length,
        icon: IconAddUser,
    },
]);

// ─── Loading ───────────────────────────────────────────────────────────────────
async function loadData() {
    errorMessage.value = "";
    try {
        const [invRes, patRes] = await Promise.all([
            api.get("/doctor-invitations"),
            api.get("/doctor-invitations/patients"),
        ]);
        const rows = invRes?.data?.data ?? [];
        invitations.value = rows
            .filter((r) => r.status === "pending")
            .map(mapInvitation);
        processedInvitations.value = rows
            .filter((r) => r.status === "accepted")
            .map(mapInvitation);
        patients.value = (patRes?.data?.data ?? []).map(mapPatient);
    } catch {
        setError(
            "Impossible de charger les données du médecin pour le moment.",
        );
        invitations.value = processedInvitations.value = patients.value = [];
    }
}

// ─── Patient Navigation ───────────────────────────────────────────────────────
async function openPatient(patient) {
    errorMessage.value = "";
    try {
        const res = await api.get(`/doctor-invitations/patients/${patient.id}`);
        const detail = mapPatientDetail(res?.data?.data, patient);
        selectedPatient.value = detail;
    } catch {
        setError(
            "Impossible de charger les détails du patient pour le moment.",
        );
    }
}

// ─── Invitations ──────────────────────────────────────────────────────────────
async function acceptInvitation(id) {
    await manageInvitation(id, "accept", notifications.itemUpdated);
}

async function rejectInvitation(id) {
    await manageInvitation(id, "reject", notifications.actionCancelled);
}

async function manageInvitation(id, action, onSuccess) {
    actionInvitationId.value = id;
    errorMessage.value = "";
    try {
        await api.post(`/doctor-invitations/${id}/${action}`);
        await loadData();
        onSuccess();
    } catch {
        setError(
            `Impossible d'${action === "accept" ? "accepter" : "rejeter"} cette invitation pour le moment.`,
        );
    } finally {
        actionInvitationId.value = null;
    }
}

// ─── Utility ───────────────────────────────────────────────────────────────
function setError(msg) {
    errorMessage.value = msg;
}

// ─── Lifecycle ────────────────────────────────────────────────────────────────
onMounted(loadData);
</script>
