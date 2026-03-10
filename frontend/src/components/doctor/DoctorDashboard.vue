<template>
  <div class="mx-auto max-w-[1260px] px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
    <div v-if="errorMessage" class="mb-4 rounded-[16px] border border-[#f3b8bb] bg-[#fff5f5] px-4 py-3 text-[14px] font-medium text-[#c63a3f]">
      {{ errorMessage }}
    </div>

    <header class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
      <div>
        <h1 class="text-[28px] font-bold leading-none tracking-[-0.03em] text-[#001b44] sm:text-[33px]">Espace Medecin</h1>
        <p class="mt-3 text-[15px] font-medium text-[#5a6881]">Suivi en temps reel de vos patients</p>
      </div>

      <div class="flex items-center gap-3 self-start">
        <button
          type="button"
          class="relative flex h-[40px] w-[50px] items-center justify-center rounded-[14px] border border-[#d7dce3] bg-[#f6f6f7] text-[#4b5568]"
          @click="showAlerts = !showAlerts"
        >
          <BellIcon class="h-[18px] w-[18px]" />
          <span class="absolute right-[-6px] top-[-7px] flex h-[22px] min-w-[22px] items-center justify-center rounded-full bg-[#ef0808] px-1 text-[12px] font-bold leading-none text-white">{{ totalAlerts }}</span>
        </button>
        <button
          type="button"
          class="inline-flex h-[40px] items-center gap-2 rounded-[14px] border border-[#b9d4ff] bg-[#edf4ff] px-5 text-[16px] font-medium text-[#1454ff]"
          @click="logout"
        >
          <LogoutIcon class="h-[17px] w-[17px]" />
          <span>Deconnexion</span>
        </button>
      </div>
    </header>
    <InlineNotifications />

    <section class="mt-10 rounded-[18px] bg-[#eef0f3] p-[6px]">
      <div class="grid grid-cols-2 gap-3 md:flex md:items-center md:gap-0">
        <button
          v-for="tab in headerTabs"
          :key="tab.key"
          type="button"
          class="inline-flex h-[48px] items-center justify-center gap-3 rounded-[14px] px-6 text-[16px] font-semibold transition"
          :class="activeHeaderTab === tab.key ? 'bg-white text-[#0a244f] shadow-[0_3px_10px_rgba(15,23,42,0.10)]' : 'text-[#4c5d7a]'"
          @click="activeHeaderTab = tab.key"
        >
          <component :is="tab.icon" class="h-[18px] w-[18px]" />
          <span>{{ tab.label }}</span>
          <span
            class="inline-flex h-[22px] min-w-[22px] items-center justify-center rounded-full px-2 text-[13px] font-semibold"
            :class="activeHeaderTab === tab.key ? 'bg-[#dbe9ff] text-[#3f6ed8]' : 'bg-[#dde2ea] text-[#6d7b93]'"
          >
            {{ tab.count }}
          </span>
        </button>
      </div>
    </section>

    <template v-if="activeHeaderTab === 'patients'">
      <DoctorPatientList
        v-if="!selectedPatient"
        :patients="patients"
        v-model:show-alerts="showAlerts"
        @open-patient="openPatient"
      />
      <DoctorPatientDetail
        v-else
        :key="selectedPatient.id"
        :patient="selectedPatient"
        @back="backToPatientList"
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
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useNotificationsStore } from '@/stores/notifications'
import api from '@/services/api'
import { BellIcon, LogoutIcon, UserPlusIcon, UsersIcon } from '@/components/doctor/DoctorIcons.js'
import { mapInvitation, mapPatient, mapPatientDetailResponse } from '@/components/doctor/doctorUtils.js'
import InlineNotifications from '@/components/ui/InlineNotifications.vue'
import DoctorInvitations from '@/components/doctor/DoctorInvitations.vue'
import DoctorPatientDetail from '@/components/doctor/DoctorPatientDetail.vue'
import DoctorPatientList from '@/components/doctor/DoctorPatientList.vue'

const router = useRouter()
const authStore = useAuthStore()
const notifications = useNotificationsStore()

// ---------------------------------------------------------------------------
// Reactive state
// ---------------------------------------------------------------------------

const activeHeaderTab = ref('patients')
const showAlerts = ref(true)
const errorMessage = ref('')
const patients = ref([])
const invitations = ref([])
const processedInvitations = ref([])
const actionInvitationId = ref(null)
const detailCache = ref({})
const selectedPatient = ref(null)

// ---------------------------------------------------------------------------
// Computed
// ---------------------------------------------------------------------------

const headerTabs = computed(() => [
  { key: 'patients', label: 'Mes Patients', count: patients.value.length, icon: UsersIcon },
  { key: 'invitations', label: "Invitations d'ajout", count: invitations.value.length, icon: UserPlusIcon }
])

const totalAlerts = computed(() =>
  patients.value.reduce((sum, patient) => sum + (Array.isArray(patient.alerts) ? patient.alerts.length : 0), 0)
)

// ---------------------------------------------------------------------------
// Data loading
// ---------------------------------------------------------------------------

async function loadDoctorData() {
  errorMessage.value = ''
  try {
    const [invitationsRes, patientsRes] = await Promise.all([
      api.get('/doctor-invitations'),
      api.get('/doctor-invitations/patients')
    ])
    const invitationRows = Array.isArray(invitationsRes?.data?.data) ? invitationsRes.data.data : []
    invitations.value = invitationRows.filter((item) => item.status === 'pending').map(mapInvitation)
    processedInvitations.value = invitationRows.filter((item) => item.status === 'accepted').map(mapInvitation)
    patients.value = (Array.isArray(patientsRes?.data?.data) ? patientsRes.data.data : []).map(mapPatient)
  } catch (_) {
    errorMessage.value = "Impossible de charger les donnees medecin pour le moment."
    notifications.error(errorMessage.value)
    invitations.value = []
    processedInvitations.value = []
    patients.value = []
  }
}

// ---------------------------------------------------------------------------
// Patient detail navigation
// ---------------------------------------------------------------------------

async function openPatient(patient) {
  errorMessage.value = ''
  if (detailCache.value[patient.id]) {
    selectedPatient.value = detailCache.value[patient.id]
    return
  }
  try {
    const res = await api.get(`/doctor-invitations/patients/${patient.id}`)
    const detail = mapPatientDetailResponse(res?.data?.data, patient)
    detailCache.value = { ...detailCache.value, [patient.id]: detail }
    selectedPatient.value = detail
  } catch (_) {
    errorMessage.value = "Impossible de charger le detail du patient pour le moment."
    notifications.error(errorMessage.value)
  }
}

function backToPatientList() {
  selectedPatient.value = null
}

// ---------------------------------------------------------------------------
// Invitation actions
// ---------------------------------------------------------------------------

async function acceptInvitation(invitationId) {
  actionInvitationId.value = invitationId
  errorMessage.value = ''
  try {
    await api.post(`/doctor-invitations/${invitationId}/accept`)
    await loadDoctorData()
    notifications.actionUpdated()
  } catch (_) {
    errorMessage.value = "Impossible d'accepter cette invitation pour le moment."
    notifications.error(errorMessage.value)
  } finally {
    actionInvitationId.value = null
  }
}

async function rejectInvitation(invitationId) {
  actionInvitationId.value = invitationId
  errorMessage.value = ''
  try {
    await api.post(`/doctor-invitations/${invitationId}/reject`)
    await loadDoctorData()
    notifications.actionCanceled()
  } catch (_) {
    errorMessage.value = "Impossible de refuser cette invitation pour le moment."
    notifications.error(errorMessage.value)
  } finally {
    actionInvitationId.value = null
  }
}

// ---------------------------------------------------------------------------
// Lifecycle & auth
// ---------------------------------------------------------------------------

onMounted(async () => {
  await loadDoctorData()
})

async function logout() {
  await authStore.logout()
  router.push({ name: 'doctor-login' })
}
</script>
