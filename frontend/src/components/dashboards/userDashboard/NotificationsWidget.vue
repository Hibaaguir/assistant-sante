<!--
  NotificationsWidget.vue
  Widget affichant les notifications de traitements persistant.
  Responsable: Affichage, chargement, refresh et marquer comme lue.
-->
<template>
  <section class="mt-5 rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div v-if="chargementNotifications" class="px-4 py-3 text-sm text-slate-600">
      Chargement des notifications...
    </div>

    <div v-else-if="erreurNotification" class="rounded-2xl border border-red-100 bg-red-50 px-4 py-3 text-sm text-red-700">
      {{ erreurNotification }}
    </div>

    <div v-else-if="notificationsVisibles.length > 0">
      <div class="mb-4 flex items-center justify-between gap-3 border-b border-slate-100 px-4 py-4">
        <div>
          <h2 class="text-[22px] font-semibold leading-none text-slate-900">Notifications traitements</h2>
          <p class="mt-1 text-sm text-slate-600">Rappels journaliers et oublis detectes</p>
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

      <ul class="space-y-2 p-4">
        <li
          v-for="notification in notificationsVisibles"
          :key="notification.id"
          class="rounded-lg border border-blue-200 bg-blue-50 px-3 py-2"
        >
          <div class="flex items-start justify-between gap-2">
            <div class="flex-1">
              <p class="text-sm font-semibold text-slate-900">{{ notification.data?.title || 'Notification' }}</p>
              <p class="mt-0.5 text-xs text-slate-700">{{ notification.data?.message || '' }}</p>
              <p class="mt-1 text-xs text-slate-500">{{ formaterDateHeureNotification(notification.created_at) }}</p>
            </div>

            <button
              type="button"
              class="mt-1 rounded-md border border-blue-200 bg-white px-2 py-1 text-xs font-semibold text-blue-700 transition hover:bg-blue-100"
              @click="marquerNotificationLue(notification.id)"
            >
              ✓
            </button>
          </div>
        </li>
      </ul>
    </div>

    <div v-else class="px-4 py-3 text-sm text-slate-500 text-center">
      Aucune notification pour le moment
    </div>
  </section>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/services/api'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const authStore = useAuthStore()
const listeNotifications = ref([])
const chargementNotifications = ref(false)
const erreurNotification = ref('')
let minuterieRafraichissementNotifications = null

const nombreNonLues = computed(() => listeNotifications.value.filter((notification) => !notification.read_at).length)
const notificationsVisibles = computed(() => listeNotifications.value)

async function gererErreurAuthentification(error) {
  if (error?.response?.status !== 401) {
    throw error
  }
  await authStore.deconnexion({ appelerApi: false })
  await router.replace({ name: 'connexion' })
}

function formaterDateHeureNotification(value) {
  if (!value) return ''
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return ''
  return date.toLocaleString('fr-FR', {
    day: '2-digit',
    month: 'short',
    hour: '2-digit',
    minute: '2-digit'
  })
}

async function chargerListeNotifications({ silencieux = false } = {}) {
  if (!silencieux) chargementNotifications.value = true
  erreurNotification.value = ''

  try {
    const res = await api.get('/notifications')
    listeNotifications.value = Array.isArray(res?.data?.data) ? res.data.data : []
  } catch (error) {
    if (error?.response?.status === 401) {
      await gererErreurAuthentification(error)
      return
    }
    erreurNotification.value = 'Impossible de charger les notifications pour le moment.'
  } finally {
    chargementNotifications.value = false
  }
}

async function marquerNotificationLue(idNotification) {
  try {
    await api.post(`/notifications/${idNotification}/read`)
    listeNotifications.value = listeNotifications.value.map((notification) => (
      notification.id === idNotification
        ? { ...notification, read_at: notification.read_at || new Date().toISOString() }
        : notification
    ))
  } catch (_) {
    erreurNotification.value = 'Impossible de marquer cette notification comme lue.'
  }
}

async function marquerToutLu() {
  try {
    await api.post('/notifications/read-all')
    const maintenant = new Date().toISOString()
    listeNotifications.value = listeNotifications.value.map((notification) => ({
      ...notification,
      read_at: notification.read_at || maintenant
    }))
  } catch (_) {
    erreurNotification.value = 'Impossible de marquer toutes les notifications comme lues.'
  }
}

onMounted(async () => {
  await chargerListeNotifications()
  minuterieRafraichissementNotifications = window.setInterval(() => {
    chargerListeNotifications({ silencieux: true })
  }, 60000)
})

onUnmounted(() => {
  if (minuterieRafraichissementNotifications !== null) {
    window.clearInterval(minuterieRafraichissementNotifications)
    minuterieRafraichissementNotifications = null
  }
})
</script>
