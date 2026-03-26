<!--
  TableauDeBordMedecin.vue
  Dashboard principal du médecin : liste des patients, détail interactif,
  gestion des invitations (accepter / refuser).
-->
<template>
  <div class="mx-auto max-w-[1260px] px-4 py-6 sm:px-6 lg:px-8 lg:py-8">

    <p v-if="errorMessage" class="mb-4 rounded-[16px] border border-[#f3b8bb] bg-[#fff5f5] px-4 py-3 text-[14px] font-medium text-[#c63a3f]">
      {{ errorMessage }}
    </p>

    <header>
      <h1 class="text-[28px] font-bold leading-none tracking-[-0.03em] text-[#001b44] sm:text-[33px]">Espace Médecin</h1>
      <p class="mt-3 text-[15px] font-medium text-[#5a6881]">Suivi en temps réel de vos patients</p>
    </header>

    <NotificationsEnLigne />

    <!-- Onglets -->
    <nav class="mt-10 rounded-[18px] bg-[#eef0f3] p-[6px]">
      <div class="grid grid-cols-2 gap-3 md:flex md:items-center md:gap-0">
        <button
          v-for="tab in onglets"
          :key="tab.key"
          type="button"
          class="inline-flex h-[48px] items-center justify-center gap-3 rounded-[14px] px-6 text-[16px] font-semibold transition"
          :class="ongletActif === tab.key ? 'bg-white text-[#0a244f] shadow-[0_3px_10px_rgba(15,23,42,0.10)]' : 'text-[#4c5d7a]'"
          @click="ongletActif = tab.key"
        >
          <component :is="tab.icon" class="h-[18px] w-[18px]" />
          <span>{{ tab.label }}</span>
          <span
            class="inline-flex h-[22px] min-w-[22px] items-center justify-center rounded-full px-2 text-[13px] font-semibold"
            :class="ongletActif === tab.key ? 'bg-[#dbe9ff] text-[#3f6ed8]' : 'bg-[#dde2ea] text-[#6d7b93]'"
          >
            {{ tab.count }}
          </span>
        </button>
      </div>
    </nav>

    <!-- Contenu -->
    <template v-if="ongletActif === 'patients'">
      <ListePatientsMedecin v-if="!patientSelectionne" :patients="patients" @open-patient="ouvrirPatient" />
      <DetailPatientMedecin v-else :key="patientSelectionne.id" :patient="patientSelectionne" @back="patientSelectionne = null" />
    </template>

    <InvitationsMedecin
      v-else
      :invitations="invitations"
      :processed-invitations="processedInvitations"
      :action-invitation-id="actionInvitationId"
      @accept-invitation="accepterInvitation"
      @reject-invitation="refuserInvitation"
    />

  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useNotificationsStore } from '@/stores/notifications'
import api from '@/services/api'
import { IconeAjoutUtilisateur, IconeUtilisateurs } from '@/components/doctor/IconesMedecin.js'
import { mapperInvitation, mapperPatient, mapperDetailPatient } from '@/components/doctor/utilitairesMedecin.js'
import NotificationsEnLigne    from '@/components/ui/NotificationsEnLigne.vue'
import InvitationsMedecin      from '@/components/doctor/InvitationsMedecin.vue'
import DetailPatientMedecin    from '@/components/doctor/DetailPatientMedecin.vue'
import ListePatientsMedecin    from '@/components/doctor/ListePatientsMedecin.vue'

const notifications = useNotificationsStore()

// ─── État ─────────────────────────────────────────────────────────────────────
const ongletActif        = ref('patients')
const errorMessage       = ref('')
const patients           = ref([])
const invitations        = ref([])
const processedInvitations = ref([])
const actionInvitationId = ref(null)
const detailCache        = ref({})
const patientSelectionne = ref(null)

// ─── Onglets ──────────────────────────────────────────────────────────────────
const onglets = computed(() => [
  { key: 'patients',    label: 'Mes Patients',       count: patients.value.length,    icon: IconeUtilisateurs },
  { key: 'invitations', label: "Invitations d'ajout", count: invitations.value.length, icon: IconeAjoutUtilisateur },
])

// ─── Chargement ───────────────────────────────────────────────────────────────
async function chargerDonnees() {
  errorMessage.value = ''
  try {
    const [invRes, patRes] = await Promise.all([
      api.get('/doctor-invitations'),
      api.get('/doctor-invitations/patients'),
    ])
    const rows = invRes?.data?.data ?? []
    invitations.value        = rows.filter(r => r.status === 'pending').map(mapperInvitation)
    processedInvitations.value = rows.filter(r => r.status === 'accepted').map(mapperInvitation)
    patients.value           = (patRes?.data?.data ?? []).map(mapperPatient)
  } catch {
    setError("Impossible de charger les données médecin pour le moment.")
    invitations.value = processedInvitations.value = patients.value = []
  }
}

// ─── Navigation patient ───────────────────────────────────────────────────────
async function ouvrirPatient(patient) {
  errorMessage.value = ''
  if (detailCache.value[patient.id]) {
    patientSelectionne.value = detailCache.value[patient.id]
    return
  }
  try {
    const res    = await api.get(`/doctor-invitations/patients/${patient.id}`)
    const detail = mapperDetailPatient(res?.data?.data, patient)
    detailCache.value = { ...detailCache.value, [patient.id]: detail }
    patientSelectionne.value = detail
  } catch {
    setError("Impossible de charger le détail du patient pour le moment.")
  }
}

// ─── Invitations ──────────────────────────────────────────────────────────────
async function accepterInvitation(id) {
  await gererInvitation(id, 'accept', notifications.actionModifiee)
}

async function refuserInvitation(id) {
  await gererInvitation(id, 'reject', notifications.actionAnnulee)
}

async function gererInvitation(id, action, onSuccess) {
  actionInvitationId.value = id
  errorMessage.value = ''
  try {
    await api.post(`/doctor-invitations/${id}/${action}`)
    await chargerDonnees()
    onSuccess()
  } catch {
    setError(`Impossible d'${action === 'accept' ? 'accepter' : 'refuser'} cette invitation pour le moment.`)
  } finally {
    actionInvitationId.value = null
  }
}

// ─── Utilitaire ───────────────────────────────────────────────────────────────
function setError(msg) {
  errorMessage.value = msg
  notifications.erreur(msg)
}

// ─── Lifecycle ────────────────────────────────────────────────────────────────
onMounted(chargerDonnees)
</script>