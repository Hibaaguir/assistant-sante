<template>
  <div>
    <section class="mt-8 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
      <article
        v-for="card in cartesStatistiques"
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

    <section class="mt-8 rounded-[18px] border border-[#d4d9e1] bg-white p-4 shadow-[0_1px_4px_rgba(15,23,42,0.05)] sm:p-6">
      <div class="flex flex-col gap-4 xl:flex-row xl:items-center">
        <label class="relative block xl:flex-1">
          <IconeRecherche class="pointer-events-none absolute left-5 top-1/2 h-[20px] w-[20px] -translate-y-1/2 text-[#9aa5b7]" />
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
            :class="ongletPatientActif === tab.key ? tab.activeClass : 'bg-[#f1f2f4] text-[#243657]'"
            @click="ongletPatientActif = tab.key"
          >
            {{ tab.label }}
          </button>
        </div>
      </div>
    </section>

    <section class="mt-6 space-y-4">
      <article
        v-for="patient in patientsFiltres"
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
                  <IconeHorloge class="h-[16px] w-[16px]" />
                  {{ patient.lastSeen }}
                </span>
                <span class="text-[#9aa5b7]">•</span>
                <span>RDV : {{ patient.nextVisit }}</span>
              </div>

              <div class="mt-4 flex flex-wrap gap-3">
                <span class="inline-flex h-[34px] items-center gap-2 rounded-[10px] border border-[#f4bcc3] bg-[#fff5f6] px-3 text-[14px] font-semibold text-[#102241]">
                  <IconeCoeur class="h-[16px] w-[16px] text-[#ff2143]" />
                  {{ patient.heartRate }}
                </span>
                <span class="inline-flex h-[34px] items-center gap-2 rounded-[10px] border border-[#aac8ff] bg-[#eff6ff] px-3 text-[14px] font-semibold text-[#102241]">
                  <IconeOnde class="h-[16px] w-[16px] text-[#1454ff]" />
                  {{ patient.bloodPressure }}
                </span>
                <span
                  v-if="patient.glucose"
                  class="inline-flex h-[34px] items-center gap-2 rounded-[10px] border border-[#f4bcc3] bg-[#fff5f6] px-3 text-[14px] font-semibold text-[#102241]"
                >
                  <IconeGoutte class="h-[16px] w-[16px] text-[#ff3b30]" />
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

        </div>
      </article>
    </section>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import {
  IconeTriangleAlerte,
  IconeHorloge,
  IconeGoutte,
  IconeCoeur,
  IconeCoeurPlein,
  IconePouls,
  IconeRecherche,
  IconeContourUtilisateur,
  IconeOnde
} from '@/components/doctor/IconesMedecin.js'

const props = defineProps({
  patients: {
    type: Array,
    default: () => []
  }
})

defineEmits(['open-patient'])

const search = ref('')
const ongletPatientActif = ref('all')

const patientTabs = [
  { key: 'all', label: 'Tous', activeClass: 'bg-[#1454ff] text-white shadow-[0_6px_16px_rgba(20,84,255,0.25)]' },
  { key: 'critical', label: 'Critiques', activeClass: 'bg-[#f80000] text-white shadow-[0_6px_16px_rgba(248,0,0,0.18)]' },
  { key: 'watch', label: 'Surveillance', activeClass: 'bg-[#eb7b00] text-white shadow-[0_6px_16px_rgba(235,123,0,0.2)]' },
  { key: 'stable', label: 'Stables', activeClass: 'bg-[#08b33b] text-white shadow-[0_6px_16px_rgba(8,179,59,0.2)]' }
]

const cartesStatistiques = computed(() => {
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
      icon: IconeContourUtilisateur,
      iconWrapClass: 'bg-[#dbe9ff]',
      iconClass: 'text-[#1454ff]'
    },
    {
      key: 'critical',
      label: 'Critiques',
      value: counts.critical,
      valueClass: 'text-[#ff1f2d]',
      borderClass: 'border-[#f3b8bb]',
      icon: IconeTriangleAlerte,
      iconWrapClass: 'bg-[#fee3e5]',
      iconClass: 'text-[#ff1f2d]'
    },
    {
      key: 'watch',
      label: 'Surveillance',
      value: counts.watch,
      valueClass: 'text-[#ef7a00]',
      borderClass: 'border-[#f0cb58]',
      icon: IconePouls,
      iconWrapClass: 'bg-[#fff0c8]',
      iconClass: 'text-[#ef7a00]'
    },
    {
      key: 'stable',
      label: 'Stables',
      value: counts.stable,
      valueClass: 'text-[#07b33f]',
      borderClass: 'border-[#b5e6c6]',
      icon: IconeCoeurPlein,
      iconWrapClass: 'bg-[#d2f3de]',
      iconClass: 'text-[#07b33f]'
    }
  ]
})

const patientsFiltres = computed(() => {
  const term = search.value.trim().toLowerCase()

  return props.patients.filter((patient) => {
    const matchesTab = ongletPatientActif.value === 'all' || patient.status === ongletPatientActif.value
    const matchesSearch =
      !term ||
      patient.name.toLowerCase().includes(term) ||
      patient.tags.some((tag) => tag.toLowerCase().includes(term))

    return matchesTab && matchesSearch
  })
})
</script>
