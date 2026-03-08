<template>
  <div>
    <section class="mt-8 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
      <article
        v-for="card in statCards"
        :key="card.key"
        class="rounded-[18px] border bg-white px-5 py-6 shadow-[0_1px_3px_rgba(15,23,42,0.05)]"
        :class="card.borderClass"
      >
        <div class="flex items-center justify-between gap-4">
          <div>
            <p class="text-[15px] font-medium text-[#455572]">{{ card.label }}</p>
            <p class="mt-4 text-[18px] font-semibold leading-none" :class="card.valueClass">{{ card.value }}</p>
          </div>
          <div class="flex h-[48px] w-[48px] items-center justify-center rounded-[15px]" :class="card.iconWrapClass">
            <component :is="card.icon" class="h-[24px] w-[24px]" :class="card.iconClass" />
          </div>
        </div>
      </article>
    </section>

    <section v-if="showAlerts" class="mt-8 rounded-[18px] border border-[#d4d9e1] bg-white p-6 shadow-[0_1px_4px_rgba(15,23,42,0.05)]">
      <div class="flex items-center justify-between gap-3">
        <h2 class="text-[18px] font-bold text-[#06214d]">Alertes actives ({{ alerts.length }})</h2>
        <button type="button" class="text-[14px] font-medium text-[#30466e]" @click="$emit('update:showAlerts', false)">Masquer</button>
      </div>

      <div class="mt-6 space-y-4">
        <article
          v-for="alert in alerts"
          :key="alert.id"
          class="flex flex-col gap-4 rounded-[17px] border px-4 py-4 md:flex-row md:items-start md:justify-between md:px-5"
          :class="alert.rowClass"
        >
          <div class="flex items-start gap-4">
            <div class="mt-[2px] flex h-[40px] w-[40px] shrink-0 items-center justify-center rounded-full" :class="alert.iconWrapClass">
              <AlertTriangleIcon class="h-[20px] w-[20px]" :class="alert.iconClass" />
            </div>
            <div>
              <p class="text-[16px] font-semibold text-[#031a46]">{{ alert.patient }}</p>
              <p class="mt-1 text-[14px] text-[#31405e]">{{ alert.message }}</p>
            </div>
          </div>
          <p class="shrink-0 pt-1 text-[14px] font-medium text-[#5f6d85] md:text-right">{{ alert.time }}</p>
        </article>
      </div>
    </section>

    <section class="mt-8 rounded-[18px] border border-[#d4d9e1] bg-white p-4 shadow-[0_1px_4px_rgba(15,23,42,0.05)] sm:p-6">
      <div class="flex flex-col gap-4 xl:flex-row xl:items-center">
        <label class="relative block xl:flex-1">
          <SearchIcon class="pointer-events-none absolute left-5 top-1/2 h-[20px] w-[20px] -translate-y-1/2 text-[#9aa5b7]" />
          <input
            v-model="search"
            type="text"
            placeholder="Rechercher un patient..."
            class="h-[50px] w-full rounded-[16px] border border-[#d1d7e1] bg-[#fdfdfd] pl-13 pr-4 text-[15px] text-[#1a2640] outline-none placeholder:text-[#96a2b4]"
          />
        </label>

        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 xl:w-auto">
          <button
            v-for="tab in patientTabs"
            :key="tab.key"
            type="button"
            class="h-[50px] rounded-[15px] px-5 text-[15px] font-semibold transition"
            :class="activePatientTab === tab.key ? tab.activeClass : 'bg-[#f1f2f4] text-[#243657]'"
            @click="activePatientTab = tab.key"
          >
            {{ tab.label }}
          </button>
        </div>
      </div>
    </section>

    <section class="mt-6 space-y-4">
      <article
        v-for="patient in filteredPatients"
        :key="patient.id"
        class="cursor-pointer rounded-[18px] border border-[#d4d9e1] bg-white px-6 py-6 shadow-[0_1px_4px_rgba(15,23,42,0.05)] transition hover:border-[#c7d5f5] hover:shadow-[0_8px_18px_rgba(15,23,42,0.08)]"
        @click="$emit('open-patient', patient)"
      >
        <div class="flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">
          <div class="flex items-start gap-4">
            <div class="flex h-[58px] w-[58px] shrink-0 items-center justify-center rounded-full text-[18px] font-bold text-white" :style="{ backgroundColor: patient.avatarColor }">
              {{ patient.initials }}
            </div>

            <div>
              <div class="flex items-center gap-3">
                <h3 class="text-[20px] font-bold text-[#031a46]">{{ patient.name }}</h3>
                <span class="h-[12px] w-[12px] rounded-full" :style="{ backgroundColor: patient.dotColor }" />
              </div>

              <div class="mt-4 flex flex-wrap items-center gap-x-4 gap-y-2 text-[14px] text-[#3f4d66]">
                <span>{{ patient.age }} ans</span>
                <span class="text-[#9aa5b7]">•</span>
                <span class="inline-flex items-center gap-1.5">
                  <ClockIcon class="h-[16px] w-[16px]" />
                  {{ patient.lastSeen }}
                </span>
                <span class="text-[#9aa5b7]">•</span>
                <span>RDV : {{ patient.nextVisit }}</span>
              </div>

              <div class="mt-4 flex flex-wrap gap-3">
                <span class="inline-flex h-[34px] items-center gap-2 rounded-[10px] border border-[#f4bcc3] bg-[#fff5f6] px-3 text-[14px] font-semibold text-[#102241]">
                  <HeartIcon class="h-[16px] w-[16px] text-[#ff2143]" />
                  {{ patient.heartRate }}
                </span>
                <span class="inline-flex h-[34px] items-center gap-2 rounded-[10px] border border-[#aac8ff] bg-[#eff6ff] px-3 text-[14px] font-semibold text-[#102241]">
                  <WaveIcon class="h-[16px] w-[16px] text-[#1454ff]" />
                  {{ patient.bloodPressure }}
                </span>
                <span
                  v-if="patient.glucose"
                  class="inline-flex h-[34px] items-center gap-2 rounded-[10px] border border-[#f4bcc3] bg-[#fff5f6] px-3 text-[14px] font-semibold text-[#102241]"
                >
                  <DropIcon class="h-[16px] w-[16px] text-[#ff3b30]" />
                  {{ patient.glucose }}
                </span>
              </div>

              <div class="mt-4 flex flex-wrap gap-2">
                <span
                  v-for="tag in patient.tags"
                  :key="tag"
                  class="inline-flex h-[26px] items-center rounded-full bg-[#f0f1f4] px-3 text-[14px] font-medium text-[#495972]"
                >
                  {{ tag }}
                </span>
              </div>
            </div>
          </div>

          <div v-if="patient.alertCount" class="flex shrink-0 flex-col items-end gap-2 xl:min-w-[160px]">
            <div class="inline-flex h-[40px] items-center gap-2 rounded-[12px] border px-4 text-[14px] font-semibold" :class="patient.alertBadgeClass">
              <AlertTriangleIcon class="h-[16px] w-[16px]" />
              {{ patient.alertCount }} alerte<span v-if="patient.alertCount > 1">s</span>
            </div>
            <p v-if="patient.alertLabel" class="text-[14px] font-medium" :class="patient.alertLabelClass">{{ patient.alertLabel }}</p>
          </div>
        </div>
      </article>
    </section>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import {
  AlertTriangleIcon,
  ClockIcon,
  DropIcon,
  HeartIcon,
  HeartSolidIcon,
  PulseIcon,
  SearchIcon,
  UserOutlineIcon,
  WaveIcon
} from '@/components/doctor/DoctorIcons.js'

const props = defineProps({
  patients: {
    type: Array,
    default: () => []
  },
  showAlerts: {
    type: Boolean,
    default: true
  }
})

defineEmits(['open-patient', 'update:showAlerts'])

const search = ref('')
const activePatientTab = ref('all')

const patientTabs = [
  { key: 'all', label: 'Tous', activeClass: 'bg-[#1454ff] text-white shadow-[0_6px_16px_rgba(20,84,255,0.25)]' },
  { key: 'critical', label: 'Critiques', activeClass: 'bg-[#f80000] text-white shadow-[0_6px_16px_rgba(248,0,0,0.18)]' },
  { key: 'watch', label: 'Surveillance', activeClass: 'bg-[#eb7b00] text-white shadow-[0_6px_16px_rgba(235,123,0,0.2)]' },
  { key: 'stable', label: 'Stables', activeClass: 'bg-[#08b33b] text-white shadow-[0_6px_16px_rgba(8,179,59,0.2)]' }
]

const alerts = computed(() =>
  props.patients
    .flatMap((patient) => (Array.isArray(patient.alerts) ? patient.alerts : []).map((alert) => ({
      ...alert,
      patient: patient.name
    })))
    .sort((a, b) => new Date(b.isoTime || 0).getTime() - new Date(a.isoTime || 0).getTime())
)

const statCards = computed(() => {
  const counts = {
    total: props.patients.length,
    critical: props.patients.filter((item) => item.status === 'critical').length,
    watch: props.patients.filter((item) => item.status === 'watch').length,
    stable: props.patients.filter((item) => item.status === 'stable').length
  }

  return [
    {
      key: 'total',
      label: 'Total patients',
      value: counts.total,
      valueClass: 'text-[#031a46]',
      borderClass: 'border-[#d7dce3]',
      icon: UserOutlineIcon,
      iconWrapClass: 'bg-[#dbe9ff]',
      iconClass: 'text-[#1454ff]'
    },
    {
      key: 'critical',
      label: 'Critiques',
      value: counts.critical,
      valueClass: 'text-[#ff1f2d]',
      borderClass: 'border-[#f3b8bb]',
      icon: AlertTriangleIcon,
      iconWrapClass: 'bg-[#fee3e5]',
      iconClass: 'text-[#ff1f2d]'
    },
    {
      key: 'watch',
      label: 'Surveillance',
      value: counts.watch,
      valueClass: 'text-[#ef7a00]',
      borderClass: 'border-[#f0cb58]',
      icon: PulseIcon,
      iconWrapClass: 'bg-[#fff0c8]',
      iconClass: 'text-[#ef7a00]'
    },
    {
      key: 'stable',
      label: 'Stables',
      value: counts.stable,
      valueClass: 'text-[#07b33f]',
      borderClass: 'border-[#b5e6c6]',
      icon: HeartSolidIcon,
      iconWrapClass: 'bg-[#d2f3de]',
      iconClass: 'text-[#07b33f]'
    }
  ]
})

const filteredPatients = computed(() => {
  const term = search.value.trim().toLowerCase()

  return props.patients.filter((patient) => {
    const matchesTab = activePatientTab.value === 'all' || patient.status === activePatientTab.value
    const matchesSearch =
      !term ||
      patient.name.toLowerCase().includes(term) ||
      patient.tags.some((tag) => tag.toLowerCase().includes(term))

    return matchesTab && matchesSearch
  })
})
</script>
